<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Tests\PostgresTestCase;

class DashboardContentTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-09-27 10:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function booking(User $user, array $changes = []): Booking
    {
        return Booking::create(array_replace([
            'room_id' => Room::where('code', 'DEMO-SM')->firstOrFail()->id, 'user_id' => $user->id,
            'date' => '2026-09-28', 'start_time' => '09:00', 'end_time' => '10:00',
            'agenda' => 'Agenda mendatang', 'unit_name' => 'Unit', 'status' => 'approved', 'requires_approval' => false,
            'submission_token' => (string) Str::uuid(), 'request_hash' => str_repeat('a', 64),
        ], $changes));
    }

    public function test_employee_dashboard_lists_only_own_upcoming_bookings(): void
    {
        $employee = User::where('role', 'user')->firstOrFail();
        $other = User::where('role', 'room_pic')->firstOrFail();
        $upcoming = $this->booking($employee);
        $this->booking($employee, ['agenda' => 'Agenda sudah lewat', 'date' => '2026-09-27', 'start_time' => '08:00', 'end_time' => '09:00']);
        $this->booking($employee, ['agenda' => 'Agenda ditolak', 'status' => 'rejected']);
        $this->booking($other, ['agenda' => 'Agenda orang lain']);

        $this->actingAs($employee)->get('/dashboard')->assertOk()
            ->assertSee('Rapat Mendatang')->assertSee('Agenda mendatang')
            ->assertSee(route('my-bookings.show', $upcoming), false)
            ->assertDontSee('Agenda sudah lewat')->assertDontSee('Agenda ditolak')->assertDontSee('Agenda orang lain')
            ->assertViewHas('stats', fn ($stats) => array_column($stats, 'value') === [1, 0, 3]);
    }

    public function test_pic_dashboard_lists_pending_approvals_for_assigned_rooms(): void
    {
        $pic = User::where('email', 'pic@example.test')->firstOrFail();
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $room->pics()->syncWithoutDetaching([$pic->id]);
        $pending = $this->booking(User::where('role', 'user')->firstOrFail(), ['status' => 'pending', 'requires_approval' => true]);

        $this->actingAs($pic)->get('/pic/dashboard')->assertOk()
            ->assertSee('Menunggu Tinjauan Anda')
            ->assertSee(route('pic.approvals.show', $pending), false)
            ->assertViewHas('pendingApprovals', fn ($approvals) => $approvals->modelKeys() === [$pending->id]);
    }
}
