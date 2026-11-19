<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchFilterRequest;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(BranchFilterRequest $request): View
    {
        $bookings = Booking::with(['room.branch', 'applicant', 'organizationalUnit'])
            ->whereIn('room_id', Room::query()->visibleTo($request->user())->select('rooms.id'))
            ->when($request->filled('branch_id'), fn ($query) => $query->whereHas('room', fn ($rooms) => $rooms->where('branch_id', $request->integer('branch_id'))))
            ->latest('id')->paginate(20)->withQueryString();

        return view('admin.bookings.index', compact('bookings') + ['branches' => $request->user()->isSuperAdmin() ? Branch::orderBy('name')->get() : collect()]);
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($request->user()->canAccessBranch($booking->room->branch_id), 404);
        $booking->load(['room', 'applicant', 'organizationalUnit']);

        return view('admin.bookings.show', compact('booking'));
    }
}
