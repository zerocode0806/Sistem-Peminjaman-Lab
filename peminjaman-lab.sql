-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 12, 2026 at 01:40 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.12

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
-- Table structure for table `data_lab`
--

CREATE TABLE `data_lab` (
  `id_lab` int NOT NULL,
  `nama_lab` varchar(100) NOT NULL,
  `stok` int NOT NULL,
  `status` enum('availabel','not available') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `data_lab`
--

INSERT INTO `data_lab` (`id_lab`, `nama_lab`, `stok`, `status`) VALUES
(1, 'Game dan Multimedia', 39, 'availabel'),
(2, 'Sistem Komputer', 39, 'availabel'),
(3, 'Sistem Cerdas', 40, 'availabel'),
(4, 'Komputasi', 39, 'availabel'),
(5, 'Algoritma dan Pemrograman', 39, 'availabel'),
(6, 'RPL', 40, 'availabel');

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
  `status` enum('disetujui','ditolak','menunggu','selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `data_pinjam`
--

INSERT INTO `data_pinjam` (`id_data`, `nim`, `tanggal`, `jam_mulai`, `jam_selesai`, `nama_lab`, `status`) VALUES
(4, '251080200146', '2026-02-05', '11:00:00', '13:00:00', 'Game dan Multimedia', 'selesai'),
(5, '251080200146', '2026-02-05', '13:00:00', '15:00:00', 'Komputasi', 'ditolak'),
(6, '251080200113', '2026-02-05', '12:17:00', '14:00:00', 'RPL', 'selesai'),
(7, '251080200146', '2026-02-08', '14:00:00', '15:00:00', 'Komputasi', 'selesai'),
(8, '251080200146', '2026-02-08', '12:00:00', '14:35:00', 'RPL', 'selesai'),
(9, '251080200146', '2026-02-10', '15:01:00', '16:01:00', 'Komputasi', 'selesai'),
(10, '251080200146', '2026-02-12', '20:00:00', '21:00:00', 'Game dan Multimedia', 'selesai'),
(11, '251080200146', '2026-02-12', '21:03:00', '22:03:00', 'Game dan Multimedia', 'selesai'),
(12, '251080200146', '2026-02-12', '20:10:00', '21:10:00', 'Algoritma dan Pemrograman', 'selesai'),
(13, '251080200146', '2026-02-12', '21:12:00', '22:12:00', 'Komputasi', 'selesai'),
(14, '251080200146', '2026-02-12', '20:21:00', '21:21:00', 'Sistem Komputer', 'disetujui');

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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

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
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`nim`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `data_lab`
--
ALTER TABLE `data_lab`
  MODIFY `id_lab` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `data_pinjam`
--
ALTER TABLE `data_pinjam`
  MODIFY `id_data` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
