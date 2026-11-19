<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Floor;
use App\Models\Room;
use App\Models\User;
use App\Models\WhatsappRecipient;
use App\Models\WhatsappTemplate;
use App\Services\BookingWhatsapp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\PostgresTestCase;

class BranchAdminTest extends PostgresTestCase
{
    private Room $hq;

    private Room $other;

    private Branch $branch;

    private User $admin;

    private User $superAdmin;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hq = Room::where('code', 'DEMO-SM')->firstOrFail();
        $this->branch = Branch::create(['name' => 'CABANG-LAIN-PRIVAT']);
        $this->other = $this->hq->replicate();
        $this->other->fill(['branch_id' => $this->branch->id, 'name' => 'RUANG-CABANG-PRIVAT', 'code' => 'PRIVATE-CBG'])->save();
        $this->superAdmin = User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail();
        $this->employee = User::where('role', User::ROLE_USER)->firstOrFail();
        $this->admin = new User;
        $this->admin->forceFill(['name' => 'Admin Pusat', 'email' => 'admin-pusat@example.test', 'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN, 'is_active' => true])->save();
        $this->admin->branches()->sync([$this->hq->branch_id]);
    }

    private function booking(Room $room, string $agenda): Booking
    {
        return Booking::create([
            'room_id' => $room->id, 'user_id' => $this->employee->id,
            'date' => now(config('booking.timezone'))->addDays(2)->toDateString(),
            'start_time' => '12:00', 'end_time' => '13:00', 'agenda' => $agenda,
            'status' => 'pending', 'requires_approval' => true,
            'submission_token' => (string) Str::uuid(), 'request_hash' => str_repeat('a', 64),
        ]);
    }

    private function userData(array $overrides = []): array
    {
        return array_replace(['name' => 'Akun Baru', 'email' => 'akun-baru@example.test', 'role' => User::ROLE_USER,
            'is_active' => 1, 'password' => 'Password-123', 'password_confirmation' => 'Password-123'], $overrides);
    }

    public function test_role_labels_and_global_master_data_stay_with_super_admin(): void
    {
        $this->assertSame('Super Admin', $this->superAdmin->roleLabel());
        $this->assertSame('Admin', $this->admin->roleLabel());
        $this->assertSame('admin.dashboard', $this->admin->dashboardRouteName());

        $this->actingAs($this->admin)->get('/admin/dashboard')->assertOk()
            ->assertSee(route('admin.users.index'), false)
            ->assertDontSee(route('admin.branches.index'), false)
            ->assertDontSee(route('admin.facilities.index'), false)
            ->assertDontSee(route('admin.whatsapp.index'), false);
        foreach (['/admin/branches', '/admin/floors', '/admin/facilities', '/admin/organizational-units', '/admin/whatsapp'] as $url) {
            $this->get($url)->assertForbidden();
            $this->actingAs($this->superAdmin)->get($url)->assertOk();
            $this->actingAs($this->admin);
        }
        // Branch Admin has no location dropdown or filter.
        $this->get('/admin/rooms?branch_id='.$this->hq->branch_id)->assertForbidden();
        $this->get('/my-bookings')->assertForbidden();
    }

    public function test_rooms_and_bookings_are_limited_to_the_managed_branch(): void
    {
        $this->booking($this->hq, 'AGENDA-PUSAT');
        $foreign = $this->booking($this->other, 'AGENDA-CABANG');
        $this->actingAs($this->admin);

        $this->get('/admin/rooms')->assertOk()->assertSee($this->hq->name)->assertDontSee($this->other->name);
        $this->get(route('admin.rooms.edit', $this->hq))->assertOk();
        $this->get(route('admin.rooms.edit', $this->other))->assertForbidden();
        $this->patch(route('admin.rooms.status', $this->other), ['is_active' => 0])->assertForbidden();
        $this->put(route('admin.rooms.pics.update', $this->other), ['pic_ids' => []])->assertForbidden();
        $this->assertTrue($this->other->fresh()->is_active);

        $this->get('/admin/bookings')->assertOk()->assertSee('AGENDA-PUSAT')->assertDontSee('AGENDA-CABANG');
        $this->get('/admin/bookings/'.$foreign->id)->assertNotFound();
        $this->get('/admin/schedule')->assertOk();

        $room = ['name' => 'Ruang Baru Pusat', 'floor_id' => Floor::firstOrFail()->id, 'access_type' => 'all',
            'requires_approval' => 0, 'status' => 'available', 'is_active' => 1, 'facility_ids' => []];
        $this->post('/admin/rooms', $room + ['branch_id' => $this->branch->id])->assertSessionHasErrors('branch_id');
        $this->post('/admin/rooms', $room)->assertSessionHasNoErrors();
        $this->assertSame($this->hq->branch_id, Room::where('name', 'Ruang Baru Pusat')->sole()->branch_id);
    }

    public function test_approvals_and_reports_are_limited_to_the_managed_branch(): void
    {
        $local = $this->booking($this->hq, 'AGENDA-PUSAT');
        $foreign = $this->booking($this->other, 'AGENDA-CABANG');
        $this->actingAs($this->admin);

        $this->get('/admin/approvals')->assertOk()->assertSee('AGENDA-PUSAT')->assertDontSee('AGENDA-CABANG');
        $this->post('/admin/approvals/'.$foreign->id, ['decision' => 'approved'])->assertNotFound();
        $this->assertSame('pending', $foreign->fresh()->status);
        $this->post('/admin/approvals/'.$local->id, ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->assertSame('approved', $local->fresh()->status);
        $this->assertSame($this->admin->id, $local->fresh()->decided_by);

        $month = substr($local->date, 0, 7);
        $this->get('/admin/reports/monthly?month='.$month)->assertOk()->assertSee('AGENDA-PUSAT')->assertDontSee('AGENDA-CABANG');
        $this->get('/admin/reports/monthly?month='.$month.'&branch_id='.$this->branch->id)->assertSessionHasErrors('branch_id');
    }

    public function test_branch_admin_manages_only_user_and_pic_accounts_of_its_branch(): void
    {
        $outsider = User::create(['name' => 'Pegawai Cabang', 'email' => 'cabang@example.test', 'password' => 'password']);
        $outsider->forceFill(['role' => User::ROLE_USER, 'is_active' => true])->save();
        $outsider->branches()->sync([$this->branch->id]);
        $this->actingAs($this->admin);

        $this->get('/admin/users')->assertOk()->assertSee($this->employee->email)
            ->assertDontSee($outsider->email)->assertDontSee($this->superAdmin->email);
        $this->get('/admin/users/'.$outsider->id.'/edit')->assertForbidden();
        $this->get('/admin/users/'.$this->superAdmin->id.'/edit')->assertForbidden();
        $this->patch('/admin/users/'.$outsider->id.'/status', ['is_active' => 0])->assertForbidden();
        $this->assertTrue($outsider->fresh()->is_active);
        $this->get('/admin/users/create')->assertOk()->assertDontSee('value="super_admin"', false)
            ->assertDontSee('value="admin"', false)->assertDontSee('name="branch_ids[]"', false);

        $this->post('/admin/users', $this->userData(['role' => User::ROLE_SUPER_ADMIN]))->assertSessionHasErrors('role');
        $this->post('/admin/users', $this->userData(['role' => User::ROLE_ADMIN]))->assertSessionHasErrors('role');
        $this->post('/admin/users', $this->userData(['branch_selection_present' => 1, 'branch_ids' => [$this->branch->id]]))->assertSessionHasNoErrors();
        $created = User::where('email', 'akun-baru@example.test')->sole();
        $this->assertSame([$this->hq->branch_id], $created->branches()->pluck('branches.id')->all());

        // Editing keeps location permissions that only Super Admin can change.
        $created->branches()->attach($this->branch->id);
        $this->put('/admin/users/'.$created->id, $this->userData(['name' => 'Nama Diubah', 'password' => '', 'password_confirmation' => '',
            'branch_selection_present' => 1, 'branch_ids' => []]))->assertSessionHasNoErrors();
        $this->assertSame('Nama Diubah', $created->fresh()->name);
        $this->assertEqualsCanonicalizing([$this->hq->branch_id, $this->branch->id], $created->branches()->pluck('branches.id')->all());
    }

    public function test_branch_admin_can_receive_approval_whatsapp_only_for_its_branch(): void
    {
        $template = WhatsappTemplate::where('event', 'approval_request')->firstOrFail();
        $template->update(['include_room_pics' => false]);
        $this->admin->forceFill(['whatsapp_number' => '+6281200000001'])->save();
        $this->superAdmin->forceFill(['whatsapp_number' => '+6281200000002'])->save();
        foreach ([$this->admin, $this->superAdmin] as $recipient) {
            WhatsappRecipient::create(['purpose' => 'approval_request', 'user_id' => $recipient->id, 'is_active' => true]);
        }
        $whatsapp = app(BookingWhatsapp::class);

        $localBooking = $this->booking($this->hq, 'AGENDA-PUSAT');
        $local = $whatsapp->recipients($localBooking, 'approval_request', $template)->pluck('id');
        $this->assertEqualsCanonicalizing([$this->admin->id, $this->superAdmin->id], $local->all());
        $foreign = $whatsapp->recipients($this->booking($this->other, 'AGENDA-CABANG'), 'approval_request', $template)->pluck('id');
        $this->assertSame([$this->superAdmin->id], $foreign->values()->all());
        $this->assertSame(route('admin.approvals.show', $localBooking->id), $whatsapp->render($localBooking, '{review_url}', $this->admin));
    }

    public function test_super_admin_assigns_exactly_one_branch_to_a_branch_admin(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get('/admin/users/create')->assertOk()->assertSee('value="admin"', false)->assertSee('Super Admin');
        $base = $this->userData(['role' => User::ROLE_ADMIN, 'branch_selection_present' => 1]);
        $this->post('/admin/users', $base)->assertSessionHasErrors('branch_ids');
        $this->post('/admin/users', $base + ['branch_ids' => [$this->hq->branch_id, $this->branch->id]])->assertSessionHasErrors('branch_ids');
        $this->post('/admin/users', $base + ['branch_ids' => [$this->branch->id]])->assertSessionHasNoErrors();
        $created = User::where('email', 'akun-baru@example.test')->sole();
        $this->assertSame(User::ROLE_ADMIN, $created->role);
        $this->assertSame($this->branch->id, $created->managedBranchId());

        $this->actingAs($created)->get('/admin/rooms')->assertOk()->assertSee($this->other->name)->assertDontSee($this->hq->name);
    }
}
