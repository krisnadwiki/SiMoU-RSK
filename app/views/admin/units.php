<?php
/**
 * views/admin/units.php — Manage Internal Units
 */

require __DIR__ . '/../layouts/header.php';
?>

<div class="page-body">

    <div class="d-flex align-center justify-between mb-3">
        <div>
            <h3 style="font-size:1rem; font-weight:700;">
                <i class="fa-solid fa-sitemap" style="color:var(--primary);"></i>
                Unit Kerja Internal RSUD Kilisuci
            </h3>
            <p class="text-muted fs-sm"><?= count($units) ?> unit kerja terdaftar</p>
        </div>
        <button class="btn btn-primary btn-sm" onclick="openModal('modalCreate')">
            <i class="fa-solid fa-plus"></i> Tambah Unit
        </button>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Unit Kerja Internal</th>
                    <th>Jumlah Dokumen MoU</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($units as $i => $unit): ?>
                <tr>
                    <td style="color:var(--text-subtle); font-size:.78rem;"><?= $i + 1 ?></td>
                    <td>
                        <div style="font-weight:600; color:var(--text-main); display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-hospital" style="color:var(--primary);"></i>
                            <?= e($unit['name']) ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= (int)$unit['mou_count'] > 0 ? 'badge-primary' : 'badge-secondary' ?>">
                            <?= (int)$unit['mou_count'] ?> MoU
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm"
                                    onclick="openEditModal(<?= $unit['id'] ?>, '<?= e(addslashes($unit['name'])) ?>')"
                                    title="Edit Unit Kerja">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <?php if (has_role('superadmin') && (int)$unit['mou_count'] === 0): ?>
                            <form method="POST" action="<?= APP_URL ?>/admin/units/<?= $unit['id'] ?>/delete"
                                  id="deleteUnit<?= $unit['id'] ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="button" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:rgba(220,38,38,.2);" title="Hapus Unit Kerja"
                                        onclick="confirmDeleteUnit(<?= $unit['id'] ?>, '<?= e(addslashes($unit['name'])) ?>')">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </button>
                            </form>
                            <?php elseif (has_role('superadmin')): ?>
                            <button class="btn btn-outline btn-sm" disabled style="opacity:.4; cursor:not-allowed;" title="Tidak dapat dihapus - sedang digunakan oleh dokumen MoU">
                                <i class="fa-solid fa-lock"></i> Terkunci
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($units)): ?>
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <i class="fa-solid fa-sitemap"></i>
                            <h3>Belum ada unit kerja terdaftar</h3>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create -->
<div class="modal-overlay" id="modalCreate">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-hospital"></i> Tambah Unit Kerja</div>
            <button class="modal-close" onclick="closeModal('modalCreate')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/admin/units/create">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Unit Kerja <span class="form-required">*</span></label>
                    <input type="text" name="name" class="form-control" required
                           placeholder="Contoh: Instalasi Gawat Darurat (IGD)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalCreate')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-pen-to-square"></i> Edit Unit Kerja</div>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" id="editUnitForm" action="">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Unit Kerja <span class="form-required">*</span></label>
                    <input type="text" name="name" id="editUnitName" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php
$appUrl = APP_URL;
$extraScript = <<<JS
function openEditModal(id, name) {
    document.getElementById('editUnitForm').action = '{$appUrl}/admin/units/' + id + '/edit';
    document.getElementById('editUnitName').value  = name;
    openModal('modalEdit');
}
function confirmDeleteUnit(id, name) {
    showConfirmModal(
        'Yakin ingin menghapus unit kerja "' + name + '"? Tindakan ini tidak dapat dibatalkan.',
        'Hapus Unit Kerja',
        function() { document.getElementById('deleteUnit' + id).submit(); }
    );
}
JS;
require __DIR__ . '/../layouts/footer.php';
?>
