-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 25, 2026 at 07:09 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dapur_ina_aina`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `uuid_detail_pesanan` char(36) NOT NULL DEFAULT (uuid()),
  `uuid_pesanan` char(36) NOT NULL,
  `uuid_kategori` char(36) NOT NULL,
  `quantitas` int NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`uuid_detail_pesanan`, `uuid_pesanan`, `uuid_kategori`, `quantitas`, `harga`, `subtotal`, `created_at`) VALUES
('38e9c3b1-b895-11f1-ae78-10ffe042c1bf', '38e9ad20-b895-11f1-ae78-10ffe042c1bf', '7e45d39f-b893-11f1-ae78-10ffe042c1bf', 7, 20000.00, 140000.00, '2026-09-25 10:57:26'),
('41effb10-b8a8-11f1-b62c-10ffe042c1bf', '41efe56c-b8a8-11f1-b62c-10ffe042c1bf', '789c928e-b893-11f1-ae78-10ffe042c1bf', 2, 12000.00, 24000.00, '2026-09-25 13:13:42'),
('91ae8d81-b894-11f1-ae78-10ffe042c1bf', '91ae78e9-b894-11f1-ae78-10ffe042c1bf', '7e45d39f-b893-11f1-ae78-10ffe042c1bf', 4, 20000.00, 80000.00, '2026-09-25 10:52:46'),
('9e422b1a-b895-11f1-ae78-10ffe042c1bf', '9e420c5b-b895-11f1-ae78-10ffe042c1bf', '7e45d39f-b893-11f1-ae78-10ffe042c1bf', 2, 20000.00, 40000.00, '2026-09-25 11:00:16'),
('9e4239ec-b895-11f1-ae78-10ffe042c1bf', '9e420c5b-b895-11f1-ae78-10ffe042c1bf', '7ab84596-b893-11f1-ae78-10ffe042c1bf', 2, 32000.00, 64000.00, '2026-09-25 11:00:16');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `uuid_kategori` char(36) NOT NULL DEFAULT (uuid()),
  `nama_kategori` varchar(15) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`uuid_kategori`, `nama_kategori`, `created_at`) VALUES
('789c928e-b893-11f1-ae78-10ffe042c1bf', 'Makanan Utama', '2026-09-25 10:44:54'),
('7ab84596-b893-11f1-ae78-10ffe042c1bf', 'Minuman', '2026-09-25 10:44:58'),
('7e45d39f-b893-11f1-ae78-10ffe042c1bf', 'Appetizer', '2026-09-25 10:45:04');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `uuid_pesanan` char(36) NOT NULL DEFAULT (uuid()),
  `kode_pesanan` int NOT NULL,
  `nama_customer` varchar(50) NOT NULL,
  `telfon_customer` varchar(16) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status_pembayaran` enum('PENDING','PAID') NOT NULL DEFAULT 'PENDING',
  `status_pesanan` enum('SEDANG DIPROSES','SIAP','SELESAI') NOT NULL DEFAULT 'SEDANG DIPROSES',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`uuid_pesanan`, `kode_pesanan`, `nama_customer`, `telfon_customer`, `total`, `status_pembayaran`, `status_pesanan`, `created_at`, `paid_at`, `updated_at`) VALUES
('38e9ad20-b895-11f1-ae78-10ffe042c1bf', 246040, 'bnbn', 'bnbn', 140000.00, 'PENDING', 'SEDANG DIPROSES', '2026-09-25 10:57:26', NULL, '2026-09-25 10:57:26'),
('41efe56c-b8a8-11f1-b62c-10ffe042c1bf', 279141, 'Yega', '111', 24000.00, 'PAID', 'SIAP', '2026-09-25 13:13:42', '2026-09-25 13:13:50', '2026-09-25 13:13:55'),
('91ae78e9-b894-11f1-ae78-10ffe042c1bf', 916049, 'cahaya', '1234', 80000.00, 'PENDING', 'SEDANG DIPROSES', '2026-09-25 10:52:46', NULL, '2026-09-25 10:52:46'),
('9e420c5b-b895-11f1-ae78-10ffe042c1bf', 405866, 'Noval', '08998726260', 104000.00, 'PAID', 'SELESAI', '2026-09-25 11:00:16', '2026-09-25 11:01:07', '2026-09-25 13:11:29');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `uuid_produk` char(36) NOT NULL DEFAULT (uuid()),
  `uuid_kategori` char(36) NOT NULL,
  `nama_produk` varchar(50) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`uuid_produk`, `uuid_kategori`, `nama_produk`, `harga`, `stok`, `created_at`) VALUES
('9053a049-b893-11f1-ae78-10ffe042c1bf', '789c928e-b893-11f1-ae78-10ffe042c1bf', 'Cah Kangkung', 12000.00, 68, '2026-09-25 10:45:34'),
('99f42047-b893-11f1-ae78-10ffe042c1bf', '7ab84596-b893-11f1-ae78-10ffe042c1bf', 'Choco Mint', 32000.00, 30, '2026-09-25 10:45:50'),
('a42745ea-b893-11f1-ae78-10ffe042c1bf', '7e45d39f-b893-11f1-ae78-10ffe042c1bf', 'Dimsum', 20000.00, 87, '2026-09-25 10:46:07'),
('f52f9f67-b895-11f1-ae78-10ffe042c1bf', '7ab84596-b893-11f1-ae78-10ffe042c1bf', 'Kopi Tuku', 32000.00, 10, '2026-09-25 11:02:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uuid_user` char(36) NOT NULL DEFAULT (uuid()),
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('cashier','administrator') NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uuid_user`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
('6f6319ac-b894-11f1-ae78-10ffe042c1bf', 'Mauly Sitta', 'mauly@gmail.com', '1234', 'cashier', '2026-09-25 10:51:48'),
('d4517230-b854-11f1-b421-345a6026d61b', 'Noval Fatriand', 'fatriandn@gmail.com', '1234', 'administrator', '2026-09-25 03:16:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`uuid_detail_pesanan`),
  ADD KEY `uuid_pesanan` (`uuid_pesanan`),
  ADD KEY `uuid_kategori` (`uuid_kategori`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`uuid_kategori`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`uuid_pesanan`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`uuid_produk`),
  ADD KEY `uuid_kategori` (`uuid_kategori`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uuid_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`uuid_pesanan`) REFERENCES `pesanan` (`uuid_pesanan`),
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`uuid_kategori`) REFERENCES `kategori` (`uuid_kategori`);

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`uuid_kategori`) REFERENCES `kategori` (`uuid_kategori`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
