<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_participants' => Participant::count(),
            'pending' => Participant::where('status', 'pending')->count(),
            'approved' => Participant::where('status', 'approved')->count(),
            'rejected' => Participant::where('status', 'rejected')->count(),
            'total_users' => User::count(),
        ];

        return view('admin.dashboard', $stats);
    }

    /**
     * View all participants
     */
    public function participants(Request $request)
    {
        $query = Participant::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $participants = $query->with('creator')->paginate(15);

        return view('admin.participants', ['participants' => $participants]);
    }

    /**
     * View participant details
     */
    public function viewParticipant($id)
    {
        $participant = Participant::with('creator')->findOrFail($id);
        return view('admin.participant-detail', ['participant' => $participant]);
    }

    /**
     * Approve participant
     */
    public function approveParticipant($id)
    {
        $participant = Participant::findOrFail($id);
        $participant->update(['status' => 'approved']);

        return back()->with('success', 'Participant approved successfully');
    }

    /**
     * Reject participant
     */
    public function rejectParticipant($id)
    {
        $participant = Participant::findOrFail($id);
        $participant->update(['status' => 'rejected']);

        return back()->with('success', 'Participant rejected successfully');
    }

    /**
     * Delete participant
     */
    public function deleteParticipant($id)
    {
        $participant = Participant::findOrFail($id);
        $participant->delete();

        return back()->with('success', 'Participant deleted successfully');
    }
    
    /**
     * Manage users
     */
    public function users(Request $request)
    {
        $users = User::paginate(15);
        return view('admin.users', ['users' => $users]);
    }

    /**
     * Create user
     */
    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'is_admin' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'is_admin' => $validated['is_admin'] ?? false,
            'created_by_admin' => true,
        ]);

        return back()->with('success', 'User created successfully');
    }

    /**
     * Edit user
     */
    public function editUser($id, Request $request)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === Auth::id() && !$request->boolean('is_admin')) {
            return back()->with('error', 'You cannot remove your own admin status');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'is_admin' => 'boolean',
        ]);

        $user->update($validated);

        return back()->with('success', 'User updated successfully');
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        if ($id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'User deleted successfully');
    }

    /**
     * Export participants (CSV)
     */
    public function exportParticipants()
    {
        $participants = Participant::all();
        
        $filename = 'participants_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($participants) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Full Name', 'Email', 'Mobile', 'City', 'Nationality',
                'Kit Size', 'Status', 'Created At'
            ]);
            
            // Data
            foreach ($participants as $p) {
                fputcsv($file, [
                    $p->id,
                    $p->full_name,
                    $p->email,
                    $p->mobile,
                    $p->city,
                    $p->nationality,
                    $p->kit_size,
                    $p->status,
                    $p->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show edit form for participant
     */
    public function editParticipant($id)
    {
        $participant = Participant::findOrFail($id);
        $categories = Category::all();
        return view('admin.participant-edit', ['participant' => $participant, 'categories' => $categories]);
    }

    /**
     * Update participant details
     */
    public function updateParticipant(Request $request, $id)
    {
        $participant = Participant::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'nick_name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string',
            'email' => 'required|email|unique:participants,email,' . $id,
            'dob' => 'required|date',
            'nationality' => 'required|string|max:255',
            'kit_size' => 'required|in:small,medium,large,xl,xxl',
            'shirt_number' => 'required|string|max:10',
            'performance' => 'nullable|string',
            'airline' => 'nullable|string|max:255',
            'arrival_date' => 'nullable|date',
            'arrival_time' => 'nullable|date_format:H:i',
            'hotel_name' => 'nullable|string|max:255',
            'checkin' => 'nullable|date',
            'checkout' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $participant->update($validated);

        return redirect()->route('admin.participant.view', $participant->id)->with('success', 'Participant updated successfully');
    }

    /**
     * View all categories
     */
    public function categories()
    {
        $categories = Category::withCount('participants')->paginate(15);
        return view('admin.categories', ['categories' => $categories]);
    }

    /**
     * Create category
     */
    public function createCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:categories|max:255',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);
        return back()->with('success', 'Category created successfully');
    }

    /**
     * Delete category
     */
    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return back()->with('success', 'Category deleted successfully');
    }

    /**
     * Download participant file
     */
    public function downloadFile($participantId, $fileType)
    {
        $participant = Participant::findOrFail($participantId);
        
        $fileMap = [
            'passport' => 'passport_picture',
            'id' => 'id_picture',
            'hotel' => 'hotel_reservation',
            'flight' => 'flight_reservation',
        ];

        if (!isset($fileMap[$fileType])) {
            abort(404, 'File type not found');
        }

        $field = $fileMap[$fileType];
        $filePath = $participant->$field;

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }

        // Generate meaningful filename
        $fileNameMap = [
            'passport_picture' => 'Passport_Photo_' . $participant->id . '_' . $participant->full_name,
            'id_picture' => 'ID_Picture_' . $participant->id . '_' . $participant->full_name,
            'hotel_reservation' => 'Hotel_Reservation_' . $participant->id . '_' . $participant->full_name,
            'flight_reservation' => 'Flight_Reservation_' . $participant->id . '_' . $participant->full_name,
        ];

        $fileName = $fileNameMap[$field] ?? basename($filePath);
        $originalFileName = basename($filePath);
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($filePath, $fileName . '.' . $fileExtension);
    }

    /**
     * Preview participant file
     */
    public function previewFile($participantId, $fileType)
    {
        $participant = Participant::findOrFail($participantId);
        
        $fileMap = [
            'passport' => 'passport_picture',
            'id' => 'id_picture',
            'hotel' => 'hotel_reservation',
            'flight' => 'flight_reservation',
        ];

        if (!isset($fileMap[$fileType])) {
            abort(404, 'File type not found');
        }

        $field = $fileMap[$fileType];
        $filePath = $participant->$field;

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->response($filePath);
    }

}
