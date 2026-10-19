<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\DraftPick;
use App\Models\DraftRound;
use App\Models\LeagueRoundConfig;
use App\Models\Participant;
use App\Models\Team;
use App\Services\DraftService;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class DraftBoard extends Component
{
    public string $leagueType;

    public string $selectedCategoryKey = '';

    public function mount(string $leagueType): void
    {
        $this->leagueType = $leagueType;
    }

    public function selectCategory(string $categoryKey): void
    {
        $this->selectedCategoryKey = $categoryKey;
    }

    #[On('echo:draft.league.{leagueType},.draft.turn.changed')]
    public function turnChanged(): void
    {
        // Component re-renders automatically on broadcast.
    }

    public function tickTurn(int $roundId): void
    {
        $round = DraftRound::find($roundId);

        if (! $round) {
            return;
        }

        app(DraftService::class)->tick($round);
    }

    public function pollActiveRound(): void
    {
        $activeRound = DraftRound::query()
            ->where('status', 'active')
            ->where('league_type', $this->leagueType)
            ->latest('id')
            ->first();

        if (! $activeRound) {
            return;
        }

        app(DraftService::class)->tick($activeRound);
    }

    public function render(): View
    {
        $activeRound = DraftRound::query()
            ->with(['category', 'currentTeam'])
            ->where('status', 'active')
            ->where('league_type', $this->leagueType)
            ->latest('id')
            ->first();

        $teams = Team::query()
            ->where('league_type', $this->leagueType)
            ->withCount(['participants' => fn ($q) => $q->where('league_type', $this->leagueType)])
            ->orderBy('name')
            ->get();

        $teamsById = $teams->keyBy('id');

        $activeRoundEligibleCategoryIds = [];
        $activeRoundTeamPickCounts = [];
        $activeRoundProcessedTeamIds = [];
        $activeRoundRemainingSeconds = 0;

        if ($activeRound) {
            $draftService = app(DraftService::class);
            $activeRoundEligibleCategoryIds = $draftService->getEligibleCategoryIds($activeRound);

            $activeRoundTeamPickCounts = DraftPick::query()
                ->selectRaw('team_id, COUNT(*) as picks_count')
                ->where('draft_round_id', $activeRound->id)
                ->groupBy('team_id')
                ->pluck('picks_count', 'team_id')
                ->map(fn ($v) => (int) $v)
                ->toArray();

            $pickOrder = collect($activeRound->pick_order)
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->values()
                ->all();

            $consumedSlots = max(0, min((int) $activeRound->current_pick_number - 1, count($pickOrder)));
            $activeRoundProcessedTeamIds = array_values(array_unique(array_slice($pickOrder, 0, $consumedSlots)));

            if ($activeRound->current_turn_started_at) {
                $elapsed = abs(Carbon::now()->diffInSeconds($activeRound->current_turn_started_at));
                $activeRoundRemainingSeconds = max(0, (int) $activeRound->turn_time_seconds - $elapsed);
            }
        }

        $categories = Category::query()
            ->withCount([
                'participants' => fn ($q) => $q->where('league_type', $this->leagueType),
                'participants as draftable_participants_count' => fn ($q) => $q
                    ->where('status', 'approved')
                    ->where('league_type', $this->leagueType)
                    ->whereNull('team_id'),
            ])
            ->with(['participants' => fn ($q) => $q
                ->where('status', 'approved')
                ->where('league_type', $this->leagueType)
                ->whereNull('team_id')
                ->orderBy('first_name')
                ->orderBy('last_name'),
            ])
            ->orderBy('name')
            ->get();

        $uncategorizedDraftableParticipants = Participant::query()
            ->where('status', 'approved')
            ->where('league_type', $this->leagueType)
            ->whereNull('team_id')
            ->whereNull('category_id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $leagueRoundConfigs = LeagueRoundConfig::query()
            ->where('league_type', $this->leagueType)
            ->orderBy('round_number')
            ->get();

        $completedRoundsCount = DraftRound::query()
            ->where('status', 'completed')
            ->where('league_type', $this->leagueType)
            ->count();

        $nextLeagueRoundNumber = $completedRoundsCount + ($activeRound ? 1 : 0) + 1;
        $nextLeagueRoundConfig = $leagueRoundConfigs->firstWhere('round_number', $nextLeagueRoundNumber);
        $totalLeagueRounds = $leagueRoundConfigs->count();

        $availableCategoryKeys = $categories
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->push('uncategorized')
            ->all();

        if (! in_array($this->selectedCategoryKey, $availableCategoryKeys, true)) {
            $this->selectedCategoryKey = (string) ($categories->first()?->id ?? 'uncategorized');
        }

        return view('livewire.draft-board', [
            'activeRound' => $activeRound,
            'teams' => $teams,
            'teamsById' => $teamsById,
            'activeRoundEligibleCategoryIds' => $activeRoundEligibleCategoryIds,
            'activeRoundTeamPickCounts' => $activeRoundTeamPickCounts,
            'activeRoundProcessedTeamIds' => $activeRoundProcessedTeamIds,
            'activeRoundRemainingSeconds' => $activeRoundRemainingSeconds,
            'categories' => $categories,
            'uncategorizedDraftableParticipants' => $uncategorizedDraftableParticipants,
            'leagueRoundConfigs' => $leagueRoundConfigs,
            'completedRoundsCount' => $completedRoundsCount,
            'nextLeagueRoundNumber' => $nextLeagueRoundNumber,
            'nextLeagueRoundConfig' => $nextLeagueRoundConfig,
            'totalLeagueRounds' => $totalLeagueRounds,
            'selectedCategoryKey' => $this->selectedCategoryKey,
        ]);
    }
}
