<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        if ($request->user()->can('access-admin')) {
            return app(Admin\DashboardController::class)->index($request);
        }
        $user = $request->user()->load('organizationalUnit');
        $now = CarbonImmutable::now(config('booking.timezone'));
        $today = $now->toDateString();

        if ($user->can('access-pic')) {
            return view('dashboard', [
                'user' => $user,
                'title' => 'Dashboard PIC',
                'stats' => [
                    [
                        'label' => 'Menunggu approval',
                        'icon' => 'clock',
                        'value' => $user->pendingApprovalsCount(),
                        'description' => 'Pengajuan perlu ditinjau',
                    ],
                    [
                        'label' => 'Ruangan tanggung jawab',
                        'icon' => 'room',
                        'value' => $user->managedRooms()->visibleTo($user)->count(),
                        'description' => 'Ruangan ditugaskan',
                    ],
                    [
                        'label' => 'Jadwal hari ini',
                        'icon' => 'calendar',
                        'value' => Booking::query()->whereIn('room_id', Room::query()->visibleTo($user)->select('rooms.id'))
                            ->where('date', $today)
                            ->whereIn('status', ['pending', 'approved', 'completed'])
                            ->whereHas('room.pics', fn ($pics) => $pics->where('users.id', $user->id))
                            ->count(),
                        'description' => 'Penggunaan hari ini',
                    ],
                ],
                'pendingApprovals' => $user->pendingApprovalsQuery()->with(['room', 'applicant'])
                    ->orderBy('date')->orderBy('start_time')->limit(5)->get(),
            ]);
        }

        $own = fn (): Builder => Booking::query()->where('user_id', $user->id)
            ->whereIn('room_id', Room::query()->visibleTo($user)->select('rooms.id'));
        $upcoming = fn (): Builder => $own()->whereIn('status', ['pending', 'approved'])
            ->where(fn (Builder $query) => $query->where('date', '>', $today)
                ->orWhere(fn (Builder $query) => $query->where('date', $today)->where('end_time', '>', $now->format('H:i:s'))));

        return view('dashboard', [
            'user' => $user,
            'title' => 'Dashboard Pegawai',
            'stats' => [
                ['label' => 'Rapat mendatang', 'icon' => 'calendar', 'value' => $upcoming()->where('status', 'approved')->count(), 'description' => 'Sudah disetujui', 'tone' => 'primary'],
                ['label' => 'Menunggu persetujuan', 'icon' => 'clock', 'value' => $upcoming()->where('status', 'pending')->count(), 'description' => 'Masih diproses PIC', 'tone' => 'warning'],
                ['label' => 'Pengajuan bulan ini', 'icon' => 'room', 'value' => $own()->whereBetween('date', [$now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString()])->count(), 'description' => $now->locale('id')->translatedFormat('F Y'), 'tone' => 'secondary'],
            ],
            'upcomingBookings' => $upcoming()->with('room')->orderBy('date')->orderBy('start_time')->limit(4)->get(),
        ]);
    }
}
