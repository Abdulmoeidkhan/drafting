<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DraftPick;
use App\Models\DraftRound;
use App\Models\Participant;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class TeamController extends Controller
{
    /**
     * Team dashboard for team-role users.
     */
    public function teamDashboard()
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized. Team access required.');
        }

        $team = Team::query()
            ->with(['participants.category'])
            ->whereRaw('LOWER(email) = ?', [strtolower((string) $user->email)])
            ->first();

        if (!$user->isAdmin() && !$user->hasRole('team')) {
            if (!$team) {
                abort(403, 'Unauthorized. Team access required.');
            }

            // Self-heal missing role assignment for linked team accounts.
            try {
                $user->syncRoles(['team']);
            } catch (Throwable $e) {
                // Continue with access based on trusted email-to-team link.
            }
        }

        if (!$team) {
            abort(404, 'No team is linked to this account email.');
        }

        return view('team.dashboard', [
            'team' => $team,
            'participants' => $team->participants,
        ]);
    }

    /**
     * Teams page with draft module tabs.
     */
    public function index(Request $request)
    {
        $teams = Team::withCount('participants')->orderBy('name')->get();

        $activeRound = DraftRound::query()
            ->with(['category', 'startTeam', 'currentTeam'])
            ->where('status', 'active')
            ->latest('id')
            ->first();

        $activeRoundEligibleCategoryIds = $activeRound ? $this->getEligibleCategoryIds($activeRound) : [];

        $activeRoundTeamPickCounts = $activeRound
            ? DraftPick::query()
                ->selectRaw('team_id, COUNT(*) as picks_count')
                ->where('draft_round_id', $activeRound->id)
                ->groupBy('team_id')
                ->pluck('picks_count', 'team_id')
                ->map(fn ($value) => (int) $value)
                ->toArray()
            : [];

        $activeRoundRemainingSeconds = 0;

        if ($activeRound?->current_turn_started_at) {
            $elapsed = Carbon::now()->diffInSeconds($activeRound->current_turn_started_at);
            $activeRoundRemainingSeconds = max(0, (int) $activeRound->turn_time_seconds - $elapsed);
        }

        $categories = Category::query()
            ->withCount('participants')
            ->with([
                'participants' => function ($query) {
                    $query->where('status', 'approved')
                        ->whereNull('team_id')
                        ->orderBy('first_name')
                        ->orderBy('last_name');
                },
            ])
            ->withCount([
                'participants as draftable_participants_count' => function ($query) {
                    $query->where('status', 'approved')->whereNull('team_id');
                },
            ])
            ->orderBy('name')
            ->get();

        $uncategorizedDraftableParticipants = Participant::query()
            ->where('status', 'approved')
            ->whereNull('team_id')
            ->whereNull('category_id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $draftedParticipants = Participant::query()
            ->with(['team', 'category'])
            ->whereNotNull('team_id')
            ->orderByDesc('drafted_at')
            ->get();

        return view('admin.teams', [
            'teams' => $teams,
            'categories' => $categories,
            'uncategorizedDraftableParticipants' => $uncategorizedDraftableParticipants,
            'draftedParticipants' => $draftedParticipants,
            'activeRound' => $activeRound,
            'activeRoundEligibleCategoryIds' => $activeRoundEligibleCategoryIds,
            'activeRoundTeamPickCounts' => $activeRoundTeamPickCounts,
            'activeRoundRemainingSeconds' => $activeRoundRemainingSeconds,
            'activeTab' => $request->query('tab', 'teams'),
            'activeDraftCategory' => (string) $request->query('category', ($categories->first()?->id ? (string) $categories->first()->id : 'uncategorized')),
        ]);
    }

    /**
     * Start a new turn-based draft round.
     */
    public function startRound(Request $request)
    {
        if (DraftRound::query()->where('status', 'active')->exists()) {
            return back()->with('error', 'Finish or close the active draft round before starting a new one.');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'higher_category_ids' => 'nullable|array',
            'higher_category_ids.*' => 'integer|exists:categories,id',
            'starting_team_id' => 'required|exists:teams,id',
            'picks_per_team' => 'required|integer|min:1|max:10',
            'turn_time_seconds' => 'required|integer|in:120,150,180',
        ]);

        $teams = Team::query()->orderBy('name')->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (count($teams) === 0) {
            return back()->with('error', 'Create at least one team before starting a draft round.');
        }

        $startingTeamId = (int) $validated['starting_team_id'];

        if (!in_array($startingTeamId, $teams, true)) {
            return back()->with('error', 'Selected starting team is invalid.');
        }

        $pickOrder = $this->buildPickOrder($teams, $startingTeamId);

        $higherCategoryIds = collect($validated['higher_category_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id !== (int) $validated['category_id'])
            ->unique()
            ->values()
            ->all();

        DraftRound::create([
            'category_id' => (int) $validated['category_id'],
            'start_team_id' => $startingTeamId,
            'current_team_id' => $pickOrder[0],
            'pick_order' => $pickOrder,
            'higher_category_ids' => $higherCategoryIds,
            'picks_per_team' => (int) $validated['picks_per_team'],
            'turn_time_seconds' => (int) $validated['turn_time_seconds'],
            'current_pick_number' => 1,
            'total_picks_planned' => count($pickOrder) * (int) $validated['picks_per_team'],
            'current_turn_started_at' => now(),
            'status' => 'active',
        ]);

        return redirect()->route('admin.teams', [
            'tab' => 'draft',
            'category' => (string) $validated['category_id'],
        ])->with('success', 'Draft round started successfully.');
    }

    /**
     * Pick a player for the current team in an active round.
     */
    public function pickInRound(Request $request, DraftRound $round, Participant $participant)
    {
        $result = DB::transaction(function () use ($round, $participant) {
            $lockedRound = DraftRound::query()->lockForUpdate()->findOrFail($round->id);

            if ($lockedRound->status !== 'active') {
                return ['error' => 'This draft round is already closed.'];
            }

            if ($this->isTurnExpired($lockedRound)) {
                $this->advanceTurnOrComplete($lockedRound);
                return ['error' => 'Turn time expired. Turn has been auto-skipped to the next team.'];
            }

            $lockedParticipant = Participant::query()->lockForUpdate()->findOrFail($participant->id);

            if ($lockedParticipant->status !== 'approved') {
                return ['error' => 'Only approved participants can be drafted.'];
            }

            if ($lockedParticipant->team_id !== null) {
                return ['error' => 'This player is already drafted by another team.'];
            }

            $eligibleCategoryIds = $this->getEligibleCategoryIds($lockedRound);

            if ($lockedParticipant->category_id === null || !in_array((int) $lockedParticipant->category_id, $eligibleCategoryIds, true)) {
                return ['error' => 'This player is not eligible for the active draft round category rules.'];
            }

            $currentTeam = Team::query()->lockForUpdate()->findOrFail($lockedRound->current_team_id);

            if ($currentTeam->participants()->count() >= $currentTeam->max_players) {
                return ['error' => 'Current team roster is full. Update max players or close this round.'];
            }

            $currentTeamRoundPicks = DraftPick::query()
                ->where('draft_round_id', $lockedRound->id)
                ->where('team_id', $currentTeam->id)
                ->count();

            if ($currentTeamRoundPicks >= $lockedRound->picks_per_team) {
                return ['error' => 'Current team has already completed picks for this round.'];
            }

            DraftPick::create([
                'draft_round_id' => $lockedRound->id,
                'team_id' => $currentTeam->id,
                'participant_id' => $lockedParticipant->id,
                'pick_number' => (int) $lockedRound->current_pick_number,
                'picked_at' => now(),
            ]);

            $lockedParticipant->update([
                'team_id' => $currentTeam->id,
                'drafted_at' => now(),
            ]);

            $nextTeamId = $this->findNextTeamId($lockedRound, $currentTeam->id);

            if ($nextTeamId === null) {
                $lockedRound->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'current_turn_started_at' => null,
                ]);

                return ['success' => 'Pick completed. Draft round is now finished.'];
            }

            $lockedRound->update([
                'current_team_id' => $nextTeamId,
                'current_pick_number' => (int) $lockedRound->current_pick_number + 1,
                'current_turn_started_at' => now(),
            ]);

            return ['success' => 'Pick completed successfully.'];
        });

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', $result['success']);
    }

    /**
     * Check and auto-skip turn when timer expires.
     */
    public function tickRound(DraftRound $round)
    {
        $result = DB::transaction(function () use ($round) {
            $lockedRound = DraftRound::query()->lockForUpdate()->findOrFail($round->id);

            if ($lockedRound->status !== 'active') {
                return [
                    'advanced' => false,
                    'round_closed' => true,
                    'message' => 'Round is already closed.',
                ];
            }

            if (!$this->isTurnExpired($lockedRound)) {
                return [
                    'advanced' => false,
                    'round_closed' => false,
                    'message' => 'Turn is still active.',
                ];
            }

            $advanced = $this->advanceTurnOrComplete($lockedRound);

            return [
                'advanced' => $advanced,
                'round_closed' => !$advanced,
                'message' => $advanced
                    ? 'Turn expired and moved to next team.'
                    : 'Turn expired and round completed.',
            ];
        });

        return response()->json($result);
    }

    /**
     * Close an active draft round manually.
     */
    public function closeRound(DraftRound $round)
    {
        if ($round->status !== 'active') {
            return back()->with('error', 'This draft round is already closed.');
        }

        $round->update([
            'status' => 'completed',
            'completed_at' => now(),
            'current_turn_started_at' => null,
        ]);

        return back()->with('success', 'Draft round closed successfully.');
    }

    /**
     * Create a team (players are not assigned here).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
            'short_code' => 'nullable|string|max:10|unique:teams,short_code',
            'captain_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:teams,email',
            'max_players' => 'required|integer|min:1|max:30',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $existingUser = User::query()->where('email', $validated['email'])->first();

        if ($existingUser && $existingUser->isAdmin()) {
            return back()->withInput()->with('error', 'This email belongs to an admin account. Use a different team email.');
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('team-logos', 'public');
        }

        Team::create($validated);

        $accountResult = $this->ensureTeamUserAccount($validated['name'], $validated['email']);

        if (!empty($accountResult['blocked'])) {
            return back()->with('error', $accountResult['message']);
        }

        if ($accountResult['created']) {
            return back()
                ->with('success', 'Team created successfully. Team login user has been created.')
                ->with('account_credentials', [
                    'label' => 'Team Account',
                    'email' => $accountResult['email'],
                    'password' => $accountResult['password'],
                ]);
        }

        return back()->with('success', 'Team created successfully. Existing user was linked with team access.');
    }

    /**
     * Update team details (without direct player selection).
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id,
            'short_code' => 'nullable|string|max:10|unique:teams,short_code,' . $team->id,
            'captain_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:teams,email,' . $team->id,
            'max_players' => 'required|integer|min:1|max:30',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $existingUser = User::query()->where('email', $validated['email'])->first();

        if ($existingUser && $existingUser->isAdmin()) {
            return back()->withInput()->with('error', 'This email belongs to an admin account. Use a different team email.');
        }

        if ($team->participants()->count() > (int) $validated['max_players']) {
            return back()->with('error', 'Cannot reduce max players below the number of already drafted players.');
        }

        if ($request->hasFile('logo')) {
            if ($team->logo && Storage::disk('public')->exists($team->logo)) {
                Storage::disk('public')->delete($team->logo);
            }

            $validated['logo'] = $request->file('logo')->store('team-logos', 'public');
        }

        $team->update($validated);

        $accountResult = $this->ensureTeamUserAccount($validated['name'], $validated['email']);

        if (!empty($accountResult['blocked'])) {
            return back()->with('error', $accountResult['message']);
        }

        if ($accountResult['created']) {
            return back()
                ->with('success', 'Team updated successfully. Team login user has been created.')
                ->with('account_credentials', [
                    'label' => 'Team Account',
                    'email' => $accountResult['email'],
                    'password' => $accountResult['password'],
                ]);
        }

        return back()->with('success', 'Team updated successfully.');
    }

    /**
     * Delete team and release drafted players back to pool.
     */
    public function destroy(Team $team)
    {
        Participant::where('team_id', $team->id)->update([
            'team_id' => null,
            'drafted_at' => null,
        ]);

        if ($team->logo && Storage::disk('public')->exists($team->logo)) {
            Storage::disk('public')->delete($team->logo);
        }

        $team->delete();

        return back()->with('success', 'Team deleted and drafted players returned to the draft pool.');
    }

    /**
     * Draft an approved player to a team.
     */
    public function draftPlayer(Request $request, Team $team, Participant $participant)
    {
        if (DraftRound::query()->where('status', 'active')->exists()) {
            return back()->with('error', 'Use active round picking while a draft round is running.');
        }

        if ($participant->status !== 'approved') {
            return back()->with('error', 'Only approved participants can be drafted.');
        }

        if ($participant->team_id !== null) {
            return back()->with('error', 'This player is already drafted by another team.');
        }

        if ($team->participants()->count() >= $team->max_players) {
            return back()->with('error', 'Team roster is full. Increase max players or draft to another team.');
        }

        $participant->update([
            'team_id' => $team->id,
            'drafted_at' => now(),
        ]);

        return back()->with('success', 'Player drafted successfully.');
    }

    /**
     * Release a drafted player back to draft pool.
     */
    public function releasePlayer(Team $team, Participant $participant)
    {
        if ((int) $participant->team_id !== (int) $team->id) {
            return back()->with('error', 'Player does not belong to this team.');
        }

        $participant->update([
            'team_id' => null,
            'drafted_at' => null,
        ]);

        return back()->with('success', 'Player released back to draft pool.');
    }

    /**
     * Rotate team ids so selected team starts the pick order.
     */
    private function buildPickOrder(array $teamIds, int $startingTeamId): array
    {
        $startIndex = array_search($startingTeamId, $teamIds, true);

        if ($startIndex === false) {
            return $teamIds;
        }

        return array_values(array_merge(
            array_slice($teamIds, $startIndex),
            array_slice($teamIds, 0, $startIndex)
        ));
    }

    /**
     * Eligible categories are the selected category plus optional higher categories.
     */
    private function getEligibleCategoryIds(DraftRound $round): array
    {
        return collect([$round->category_id])
            ->merge($round->higher_category_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Find next team in order that still has remaining picks in this round.
     */
    private function findNextTeamId(DraftRound $round, int $currentTeamId): ?int
    {
        $order = collect($round->pick_order)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        if (empty($order)) {
            return null;
        }

        $currentIndex = array_search($currentTeamId, $order, true);
        $startIndex = $currentIndex === false ? 0 : $currentIndex;

        for ($step = 1; $step <= count($order); $step++) {
            $candidate = $order[($startIndex + $step) % count($order)];

            $candidatePicks = DraftPick::query()
                ->where('draft_round_id', $round->id)
                ->where('team_id', $candidate)
                ->count();

            if ($candidatePicks < $round->picks_per_team) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Ensure a team-role user exists for a team email.
     */
    private function ensureTeamUserAccount(string $teamName, string $email): array
    {
        $user = User::query()->where('email', $email)->first();

        if ($user && $user->isAdmin()) {
            return [
                'blocked' => true,
                'message' => 'This email belongs to an admin account. Use a different team email.',
                'created' => false,
                'email' => $email,
                'password' => null,
            ];
        }

        if ($user) {
            $user->update([
                'name' => $teamName,
                'created_by_admin' => true,
            ]);
            $user->syncRoles(['team']);

            return [
                'created' => false,
                'email' => $user->email,
                'password' => null,
            ];
        }

        $plainPassword = 'Team@' . Str::upper(Str::random(8)) . random_int(10, 99);

        $user = User::create([
            'name' => $teamName,
            'email' => $email,
            'password' => $plainPassword,
            'is_admin' => false,
            'created_by_admin' => true,
        ]);

        $user->syncRoles(['team']);

        return [
            'created' => true,
            'email' => $user->email,
            'password' => $plainPassword,
        ];
    }

    /**
     * Whether active turn timer is already expired.
     */
    private function isTurnExpired(DraftRound $round): bool
    {
        if (!$round->current_turn_started_at) {
            return false;
        }

        return Carbon::now()->diffInSeconds($round->current_turn_started_at) >= (int) $round->turn_time_seconds;
    }

    /**
     * Move turn to next eligible team or complete round if none remain.
     */
    private function advanceTurnOrComplete(DraftRound $round): bool
    {
        $nextTeamId = $this->findNextTeamId($round, (int) $round->current_team_id);

        if ($nextTeamId === null) {
            $round->update([
                'status' => 'completed',
                'completed_at' => now(),
                'current_turn_started_at' => null,
            ]);

            return false;
        }

        $round->update([
            'current_team_id' => $nextTeamId,
            'current_pick_number' => (int) $round->current_pick_number + 1,
            'current_turn_started_at' => now(),
        ]);

        return true;
    }
}
