<?php
/**
 * views/admin/master/categories.php — Kategori MoU
 */

require __DIR__ . '/../../layouts/header.php';
?>

<div class="page-body">

    <!-- Toolbar -->
    <div class="d-flex align-center justify-between mb-3">
        <div>
            <h3 style="font-size:1rem; font-weight:700;">
                <i class="fa-solid fa-layer-group" style="color:var(--primary);"></i>
                Kategori MoU
            </h3>
            <p class="text-muted fs-sm">Kelola referensi kategori yang digunakan pada dokumen MoU / MoA</p>
        </div>
        <?php if (has_role('superadmin')): ?>
        <button class="btn btn-primary btn-sm" onclick="openModal('modalAddCategory')">
            <i class="fa-solid fa-plus"></i> Tambah Kategori
        </button>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah MoU</th>
                    <?php if (has_role('superadmin')): ?><th>Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $i => $cat): ?>
                <tr>
                    <td style="color:var(--text-muted); font-size:.78rem;"><?= $i + 1 ?></td>
                    <td style="font-weight:600;"><?= e($cat['name']) ?></td>
                    <td style="color:var(--text-muted); font-size:.82rem;"><?= e($cat['description'] ?? '-') ?></td>
                    <td>
                        <span class="badge <?= (int)$cat['mou_count'] > 0 ? 'badge-active' : 'badge-secondary' ?>">
                            <?= (int)$cat['mou_count'] ?> MoU
                        </span>
                    </td>
                    <?php if (has_role('superadmin')): ?>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm"
                                    onclick="openEditCategory(<?= $cat['id'] ?>, '<?= e(addslashes($cat['name'])) ?>', '<?= e(addslashes($cat['description'] ?? '')) ?>')"
                                    title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <?php if ((int)$cat['mou_count'] === 0): ?>
                            <form method="POST" action="<?= APP_URL ?>/admin/master/categories/<?= $cat['id'] ?>/delete"
                                  id="deleteCategory<?= $cat['id'] ?>">
                                <?= csrf_field() ?>
                                <button type="button" class="btn btn-danger btn-sm" title="Hapus"
                                        onclick="confirmDeleteCategory(<?= $cat['id'] ?>, '<?= e(addslashes($cat['name'])) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <button class="btn btn-outline btn-sm" disabled title="Tidak bisa dihapus - sedang digunakan">
                                <i class="fa-solid fa-lock" style="color:var(--text-muted);"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fa-solid fa-layer-group"></i>
                            <h3>Belum ada kategori</h3>
                            <p>Tambahkan kategori MoU untuk mulai mengorganisasi dokumen kerjasama</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (has_role('superadmin')): ?>
<!-- ── Modal Tambah Kategori ─────────────────────────────────────── -->
<div class="modal-overlay" id="modalAddCategory">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-plus" style="color:var(--primary);"></i> Tambah Kategori MoU</div>
            <button class="modal-close" onclick="closeModal('modalAddCategory')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/admin/master/categories/create" onsubmit="startProgressBar()">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Kategori <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="mis. Pendidikan & Penelitian" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Deskripsi singkat kategori ini…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAddCategory')">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Modal Edit Kategori ───────────────────────────────────────── -->
<div class="modal-overlay" id="modalEditCategory">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-pen-to-square" style="color:var(--primary);"></i> Edit Kategori MoU</div>
            <button class="modal-close" onclick="closeModal('modalEditCategory')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" id="formEditCategory" onsubmit="startProgressBar()">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Kategori <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" id="editCatName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="editCatDesc" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditCategory')">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
$extraScript = <<<'JS'
function openEditCategory(id, name, desc) {
    document.getElementById('editCatName').value = name;
    document.getElementById('editCatDesc').value = desc;
    document.getElementById('formEditCategory').action =
        window._APP_URL + '/admin/master/categories/' + id + '/edit';
    openModal('modalEditCategory');
}

function confirmDeleteCategory(id, name) {
    showConfirmModal(
        'Yakin ingin menghapus kategori "' + name + '"? Tindakan ini tidak dapat dibatalkan.',
        'Hapus Kategori',
        function() {
            document.getElementById('deleteCategory' + id).submit();
        }
    );
}
JS;

require __DIR__ . '/../../layouts/footer.php';
?>
