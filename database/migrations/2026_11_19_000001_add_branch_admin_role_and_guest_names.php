<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Branch Admin (DEC-061): manages exactly one office/branch through branch_user.
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('user', 'room_pic', 'admin', 'super_admin'))");

        // Receptionist booking on behalf of guests is removed (DEC-062).
        Schema::table('bookings', fn (Blueprint $table) => $table->text('guest_names')->nullable());
        DB::statement("UPDATE bookings SET guest_names = guest_name || COALESCE(' (' || NULLIF(guest_organization, '') || ')', '') WHERE guest_name IS NOT NULL AND guest_name <> ''");
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn(['guest_name', 'guest_organization', 'guest_reference']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_receptionist'));
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->boolean('is_receptionist')->default(false));
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('guest_name')->nullable();
            $table->string('guest_organization')->nullable();
            $table->string('guest_reference', 20)->nullable();
        });
        // Free-text guest lists cannot be split back into name/organization/reference reliably.
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn('guest_names'));

        DB::table('users')->where('role', 'admin')->update(['role' => 'user', 'is_active' => false]);
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('user', 'room_pic', 'super_admin'))");
    }
};
