
CREATE DATABASE IF NOT EXISTS pengadaan_barang
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE pengadaan_barang;

DROP TABLE IF EXISTS approval_workflow;
DROP TABLE IF EXISTS notifikasi;
DROP TABLE IF EXISTS detail_barang;
DROP TABLE IF EXISTS pengajuan_barang;
DROP TABLE IF EXISTS laporan_pengadaan;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS users;


CREATE TABLE users (
  id          INT(11)      NOT NULL AUTO_INCREMENT,
  nama        VARCHAR(100) NOT NULL,
  email       VARCHAR(100) NOT NULL,
  password    VARCHAR(255) NOT NULL,
  role        ENUM('staff','approver','admin') NOT NULL DEFAULT 'staff',
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE barang (
  id          INT(11)      NOT NULL AUTO_INCREMENT,
  nama_barang VARCHAR(150) NOT NULL,
  satuan      VARCHAR(30)  NOT NULL,
  keterangan  TEXT         DEFAULT NULL,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pengajuan_barang (
  id                INT(11)   NOT NULL AUTO_INCREMENT,
  user_id           INT(11)   NOT NULL,
  tanggal_pengajuan DATE      NOT NULL,
  keterangan        TEXT      DEFAULT NULL,
  status            ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT fk_pengajuan_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE detail_barang (
  id           INT(11)      NOT NULL AUTO_INCREMENT,
  pengajuan_id INT(11)      NOT NULL,
  nama_barang  VARCHAR(150) NOT NULL,
  jumlah       INT(11)      NOT NULL DEFAULT 1,
  satuan       VARCHAR(30)  NOT NULL,
  keterangan   TEXT         DEFAULT NULL,
  PRIMARY KEY (id),
  KEY pengajuan_id (pengajuan_id),
  CONSTRAINT fk_detail_pengajuan FOREIGN KEY (pengajuan_id) REFERENCES pengajuan_barang (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE approval_workflow (
  id           INT(11)   NOT NULL AUTO_INCREMENT,
  pengajuan_id INT(11)   NOT NULL,
  approver_id  INT(11)   NOT NULL,
  status       ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  catatan      TEXT      DEFAULT NULL,
  tanggal_aksi DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY pengajuan_id (pengajuan_id),
  KEY approver_id (approver_id),
  CONSTRAINT fk_approval_pengajuan FOREIGN KEY (pengajuan_id) REFERENCES pengajuan_barang (id) ON DELETE CASCADE,
  CONSTRAINT fk_approval_user FOREIGN KEY (approver_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE notifikasi (
  id       INT(11)   NOT NULL AUTO_INCREMENT,
  user_id  INT(11)   NOT NULL,
  pesan    TEXT      NOT NULL,
  dibaca   TINYINT(1) NOT NULL DEFAULT 0,
  tanggal  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE laporan_pengadaan (
  id              INT(11)     NOT NULL AUTO_INCREMENT,
  periode         VARCHAR(20) NOT NULL,
  total_pengajuan INT(11)     NOT NULL DEFAULT 0,
  total_disetujui INT(11)     NOT NULL DEFAULT 0,
  total_ditolak   INT(11)     NOT NULL DEFAULT 0,
  generated_at    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================================
--  DATA AWAL (CONTOH)
--  Semua akun di bawah ini passwordnya:  password
-- =====================================================================

-- Akun pengguna. Hash di bawah adalah hasil enkripsi dari kata "password".
INSERT INTO users (nama, email, password, role) VALUES
('Administrator',        'admin@ypac.test',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Robiatul Adawiyah',    'approver@ypac.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'approver'),
('Staff Unit Produksi',  'staff@ypac.test',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff');

-- Master barang contoh
INSERT INTO barang (nama_barang, satuan, keterangan) VALUES
('Kain Katun Putih', 'Meter', 'Bahan baku menjahit'),
('Cat Akrilik',      'Buah',  'Untuk seni lukis'),
('Benang Wol',       'Gulung','Bahan kerajinan rajut'),
('Kertas Karton',    'Lembar','Bahan kerajinan tangan'),
('Lem Fox',          'Botol', 'Perlengkapan produksi');

-- Pengajuan contoh (dibuat oleh staff, id user = 3)
INSERT INTO pengajuan_barang (user_id, tanggal_pengajuan, keterangan, status) VALUES
(3, '2026-05-06', 'Kebutuhan bahan produksi bulan Mei', 'menunggu'),
(3, '2026-05-04', 'Kebutuhan seni lukis', 'disetujui'),
(3, '2026-04-29', 'Restok kertas karton', 'ditolak');

-- Detail barang untuk pengajuan di atas
INSERT INTO detail_barang (pengajuan_id, nama_barang, jumlah, satuan, keterangan) VALUES
(1, 'Kain Katun Putih', 3,  'Meter',  'Warna putih polos'),
(1, 'Cat Akrilik',      10, 'Buah',   'Aneka warna'),
(1, 'Benang Wol',       3,  'Gulung', '-'),
(2, 'Cat Akrilik',      5,  'Buah',   'Warna primer'),
(2, 'Kertas Karton',    20, 'Lembar', 'Ukuran A3'),
(3, 'Kertas Karton',    50, 'Lembar', 'Stok habis');

-- Riwayat approval untuk pengajuan yang sudah diputuskan (approver id = 2)
INSERT INTO approval_workflow (pengajuan_id, approver_id, status, catatan) VALUES
(2, 2, 'disetujui', 'Disetujui, silakan diproses.'),
(3, 2, 'ditolak',   'Anggaran belum tersedia bulan ini.');

-- Notifikasi contoh untuk staff (id = 3)
INSERT INTO notifikasi (user_id, pesan, dibaca) VALUES
(3, 'Pengajuan #PB-2026-002 telah DISETUJUI oleh Approver.', 0),
(3, 'Pengajuan #PB-2026-003 telah DITOLAK oleh Approver.', 1);

-- Rekap laporan contoh
INSERT INTO laporan_pengadaan (periode, total_pengajuan, total_disetujui, total_ditolak) VALUES
('Mei 2026', 3, 1, 1);
