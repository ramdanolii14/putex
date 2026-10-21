/* Skrip ringan untuk seluruh halaman. */
document.addEventListener('DOMContentLoaded', function () {

  // 1. Modal konfirmasi hapus.
  //    Tombol cukup diberi: class="tombol-hapus" data-nama="..." data-aksi="url.php" data-id="1"
  var modalEl = document.getElementById('modalHapus');
  if (modalEl) {
    document.querySelectorAll('.tombol-hapus').forEach(function (btn) {
      btn.addEventListener('click', function () {
        modalEl.querySelector('#hapusNama').textContent = btn.dataset.nama || 'data ini';
        modalEl.querySelector('#formHapus').action = btn.dataset.aksi || '';
        modalEl.querySelector('#hapusId').value = btn.dataset.id || '';
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      });
    });
  }

  // 2. Pratinjau gambar sebelum diunggah.
  document.querySelectorAll('input[type="file"][data-pratinjau]').forEach(function (input) {
    input.addEventListener('change', function () {
      var target = document.querySelector(input.dataset.pratinjau);
      if (target && input.files && input.files[0]) {
        target.src = URL.createObjectURL(input.files[0]);
        target.classList.remove('d-none');
      }
    });
  });

  // 3. Notifikasi menutup sendiri setelah 5 detik.
  document.querySelectorAll('.notif').forEach(function (el) {
    setTimeout(function () {
      var a = bootstrap.Alert.getOrCreateInstance(el);
      if (a) { a.close(); }
    }, 5000);
  });

  // 4. Form verifikasi: kolom alasan hanya muncul saat memilih "Tolak".
  var pilihStatus = document.getElementById('pilihStatus');
  if (pilihStatus) {
    var kotakAlasan = document.getElementById('kotakAlasan');
    var ubah = function () {
      var tolak = pilihStatus.value === 'ditolak';
      kotakAlasan.classList.toggle('d-none', !tolak);
      kotakAlasan.querySelector('textarea').required = tolak;
    };
    pilihStatus.addEventListener('change', ubah);
    ubah();
  }
});
