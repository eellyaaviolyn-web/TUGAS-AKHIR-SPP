-- Database: `spp_new`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

-- Struktur tabel users (unified login system)
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas','siswa') NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Struktur tabel kelas
CREATE TABLE `kelas` (
  `id_kelas` int(11) NOT NULL,
  `nama_kelas` varchar(15) NOT NULL,
  `kompetensi_keahlian` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Struktur tabel spp
CREATE TABLE `spp` (
  `id_spp` int(11) NOT NULL,
  `tahun` year(4) NOT NULL,
  `nominal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Struktur tabel siswa (linked to users)
CREATE TABLE `siswa` (
  `nisn` varchar(10) NOT NULL,
  `nis` varchar(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `alamat` text,
  `no_telp` varchar(13) DEFAULT NULL,
  `id_spp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Struktur tabel petugas (linked to users)
CREATE TABLE `petugas` (
  `id_petugas` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_petugas` varchar(50) NOT NULL,
  `nip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Struktur tabel pembayaran (updated with upload feature)
CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `nisn` varchar(10) NOT NULL,
  `bulan_dibayar` varchar(10) NOT NULL,
  `tahun_dibayar` varchar(4) NOT NULL,
  `id_spp` int(11) NOT NULL,
  `jumlah_bayar` int(11) NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `tgl_upload` timestamp DEFAULT CURRENT_TIMESTAMP,
  `tgl_verifikasi` timestamp NULL DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Insert sample data
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'admin', 'admin@spp.com', '$2y$10$fzafzw6hYOCoLrT8z9Ay3eZ85q0GrtcrQQffmqRR/UolP1WWK/Xiu', 'admin'),
(2, 'petugas', 'petugas@spp.com', '$2y$10$Sw3dRzwEaVnDcjt5/.a/TeXt1QKXbCYE3PSAB3btD6y22SQ97gIyO', 'petugas'),
(3, '321', 'rifki@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa'),
(4, '123', 'ahmad@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa');

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `kompetensi_keahlian`) VALUES
(15, 'XII RPL 4', 'Rekayasa Perangkat Lunak'),
(18, 'XII RPL 5', 'Rekayasa Perangkat Lunak'),
(19, 'XII RPL 3', 'Rekayasa Perangkat Lunak'),
(20, 'XII TKR 2', 'Teknik Kendaraan Ringan'),
(24, 'XII AK 2', 'Akuntansi');

INSERT INTO `spp` (`id_spp`, `tahun`, `nominal`) VALUES
(10, 2024, 150000),
(11, 2025, 175000),
(13, 2023, 125000);

INSERT INTO `petugas` (`id_petugas`, `user_id`, `nama_petugas`, `nip`) VALUES
(1, 1, 'Administrator', 'ADM001'),
(2, 2, 'Petugas SPP', 'PTG001');

INSERT INTO `siswa` (`nisn`, `nis`, `user_id`, `nama`, `id_kelas`, `alamat`, `no_telp`, `id_spp`) VALUES
('1234567890', '321', 3, 'Rifki Ahmad', 18, 'Bandung Barat', '081234567890', 10),
('0987654321', '123', 4, 'Ahmad Rifki Fahrezi', 20, 'Bandung Timur', '081234567891', 10);

-- --------------------------------------------------------

-- Indexes and constraints
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`);

ALTER TABLE `spp`
  ADD PRIMARY KEY (`id_spp`);

ALTER TABLE `siswa`
  ADD PRIMARY KEY (`nisn`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `id_kelas` (`id_kelas`),
  ADD KEY `id_spp` (`id_spp`);

ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `nisn` (`nisn`),
  ADD KEY `id_spp` (`id_spp`),
  ADD KEY `verified_by` (`verified_by`);

-- Auto increment
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `kelas`
  MODIFY `id_kelas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

ALTER TABLE `spp`
  MODIFY `id_spp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

ALTER TABLE `petugas`
  MODIFY `id_petugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT;

-- Foreign key constraints
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_3` FOREIGN KEY (`id_spp`) REFERENCES `spp` (`id_spp`) ON DELETE CASCADE;

ALTER TABLE `petugas`
  ADD CONSTRAINT `petugas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`nisn`) REFERENCES `siswa` (`nisn`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`id_spp`) REFERENCES `spp` (`id_spp`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembayaran_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;