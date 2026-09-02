<?php
/**
 * views/admin/users.php — Manage Administrator Users
 */

require __DIR__ . '/../layouts/header.php';
?>

<div class="page-body">

    <div class="d-flex align-center justify-between mb-3">
        <div>
            <h3 style="font-size:1rem; font-weight:700;">
                <i class="fa-solid fa-users-gear" style="color:var(--primary);"></i>
                Manajemen Pengguna System
            </h3>
            <p class="text-muted fs-sm"><?= count($users) ?> pengguna terdaftar</p>
        </div>
        <button class="btn btn-primary btn-sm" onclick="openModal('modalCreateUser')">
            <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
        </button>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pengguna</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tanggal Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $i => $u): ?>
                <tr>
                    <td style="color:var(--text-subtle); font-size:.78rem;"><?= $i + 1 ?></td>
                    <td>
                        <div style="font-weight:600; color:var(--text-main);"><?= e($u['name']) ?></div>
                        <div style="font-size:.72rem; color:var(--text-muted);">@<?= e($u['username']) ?></div>
                    </td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <span class="badge <?= $u['role'] === 'superadmin' ? 'badge-primary' : 'badge-secondary' ?>">
                            <?= e($u['role']) ?>
                        </span>
                    </td>
                    <td><?= format_date_id($u['created_at'], 'medium') ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm"
                                    onclick="openEditUserModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)"
                                    title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <?php if ($u['id'] !== current_user()['id']): ?>
                            <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/delete"
                                  onsubmit="return confirm('Hapus pengguna <?= e(addslashes($u['username'])) ?>?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Modal Create User -->
<div class="modal-overlay" id="modalCreateUser">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-user-plus"></i> Tambah Pengguna Baru</div>
            <button class="modal-close" onclick="closeModal('modalCreateUser')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/admin/users/create" onsubmit="return validatePasswordMatch(this)">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="form-required">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Nama administrator">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username <span class="form-required">*</span></label>
                        <input type="text" name="username" class="form-control" required placeholder="username">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control">
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="form-required">*</span></label>
                    <input type="email" name="email" class="form-control" required placeholder="email@rsudkilisuci.kedirikota.go.id">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password <span class="form-required">*</span></label>
                        <input type="password" name="password" class="form-control" required placeholder="Password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ulangi Password <span class="form-required">*</span></label>
                        <input type="password" name="password_confirm" class="form-control" required placeholder="Ulangi password">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalCreateUser')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal-overlay" id="modalEditUser">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-pen-to-square"></i> Edit Pengguna</div>
            <button class="modal-close" onclick="closeModal('modalEditUser')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" id="editUserForm" action="" onsubmit="return validatePasswordMatch(this)">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="form-required">*</span></label>
                    <input type="text" name="name" id="editUserName" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email <span class="form-required">*</span></label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select name="role" id="editUserRole" class="form-control">
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password Baru (opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Password baru">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ulangi Password Baru</label>
                        <input type="password" name="password_confirm" class="form-control" placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="form-text">Kosongkan kolom password jika tidak ingin mengubah password user.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditUser')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
$appUrl = APP_URL;
$extraScript = <<<JS
function openEditUserModal(u) {
    document.getElementById('editUserForm').action = '{$appUrl}/admin/users/' + u.id + '/edit';
    document.getElementById('editUserName').value  = u.name || '';
    document.getElementById('editUserEmail').value = u.email || '';
    const rSel = document.getElementById('editUserRole');
    for (let opt of rSel.options) {
        opt.selected = opt.value === u.role;
    }
    openModal('modalEditUser');
}

function validatePasswordMatch(form) {
    const p1 = form.querySelector('[name="password"]').value;
    const p2 = form.querySelector('[name="password_confirm"]').value;
    if (p1 !== p2) {
        showToast('Konfirmasi password tidak cocok dengan password baru!', 'error');
        return false;
    }
    return true;
}
JS;
require __DIR__ . '/../layouts/footer.php';
?>
