-- ============================================================
--  SiMoU — Sistem Informasi MoU / MoA
--  RSUD Kilisuci — Kota Kediri
--  Database Initialization Script (Clean Production Ready)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `simou_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `simou_db`;

-- ────────────────────────────────────────────────────────────
-- 1. Tabel users — Admin & Superadmin
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(50)  NOT NULL,
    `password`   VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(100) NOT NULL,
    `role`       ENUM('admin','superadmin') NOT NULL DEFAULT 'admin',
    `is_active`  TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deactivated',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_username` (`username`),
    UNIQUE KEY `uq_email`    (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 2. Tabel institutions — Partner / Mitra
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `institutions` (
    `id`             INT          NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(150) NOT NULL,
    `category`       VARCHAR(80)  NOT NULL DEFAULT 'Pendidikan'
                     COMMENT 'Pendidikan|Kesehatan|Pemerintah|BUMN/BUMD|Swasta|Organisasi/Asosiasi|Keuangan|Profesional|Internasional|Lainnya',
    `address`        TEXT,
    `contact_person` VARCHAR(100),
    `phone`          VARCHAR(30),
    `email`          VARCHAR(100),
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 3. Tabel categories — Kategori MoU
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
    `id`          INT          NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `description` TEXT,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 4. Tabel units — Unit Kerja Internal RSUD Kilisuci
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `units` (
    `id`   INT         NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 5. Tabel mous — Dokumen MoU / MoA Utama (ID = UUID CHAR(36))
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mous` (
    `id`               CHAR(36)     NOT NULL,
    `mou_number`       VARCHAR(100) NOT NULL COMMENT 'Nomor surat RSUD Kilisuci',
    `mou_number_mitra` VARCHAR(100)     NULL DEFAULT NULL COMMENT 'Nomor surat dari Mitra/Institusi',
    `title`            VARCHAR(255) NOT NULL,
    `institution_id` INT          NOT NULL,
    `category_id`    INT          NOT NULL,
    `unit_id`        INT              NULL DEFAULT NULL,
    `doc_type`       ENUM('MoU','MoA') NOT NULL DEFAULT 'MoU',
    `start_date`     DATE         NOT NULL,
    `end_date`       DATE         NOT NULL,
    `status`         ENUM('active','expiring_soon','expired','terminated')
                     NOT NULL DEFAULT 'active',
    `reminder_days`  INT          NOT NULL DEFAULT 60 COMMENT 'Pengingat H-berapa (30, 60, 90, 120)',
    `description`    TEXT,
    `file_path`      VARCHAR(255)     NULL DEFAULT NULL,
    `created_by`     INT              NULL DEFAULT NULL,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                     ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_mou_number` (`mou_number`),
    KEY `idx_status`     (`status`),
    KEY `idx_end_date`   (`end_date`),
    CONSTRAINT `fk_mou_institution`
        FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_mou_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
        ON DELETE RESTRICT,
    CONSTRAINT `fk_mou_unit`
        FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 6. Tabel mou_renewals — Histori Perpanjangan
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mou_renewals` (
    `id`             INT          NOT NULL AUTO_INCREMENT,
    `mou_id`         CHAR(36)     NOT NULL,
    `renewal_number` VARCHAR(100) NOT NULL,
    `new_start_date` DATE         NOT NULL,
    `new_end_date`   DATE         NOT NULL,
    `document_path`  VARCHAR(255)     NULL DEFAULT NULL,
    `notes`          TEXT,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_renewal_mou`
        FOREIGN KEY (`mou_id`) REFERENCES `mous`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 7. Tabel activity_logs — Audit Trail
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id`          INT          NOT NULL AUTO_INCREMENT,
    `user_id`     INT              NULL DEFAULT NULL,
    `action`      VARCHAR(50)  NOT NULL,
    `description` TEXT         NOT NULL,
    `ip_address`  VARCHAR(45)  NOT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id`   (`user_id`),
    KEY `idx_action`    (`action`),
    KEY `idx_created_at`(`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  DATA MASTER INSIAL (PRODUKSI)
-- ============================================================

-- Default superadmin (password: password123)
INSERT INTO `users` (`username`, `password`, `name`, `email`, `role`)
VALUES ('admin',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Administrator RSUD Kilisuci',
        'admin@rsudkilisuci.kedirikota.go.id',
        'superadmin')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Kategori MoU
INSERT INTO `categories` (`name`, `description`) VALUES
('Pendidikan & Penelitian',  'Kerjasama bidang pendidikan, magang, dan penelitian akademik'),
('Pelayanan Kesehatan',      'Kerjasama rujukan, pelayanan, dan fasilitasi kesehatan'),
('Pengadaan & Logistik',     'Kerjasama pengadaan barang, alat kesehatan, dan logistik'),
('Teknologi Informasi',      'Kerjasama pengembangan sistem informasi dan infrastruktur TI'),
('SDM & Pelatihan',          'Kerjasama peningkatan kompetensi sumber daya manusia'),
('Lainnya',                  'Kategori kerjasama umum / tidak terklasifikasi')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Unit Kerja Internal
INSERT INTO `units` (`name`) VALUES
('Instalasi Gawat Darurat (IGD)'),
('Instalasi Rawat Inap'),
('Instalasi Rawat Jalan'),
('Instalasi Bedah Sentral'),
('Instalasi Radiologi'),
('Instalasi Laboratorium'),
('Instalasi Farmasi'),
('Instalasi Gizi'),
('Bagian Umum & Kepegawaian'),
('Bagian Keuangan & Anggaran'),
('Bagian Perencanaan & Evaluasi'),
('Bidang Pelayanan Medis'),
('Bidang Keperawatan'),
('Bidang Penunjang Medis'),
('ICT / Teknologi Informasi'),
('Komite Mutu & Keselamatan Pasien'),
('Komite Medik'),
('Komite Keperawatan')
ON DUPLICATE KEY UPDATE `id` = `id`;
