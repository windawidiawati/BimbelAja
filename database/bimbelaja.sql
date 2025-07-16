<<<<<<< HEAD
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Jul 2025 pada 05.36
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
(6, 'Bahasa Inggris'),
(7, 'SBdP'),
(8, 'PJOK'),
(9, 'Bahasa Daerah'),
(10, 'Pendidikan Agama'),
(11, 'Tematik Terpadu'),
(12, 'TIK'),
(13, 'Seni Budaya'),
(14, 'Fisika'),
(15, 'Kimia'),
(16, 'Biologi'),
(17, 'Ekonomi'),
(18, 'Sosiologi'),
(19, 'Geografi'),
(20, 'Sejarah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `jenjang` enum('SD','SMP','SMA') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `jenjang`) VALUES
(1, 'Kelas 7', 'SMP'),
(2, 'Kelas 8', 'SMP'),
(3, 'Kelas 9', 'SMP'),
(4, 'Kelas 10 IPA', 'SMA'),
(5, 'Kelas 10 IPS', 'SMA'),
(6, 'Kelas 11 IPA', 'SMA'),
(7, 'Kelas 11 IPS', 'SMA'),
(8, 'Kelas 12 IPA', 'SMA'),
(9, 'Kelas 12 IPS', 'SMA'),
(10, 'Kelas 1 SD', 'SD'),
(11, 'Kelas 2 SD', 'SD'),
(12, 'Kelas 3 SD', 'SD'),
(13, 'Kelas 4 SD', 'SD'),
(14, 'Kelas 5 SD', 'SD'),
(15, 'Kelas 6 SD', 'SD');

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
  `kategori_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kelas_online`
--

INSERT INTO `kelas_online` (`id`, `topik`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `link_zoom`, `tutor_id`, `kelas_id`, `kategori_id`) VALUES
(1, 'klassashgan', '2025-07-16 00:00:00', '11:29:00', '12:30:00', 'https://id.search.yahoo.com/search?fr=mcafee&type=E211ID885G0&p=gmeet', 12, 1, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `langganan`
--

CREATE TABLE `langganan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `paket` varchar(50) NOT NULL,
  `jenjang` varchar(20) DEFAULT NULL,
  `kelas` varchar(10) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_berakhir` date NOT NULL,
  `status` enum('aktif','expired') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `kategori_id` int(11) DEFAULT NULL,
  `status` enum('proses','diterima','ditolak') DEFAULT 'proses'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `materi`
--

INSERT INTO `materi` (`id`, `judul`, `deskripsi`, `file`, `tutor_id`, `kelas_id`, `created_at`, `tipe_file`, `kategori_id`, `status`) VALUES
(3, 'iot', 'hjjkskksks', '6870ac61a3cd7_07_PPB_Flutter_Dasar__Updated_Mar_2025_.pdf', 12, 1, '2025-07-11 13:17:05', 'pdf', 4, 'proses'),
(4, 'iot', 'hjjkskksks', '6870ae621f53f_07_PPB_Flutter_Dasar__Updated_Mar_2025_.pdf', 12, 1, '2025-07-11 13:25:38', 'pdf', 4, 'proses'),
(6, 'baba', 'baba', 'LAPORAN_PERTANGGUNG_JAWABAN_HIMATIF_16__kahim_wakahim__Newww.pdf', 12, 12, '2025-07-16 09:28:57', 'pdf', 13, 'proses'),
(7, 'asadsds', 'ddddddddddddd', 'LAPORAN_PERTANGGUNG_JAWABAN_HIMATIF_16__kahim_wakahim__Newww.pdf', 12, 8, '2025-07-16 09:44:11', 'pdf', 6, 'proses');

-- --------------------------------------------------------

--
-- Struktur dari tabel `paket`
--

CREATE TABLE `paket` (
  `id` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `jenjang` varchar(20) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `harga` int(11) NOT NULL,
  `durasi` int(11) NOT NULL,
  `satuan_durasi` enum('bulan','tahun') NOT NULL DEFAULT 'bulan',
  `warna` varchar(20) DEFAULT '#0d6efd',
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `paket`
--

INSERT INTO `paket` (`id`, `nama`, `kategori`, `jenjang`, `kelas`, `harga`, `durasi`, `satuan_durasi`, `warna`, `deskripsi`, `status`, `created_at`, `updated_at`) VALUES
(2, 'coba', 'Basic', 'SMP', '7', 3000000, 1, 'bulan', 'primary', 'ggggggg', 'aktif', '2025-07-15 07:15:54', '2025-07-15 07:15:54'),
(3, 'coba', 'Premium', 'SMP', '7', 300000, 3, 'bulan', 'primary', 'wardah jokjdkjsljoisejfsdkjfdl', 'aktif', '2025-07-15 07:19:43', '2025-07-15 07:19:43');

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

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `user_id`, `paket`, `harga`, `status`, `tanggal`) VALUES
(1, 14, 'coba', 3000000, 'pending', '2025-07-15 09:32:44'),
(2, 14, 'coba', 3000000, 'pending', '2025-07-15 09:37:38'),
(3, 14, 'coba', 3000000, 'pending', '2025-07-15 09:41:08'),
(4, 14, 'coba', 3000000, 'pending', '2025-07-15 09:42:05'),
(5, 14, 'coba', 3000000, 'pending', '2025-07-15 09:42:22'),
(6, 14, 'coba', 3000000, 'pending', '2025-07-15 09:47:08'),
(7, 14, 'coba', 300000, 'pending', '2025-07-15 09:47:21'),
(8, 14, 'coba', 300000, 'pending', '2025-07-15 09:51:05'),
(9, 14, 'coba', 300000, 'pending', '2025-07-15 09:51:47'),
(10, 14, 'coba', 300000, 'pending', '2025-07-15 09:52:48'),
(11, 14, 'coba', 300000, 'pending', '2025-07-16 04:26:23'),
(12, 14, 'coba', 3000000, 'pending', '2025-07-16 05:13:47');

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
(1, 'babaa', 'baba', 'baba', 'baba', 'baba', 'D', 12, '2025-07-16 09:29:38', 8, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','siswa','tutor') NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `keahlian` varchar(100) DEFAULT NULL,
  `kelas` varchar(10) DEFAULT NULL,
  `jenjang` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama`, `keahlian`, `kelas`, `jenjang`) VALUES
(12, 'tutor', '$2y$10$kTKMEeU91ImWfKqTDzlIcOQU1DbAU7r7gp6/kC3XyVdKsIetI7iW.', 'tutor', 'april', 'Matematika', NULL, NULL),
(14, 'siswa', '$2y$10$b/EwFR3HSuSbSC3EE5EGeeZ00ndRxZOPVZqaM6Sq/s7IwB77ujJXG', 'siswa', 'widia', NULL, '9', 'SMP'),
(15, 'admin', '$2y$10$gnJnC8zneAP4sVw4ldT.7u2/YK81aLt6Nj96OTQ9ZAIA8aZcyaUaq', 'admin', 'Rafli', NULL, NULL, NULL);

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
-- Indeks untuk tabel `langganan`
--
ALTER TABLE `langganan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutor_id` (`tutor_id`);

--
-- Indeks untuk tabel `paket`
--
ALTER TABLE `paket`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `tutor_id` (`tutor_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori_materi`
--
ALTER TABLE `kategori_materi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
-- AUTO_INCREMENT untuk tabel `langganan`
--
ALTER TABLE `langganan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `materi`
--
ALTER TABLE `materi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `paket`
--
ALTER TABLE `paket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `progress`
--
ALTER TABLE `progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `soal`
--
ALTER TABLE `soal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `forum`
--
ALTER TABLE `forum`
  ADD CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `kelas_online`
--
ALTER TABLE `kelas_online`
  ADD CONSTRAINT `kelas_online_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `langganan`
--
ALTER TABLE `langganan`
  ADD CONSTRAINT `langganan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `materi`
--
ALTER TABLE `materi`
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
  ADD CONSTRAINT `soal_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
=======
-- -- phpMyAdmin SQL Dump
-- -- version 5.2.1
-- -- https://www.phpmyadmin.net/
-- --
-- -- Host: 127.0.0.1
-- -- Waktu pembuatan: 11 Jul 2025 pada 06.28
-- -- Versi server: 10.4.32-MariaDB
-- -- Versi PHP: 8.2.12

-- SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- START TRANSACTION;
-- SET time_zone = "+00:00";


-- /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
-- /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
-- /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
-- /*!40101 SET NAMES utf8mb4 */;

-- --
-- -- Database: `bimbelaja`
-- --

-- -- --------------------------------------------------------

-- --
-- -- Struktur dari tabel `forum`
-- --

-- CREATE TABLE `forum` (
--   `id` int(11) NOT NULL,
--   `parent_id` int(11) DEFAULT NULL,
--   `judul` varchar(100) DEFAULT NULL,
--   `isi` text DEFAULT NULL,
--   `user_id` int(11) DEFAULT NULL,
--   `role` enum('siswa','tutor') NOT NULL DEFAULT 'siswa',
--   `created_at` datetime DEFAULT current_timestamp()
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -- --------------------------------------------------------

-- --
-- -- Struktur dari tabel `kategori_materi`
-- --

-- CREATE TABLE `kategori_materi` (
--   `id` int(11) NOT NULL,
--   `nama_kategori` varchar(100) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --
-- -- Dumping data untuk tabel `kategori_materi`
-- --

-- INSERT INTO `kategori_materi` (`id`, `nama_kategori`) VALUES
-- (1, 'Matematika'),
-- (2, 'IPA'),
-- (3, 'Bahasa Indonesia'),
-- (4, 'IPS'),
-- (5, 'Pendidikan Kewarganegaraan'),
-- (6, 'Bahasa Inggris');

-- -- --------------------------------------------------------

-- --
-- -- Struktur dari tabel `kelas`
-- --

-- CREATE TABLE `kelas` (
--   `id` int(11) NOT NULL,
--   `nama_kelas` varchar(100) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --
-- -- Dumping data untuk tabel `kelas`
-- --

-- INSERT INTO `kelas` (`id`, `nama_kelas`) VALUES
-- (1, 'Kelas 7'),
-- (2, 'Kelas 8'),
-- (3, 'Kelas 9'),
-- (4, 'Kelas 10 IPA'),
-- (5, 'Kelas 10 IPS'),
-- (6, 'Kelas 11 IPA'),
-- (7, 'Kelas 11 IPS'),
-- (8, 'Kelas 12 IPA'),
-- (9, 'Kelas 12 IPS'),
-- (10, 'Kelas 1 SD'),
-- (11, 'Kelas 2 SD'),
-- (12, 'Kelas 3 SD'),
-- (13, 'Kelas 4 SD'),
-- (14, 'Kelas 5 SD'),
-- (15, 'Kelas 6 SD'),
-- (16, 'Kelas 8 SMP'),
-- (17, 'Kelas 9 SMP');

-- -- --------------------------------------------------------

-- --
-- -- Struktur dari tabel `kelas_online`
-- --

-- CREATE TABLE `kelas_online` (
--   `id` int(11) NOT NULL,
--   `topik` varchar(100) DEFAULT NULL,
--   `tanggal` datetime DEFAULT NULL,
--   `waktu_mulai` time DEFAULT NULL,
--   `waktu_selesai` time DEFAULT NULL,
--   `link_zoom` text DEFAULT NULL,
--   `tutor_id` int(11) DEFAULT NULL,
--   `kelas_id` int(11) DEFAULT NULL,
--   `kategori_id` int(11) DEFAULT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -- --------------------------------------------------------

-- --
-- -- Struktur dari tabel `materi`
-- --

-- CREATE TABLE `materi` (
--   `id` int(11) NOT NULL,
--   `judul` varchar(100) NOT NULL,
--   `deskripsi` text DEFAULT NULL,
--   `file` varchar(100) DEFAULT NULL,
--   `tutor_id` int(11) DEFAULT NULL,
--   `kelas_id` int(11) DEFAULT NULL,
--   `created_at` datetime DEFAULT current_timestamp(),
--   `tipe_file` enum('pdf','video','lain') DEFAULT 'pdf',
--   `kategori_id` int(11) DEFAULT NULL,
--   `status` enum('proses','diterima','ditolak') DEFAULT 'proses'
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -- --------------------------------------------------------

-- --
-- -- Struktur dari tabel `pembayaran`
-- --

-- CREATE TABLE `pembayaran` (
--   `id` int(11) NOT NULL,
--   `user_id` int(11) DEFAULT NULL,
--   `paket` varchar(50) DEFAULT NULL,
--   `harga` int(11) DEFAULT NULL,
--   `status` enum('pending','lunas','ditolak') DEFAULT 'pending',
--   `tanggal` datetime DEFAULT current_timestamp()
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -- --------------------------------------------------------

-- --
-- -- Struktur dari tabel `progress`
-- --

-- CREATE TABLE `progress` (
--   `id` int(11) NOT NULL,
--   `siswa_id` int(11) DEFAULT NULL,
--   `materi_id` int(11) DEFAULT NULL,
--   `status` enum('belum','proses','selesai') DEFAULT 'belum',
--   `updated_at` datetime DEFAULT current_timestamp()
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -- --------------------------------------------------------

-- --
-- -- Struktur dari tabel `soal`
-- --

-- CREATE TABLE `soal` (
--   `id` int(11) NOT NULL,
--   `pertanyaan` text NOT NULL,
--   `opsi_a` varchar(100) DEFAULT NULL,
--   `opsi_b` varchar(100) DEFAULT NULL,
--   `opsi_c` varchar(100) DEFAULT NULL,
--   `opsi_d` varchar(100) DEFAULT NULL,
--   `jawaban` char(1) DEFAULT NULL,
--   `tutor_id` int(11) DEFAULT NULL,
--   `created_at` datetime DEFAULT current_timestamp(),
--   `kelas_id` int(11) DEFAULT NULL,
--   `kategori_id` int(11) DEFAULT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -- --------------------------------------------------------

-- --
-- -- Struktur dari tabel `users`
-- --

-- CREATE TABLE `users` (
--   `id` int(11) NOT NULL,
--   `username` varchar(50) NOT NULL,
--   `password` varchar(255) NOT NULL,
--   `role` enum('admin','siswa','tutor') NOT NULL,
--   `nama` varchar(100) DEFAULT NULL,
--   `keahlian` varchar(100) DEFAULT NULL,
--   `kelas` varchar(10) DEFAULT NULL,
--   `jenjang` varchar(20) DEFAULT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --
-- -- Dumping data untuk tabel `users`
-- --

-- INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama`, `keahlian`, `kelas`, `jenjang`) VALUES
-- (11, 'windawidia', '$2y$10$XGWOivu9khGFr.qvMX92u.Hc84RVlrBzxCjif5w9olpGcCbv4xQgS', 'siswa', 'winda', NULL, 'XII IPA', 'SMA'),
-- (12, 'tutor', '$2y$10$kTKMEeU91ImWfKqTDzlIcOQU1DbAU7r7gp6/kC3XyVdKsIetI7iW.', 'tutor', 'april', 'Matematika', NULL, NULL);

-- --
-- -- Indexes for dumped tables
-- --

-- --
-- -- Indeks untuk tabel `forum`
-- --
-- ALTER TABLE `forum`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `user_id` (`user_id`);

-- --
-- -- Indeks untuk tabel `kategori_materi`
-- --
-- ALTER TABLE `kategori_materi`
--   ADD PRIMARY KEY (`id`);

-- --
-- -- Indeks untuk tabel `kelas`
-- --
-- ALTER TABLE `kelas`
--   ADD PRIMARY KEY (`id`);

-- --
-- -- Indeks untuk tabel `kelas_online`
-- --
-- ALTER TABLE `kelas_online`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `tutor_id` (`tutor_id`);

-- --
-- -- Indeks untuk tabel `materi`
-- --
-- ALTER TABLE `materi`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `tutor_id` (`tutor_id`);

-- --
-- -- Indeks untuk tabel `pembayaran`
-- --
-- ALTER TABLE `pembayaran`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `user_id` (`user_id`);

-- --
-- -- Indeks untuk tabel `progress`
-- --
-- ALTER TABLE `progress`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `siswa_id` (`siswa_id`),
--   ADD KEY `materi_id` (`materi_id`);

-- --
-- -- Indeks untuk tabel `soal`
-- --
-- ALTER TABLE `soal`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `tutor_id` (`tutor_id`);

-- --
-- -- Indeks untuk tabel `users`
-- --
-- ALTER TABLE `users`
--   ADD PRIMARY KEY (`id`);

-- --
-- -- AUTO_INCREMENT untuk tabel yang dibuang
-- --

-- --
-- -- AUTO_INCREMENT untuk tabel `forum`
-- --
-- ALTER TABLE `forum`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT untuk tabel `kategori_materi`
-- --
-- ALTER TABLE `kategori_materi`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

-- --
-- -- AUTO_INCREMENT untuk tabel `kelas`
-- --
-- ALTER TABLE `kelas`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

-- --
-- -- AUTO_INCREMENT untuk tabel `kelas_online`
-- --
-- ALTER TABLE `kelas_online`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT untuk tabel `materi`
-- --
-- ALTER TABLE `materi`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

-- --
-- -- AUTO_INCREMENT untuk tabel `pembayaran`
-- --
-- ALTER TABLE `pembayaran`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT untuk tabel `progress`
-- --
-- ALTER TABLE `progress`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT untuk tabel `soal`
-- --
-- ALTER TABLE `soal`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT untuk tabel `users`
-- --
-- ALTER TABLE `users`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

-- --
-- -- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
-- --

-- --
-- -- Ketidakleluasaan untuk tabel `forum`
-- --
-- ALTER TABLE `forum`
--   ADD CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

-- --
-- -- Ketidakleluasaan untuk tabel `kelas_online`
-- --
-- ALTER TABLE `kelas_online`
--   ADD CONSTRAINT `kelas_online_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`);

-- --
-- -- Ketidakleluasaan untuk tabel `materi`
-- --
-- ALTER TABLE `materi`
--   ADD CONSTRAINT `materi_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`);

-- --
-- -- Ketidakleluasaan untuk tabel `pembayaran`
-- --
-- ALTER TABLE `pembayaran`
--   ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

-- --
-- -- Ketidakleluasaan untuk tabel `progress`
-- --
-- ALTER TABLE `progress`
--   ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `users` (`id`),
--   ADD CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`materi_id`) REFERENCES `materi` (`id`);

-- --
-- -- Ketidakleluasaan untuk tabel `soal`
-- --
-- ALTER TABLE `soal`
--   ADD CONSTRAINT `soal_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`);
-- COMMIT;

-- /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
-- /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
-- /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
>>>>>>> origin
