# CLAUDE.md

Aturan utama proyek ada di AGENTS.md dan berlaku penuh untuk Claude:

@AGENTS.md

## Ringkasan cepat

Sistem Booking Ruang Rapat PT Pelindo Multi Terminal. Laravel 10 (PHP 8.1+), Blade + Livewire 3 + Tailwind 3 via Vite 4, PostgreSQL. Bahasa UI, dokumen, dan catatan progress: Bahasa Indonesia.

Role (`App\Models\User`): `user`, `room_pic`, `admin` (Admin satu kantor/cabang), `super_admin` (Super Admin semua lokasi), lihat DEC-061. Gate di `AuthServiceProvider`: `access-general` (semua role), `access-employee` (user + PIC), `access-pic` (PIC + Admin + Super Admin), `access-admin` (Admin + Super Admin), `access-super-admin`, serta `manage-room` dan `manage-user` untuk batas lokasi per record. Data Admin cabang dibatasi lewat `Room::visibleTo()` dan `User::canAccessBranch()`. Master data global dan dropdown lokasi hanya untuk Super Admin.

## Peta halaman

| Area | URL | View utama |
| --- | --- | --- |
| Publik | `/` | `welcome` (layout `public`) |
| Tamu | `/tamu`, `/tamu/jadwal` | `guests/*` (layout `visitor`) |
| Login | `/login` | `auth/login` |
| Umum (login) | `/dashboard`, `/rooms`, `/rooms/{room}`, `/schedule` | `dashboard`, `user/rooms/*`, `shared/schedule` |
| Pegawai | `/my-bookings`, `/my-bookings/{id}`, `/rooms/{room}/book` | `user/bookings/*` (form: Livewire `BookingPreparationForm`) |
| PIC | `/pic/dashboard`, `/pic/rooms`, `/pic/approvals[/history]`, `/pic/reports/monthly` | `pic/*`, `reports/monthly` |
| Admin + Super Admin | `/admin/{dashboard,users,rooms,bookings,approvals,schedule,reports/monthly}` | `admin/*` (approval admin memakai view `pic/approvals/*`) |
| Super Admin saja | `/admin/{branches,floors,facilities,organizational-units,whatsapp}` | `admin/*` |

Layout: `layouts/app` satu-satunya file `<html>`/`<head>`. PIC/Admin/Super Admin memakai `layouts.workspace` (sidebar), role `user` memakai `layouts.employee` (navbar), dan halaman lintas role memakai `layouts.panel` yang memilih otomatis. Lainnya: `layouts.public`, `layouts.visitor`, `layouts.report`. Sidebar, topbar, dan navbar ada di `layouts/partials/`. Detail ada di `docs/FRONTEND_GUIDE.md` bagian "Struktur layout".

Logic bisnis ada di `app/Services` (mis. `BookingService`, `BookingSchedule`, `RoomService`, `UserService`). Validasi ada di `app/Http/Requests`. Komponen Livewire ada di `app/Livewire`.

## Perintah

- Dev: `php artisan serve` dan `npm run dev`
- Build aset: `npm run build`
- Cek Blade: `php artisan view:cache` lalu `php artisan view:clear`
- Format PHP: `vendor/bin/pint --dirty`
- Test: `APP_ENV=testing php artisan test --filter=<NamaTest>`

## Catatan test

- Shell di mesin ini mengekspor isi `.env` (`APP_ENV=local`), yang mengalahkan `phpunit.xml`. Selalu jalankan test dengan `APP_ENV=testing php artisan test ...`. Tanpa itu, `PostgresTestCase` gagal di assertion environment, dan itu memang pengaman.
- Test fitur memakai `Tests\PostgresTestCase`, yang membuat schema sementara di database PostgreSQL dari `.env`, lalu migrate, seed `FoundationSeeder`, dan drop schema. Database itu remote, jadi test lambat. Jalankan test yang relevan dengan `--filter`, bukan full suite, kecuali diminta.
- Jangan pernah menjalankan `migrate:fresh`, `migrate:reset`, `db:wipe`, atau seeder ke database aplikasi. Database dipakai bersama.

## Kebiasaan kerja

- Setelah selesai: jalankan test relevan, update `docs/PROGRESS.md` (entri baru di atas bagian "Last Update"), dan update `docs/DECISIONS.md` jika ada keputusan baru (nomor DEC berikutnya).
- Jangan commit kecuali diminta. Jangan commit `.env`, `node_modules`, atau `public/build`.
