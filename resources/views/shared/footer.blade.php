{{-- Footer bersama untuk semua layout (publik, login, pegawai, workspace, tamu). Tanpa tautan Login/Dashboard (DEC-036). --}}
<footer class="relative z-10 mt-6 bg-primaryDark text-secondaryLight">
    <div aria-hidden="true" class="h-1 bg-gradient-to-r from-primary via-secondary to-primary"></div>
    <div class="mx-auto max-w-[1800px] px-5 sm:px-8">
        <div class="grid gap-10 py-12 md:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] md:gap-16">
            <div class="min-w-0">
                <x-application-logo variant="dark" class="h-9 w-auto max-w-[180px] object-contain" />
                <p class="mt-6 text-2xl font-semibold leading-snug text-white">Ruang untuk berkolaborasi.</p>
                <p class="mt-3 max-w-md text-base leading-7">Rencanakan pertemuan, temukan ruangan yang sesuai, dan pantau pengajuan Anda dalam satu tempat.</p>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-white">Bantuan &amp; Kontak</p>
                <p class="mt-3 text-sm leading-6">Kendala akun atau akses ruangan? Hubungi Admin kantor Anda atau:</p>
                <ul class="mt-5 space-y-4 text-base">
                    <li class="flex items-center gap-3">
                        <span aria-hidden="true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><rect x="3" y="5" width="18" height="14" rx="3" stroke-linejoin="round" /><path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6" /></svg>
                        </span>
                        <a href="mailto:info@pelindomultiterminal.co.id" class="min-w-0 rounded-sm [overflow-wrap:anywhere] transition-colors hover:text-white hover:underline focus-visible:outline-white">info@pelindomultiterminal.co.id</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <span aria-hidden="true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8 3 2 5-3 2a14 14 0 0 0 7 7l2-3 5 2v3a2 2 0 0 1-2 2A18 18 0 0 1 3 5a2 2 0 0 1 2-2h3Z" /></svg>
                        </span>
                        <a href="tel:+626141000000" class="rounded-sm transition-colors hover:text-white hover:underline focus-visible:outline-white">+62 61 4100 0000</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 border-t border-white/15 py-5 text-sm leading-6">
            <p>&copy; {{ now()->year }} PT Pelindo Multi Terminal &middot; Booking Room untuk pegawai internal</p>
            <a href="#content" class="inline-flex min-h-[44px] items-center gap-2 rounded-lg px-2 font-semibold text-white hover:bg-white/10 focus-visible:outline-white">Kembali ke atas <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5m-6 6 6-6 6 6" /></svg></a>
        </div>
    </div>
</footer>
