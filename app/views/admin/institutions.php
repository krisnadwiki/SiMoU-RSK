<?php
/**
 * views/admin/institutions.php — Manage Partner Institutions
 */

require __DIR__ . '/../layouts/header.php';

$catOptions = [
    'Pendidikan',
    'Kesehatan',
    'Pemerintah',
    'BUMN/BUMD',
    'Swasta',
    'Organisasi/Asosiasi',
    'Keuangan',
    'Profesional',
    'Internasional',
    'Lainnya'
];

$catBadges = [
    'Pendidikan'          => 'badge-primary',
    'Kesehatan'           => 'badge-success',
    'Pemerintah'          => 'badge-info',
    'BUMN/BUMD'           => 'badge-warning',
    'Swasta'              => 'badge-accent',
    'Organisasi/Asosiasi' => 'badge-expiring',
    'Keuangan'            => 'badge-success',
    'Profesional'         => 'badge-primary',
    'Internasional'       => 'badge-moa',
    'Lainnya'             => 'badge-outline',
];
?>

<div class="page-body">

    <div class="d-flex align-center justify-between mb-3">
        <div>
            <h3 style="font-size:1rem; font-weight:700;">
                <i class="fa-solid fa-building" style="color:var(--primary);"></i>
                Institusi &amp; Mitra Kerjasama
            </h3>
            <p class="text-muted fs-sm">Total <?= $total ?? count($institutions) ?> institusi terdaftar</p>
        </div>
        <button class="btn btn-primary btn-sm" onclick="openModal('modalCreate')">
            <i class="fa-solid fa-plus"></i> Tambah Institusi
        </button>
    </div>

    <!-- Search & Filter bar -->
    <form method="GET" action="<?= APP_URL ?>/admin/institutions" class="filter-bar mb-3">
        <div class="search-box flex-1">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" placeholder="Cari nama, kontak, atau email…"
                   value="<?= e($search ?? '') ?>">
        </div>
        <select name="category" class="form-control" style="max-width:210px;" onchange="this.form.submit()">
            <option value="">-- Semua Kategori --</option>
            <?php foreach ($catOptions as $c): ?>
            <option value="<?= $c ?>" <?= ($category ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filter</button>
        <a href="<?= APP_URL ?>/admin/institutions" class="btn btn-outline btn-sm">Reset</a>
    </form>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Institusi</th>
                    <th>Kategori</th>
                    <th>Kontak</th>
                    <th>Email / Telepon</th>
                    <th>Dok. MoU</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($institutions as $i => $inst): 
                $badgeClass = $catBadges[$inst['category']] ?? 'badge-primary';
            ?>
                <tr>
                    <td style="color:var(--text-subtle); font-size:.78rem;"><?= ($pag['offset'] ?? 0) + $i + 1 ?></td>
                    <td>
                        <div style="font-weight:600; color:var(--text-main);"><?= e($inst['name']) ?></div>
                        <?php if ($inst['address']): ?>
                        <div style="font-size:.72rem; color:var(--text-muted);">
                            <i class="fa-solid fa-location-dot"></i> <?= e(mb_strimwidth($inst['address'], 0, 50, '…')) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge <?= $badgeClass ?>" style="font-size:.68rem; font-weight:600;"><?= e($inst['category']) ?></span></td>
                    <td><?= e($inst['contact_person'] ?: '-') ?></td>
                    <td>
                        <?php if ($inst['email']): ?>
                        <div style="font-size:.78rem;"><i class="fa-solid fa-envelope"></i> <?= e($inst['email']) ?></div>
                        <?php endif; ?>
                        <?php if ($inst['phone']): ?>
                        <div style="font-size:.78rem;"><i class="fa-solid fa-phone"></i> <?= e($inst['phone']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-primary"><?= (int)$inst['mou_count'] ?> dok.</span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm"
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($inst), ENT_QUOTES) ?>)"
                                    title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <?php if (has_role('superadmin') && (int)$inst['mou_count'] === 0): ?>
                            <form method="POST" action="<?= APP_URL ?>/admin/institutions/<?= $inst['id'] ?>/delete"
                                  id="deleteInst<?= $inst['id'] ?>">
                                <?= csrf_field() ?>
                                <button type="button" class="btn btn-danger btn-sm" title="Hapus"
                                        onclick="confirmDeleteInst(<?= $inst['id'] ?>, '<?= e(addslashes($inst['name'])) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($institutions)): ?>
                <tr><td colspan="7"><div class="empty-state">
                    <i class="fa-solid fa-building"></i>
                    <h3>Belum ada institusi terdaftar</h3>
                </div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if (($pag['total_pages'] ?? 1) > 1): ?>
        <div class="pagination">
            <span>Halaman <?= $pag['current_page'] ?> dari <?= $pag['total_pages'] ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Create Institusi -->
<div class="modal-overlay" id="modalCreate">
    <div class="modal" style="max-width:580px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-building"></i> Tambah Institusi Baru</div>
            <button class="modal-close" onclick="closeModal('modalCreate')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/admin/institutions/create">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Nama Institusi <span class="form-required">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Nama lengkap institusi">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-control">
                            <?php foreach ($catOptions as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kontak Person</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="Nama penanggung jawab">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@institusi.go.id">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" class="form-control" placeholder="0354-xxxxxx">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap institusi"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalCreate')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Institusi -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal" style="max-width:580px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-pen-to-square"></i> Edit Institusi</div>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" id="editForm" action="">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Nama Institusi <span class="form-required">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="category" id="editCategory" class="form-control">
                            <?php foreach ($catOptions as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kontak Person</label>
                        <input type="text" name="contact_person" id="editContact" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" id="editPhone" class="form-control">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="editAddress" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
$appUrl = APP_URL;
$extraScript = <<<JS
function openEditModal(inst) {
    document.getElementById('editForm').action = '{$appUrl}/admin/institutions/' + inst.id + '/edit';
    document.getElementById('editName').value     = inst.name || '';
    document.getElementById('editContact').value  = inst.contact_person || '';
    document.getElementById('editEmail').value    = inst.email || '';
    document.getElementById('editPhone').value    = inst.phone || '';
    document.getElementById('editAddress').value  = inst.address || '';
    const catSel = document.getElementById('editCategory');
    for (let opt of catSel.options) {
        opt.selected = opt.value === inst.category;
    }
    openModal('modalEdit');
}
function confirmDeleteInst(id, name) {
    showConfirmModal(
        'Yakin ingin menghapus institusi "' + name + '"? Tindakan ini tidak dapat dibatalkan.',
        'Hapus Institusi',
        function() { document.getElementById('deleteInst' + id).submit(); }
    );
}
JS;
require __DIR__ . '/../layouts/footer.php';
?>
