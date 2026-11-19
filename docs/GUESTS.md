# Pendataan tamu dan nama tamu pada booking

Tamu membuka `/tamu`, mengisi nama, instansi, NIP opsional, nomor kontak, dan tujuan kunjungan. Kantor/cabang tidak ditampilkan sebagai isian. Lokasi ditentukan server melalui `GUEST_BRANCH_ID`; jika belum diatur, memakai kantor dengan ID pertama dari master data.
Session browser tersebut mendapat akses `/tamu/jadwal` sampai registrasinya kedaluwarsa.
Tidak membuat user/password atau memberikan akses pegawai. Jadwal tamu hanya memuat
ruangan aktif dengan akses `all` di kantor tujuan dan jam Pending/Approved, tanpa
agenda, identitas pemohon, unit, atau catatan rapat. Halaman jadwal perlu dimuat ulang
untuk memperoleh kondisi terkini; waktu kosong bukan jaminan ketersediaan.

Pembaruan 2026-11-19 (DEC-062): fitur petugas resepsionis dan booking atas nama tamu
dihapus karena booking tidak boleh dibuat atas nama tamu. Pegawai yang mengajukan booking
dapat mengisi kolom **Nama Tamu (opsional)** di form booking, satu nama per baris.
Kolom ini tersedia di semua ruangan tanpa pengaturan per ruangan dan tidak membuat booking
membutuhkan approval. Isian tidak dihubungkan ke registrasi `/tamu`; registrasi tamu tetap
hanya untuk melihat jadwal terbatas.

Nama tamu tampil di detail booking, dialog approval, laporan bulanan (layar, cetak, CSV
kolom Nama Tamu). Data lama dari fitur resepsionis (nama, instansi, kode tamu) dipindahkan
ke kolom yang sama dengan format `Nama (Instansi)`; kode registrasi lama tidak disimpan lagi.

## Masa aktif dan penghapusan

Default sementara: tiga hari sejak registrasi. Konfigurasi `.env` opsional:

```dotenv
GUEST_EXPIRY_MODE=days
GUEST_ACTIVE_DAYS=3
```

`GUEST_EXPIRY_MODE=end_of_day` berakhir pukul 17.00 WIB. Registrasi mulai pukul
17.00 atau sesudahnya berakhir pukul 17.00 hari berikutnya. Pilihan kebijakan ini
masih perlu dikonfirmasi pemilik proses; konfigurasi tidak mengubah expiry record
yang sudah tersimpan. Jalankan `php artisan config:clear` setelah mengubah konfigurasi.

Scheduler existing `php artisan schedule:run` setiap menit menjalankan
`guests:prune`. Untuk eksekusi manual: `php artisan guests:prune`.
Akses dan pengajuan baru langsung ditolak saat expiry, walaupun scheduler belum
sempat menghapus record. Data registrasi dan kontak dihapus permanen; snapshot
minimal pada booking dipertahankan untuk laporan.

## QR untuk resepsionis

QR mengarah ke URL publik aplikasi diikuti `/tamu`, bukan URL localhost yang tidak
dapat diakses dari ponsel tamu. Form juga ditautkan dari beranda dan form booking.
Untuk membuat berkas SVG siap cetak pada mesin pengembangan yang memiliki package
Python `qrcode`:

```powershell
python scripts/generate_guest_qr.py https://domain-aplikasi
```

Hasil: `storage/app/guest-registration-qr.svg`. Isi domain dengan alamat deployment
sebenarnya dan buat ulang QR jika alamat berubah. Tidak memakai layanan QR eksternal
atau menambah dependency runtime Laravel.

## Tampilan form

Form terpusat dengan lebar maksimum 768px, dua kolom pada desktop dan satu kolom pada mobile. Background hanya pada form registrasi; halaman jadwal tetap menggunakan latar aplikasi biasa. Aset gambar dan prompt tercatat di [GUEST_BACKGROUND.md](GUEST_BACKGROUND.md). NIP berupa string untuk mempertahankan nol awal, opsional bagi tamu yang tidak memilikinya. Tujuan wajib maksimal 2.000 karakter.
