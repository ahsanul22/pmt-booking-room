// Form ruangan: daftar unit akses hanya tampil saat Jenis Akses = Unit tertentu.
const syncRoomAccess = (select) => {
    const units = select.form?.querySelector('[data-room-access-units]');
    if (units) units.hidden = select.value !== 'restricted';
};

document.addEventListener('change', (event) => {
    if (event.target.matches('select[name="access_type"]')) syncRoomAccess(event.target);
});
document.querySelectorAll('select[name="access_type"]').forEach(syncRoomAccess);
