<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchFilterRequest;
use App\Http\Requests\DeleteRoomRequest;
use App\Http\Requests\SaveRoomRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Branch;
use App\Models\Facility;
use App\Models\Floor;
use App\Models\OrganizationalUnit;
use App\Models\Room;
use App\Models\User;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(BranchFilterRequest $request): View
    {
        return view('admin.rooms.index', [
            'branches' => $this->branches($request),
            'rooms' => Room::query()->visibleTo($request->user())->with(['branch', 'floor', 'facilities'])->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))->orderBy('name')->orderBy('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.rooms.create', $this->formOptions($request));
    }

    public function store(SaveRoomRequest $request, RoomService $service): RedirectResponse
    {
        $room = $service->save($request->validated());

        return redirect()->route('admin.rooms.show', $room)->with('status', 'Ruangan berhasil ditambahkan.');
    }

    public function show(Request $request, Room $room): View
    {
        return $this->edit($request, $room);
    }

    public function edit(Request $request, Room $room): View
    {
        $this->authorize('manage-room', $room);

        return view('admin.rooms.edit', $this->formOptions($request) + [
            'room' => $room->load(['floor', 'facilities', 'pics', 'allowedOrganizationalUnits']),
            'eligiblePics' => User::where('role', User::ROLE_ROOM_PIC)->whereHas('branches', fn ($query) => $query->where('branches.id', $room->branch_id))->orderBy('name')->get(),
            'selectedPicIds' => $room->pics->pluck('id')->all(),
            'selectedUnitIds' => $room->allowedOrganizationalUnits->pluck('id')->all(),
            'selectedFacilityIds' => $room->facilities->pluck('id')->all(),
        ]);
    }

    public function update(SaveRoomRequest $request, Room $room, RoomService $service): RedirectResponse
    {
        $this->authorize('manage-room', $room);
        $room = $service->save($request->validated(), $room);

        return redirect()->route('admin.rooms.show', $room)->with('status', 'Ruangan berhasil diperbarui.');
    }

    public function status(UpdateStatusRequest $request, Room $room): RedirectResponse
    {
        $this->authorize('manage-room', $room);
        $data = $request->validated();
        $room->update($data);

        return redirect()->route('admin.rooms.show', $room)
            ->with('status', $room->is_active ? 'Ruangan berhasil diaktifkan.' : 'Ruangan berhasil dinonaktifkan.');
    }

    public function destroy(DeleteRoomRequest $request, Room $room, RoomService $service): RedirectResponse
    {
        $this->authorize('manage-room', $room);
        $service->delete($room);

        return redirect()->route('admin.rooms.index')->with('status', 'Ruangan berhasil dihapus.');
    }

    /** Super Admin sees every location; Branch Admin only its own (DEC-061). */
    private function branches(Request $request)
    {
        return Branch::query()->when(! $request->user()->isSuperAdmin(), fn ($query) => $query
            ->whereIn('id', $request->user()->branches()->select('branches.id')))->orderBy('name')->get();
    }

    private function formOptions(Request $request): array
    {
        return [
            'branches' => $this->branches($request),
            'floors' => Floor::orderBy('floor_number')->get(),
            'facilities' => Facility::orderBy('name')->get(),
            'units' => OrganizationalUnit::orderBy('name')->get(),
        ];
    }
}
