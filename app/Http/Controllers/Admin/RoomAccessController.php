<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRoomAccessRequest;
use App\Models\Room;
use App\Services\RoomAssignmentService;
use Illuminate\Http\RedirectResponse;

class RoomAccessController extends Controller
{
    public function edit(Room $room): RedirectResponse
    {
        $this->authorize('manage-room', $room);

        return redirect()->to(route('admin.rooms.show', $room).'#access');
    }

    public function update(SaveRoomAccessRequest $request, Room $room, RoomAssignmentService $service): RedirectResponse
    {
        $this->authorize('manage-room', $room);

        $service->syncUnits($room, $request->validated('organizational_unit_ids'));

        return redirect()->route('admin.rooms.show', $room)->with('status', 'Unit akses ruangan berhasil diperbarui.');
    }
}
