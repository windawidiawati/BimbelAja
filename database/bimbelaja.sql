-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Jul 2025 pada 04.13
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bimbelaja`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `forum`
--

CREATE TABLE `forum` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `judul` varchar(100) DEFAULT NULL,
  `isi` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` enum('siswa','tutor') NOT NULL DEFAULT 'siswa',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `forum`
--

INSERT INTO `forum` (`id`, `parent_id`, `judul`, `isi`, `user_id`, `role`, `created_at`) VALUES
(1, NULL, 'Paragraf', 'fddddddddddddddddd', 2, 'tutor', '2025-07-10 14:32:51'),
(2, 1, 'Re: Paragraf', 'dddddddddd', 2, 'tutor', '2025-07-10 14:32:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jawaban_siswa`
--

CREATE TABLE `jawaban_siswa` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `soal_id` int(11) NOT NULL,
  `jawaban_dipilih` varchar(1) NOT NULL,
  `benar` tinyint(1) NOT NULL DEFAULT 0,
  `skor` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_materi`
--

CREATE TABLE `kategori_materi` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori_materi`
--

INSERT INTO `kategori_materi` (`id`, `nama_kategori`) VALUES
(1, 'Matematika'),
(2, 'IPA'),
(3, 'Bahasa Indonesia'),
(4, 'IPS'),
(5, 'Pendidikan Kewarganegaraan'),
(6, 'Bahasa Inggris');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`) VALUES
(1, 'Kelas 7'),
(2, 'Kelas 8'),
(3, 'Kelas 9'),
(4, 'Kelas 10 IPA'),
(5, 'Kelas 10 IPS'),
(6, 'Kelas 11 IPA'),
(7, 'Kelas 11 IPS'),
(8, 'Kelas 12 IPA'),
(9, 'Kelas 12 IPS'),
(10, 'Kelas 1 SD'),
(11, 'Kelas 2 SD'),
(12, 'Kelas 3 SD'),
(13, 'Kelas 4 SD'),
(14, 'Kelas 5 SD'),
(15, 'Kelas 6 SD'),
(16, 'Kelas 8 SMP'),
(17, 'Kelas 9 SMP');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas_online`
--

CREATE TABLE `kelas_online` (
  `id` int(11) NOT NULL,
  `topik` varchar(100) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `link_zoom` text DEFAULT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kelas_online`
--

INSERT INTO `kelas_online` (`id`, `topik`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `link_zoom`, `tutor_id`, `kelas_id`, `kategori_id`, `created_at`) VALUES
(1, 'Diskusi Paragraf', '2025-07-11 00:00:00', '17:30:00', '18:30:00', 'https://meet.google.com/landing', 2, 16, 1, '2025-07-10 14:28:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `materi`
--

CREATE TABLE `materi` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file` varchar(100) DEFAULT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `tipe_file` enum('pdf','video','lain') DEFAULT 'pdf',
  `kategori_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `materi`
--

INSERT INTO `materi` (`id`, `judul`, `deskripsi`, `file`, `tutor_id`, `kelas_id`, `created_at`, `tipe_file`, `kategori_id`) VALUES
(2, 'coba', 'nknknk', '686cc3adda1d8_LPJ_LKMM-D.pdf', 2, NULL, '2025-07-08 14:07:25', 'pdf', NULL),
(6, 'bpupki', 'andzvdsvdnbsd', '686f44ec0f3f3_soal_UAS_Basis_Data_Lanjut_-_D3TI2D.pdf', 2, 8, '2025-07-10 11:43:24', 'pdf', 3),
(7, 'bpupki', 'andzvdsvdnbsd', '686f45f0f2732_soal_UAS_Basis_Data_Lanjut_-_D3TI2D.pdf', 2, 8, '2025-07-10 11:47:44', 'pdf', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `paket` varchar(50) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `status` enum('pending','lunas','ditolak') DEFAULT 'pending',
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `progress`
--

CREATE TABLE `progress` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) DEFAULT NULL,
  `materi_id` int(11) DEFAULT NULL,
  `status` enum('belum','proses','selesai') DEFAULT 'belum',
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `soal`
--

CREATE TABLE `soal` (
  `id` int(11) NOT NULL,
  `pertanyaan` text NOT NULL,
  `opsi_a` varchar(100) DEFAULT NULL,
  `opsi_b` varchar(100) DEFAULT NULL,
  `opsi_c` varchar(100) DEFAULT NULL,
  `opsi_d` varchar(100) DEFAULT NULL,
  `jawaban` char(1) DEFAULT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `kelas_id` int(11) DEFAULT NULL,
  `kategori_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `soal`
--

INSERT INTO `soal` (`id`, `pertanyaan`, `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d`, `jawaban`, `tutor_id`, `created_at`, `kelas_id`, `kategori_id`) VALUES
(1, 'apa yang dimaksud dengan paragraf?', 'suatu gagasan yang berbentuk serangkaian kalimat yang saling berkaitan satu sama lain', 'makna pada suatu kalimat', 'mengandung tanda tanya', 'diakhiri dengan tanda titik.', 'A', 2, '2025-07-10 09:18:33', NULL, NULL),
(2, 'apa yang dimaksud dengan paragraf?', 'suatu gagasan yang berbentuk serangkaian kalimat yang saling berkaitan satu sama lain', 'makna pada suatu kalimat', 'mengandung tanda tanya', 'diakhiri dengan tanda titik.', 'A', 2, '2025-07-10 13:41:36', 2, 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `role` enum('admin','siswa','tutor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', 'admin123', 'admin'),
(2, 'tutor', 'tutor123', 'tutor'),
(3, 'siswa', 'siswa123', 'siswa');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `forum`
--
ALTER TABLE `forum`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `jawaban_siswa`
--
ALTER TABLE `jawaban_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `soal_id` (`soal_id`);

--
-- Indeks untuk tabel `kategori_materi`
--
ALTER TABLE `kategori_materi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kelas_online`
--
ALTER TABLE `kelas_online`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutor_id` (`tutor_id`);

--
-- Indeks untuk tabel `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutor_id` (`tutor_id`),
  ADD KEY `fk_kategori` (`kategori_id`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `materi_id` (`materi_id`);

--
-- Indeks untuk tabel `soal`
--
ALTER TABLE `soal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutor_id` (`tutor_id`),
  ADD KEY `fk_soal_kelas` (`kelas_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `forum`
--
ALTER TABLE `forum`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `jawaban_siswa`
--
ALTER TABLE `jawaban_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori_materi`
--
ALTER TABLE `kategori_materi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `kelas_online`
--
ALTER TABLE `kelas_online`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `materi`
--
ALTER TABLE `materi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `progress`
--
ALTER TABLE `progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `soal`
--
ALTER TABLE `soal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `forum`
--
ALTER TABLE `forum`
  ADD CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `jawaban_siswa`
--
ALTER TABLE `jawaban_siswa`
  ADD CONSTRAINT `jawaban_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jawaban_siswa_ibfk_2` FOREIGN KEY (`soal_id`) REFERENCES `soal` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kelas_online`
--
ALTER TABLE `kelas_online`
  ADD CONSTRAINT `kelas_online_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `materi`
--
ALTER TABLE `materi`
  ADD CONSTRAINT `fk_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_materi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_kategori_materi` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_materi` (`id`),
  ADD CONSTRAINT `materi_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`materi_id`) REFERENCES `materi` (`id`);

--
-- Ketidakleluasaan untuk tabel `soal`
--
ALTER TABLE `soal`
  ADD CONSTRAINT `fk_soal_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  ADD CONSTRAINT `soal_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
