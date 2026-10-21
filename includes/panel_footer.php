    </main>
  </div><!-- /panel-isi -->
</div><!-- /panel-wrap -->

<!-- Modal konfirmasi hapus (dipakai semua tabel) -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusJudul" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:14px">
      <div class="modal-header border-0">
        <h2 class="h6 modal-title" id="modalHapusJudul">Hapus data?</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body pt-0">
        <p class="mb-0">Anda akan menghapus <strong id="hapusNama">data ini</strong>. Data yang sudah dihapus tidak bisa dikembalikan.</p>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-garis" data-bs-dismiss="modal">Batal</button>
        <form method="post" id="formHapus" class="d-inline">
          <?php csrf_field(); ?>
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id" id="hapusId" value="">
          <button type="submit" class="btn btn-danger">Ya, hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
