<?php

namespace Tests\Feature;

use App\Livewire\BookingPreparationForm;
use App\Models\Branch;
use App\Models\GuestRegistration;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\PostgresTestCase;

class GuestRegistrationTest extends PostgresTestCase
{
    public function test_form_has_no_office_selector_and_uses_server_configured_office(): void
    {
        $branch = Branch::create(['name' => 'Lokasi Resepsionis']);
        config(['guests.branch_id' => $branch->id]);
        $this->get('/tamu')->assertOk()->assertSee('Tujuan kunjungan')->assertSee('NIP (opsional)')
            ->assertDontSee('name="branch_id"', false);
        $this->post('/tamu', ['name' => 'Tamu tanpa NIP', 'organization' => 'Instansi', 'contact' => '0812345',
            'purpose' => 'Koordinasi', 'branch_id' => Branch::firstOrFail()->id])->assertRedirect('/tamu/jadwal');
        $guest = GuestRegistration::sole();
        $this->assertSame($branch->id, $guest->branch_id);
        $this->assertNull($guest->nip);
        $this->assertSame('Koordinasi', $guest->purpose);
    }

    private function registerGuest(): GuestRegistration
    {
        $this->post('/tamu', ['name' => 'Tamu Uji', 'organization' => 'Instansi Uji', 'contact' => '08123456789', 'nip' => '0012345678', 'purpose' => 'Pertemuan koordinasi'])->assertRedirect('/tamu/jadwal');

        return GuestRegistration::latest('id')->firstOrFail();
    }

    public function test_registration_gates_schedule_and_expires_without_employee_access(): void
    {
        $this->get('/tamu/jadwal')->assertRedirect('/tamu');
        $this->post('/tamu', [])->assertSessionHasErrors(['name', 'organization', 'contact', 'purpose']);
        $guest = $this->registerGuest();
        $this->assertEqualsWithDelta(now()->addDays(3)->timestamp, $guest->expires_at->timestamp, 5);
        $this->assertSame('0012345678', $guest->nip);
        $this->assertSame('Pertemuan koordinasi', $guest->purpose);
        $this->get('/tamu/jadwal')->assertOk()->assertDontSee($guest->reference)->assertDontSee('08123456789');
        $this->get('/tamu/jadwal?date=invalid')->assertSessionHasErrors('date');
        $this->get('/my-bookings')->assertRedirect('/login');
        $this->travel(4)->days();
        $this->get('/tamu/jadwal')->assertRedirect('/tamu');
        $this->artisan('guests:prune')->assertSuccessful();
        $this->assertDatabaseMissing('guest_registrations', ['id' => $guest->id]);
    }

    public function test_employee_lists_guest_names_shown_in_details_report_and_csv_but_not_guest_schedule(): void
    {
        $this->registerGuest();
        $user = User::where('role', 'user')->firstOrFail();
        $this->actingAs($user);
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $input = ['date' => now(config('booking.timezone'))->addDay()->toDateString(), 'start_time' => '12:00', 'end_time' => '13:00',
            'agenda' => 'Agenda rahasia', 'guest_names' => "  Tamu Uji (Instansi Uji)\r\n\n Tamu Kedua  "];
        $token = (string) Str::uuid();
        $booking = app(BookingService::class)->submit($user, $room->id, $input, $token);
        $this->assertSame($user->id, $booking->user_id);
        $this->assertSame("Tamu Uji (Instansi Uji)\nTamu Kedua", $booking->guest_names);
        // Guest names alone never force approval; the demo room status follows its own setting.
        $this->assertSame($room->requires_approval ? 'pending' : 'approved', $booking->status);
        $this->assertSame($booking->id, app(BookingService::class)->submit($user, $room->id, $input, $token)->id);
        $this->get('/my-bookings/'.$booking->id)->assertOk()->assertSee('Nama Tamu')->assertSee('Tamu Kedua');
        $this->get('/tamu/jadwal?date='.$input['date'])->assertOk()->assertSee('12:00')->assertDontSee('Agenda rahasia')->assertDontSee('Tamu Kedua');
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $month = substr($input['date'], 0, 7);
        $this->get('/admin/reports/monthly?month='.$month)->assertOk()->assertSee('Tamu Uji (Instansi Uji)');
        $csv = $this->get('/admin/reports/monthly/export?month='.$month)->assertOk()->streamedContent();
        $this->assertStringContainsString('Tamu Uji (Instansi Uji); Tamu Kedua', $csv);
        $this->assertStringNotContainsString('Kode Registrasi Tamu', $csv);
    }

    public function test_booking_form_has_optional_guest_names_without_guest_dropdown(): void
    {
        $guest = $this->registerGuest();
        $this->actingAs(User::where('role', 'user')->firstOrFail());
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->assertSee('Nama Tamu (opsional)')->assertDontSee('Booking untuk')->assertDontSee($guest->name)
            ->set('date', now(config('booking.timezone'))->addDay()->toDateString())->set('start_time', '12:00')
            ->set('agenda', 'Rapat dengan tamu')->set('guest_names', str_repeat('a', 2001))
            ->call('submitBooking')->assertHasErrors('guest_names');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_end_of_day_expiry(): void
    {
        config(['guests.expiry_mode' => 'end_of_day']);
        $this->travelTo(CarbonImmutable::parse('2026-10-20 16:00', 'Asia/Jakarta'));
        $guest = $this->registerGuest();
        $this->assertSame('2026-10-20 17:00', $guest->expires_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i'));
        $this->travelTo(CarbonImmutable::parse('2026-10-20 17:00', 'Asia/Jakarta'));
        $this->get('/tamu/jadwal')->assertRedirect('/tamu');
        $next = $this->registerGuest();
        $this->assertSame('2026-10-21 17:00', $next->expires_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i'));
        $this->travelBack();
    }

    public function test_guest_schedule_excludes_restricted_rooms_and_other_branches(): void
    {
        $this->registerGuest();
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $room->update(['access_type' => 'restricted']);
        $this->get('/tamu/jadwal')->assertOk()->assertDontSee($room->name);
        $room->update(['access_type' => 'all', 'branch_id' => Branch::create(['name' => 'Cabang Lain'])->id]);
        $this->get('/tamu/jadwal')->assertOk()->assertDontSee($room->name);
    }
}
