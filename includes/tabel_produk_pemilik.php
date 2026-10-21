<?php
/** Tabel produk milik pemilik UMKM. Butuh variabel $daftar (array produk). */
?>
<div class="table-responsive">
  <table class="table align-middle mb-0">
    <thead>
      <tr>
        <th>Foto</th>
        <th>Nama produk</th>
        <th>Harga</th>
        <th>Kategori</th>
        <th>Status</th>
        <th>Tanggal</th>
        <th class="text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($daftar as $row): ?>
      <tr>
        <td><img class="foto-tabel" src="<?= gambar('produk', $row['foto_produk']) ?>" alt="Foto <?= e($row['nama_produk']) ?>"></td>
        <td>
          <div class="fw-bold"><?= e($row['nama_produk']) ?></div>
          <div class="bantuan">Stok <?= (int) $row['stok'] ?></div>
        </td>
        <td><?= rupiah($row['harga']) ?></td>
        <td><?= e($row['nama_kategori'] ?? 'Tanpa kategori') ?></td>
        <td>
          <?= status_badge($row['status']) ?>
          <?php if ($row['status'] === ST_DITOLAK && $row['alasan_penolakan']): ?>
            <div class="bantuan mt-1" style="max-width:260px"><i class="bi bi-chat-left-text me-1"></i><?= e(potong($row['alasan_penolakan'], 90)) ?></div>
          <?php endif; ?>
        </td>
        <td class="bantuan"><?= tanggal_id($row['created_at']) ?></td>
        <td class="text-end text-nowrap">
          <a class="btn btn-garis btn-sm" href="<?= url('pemilik/produk-edit.php?id=' . (int) $row['id']) ?>" title="Edit produk"><i class="bi bi-pencil"></i></a>
          <button type="button" class="btn btn-outline-danger btn-sm tombol-hapus"
                  data-nama="<?= e($row['nama_produk']) ?>"
                  data-id="<?= (int) $row['id'] ?>"
                  data-aksi="<?= url('pemilik/produk.php') ?>" title="Hapus produk"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
