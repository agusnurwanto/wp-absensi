CREATE TABLE `absensi_data_unit` (
  `id` int(11) NOT NULL auto_increment,
  `id_setup_unit` int(11) DEFAULT NULL,
  `id_unit` int(11) DEFAULT NULL,
  `is_skpd` tinyint(4) DEFAULT NULL,
  `kode_skpd` varchar(50) DEFAULT NULL,
  `kunci_skpd` int(11) DEFAULT NULL,
  `nama_skpd` text DEFAULT NULL,
  `posisi` varchar(30) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `id_skpd` int(11) DEFAULT NULL,
  `bidur_1` smallint(6) DEFAULT NULL,
  `bidur_2` smallint(6) DEFAULT NULL,
  `bidur_3` smallint(6) DEFAULT NULL,
  `idinduk` int(11) DEFAULT NULL,
  `ispendapatan` tinyint(4) DEFAULT NULL,
  `isskpd` tinyint(4) DEFAULT NULL,
  `kode_skpd_1` varchar(10) DEFAULT NULL,
  `kode_skpd_2` varchar(10) DEFAULT NULL,
  `kodeunit` varchar(30) DEFAULT NULL,
  `komisi` int(11) DEFAULT NULL,
  `namabendahara` text,
  `namakepala` text DEFAULT NULL,
  `namaunit` text DEFAULT NULL,
  `nipbendahara` varchar(30) DEFAULT NULL,
  `nipkepala` varchar(30) DEFAULT NULL,
  `pangkatkepala` varchar(50) DEFAULT NULL,
  `setupunit` int(11) DEFAULT NULL,
  `statuskepala` varchar(20) DEFAULT NULL,
  `mapping` varchar(10) DEFAULT NULL,
  `id_kecamatan` int(11) DEFAULT NULL,
  `id_strategi` int(11) DEFAULT NULL,
  `is_dpa_khusus` tinyint(4) DEFAULT NULL,
  `is_ppkd` tinyint(4) DEFAULT NULL,
  `set_input` tinyint(4) DEFAULT NULL,
  `update_at` datetime DEFAULT NULL,
  `tahun_anggaran` year(4) NOT NULL DEFAULT '2021',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE `absensi_data_pegawai` (
  `id` int(11) NOT NULL auto_increment,
  `nik` varchar(20) DEFAULT NULL,
  `nama` text DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `tempat_lahir` text DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` varchar(2) DEFAULT NULL COMMENT 'L=Laki-laki, P=Perempuan',
  `agama` text DEFAULT NULL,
  `no_hp` varchar(50) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `pendidikan_terakhir` text DEFAULT NULL,
  `pendidikan_sekarang` text DEFAULT NULL,
  `nama_sekolah` text DEFAULT NULL,
  `lulus` year(4) DEFAULT NULL,
  `email` text DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_instansi` int(11) DEFAULT NULL,
  `user_role` text DEFAULT NULL COMMENT 'kepala dan pegawai',
  `status_kerja` int(11) DEFAULT NULL COMMENT '0=Tidak Aktif, 1=Aktif',
  `tahun` year(4) DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `active` tinyint(4) DEFAULT 1,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE `absensi_data` (
  `id` int(11) NOT NULL auto_increment,
  `tahun_anggaran` year(4) DEFAULT 2023,
  `id_skpd` int(11) NOT NULL,
  `jml_peg` int(11) DEFAULT NULL,
  `jml_jam` int(11) DEFAULT NULL,
  `uang_makan` double DEFAULT NULL,
  `uang_lembur` double DEFAULT NULL,
  `jml_pajak` double DEFAULT NULL,
  `total_nilai` double DEFAULT NULL,
  `ket_lembur` text DEFAULT NULL,
  `user` text DEFAULT NULL,
  `waktu_mulai_spt` date NOT NULL,
  `waktu_selesai_spt` date NOT NULL,
  `jml_hari` int(11) DEFAULT NULL,
  `dasar_lembur` text DEFAULT NULL,
  `file_lampiran` text DEFAULT NULL,
  `status` tinyint(4) DEFAULT 0 COMMENT '0=belum diverifikasi, 1=disetujui admin, 2=selesai',
  `lat` text DEFAULT NULL,
  `lng` text DEFAULT NULL,
  `status_ver_admin` tinyint(4) DEFAULT NULL COMMENT '0=ditolak, 1=disetujui',
  `ket_ver_admin` text DEFAULT NULL,
  `jenis_user` varchar(50) DEFAULT NULL,
  `update_user` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `update_at` datetime NOT NULL,
  `active` tinyint(4) DEFAULT '1' COMMENT '0=hapus, 1=aktif',
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE `absensi_data_detail` (
  `id` int(11) NOT NULL auto_increment,
  `id_spt` int(11) NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `id_standar_harga_lembur` int(11) NOT NULL,
  `id_standar_harga_makan` int(11) NOT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_akhir` datetime DEFAULT NULL,
  `waktu_mulai_hadir` datetime DEFAULT NULL,
  `waktu_akhir_hadir` datetime DEFAULT NULL,
  `tipe_hari` enum('1','2') DEFAULT '1' COMMENT '1=hari libur, 2=hari kerja',
  `keterangan` text DEFAULT NULL,
  `file_lampiran` text DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `active` tinyint(4) DEFAULT 1 COMMENT '0=hapus, 1=aktif',
  `uang_lembur` double DEFAULT NULL,
  `uang_makan` double DEFAULT NULL,
  `jml_hari` int(11) DEFAULT NULL,
  `jml_jam` int(11) DEFAULT NULL,
  `jml_pajak` double DEFAULT NULL,
  `jenis_user` varchar(50) DEFAULT NULL,
  `update_user` text DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE `absensi_data_rekening_akun` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_akun` VARCHAR(64) DEFAULT NULL,
  `kode_akun` VARCHAR(64) DEFAULT NULL,
  `nama_akun` TEXT DEFAULT NULL,
  `tahun_anggaran` year(4) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_akun` (`id_akun`),
  KEY `kode_akun` (`kode_akun`),
  KEY `tahun_anggaran` (`tahun_anggaran`),
  KEY `active` (`active`)
);

CREATE TABLE `absensi_data_satuan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_satuan` VARCHAR(64) DEFAULT NULL,
  `nama_satuan` varchar(64) DEFAULT NULL,
  `tahun_anggaran` year(4) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_satuan` (`id_satuan`),
  KEY `tahun_anggaran` (`tahun_anggaran`),
  KEY `active` (`active`)
);

CREATE TABLE `absensi_data_instansi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_instansi` VARCHAR(64) DEFAULT NULL,
  `alamat_instansi` varchar(64) DEFAULT NULL,
  `username` VARCHAR(64) DEFAULT NULL,
  `email_instansi` TEXT DEFAULT NULL,
  `id_user` INT(11) DEFAULT NULL,
  `tahun` year(4) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `update_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tahun` (`tahun`),
  KEY `active` (`active`)
);

CREATE TABLE `absensi_data_kerja` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_instansi` INT(11) NOT NULL,
  `jenis` VARCHAR(20) DEFAULT 'Primary',
  `nama_kerja` VARCHAR(100) DEFAULT NULL,
  `jam_masuk` TEXT DEFAULT NULL,
  `jam_pulang` TEXT DEFAULT NULL,
  `hari_kerja` TEXT DEFAULT NULL,
  `koordinat` VARCHAR(100) DEFAULT NULL,
  `radius_meter` INT(11) DEFAULT 100,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `update_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_instansi` (`id_instansi`),
  KEY `active` (`active`)
);

CREATE TABLE `absensi_harian` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pegawai` INT(11) NOT NULL,
  `id_instansi` INT(11) NOT NULL,
  `id_kode_kerja` INT(11) NOT NULL,
  `tanggal` DATE NOT NULL,
  `waktu_masuk` DATETIME DEFAULT NULL,
  `waktu_pulang` DATETIME DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT 'Alpha' COMMENT 'Hadir/Telat/Ijin/Sakit/Alpha',
  `koordinat_masuk` VARCHAR(100) DEFAULT NULL,
  `koordinat_pulang` VARCHAR(100) DEFAULT NULL,
  `foto_masuk` TEXT DEFAULT NULL,
  `foto_pulang` TEXT DEFAULT NULL,
  `tahun` YEAR(4) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `update_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pegawai` (`id_pegawai`),
  KEY `id_instansi` (`id_instansi`),
  KEY `tanggal` (`tanggal`)
);

CREATE TABLE `absensi_ijin` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pegawai` INT(11) NOT NULL,
  `id_instansi` INT(11) NOT NULL,
  `tipe_ijin` VARCHAR(50) DEFAULT NULL,
  `jenis_ijin` VARCHAR(50) DEFAULT NULL,
  `alasan` TEXT DEFAULT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `file_lampiran` TEXT DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT 'Pending' COMMENT 'Pending/Approved/Rejected',
  `tahun` YEAR(4) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `update_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pegawai` (`id_pegawai`),
  KEY `id_instansi` (`id_instansi`)
);

CREATE TABLE `absensi_kegiatan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_instansi` INT(11) NOT NULL,
  `id_pegawai` INT(11) NOT NULL DEFAULT 0,
  `nama_kegiatan` VARCHAR(200) DEFAULT NULL,
  `tanggal` DATE DEFAULT NULL,
  `jam_mulai` TIME DEFAULT NULL,
  `jam_selesai` TIME DEFAULT NULL,
  `tempat` TEXT DEFAULT NULL,
  `uraian` TEXT DEFAULT NULL,
  `file_lampiran` TEXT DEFAULT NULL,
  `tahun` YEAR(4) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_instansi` (`id_instansi`),
  KEY `active` (`active`)
);

-- Migration: Add deleted_at column to existing tables
ALTER TABLE `absensi_data_unit` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;
ALTER TABLE `absensi_data_pegawai` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;
ALTER TABLE `absensi_data` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;
ALTER TABLE `absensi_data_detail` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;
ALTER TABLE `absensi_data_rekening_akun` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;
ALTER TABLE `absensi_data_satuan` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;
ALTER TABLE `absensi_data_instansi` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;
ALTER TABLE `absensi_data_kerja` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;
ALTER TABLE `absensi_harian` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;
ALTER TABLE `absensi_ijin` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;
ALTER TABLE `absensi_kegiatan` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL;

-- Year Management Table
CREATE TABLE `absensi_tahun` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tahun` YEAR(4) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tahun` (`tahun`),
  KEY `active` (`active`)
);
