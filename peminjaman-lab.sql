-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 17, 2026 at 12:00 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `peminjaman-lab`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama`, `email`, `username`, `password`) VALUES
(1, 'M Uabidilla Dahlan', 'dahlanubed@gmail.com', 'ubeddahlan', 'krian123'),
(2, 'Amelia Nur Aini', 'amelia@gmail.com', 'amelia', 'ameliacantik');

-- --------------------------------------------------------

--
-- Table structure for table `data_barang`
--

CREATE TABLE `data_barang` (
  `id_barang` int NOT NULL,
  `id_lab` int NOT NULL COMMENT 'FK ke data_lab.id_lab',
  `kode_barang` varchar(30) NOT NULL COMMENT 'Kode/inventaris unik barang',
  `nama_barang` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL COMMENT 'Contoh: Elektronik, Furnitur, Alat Praktikum',
  `stok` int NOT NULL DEFAULT '0',
  `kondisi` enum('baik','rusak','perbaikan') NOT NULL DEFAULT 'baik',
  `status` enum('availabel','tidak availabel') NOT NULL DEFAULT 'availabel',
  `keterangan` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `data_barang`
--

INSERT INTO `data_barang` (`id_barang`, `id_lab`, `kode_barang`, `nama_barang`, `kategori`, `stok`, `kondisi`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 2, 'BRG0001', 'Switch', 'Jaringan', 10, 'baik', 'availabel', '', '2026-07-17 11:24:48', '2026-07-17 11:24:48'),
(2, 5, 'BRG0002', 'Oculus', 'Game dan Multimedia', 1, 'baik', 'availabel', '', '2026-07-17 11:26:38', '2026-07-17 11:26:38'),
(3, 5, 'BRG0003', 'Meta Quest', 'Game dan Multimedia', 1, 'baik', 'availabel', '', '2026-07-17 11:26:58', '2026-07-17 11:26:58'),
(4, 2, 'BRG0004', 'Mouse', 'Paripheral', 8, 'baik', 'availabel', '', '2026-07-17 11:52:35', '2026-07-17 11:52:35'),
(5, 2, 'BRG0005', 'Keyboard', 'Paripheral', 7, 'baik', 'availabel', '', '2026-07-17 11:53:04', '2026-07-17 11:53:04'),
(6, 2, 'BRG0006', 'Monitor', 'Multimedia', 5, 'baik', 'availabel', '', '2026-07-17 11:53:40', '2026-07-17 11:53:40');

-- --------------------------------------------------------

--
-- Table structure for table `data_lab`
--

CREATE TABLE `data_lab` (
  `id_lab` int NOT NULL,
  `nama_lab` varchar(100) NOT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `stok` int NOT NULL,
  `jumlah_kursi` int NOT NULL DEFAULT '40',
  `jumlah_meja` int NOT NULL DEFAULT '40',
  `status` enum('availabel','not available') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `data_lab`
--

INSERT INTO `data_lab` (`id_lab`, `nama_lab`, `lokasi`, `stok`, `jumlah_kursi`, `jumlah_meja`, `status`) VALUES
(1, 'Algoritma Pemrograman', 'Gedung Lab Lantai 3', 40, 40, 40, 'availabel'),
(2, 'Sistem Komputer', 'Gedung Lab Lantai 2', 40, 40, 40, 'availabel'),
(3, 'Sistem Cerdas', 'Gedung Lab Lantai 3', 40, 40, 40, 'availabel'),
(4, 'Komputasi', 'Gedung Lab Lantai 3', 40, 40, 40, 'availabel'),
(5, 'Game dan Multimedia', 'Gedung Lab Lantai 2', 41, 40, 40, 'availabel'),
(6, 'RPL', 'Gedung Lab Lantai 3', 40, 40, 40, 'availabel'),
(7, 'Ruang Baca (RBC)', 'Gedung Lab Lantai 2', 40, 40, 40, 'availabel');

-- --------------------------------------------------------

--
-- Table structure for table `data_pinjam`
--

CREATE TABLE `data_pinjam` (
  `id_data` int NOT NULL,
  `nim` char(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `nama_lab` varchar(100) NOT NULL,
  `kursi` int DEFAULT NULL,
  `status` enum('disetujui','ditolak','menunggu','selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `data_pinjam`
--

INSERT INTO `data_pinjam` (`id_data`, `nim`, `tanggal`, `jam_mulai`, `jam_selesai`, `nama_lab`, `kursi`, `status`) VALUES
(4, '251080200146', '2026-02-05', '11:00:00', '13:00:00', 'Game dan Multimedia', NULL, 'selesai'),
(5, '251080200146', '2026-02-05', '13:00:00', '15:00:00', 'Komputasi', NULL, 'ditolak'),
(6, '251080200113', '2026-02-05', '12:17:00', '14:00:00', 'RPL', NULL, 'selesai'),
(7, '251080200146', '2026-02-08', '14:00:00', '15:00:00', 'Komputasi', NULL, 'selesai'),
(8, '251080200146', '2026-02-08', '12:00:00', '14:35:00', 'RPL', NULL, 'selesai'),
(9, '251080200146', '2026-02-10', '15:01:00', '16:01:00', 'Komputasi', NULL, 'selesai'),
(10, '251080200146', '2026-02-12', '20:00:00', '21:00:00', 'Game dan Multimedia', NULL, 'selesai'),
(11, '251080200146', '2026-02-12', '21:03:00', '22:03:00', 'Game dan Multimedia', NULL, 'selesai'),
(12, '251080200146', '2026-02-12', '20:10:00', '21:10:00', 'Algoritma dan Pemrograman', NULL, 'selesai'),
(13, '251080200146', '2026-02-12', '21:12:00', '22:12:00', 'Komputasi', NULL, 'selesai'),
(14, '251080200146', '2026-02-12', '20:21:00', '21:21:00', 'Sistem Komputer', NULL, 'selesai'),
(15, '251080200146', '2026-07-16', '15:03:00', '16:03:00', 'Game dan Multimedia', 1, 'selesai'),
(16, '251080200146', '2026-07-16', '15:05:00', '16:05:00', 'Game dan Multimedia', 2, 'selesai'),
(17, '251080200146', '2026-07-16', '18:13:00', '20:14:00', 'Game dan Multimedia', 11, 'selesai'),
(18, '251080200146', '2026-07-17', '13:13:00', '14:13:00', 'Algoritma Pemrograman', 1, 'selesai'),
(19, '251080200146', '2026-07-17', '13:19:00', '14:19:00', 'Game dan Multimedia', 1, 'selesai'),
(20, '251080200146', '2026-07-17', '15:24:00', '17:24:00', 'Algoritma Pemrograman', 1, 'disetujui'),
(21, '251080200146', '2026-07-17', '17:36:00', '19:36:00', 'Game dan Multimedia', 1, 'disetujui');

-- --------------------------------------------------------

--
-- Table structure for table `inventaris_ac`
--

CREATE TABLE `inventaris_ac` (
  `id_ac` int NOT NULL,
  `id_lab` int NOT NULL,
  `nomor_ac` int NOT NULL,
  `kondisi` enum('normal','rusak') NOT NULL DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventaris_ac`
--

INSERT INTO `inventaris_ac` (`id_ac`, `id_lab`, `nomor_ac`, `kondisi`) VALUES
(2, 1, 1, 'normal'),
(3, 1, 2, 'normal'),
(4, 1, 3, 'normal');

-- --------------------------------------------------------

--
-- Table structure for table `inventaris_meja`
--

CREATE TABLE `inventaris_meja` (
  `id_meja` int NOT NULL,
  `id_lab` int NOT NULL,
  `nomor_meja` int NOT NULL,
  `cpu_kondisi` enum('normal','rusak','instal_ulang') NOT NULL DEFAULT 'normal',
  `keyboard_kondisi` enum('normal','rusak','tidak_ada') NOT NULL DEFAULT 'normal',
  `mouse_kondisi` enum('normal','rusak','tidak_ada') NOT NULL DEFAULT 'normal',
  `monitor_kondisi` enum('normal','rusak','tidak_ada') NOT NULL DEFAULT 'normal',
  `kursi_kondisi` enum('normal','rusak','tidak_ada') NOT NULL DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventaris_meja`
--

INSERT INTO `inventaris_meja` (`id_meja`, `id_lab`, `nomor_meja`, `cpu_kondisi`, `keyboard_kondisi`, `mouse_kondisi`, `monitor_kondisi`, `kursi_kondisi`) VALUES
(1, 5, 1, 'normal', 'normal', 'normal', 'normal', 'normal'),
(2, 5, 2, 'normal', 'normal', 'normal', 'normal', 'normal'),
(3, 5, 3, 'normal', 'normal', 'normal', 'normal', 'normal'),
(4, 5, 4, 'normal', 'normal', 'normal', 'normal', 'normal'),
(5, 5, 5, 'normal', 'normal', 'normal', 'normal', 'normal'),
(6, 5, 6, 'normal', 'normal', 'normal', 'normal', 'normal'),
(7, 5, 7, 'normal', 'normal', 'normal', 'normal', 'normal'),
(8, 5, 8, 'normal', 'normal', 'normal', 'normal', 'normal'),
(9, 5, 9, 'normal', 'normal', 'normal', 'normal', 'normal'),
(10, 5, 10, 'normal', 'normal', 'normal', 'normal', 'normal'),
(11, 5, 11, 'normal', 'normal', 'normal', 'normal', 'normal'),
(12, 5, 12, 'normal', 'normal', 'normal', 'normal', 'normal'),
(13, 5, 13, 'normal', 'normal', 'normal', 'normal', 'normal'),
(14, 5, 14, 'normal', 'normal', 'normal', 'normal', 'normal'),
(15, 5, 15, 'normal', 'normal', 'normal', 'normal', 'normal'),
(16, 5, 16, 'normal', 'normal', 'normal', 'normal', 'normal'),
(17, 5, 17, 'normal', 'normal', 'normal', 'normal', 'normal'),
(18, 5, 18, 'normal', 'normal', 'normal', 'normal', 'normal'),
(19, 5, 19, 'normal', 'normal', 'normal', 'normal', 'normal'),
(20, 5, 20, 'normal', 'normal', 'normal', 'normal', 'normal'),
(21, 5, 21, 'normal', 'normal', 'normal', 'normal', 'normal'),
(22, 5, 22, 'normal', 'normal', 'normal', 'normal', 'normal'),
(23, 5, 23, 'normal', 'normal', 'normal', 'normal', 'normal'),
(24, 5, 24, 'normal', 'normal', 'normal', 'normal', 'normal'),
(25, 5, 25, 'normal', 'normal', 'normal', 'normal', 'normal'),
(26, 5, 26, 'normal', 'normal', 'normal', 'normal', 'normal'),
(27, 5, 27, 'normal', 'normal', 'normal', 'normal', 'normal'),
(28, 5, 28, 'normal', 'normal', 'normal', 'normal', 'normal'),
(29, 5, 29, 'normal', 'normal', 'normal', 'normal', 'normal'),
(30, 5, 30, 'normal', 'normal', 'normal', 'normal', 'normal'),
(31, 5, 31, 'normal', 'normal', 'normal', 'normal', 'normal'),
(32, 5, 32, 'normal', 'normal', 'normal', 'normal', 'normal'),
(33, 5, 33, 'normal', 'normal', 'normal', 'normal', 'normal'),
(34, 5, 34, 'normal', 'normal', 'normal', 'normal', 'normal'),
(35, 5, 35, 'normal', 'normal', 'normal', 'normal', 'normal'),
(36, 5, 36, 'normal', 'normal', 'normal', 'normal', 'normal'),
(37, 5, 37, 'normal', 'normal', 'normal', 'normal', 'normal'),
(38, 5, 38, 'normal', 'normal', 'normal', 'normal', 'normal'),
(39, 5, 39, 'normal', 'normal', 'normal', 'normal', 'normal'),
(40, 5, 40, 'normal', 'normal', 'normal', 'normal', 'normal'),
(41, 1, 1, 'instal_ulang', 'normal', 'normal', 'tidak_ada', 'normal'),
(42, 1, 2, 'normal', 'normal', 'normal', 'normal', 'normal'),
(43, 1, 3, 'normal', 'normal', 'tidak_ada', 'normal', 'normal'),
(44, 1, 4, 'normal', 'normal', 'normal', 'normal', 'normal'),
(45, 1, 5, 'normal', 'normal', 'normal', 'normal', 'normal'),
(46, 1, 6, 'normal', 'normal', 'normal', 'normal', 'normal'),
(47, 1, 7, 'normal', 'normal', 'normal', 'normal', 'normal'),
(48, 1, 8, 'normal', 'normal', 'normal', 'normal', 'normal'),
(49, 1, 9, 'normal', 'normal', 'normal', 'normal', 'normal'),
(50, 1, 10, 'normal', 'normal', 'normal', 'normal', 'normal'),
(51, 1, 11, 'normal', 'normal', 'normal', 'normal', 'normal'),
(52, 1, 12, 'normal', 'normal', 'normal', 'normal', 'normal'),
(53, 1, 13, 'normal', 'normal', 'normal', 'normal', 'normal'),
(54, 1, 14, 'normal', 'normal', 'normal', 'normal', 'normal'),
(55, 1, 15, 'normal', 'normal', 'normal', 'normal', 'normal'),
(56, 1, 16, 'normal', 'normal', 'normal', 'normal', 'normal'),
(57, 1, 17, 'normal', 'normal', 'normal', 'normal', 'normal'),
(58, 1, 18, 'normal', 'normal', 'normal', 'normal', 'normal'),
(59, 1, 19, 'normal', 'normal', 'normal', 'normal', 'normal'),
(60, 1, 20, 'normal', 'normal', 'normal', 'normal', 'normal'),
(61, 1, 21, 'normal', 'normal', 'normal', 'normal', 'normal'),
(62, 1, 22, 'normal', 'normal', 'normal', 'normal', 'normal'),
(63, 1, 23, 'normal', 'normal', 'normal', 'normal', 'normal'),
(64, 1, 24, 'normal', 'normal', 'normal', 'normal', 'normal'),
(65, 1, 25, 'normal', 'normal', 'normal', 'normal', 'normal'),
(66, 1, 26, 'normal', 'normal', 'normal', 'normal', 'normal'),
(67, 1, 27, 'normal', 'normal', 'normal', 'normal', 'normal'),
(68, 1, 28, 'normal', 'normal', 'normal', 'normal', 'normal'),
(69, 1, 29, 'normal', 'normal', 'normal', 'normal', 'normal'),
(70, 1, 30, 'normal', 'normal', 'normal', 'normal', 'normal'),
(71, 1, 31, 'normal', 'normal', 'normal', 'normal', 'normal'),
(72, 1, 32, 'normal', 'normal', 'normal', 'normal', 'normal'),
(73, 1, 33, 'normal', 'normal', 'normal', 'normal', 'normal'),
(74, 1, 34, 'normal', 'normal', 'normal', 'normal', 'normal'),
(75, 1, 35, 'normal', 'normal', 'normal', 'normal', 'normal'),
(76, 1, 36, 'normal', 'normal', 'normal', 'normal', 'normal'),
(77, 1, 37, 'normal', 'normal', 'normal', 'normal', 'normal'),
(78, 1, 38, 'normal', 'normal', 'normal', 'normal', 'normal'),
(79, 1, 39, 'normal', 'normal', 'normal', 'normal', 'normal'),
(80, 1, 40, 'normal', 'normal', 'normal', 'normal', 'normal');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `nama` varchar(100) NOT NULL,
  `nim` char(12) NOT NULL,
  `no_telepon` varchar(100) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`nama`, `nim`, `no_telepon`, `alamat`, `password`) VALUES
('MOHAMMAD FERDIAN RENALDY ', '251080200113', '083112255638', 'DS PENATARSEWU RT 3 RW 1', 'tahubulat'),
('ubaidillah dahlan', '251080200146', '085163024682', 'Dsn Terik, Ds Terik, Kec Krian, Kab Sidoarjo Rt 7 Rw 3', 'ubed123'),
('Agung Surya Rangga Daniswara', '251080200165', '083452763456', 'Sidoarjo Kota', 'agung123'),
('Aira Diandra', '251080200287', '0983648653978', 'Sidoarjo', 'aira123'),
('Adrian Syahputra', '251080200349', '097364286424', 'Sidoarjo', 'adrian123');

-- --------------------------------------------------------

--
-- Table structure for table `periode_inventaris`
--

CREATE TABLE `periode_inventaris` (
  `id_periode` int NOT NULL,
  `id_lab` int NOT NULL,
  `bulan` tinyint NOT NULL,
  `tahun` smallint NOT NULL,
  `jumlah_kursi` int NOT NULL DEFAULT '0',
  `jumlah_meja` int NOT NULL DEFAULT '0',
  `jumlah_ac` int NOT NULL DEFAULT '0',
  `tanggal_catat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dicatat_oleh` varchar(100) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `periode_inventaris`
--

INSERT INTO `periode_inventaris` (`id_periode`, `id_lab`, `bulan`, `tahun`, `jumlah_kursi`, `jumlah_meja`, `jumlah_ac`, `tanggal_catat`, `dicatat_oleh`, `keterangan`) VALUES
(1, 1, 7, 2026, 40, 40, 3, '2026-07-16 16:10:45', 'M Uabidilla Dahlan', '');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_ac`
--

CREATE TABLE `riwayat_ac` (
  `id_riwayat_ac` int NOT NULL,
  `id_periode` int NOT NULL,
  `id_lab` int NOT NULL,
  `nomor_ac` int NOT NULL,
  `kondisi` enum('normal','rusak') NOT NULL DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `riwayat_ac`
--

INSERT INTO `riwayat_ac` (`id_riwayat_ac`, `id_periode`, `id_lab`, `nomor_ac`, `kondisi`) VALUES
(1, 1, 1, 1, 'normal'),
(2, 1, 1, 2, 'normal'),
(3, 1, 1, 3, 'normal');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_meja`
--

CREATE TABLE `riwayat_meja` (
  `id_riwayat_meja` int NOT NULL,
  `id_periode` int NOT NULL,
  `id_lab` int NOT NULL,
  `nomor_meja` int NOT NULL,
  `cpu_kondisi` enum('normal','rusak','instal_ulang') NOT NULL DEFAULT 'normal',
  `keyboard_kondisi` enum('normal','rusak','tidak_ada') NOT NULL DEFAULT 'normal',
  `mouse_kondisi` enum('normal','rusak','tidak_ada') NOT NULL DEFAULT 'normal',
  `monitor_kondisi` enum('normal','rusak','tidak_ada') NOT NULL DEFAULT 'normal',
  `kursi_kondisi` enum('normal','rusak','tidak_ada') NOT NULL DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `riwayat_meja`
--

INSERT INTO `riwayat_meja` (`id_riwayat_meja`, `id_periode`, `id_lab`, `nomor_meja`, `cpu_kondisi`, `keyboard_kondisi`, `mouse_kondisi`, `monitor_kondisi`, `kursi_kondisi`) VALUES
(1, 1, 1, 1, 'rusak', 'normal', 'rusak', 'tidak_ada', 'normal'),
(2, 1, 1, 2, 'normal', 'normal', 'normal', 'normal', 'normal'),
(3, 1, 1, 3, 'normal', 'normal', 'tidak_ada', 'normal', 'normal'),
(4, 1, 1, 4, 'normal', 'normal', 'normal', 'normal', 'normal'),
(5, 1, 1, 5, 'normal', 'normal', 'normal', 'normal', 'normal'),
(6, 1, 1, 6, 'normal', 'normal', 'normal', 'normal', 'normal'),
(7, 1, 1, 7, 'normal', 'normal', 'normal', 'normal', 'normal'),
(8, 1, 1, 8, 'normal', 'normal', 'normal', 'normal', 'normal'),
(9, 1, 1, 9, 'normal', 'normal', 'normal', 'normal', 'normal'),
(10, 1, 1, 10, 'normal', 'normal', 'normal', 'normal', 'normal'),
(11, 1, 1, 11, 'normal', 'normal', 'normal', 'normal', 'normal'),
(12, 1, 1, 12, 'normal', 'normal', 'normal', 'normal', 'normal'),
(13, 1, 1, 13, 'normal', 'normal', 'normal', 'normal', 'normal'),
(14, 1, 1, 14, 'normal', 'normal', 'normal', 'normal', 'normal'),
(15, 1, 1, 15, 'normal', 'normal', 'normal', 'normal', 'normal'),
(16, 1, 1, 16, 'normal', 'normal', 'normal', 'normal', 'normal'),
(17, 1, 1, 17, 'normal', 'normal', 'normal', 'normal', 'normal'),
(18, 1, 1, 18, 'normal', 'normal', 'normal', 'normal', 'normal'),
(19, 1, 1, 19, 'normal', 'normal', 'normal', 'normal', 'normal'),
(20, 1, 1, 20, 'normal', 'normal', 'normal', 'normal', 'normal'),
(21, 1, 1, 21, 'normal', 'normal', 'normal', 'normal', 'normal'),
(22, 1, 1, 22, 'normal', 'normal', 'normal', 'normal', 'normal'),
(23, 1, 1, 23, 'normal', 'normal', 'normal', 'normal', 'normal'),
(24, 1, 1, 24, 'normal', 'normal', 'normal', 'normal', 'normal'),
(25, 1, 1, 25, 'normal', 'normal', 'normal', 'normal', 'normal'),
(26, 1, 1, 26, 'normal', 'normal', 'normal', 'normal', 'normal'),
(27, 1, 1, 27, 'normal', 'normal', 'normal', 'normal', 'normal'),
(28, 1, 1, 28, 'normal', 'normal', 'normal', 'normal', 'normal'),
(29, 1, 1, 29, 'normal', 'normal', 'normal', 'normal', 'normal'),
(30, 1, 1, 30, 'normal', 'normal', 'normal', 'normal', 'normal'),
(31, 1, 1, 31, 'normal', 'normal', 'normal', 'normal', 'normal'),
(32, 1, 1, 32, 'normal', 'normal', 'normal', 'normal', 'normal'),
(33, 1, 1, 33, 'normal', 'normal', 'normal', 'normal', 'normal'),
(34, 1, 1, 34, 'normal', 'normal', 'normal', 'normal', 'normal'),
(35, 1, 1, 35, 'normal', 'normal', 'normal', 'normal', 'normal'),
(36, 1, 1, 36, 'normal', 'normal', 'normal', 'normal', 'normal'),
(37, 1, 1, 37, 'normal', 'normal', 'normal', 'normal', 'normal'),
(38, 1, 1, 38, 'normal', 'normal', 'normal', 'normal', 'normal'),
(39, 1, 1, 39, 'normal', 'normal', 'normal', 'normal', 'normal'),
(40, 1, 1, 40, 'normal', 'normal', 'normal', 'normal', 'normal');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `data_barang`
--
ALTER TABLE `data_barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `uq_kode_barang` (`kode_barang`),
  ADD KEY `idx_id_lab` (`id_lab`);

--
-- Indexes for table `data_lab`
--
ALTER TABLE `data_lab`
  ADD PRIMARY KEY (`id_lab`);

--
-- Indexes for table `data_pinjam`
--
ALTER TABLE `data_pinjam`
  ADD PRIMARY KEY (`id_data`);

--
-- Indexes for table `inventaris_ac`
--
ALTER TABLE `inventaris_ac`
  ADD PRIMARY KEY (`id_ac`),
  ADD UNIQUE KEY `unique_ac_lab` (`id_lab`,`nomor_ac`);

--
-- Indexes for table `inventaris_meja`
--
ALTER TABLE `inventaris_meja`
  ADD PRIMARY KEY (`id_meja`),
  ADD UNIQUE KEY `unique_meja_lab` (`id_lab`,`nomor_meja`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`nim`);

--
-- Indexes for table `periode_inventaris`
--
ALTER TABLE `periode_inventaris`
  ADD PRIMARY KEY (`id_periode`),
  ADD UNIQUE KEY `unique_periode_lab` (`id_lab`,`bulan`,`tahun`);

--
-- Indexes for table `riwayat_ac`
--
ALTER TABLE `riwayat_ac`
  ADD PRIMARY KEY (`id_riwayat_ac`),
  ADD KEY `fk_riwayat_ac_periode` (`id_periode`);

--
-- Indexes for table `riwayat_meja`
--
ALTER TABLE `riwayat_meja`
  ADD PRIMARY KEY (`id_riwayat_meja`),
  ADD KEY `fk_riwayat_meja_periode` (`id_periode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `data_barang`
--
ALTER TABLE `data_barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `data_lab`
--
ALTER TABLE `data_lab`
  MODIFY `id_lab` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `data_pinjam`
--
ALTER TABLE `data_pinjam`
  MODIFY `id_data` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `inventaris_ac`
--
ALTER TABLE `inventaris_ac`
  MODIFY `id_ac` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventaris_meja`
--
ALTER TABLE `inventaris_meja`
  MODIFY `id_meja` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `periode_inventaris`
--
ALTER TABLE `periode_inventaris`
  MODIFY `id_periode` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `riwayat_ac`
--
ALTER TABLE `riwayat_ac`
  MODIFY `id_riwayat_ac` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `riwayat_meja`
--
ALTER TABLE `riwayat_meja`
  MODIFY `id_riwayat_meja` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `data_barang`
--
ALTER TABLE `data_barang`
  ADD CONSTRAINT `fk_barang_lab` FOREIGN KEY (`id_lab`) REFERENCES `data_lab` (`id_lab`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inventaris_ac`
--
ALTER TABLE `inventaris_ac`
  ADD CONSTRAINT `fk_ac_lab` FOREIGN KEY (`id_lab`) REFERENCES `data_lab` (`id_lab`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inventaris_meja`
--
ALTER TABLE `inventaris_meja`
  ADD CONSTRAINT `fk_meja_lab` FOREIGN KEY (`id_lab`) REFERENCES `data_lab` (`id_lab`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `periode_inventaris`
--
ALTER TABLE `periode_inventaris`
  ADD CONSTRAINT `fk_periode_lab` FOREIGN KEY (`id_lab`) REFERENCES `data_lab` (`id_lab`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `riwayat_ac`
--
ALTER TABLE `riwayat_ac`
  ADD CONSTRAINT `fk_riwayat_ac_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_inventaris` (`id_periode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `riwayat_meja`
--
ALTER TABLE `riwayat_meja`
  ADD CONSTRAINT `fk_riwayat_meja_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_inventaris` (`id_periode`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
