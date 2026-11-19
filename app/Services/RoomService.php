<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomService
{
    public function delete(Room $room): void
    {
        DB::transaction(function () use ($room) {
            $record = Room::lockForUpdate()->findOrFail($room->id);
            if (Booking::where('room_id', $record->id)->exists()) {
                throw ValidationException::withMessages([
                    'room' => 'Ruangan memiliki riwayat booking dan tidak dapat dihapus. Nonaktifkan ruangan untuk menghentikan pengajuan baru.',
                ]);
            }
            // Foreign keys cascade only the facility, PIC and unit access pivots.
            $record->delete();
        }, 3);
    }

    public function save(array $data, ?Room $room = null): Room
    {
        return DB::transaction(function () use ($data, $room) {
            $record = $room ? Room::lockForUpdate()->findOrFail($room->id) : new Room;
            $facilityIds = $data['facility_ids'];
            $unitIds = $data['organizational_unit_ids'] ?? null;
            unset($data['facility_ids'], $data['organizational_unit_ids']);
            if (! $record->exists && ! array_key_exists('capacity', $data)) {
                $data['capacity'] = 0;
            }
            if ($record->exists && isset($data['branch_id']) && (int) $data['branch_id'] !== (int) $record->branch_id && $record->bookings()->exists()) {
                throw ValidationException::withMessages(['branch_id' => 'Ruangan dengan riwayat booking tidak dapat dipindahkan lokasi. Buat ruangan baru pada lokasi tujuan.']);
            }
            $record->fill($data);
            $record->save();
            $record->facilities()->sync($facilityIds);
            // Units only apply to restricted rooms; for "all" the stored list is kept but unused.
            if ($record->access_type === 'restricted' && $unitIds !== null) {
                $record->allowedOrganizationalUnits()->sync($unitIds);
            }

            return $record;
        });
    }
}
