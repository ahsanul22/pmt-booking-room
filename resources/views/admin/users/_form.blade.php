<x-workspace-field name="name" label="Nama" type="text" autocomplete="name" required maxlength="255" :value="old('name', data_get($userRecord ?? null, 'name'))" />
@php($emailInput = old('email', data_get($userRecord ?? null, 'email')))
<x-workspace-field name="email" label="Email" type="email" autocomplete="email" required maxlength="255" :value="is_string($emailInput) ? $emailInput : ''" />
@php($whatsappInput = old('whatsapp_number', data_get($userRecord ?? null, 'whatsapp_number')))
<x-workspace-field name="whatsapp_number" label="Nomor WhatsApp" type="tel" autocomplete="tel" maxlength="30" hint="Nomor WhatsApp ini digunakan untuk menerima notifikasi terkait booking dan persetujuan ruangan." :value="is_string($whatsappInput) ? $whatsappInput : ''" />
<x-workspace-field name="password" minlength="8" hint="Minimal 8 karakter. Kosongkan saat edit jika password tetap." label="Password Awal / Baru" type="password" :required="! ($editing ?? false)" autocomplete="new-password" />
<x-workspace-field name="password_confirmation" label="Konfirmasi Password" type="password" :required="! ($editing ?? false)" autocomplete="new-password" />
<x-workspace-field type="select" name="organizational_unit_id" label="Unit Kerja">
    <option value="">Tanpa Unit Kerja</option>
    @forelse($units ?? [] as $option)
        <option value="{{ $option->id }}" @selected((string) (old('organizational_unit_id', data_get($userRecord ?? null, 'organizational_unit_id'))) === (string) $option->id)>{{ $option->name }} ({{ $option->type }}){{ $option->is_active ? '' : ' - Nonaktif' }}</option>
    @empty
        <option disabled>Belum ada pilihan tersedia.</option>
    @endforelse
</x-workspace-field>
<x-workspace-field type="select" name="role" label="Role" required>
    <option value="">Pilih Role</option>
    @foreach($roleOptions ?? [] as $roleValue => $roleLabel)
        <option value="{{ $roleValue }}" @selected(old('role', data_get($userRecord ?? null, 'role')) === $roleValue)>{{ $roleLabel }}</option>
    @endforeach
</x-workspace-field>
<x-workspace-field type="select" name="is_active" label="Status Aktif" required>
    <option value="">Pilih Status Aktif</option>
    <option value="1" @selected((string) old('is_active', isset($userRecord) ? (int) $userRecord->is_active : 1) === '1')>Aktif</option>
    <option value="0" @selected((string) old('is_active', isset($userRecord) ? (int) $userRecord->is_active : 1) === '0')>Nonaktif</option>
</x-workspace-field>

@can('access-super-admin')
<fieldset class="rounded-xl border border-slate-200 p-5 sm:col-span-2">
    <legend class="px-2 text-base font-semibold text-primaryDark">Izin Akses Kantor / Cabang</legend>
    <p class="mb-4 text-sm leading-6 text-slate-600">Pilih lokasi akses pengguna/PIC; kosong berarti tanpa akses. Admin wajib memiliki tepat satu lokasi yang dikelola. Super Admin mengakses semua lokasi. Approval PIC tetap memerlukan penugasan ruangan.</p>
    <input type="hidden" name="branch_selection_present" value="1">
    @php($branchSelection = old('branch_selection_present') ? old('branch_ids', []) : (isset($userRecord) ? $userRecord->branches->modelKeys() : []))
    @php($branchSelection = is_array($branchSelection) ? array_map('strval', $branchSelection) : [])
    <div class="grid gap-3 sm:grid-cols-2">
        @forelse($branches ?? [] as $branch)
            <label for="user-branch-{{ $branch->id }}" class="flex min-h-[44px] items-center gap-3 rounded-lg border border-slate-200 p-3 text-base">
                <input id="user-branch-{{ $branch->id }}" type="checkbox" name="branch_ids[]" value="{{ $branch->id }}" @checked(in_array((string) $branch->id, $branchSelection, true)) class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
                {{ $branch->name }}
            </label>
        @empty
            <p class="text-slate-600">Belum ada lokasi. Tambahkan melalui Kantor & Cabang.</p>
        @endforelse
    </div>
    @error('branch_ids')<p role="alert" class="mt-3 text-danger">{{ $message }}</p>@enderror
    @error('branch_ids.*')<p role="alert" class="mt-3 text-danger">{{ $message }}</p>@enderror
</fieldset>
@else
<div class="rounded-xl border border-slate-200 bg-background p-5 sm:col-span-2">
    <p class="text-base font-semibold text-primaryDark">Kantor / Cabang</p>
    <p class="mt-2 text-base leading-6 text-slate-600">{{ isset($userRecord) ? 'Izin lokasi pengguna ini diatur oleh Super Admin.' : 'Pengguna baru otomatis mendapat akses ke '.($managedBranch?->name ?? 'lokasi Anda').'.' }}</p>
</div>
@endcan
