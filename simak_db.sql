-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 17 Apr 2026 pada 15.47
-- Versi server: 8.4.3
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simak_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `alokasi_pembayaran_fleksibel`
--

CREATE TABLE `alokasi_pembayaran_fleksibel` (
  `id` bigint UNSIGNED NOT NULL,
  `fleksible_payment_id` bigint UNSIGNED NOT NULL,
  `angsuran_id` bigint UNSIGNED NOT NULL,
  `nominal_dialokasikan` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `alokasi_pembayaran_fleksibel`
--

INSERT INTO `alokasi_pembayaran_fleksibel` (`id`, `fleksible_payment_id`, `angsuran_id`, `nominal_dialokasikan`, `created_at`, `updated_at`) VALUES
(2, 3, 2, 1500000.00, '2026-04-05 10:19:02', '2026-04-05 10:19:02'),
(3, 4, 2, 15000000.00, '2026-04-05 20:24:03', '2026-04-05 20:24:03'),
(4, 5, 175, 832333.00, '2026-04-16 12:41:12', '2026-04-16 12:41:12'),
(5, 6, 175, 10000000.00, '2026-04-16 12:42:55', '2026-04-16 12:42:55'),
(6, 7, 175, 10000000.00, '2026-04-16 12:50:33', '2026-04-16 12:50:33'),
(7, 8, 175, 1000.33, '2026-04-16 12:53:02', '2026-04-16 12:53:02'),
(8, 8, 176, 9998999.67, '2026-04-16 12:53:02', '2026-04-16 12:53:02'),
(9, 9, 176, 1111111.00, '2026-04-16 12:53:25', '2026-04-16 12:53:25'),
(10, 10, 176, 1000000.00, '2026-04-16 12:54:53', '2026-04-16 12:54:53'),
(11, 11, 282, 20000000.00, '2026-04-16 13:49:00', '2026-04-16 13:49:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `angsuran`
--

CREATE TABLE `angsuran` (
  `id` bigint UNSIGNED NOT NULL,
  `penjualan_id` bigint UNSIGNED NOT NULL,
  `bulan_ke` int NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `sisa_setelah_bayar` decimal(15,2) DEFAULT NULL,
  `status` enum('unpaid','paid','partial') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `pembayaran_fleksibel_ids` text COLLATE utf8mb4_unicode_ci,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `angsuran`
--

INSERT INTO `angsuran` (`id`, `penjualan_id`, `bulan_ke`, `tanggal_jatuh_tempo`, `tanggal_bayar`, `nominal`, `sisa_setelah_bayar`, `status`, `pembayaran_fleksibel_ids`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-05-25', '2026-04-01', 37500000.00, 0.00, 'paid', NULL, 'Angsuran ke-1', '2026-04-02 00:07:17', '2026-04-02 02:59:05'),
(2, 1, 2, '2026-06-25', '2026-04-01', 37500000.00, 0.00, 'paid', NULL, 'Angsuran ke-2', '2026-04-02 00:07:17', '2026-04-07 23:17:17'),
(114, 1, 3, '2026-07-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-3', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(115, 1, 4, '2026-08-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-4', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(116, 1, 5, '2026-09-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-5', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(117, 1, 6, '2026-10-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-6', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(118, 1, 7, '2026-11-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-7', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(119, 1, 8, '2026-12-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-8', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(120, 1, 9, '2027-01-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-9', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(121, 1, 10, '2027-02-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-10', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(122, 1, 11, '2027-03-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-11', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(123, 1, 12, '2027-04-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-12', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(124, 1, 13, '2027-05-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-13', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(125, 1, 14, '2027-06-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-14', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(126, 1, 15, '2027-07-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-15', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(127, 1, 16, '2027-08-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-16', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(128, 1, 17, '2027-09-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-17', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(129, 1, 18, '2027-10-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-18', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(130, 1, 19, '2027-11-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-19', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(131, 1, 20, '2027-12-25', NULL, 20833333.33, 20833333.33, 'unpaid', NULL, 'Angsuran ke-20', '2026-04-08 20:39:12', '2026-04-08 20:39:12'),
(132, 5, 1, '2026-05-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-1', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(133, 5, 2, '2026-06-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-2', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(134, 5, 3, '2026-07-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-3', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(135, 5, 4, '2026-08-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-4', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(136, 5, 5, '2026-09-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-5', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(137, 5, 6, '2026-10-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-6', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(138, 5, 7, '2026-11-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-7', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(139, 5, 8, '2026-12-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-8', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(140, 5, 9, '2027-01-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-9', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(141, 5, 10, '2027-02-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-10', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(142, 5, 11, '2027-03-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-11', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(143, 5, 12, '2027-04-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-12', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(144, 5, 13, '2027-05-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-13', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(145, 5, 14, '2027-06-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-14', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(146, 5, 15, '2027-07-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-15', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(147, 5, 16, '2027-08-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-16', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(148, 5, 17, '2027-09-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-17', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(149, 5, 18, '2027-10-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-18', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(150, 5, 19, '2027-11-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-19', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(151, 5, 20, '2027-12-25', NULL, 12500000.00, 12500000.00, 'unpaid', NULL, 'Angsuran ke-20', '2026-04-08 20:39:43', '2026-04-08 20:39:43'),
(152, 8, 1, '2026-05-01', '2026-04-15', 14950000.00, 0.00, 'paid', NULL, 'Angsuran ke-1', '2026-04-14 21:42:40', '2026-04-14 21:43:47'),
(153, 8, 2, '2026-06-01', NULL, 14950000.00, 14950000.00, 'unpaid', NULL, 'Angsuran ke-2', '2026-04-14 21:42:40', '2026-04-14 21:42:40'),
(154, 8, 3, '2026-07-01', NULL, 14950000.00, 14950000.00, 'unpaid', NULL, 'Angsuran ke-3', '2026-04-14 21:42:40', '2026-04-14 21:42:40'),
(155, 8, 4, '2026-08-01', NULL, 14950000.00, 14950000.00, 'unpaid', NULL, 'Angsuran ke-4', '2026-04-14 21:42:40', '2026-04-14 21:42:40'),
(156, 8, 5, '2026-09-01', NULL, 14950000.00, 14950000.00, 'unpaid', NULL, 'Angsuran ke-5', '2026-04-14 21:42:40', '2026-04-14 21:42:40'),
(157, 8, 6, '2026-10-01', NULL, 14950000.00, 14950000.00, 'unpaid', NULL, 'Angsuran ke-6', '2026-04-14 21:42:40', '2026-04-14 21:42:40'),
(158, 8, 7, '2026-11-01', NULL, 14950000.00, 14950000.00, 'unpaid', NULL, 'Angsuran ke-7', '2026-04-14 21:42:40', '2026-04-14 21:42:40'),
(159, 8, 8, '2026-12-01', NULL, 14950000.00, 14950000.00, 'unpaid', NULL, 'Angsuran ke-8', '2026-04-14 21:42:40', '2026-04-14 21:42:40'),
(160, 8, 9, '2027-01-01', NULL, 14950000.00, 14950000.00, 'unpaid', NULL, 'Angsuran ke-9', '2026-04-14 21:42:40', '2026-04-14 21:42:40'),
(161, 8, 10, '2027-02-01', NULL, 14950000.00, 14950000.00, 'unpaid', NULL, 'Angsuran ke-10', '2026-04-14 21:42:40', '2026-04-14 21:42:40'),
(174, 9, 1, '2026-05-01', '2026-04-16', 20833333.33, 0.00, 'paid', NULL, 'Angsuran ke-1', '2026-04-16 12:19:02', '2026-04-16 12:40:04'),
(175, 9, 2, '2026-06-01', '2026-04-16', 20833333.33, 0.00, 'paid', NULL, 'Angsuran ke-2', '2026-04-16 12:19:02', '2026-04-16 12:53:02'),
(176, 9, 3, '2026-07-01', '2026-04-16', 20833333.33, 0.00, 'paid', NULL, 'Angsuran ke-3', '2026-04-16 12:19:02', '2026-04-16 13:48:12'),
(281, 9, 4, '2026-08-01', '2026-04-16', 20394736.84, 0.00, 'paid', NULL, 'Angsuran ke-4', '2026-04-16 13:47:33', '2026-04-16 13:48:35'),
(282, 9, 5, '2026-09-01', '2026-04-16', 20394736.84, 0.00, 'paid', NULL, 'Angsuran ke-5', '2026-04-16 13:47:33', '2026-04-16 13:49:09'),
(283, 9, 6, '2026-10-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-6', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(284, 9, 7, '2026-11-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-7', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(285, 9, 8, '2026-12-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-8', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(286, 9, 9, '2027-01-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-9', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(287, 9, 10, '2027-02-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-10', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(288, 9, 11, '2027-03-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-11', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(289, 9, 12, '2027-04-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-12', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(290, 9, 13, '2027-05-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-13', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(291, 9, 14, '2027-06-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-14', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(292, 9, 15, '2027-07-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-15', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(293, 9, 16, '2027-08-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-16', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(294, 9, 17, '2027-09-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-17', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(295, 9, 18, '2027-10-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-18', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(296, 9, 19, '2027-11-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-19', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(297, 9, 20, '2027-12-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-20', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(298, 9, 21, '2028-01-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-21', '2026-04-16 13:47:33', '2026-04-16 13:47:33'),
(299, 9, 22, '2028-02-01', NULL, 20394736.84, 20394736.84, 'unpaid', NULL, 'Angsuran ke-22', '2026-04-16 13:47:33', '2026-04-16 13:47:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `buyers`
--

CREATE TABLE `buyers` (
  `id` bigint UNSIGNED NOT NULL,
  `owner_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `nik` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `buyers`
--

INSERT INTO `buyers` (`id`, `owner_id`, `name`, `no_telepon`, `email`, `alamat`, `nik`, `created_at`, `updated_at`) VALUES
(1, 1, 'yusuf', '08123456789', 'yusuf@mail.com', 'Malang', '3578123456789012', '2026-04-01 10:54:00', '2026-04-01 10:54:00'),
(2, 1, 'siro', '084657899', 'siro@mail.com', 'Malang', '3578123456789013', '2026-04-05 10:35:36', '2026-04-05 10:35:36'),
(3, 7, 'siroo', '084657899', 'siroo@mail.com', 'Malang', '3578123456789013', '2026-04-08 20:34:41', '2026-04-08 20:34:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cash_flows`
--

CREATE TABLE `cash_flows` (
  `id` bigint UNSIGNED NOT NULL,
  `owner_id` bigint UNSIGNED DEFAULT NULL,
  `tanggal` date NOT NULL,
  `tipe_transaksi` enum('pemasukan','pengeluaran') COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `referensi_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referensi_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cash_flows`
--

INSERT INTO `cash_flows` (`id`, `owner_id`, `tanggal`, `tipe_transaksi`, `kategori`, `nominal`, `keterangan`, `referensi_type`, `referensi_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-04-01', 'pemasukan', 'Cicilan Angsuran', 21000000.00, 'Pembayaran Cicilan Angsuran ke-2 Transaksi: TRX-20260402070717601', 'App\\Models\\PaymentHistory', 2, '2026-04-07 23:17:17', '2026-04-07 23:17:17'),
(4, 1, '2026-04-15', 'pemasukan', 'Cicilan Angsuran', 14950000.00, 'Pembayaran Cicilan Angsuran ke-1 Transaksi: TRX-20260415044240613', 'App\\Models\\PaymentHistory', 3, '2026-04-14 21:43:47', '2026-04-14 21:43:47'),
(5, 1, '2026-04-16', 'pemasukan', 'DP Penjualan', 50000000.00, 'Pembayaran Uang Muka (DP) Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 4, '2026-04-16 12:39:42', '2026-04-16 12:39:42'),
(6, 1, '2026-04-16', 'pemasukan', 'Cicilan Angsuran', 20833333.33, 'Pembayaran Cicilan Angsuran ke-1 Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 5, '2026-04-16 12:40:04', '2026-04-16 12:40:04'),
(7, 1, '2026-04-16', 'pemasukan', 'Pembayaran Fleksibel', 832333.00, 'Pembayaran Fleksibel Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 6, '2026-04-16 12:41:12', '2026-04-16 12:41:12'),
(8, 1, '2026-04-16', 'pemasukan', 'Pembayaran Fleksibel', 10000000.00, 'Pembayaran Fleksibel Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 7, '2026-04-16 12:42:55', '2026-04-16 12:42:55'),
(9, 1, '2026-04-16', 'pemasukan', 'Pembayaran Fleksibel', 10000000.00, 'Pembayaran Fleksibel Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 8, '2026-04-16 12:50:33', '2026-04-16 12:50:33'),
(10, 1, '2026-04-16', 'pemasukan', 'Pembayaran Fleksibel', 10000000.00, 'Pembayaran Fleksibel Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 9, '2026-04-16 12:53:02', '2026-04-16 12:53:02'),
(11, 1, '2026-04-16', 'pemasukan', 'Pembayaran Fleksibel', 1111111.00, 'Pembayaran Fleksibel Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 10, '2026-04-16 12:53:25', '2026-04-16 12:53:25'),
(12, 1, '2026-04-16', 'pemasukan', 'Pembayaran Fleksibel', 1000000.00, 'Pembayaran Fleksibel Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 11, '2026-04-16 12:54:53', '2026-04-16 12:54:53'),
(13, 1, '2026-04-16', 'pemasukan', 'Cicilan Angsuran', 8723222.66, 'Pembayaran Cicilan Angsuran ke-3 Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 12, '2026-04-16 13:48:12', '2026-04-16 13:48:12'),
(14, 1, '2026-04-16', 'pemasukan', 'Cicilan Angsuran', 20394736.84, 'Pembayaran Cicilan Angsuran ke-4 Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 13, '2026-04-16 13:48:35', '2026-04-16 13:48:35'),
(15, 1, '2026-04-16', 'pemasukan', 'Pembayaran Fleksibel', 20000000.00, 'Pembayaran Fleksibel Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 14, '2026-04-16 13:49:00', '2026-04-16 13:49:00'),
(16, 1, '2026-04-16', 'pemasukan', 'Cicilan Angsuran', 394736.84, 'Pembayaran Cicilan Angsuran ke-5 Transaksi: TRX-20260416185558877', 'App\\Models\\PaymentHistory', 15, '2026-04-16 13:49:09', '2026-04-16 13:49:09'),
(20, 1, '2026-04-17', 'pemasukan', 'Pelunasan Penjualan', 500000000.00, 'Pelunasan Transaksi: TRX-20260417074125250', 'App\\Models\\PaymentHistory', 19, '2026-04-17 01:34:51', '2026-04-17 01:34:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `fleksible_payments`
--

CREATE TABLE `fleksible_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `sales_transaction_id` bigint UNSIGNED NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `metode_bayar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bukti_bayar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','diterima','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `fleksible_payments`
--

INSERT INTO `fleksible_payments` (`id`, `sales_transaction_id`, `nominal`, `tanggal_bayar`, `catatan`, `metode_bayar`, `bukti_bayar`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 1, 1500000.00, '2026-04-10', 'Pembayaran dipercepat untuk bulan April dan cicil dikit bulan Mei', NULL, NULL, 'pending', 1, '2026-04-05 10:19:02', '2026-04-05 10:19:02'),
(4, 1, 15000000.00, '2026-04-10', 'Pembayaran dipercepat untuk bulan April dan cicil dikit bulan Mei', NULL, NULL, 'pending', 1, '2026-04-05 20:24:03', '2026-04-05 20:24:03'),
(5, 9, 832333.00, '2026-04-16', 'kk', NULL, NULL, 'pending', 1, '2026-04-16 12:41:12', '2026-04-16 12:41:12'),
(6, 9, 10000000.00, '2026-04-16', 'f', NULL, NULL, 'pending', 1, '2026-04-16 12:42:55', '2026-04-16 12:42:55'),
(7, 9, 10000000.00, '2026-04-16', '00', NULL, NULL, 'pending', 1, '2026-04-16 12:50:33', '2026-04-16 12:50:33'),
(8, 9, 10000000.00, '2026-04-16', '11', NULL, NULL, 'pending', 1, '2026-04-16 12:53:02', '2026-04-16 12:53:02'),
(9, 9, 1111111.00, '2026-04-16', '11', NULL, NULL, 'pending', 1, '2026-04-16 12:53:25', '2026-04-16 12:53:25'),
(10, 9, 1000000.00, '2026-04-16', 'dqw', NULL, NULL, 'pending', 1, '2026-04-16 12:54:53', '2026-04-16 12:54:53'),
(11, 9, 20000000.00, '2026-04-16', 'k', NULL, NULL, 'pending', 1, '2026-04-16 13:49:00', '2026-04-16 13:49:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kavling`
--

CREATE TABLE `kavling` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `blok_nomor` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `luas` decimal(10,2) DEFAULT NULL,
  `harga_dasar` decimal(15,2) DEFAULT NULL,
  `status` enum('available','sold','reserved','active') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kavling`
--

INSERT INTO `kavling` (`id`, `project_id`, `blok_nomor`, `luas`, `harga_dasar`, `status`, `created_at`, `updated_at`) VALUES
(1, 6, 'A-1', 120.00, 150000000.00, 'sold', '2026-04-01 00:40:01', '2026-04-01 00:40:01'),
(2, 6, 'A-2', 200.00, 300000000.00, 'sold', '2026-04-05 10:36:11', '2026-04-16 11:55:58'),
(3, 6, 'A-3', 200.00, 300000000.00, 'sold', '2026-04-06 20:00:12', '2026-04-06 20:00:12'),
(5, 6, 'A-5', 120.00, 150000000.00, 'sold', '2026-04-08 00:42:11', '2026-04-14 21:42:40'),
(6, 9, 'A-1', 200.00, 300000000.00, 'sold', '2026-04-08 20:32:33', '2026-04-08 20:36:29'),
(7, 10, 'A-1', 200.00, 500000000.00, 'sold', '2026-04-16 14:25:25', '2026-04-16 14:25:43'),
(8, 10, 'A-2', 200.00, 500000000.00, 'sold', '2026-04-17 00:31:37', '2026-04-17 00:41:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(4, '2026_03_06_023652_create_personal_access_tokens_table', 1),
(5, '2026_03_06_030959_add_columns_to_users_table', 2),
(6, '2026_03_26_020148_recreate_all_database_tables', 3),
(7, '2026_03_30_00000200_add_last_login_to_users', 4),
(8, '0001_01_01_000001_create_cache_table', 5),
(9, '0001_01_01_000002_create_jobs_table', 5),
(10, '2026_03_30_032351_create_personal_access_tokens_table', 5),
(11, '2026_03_31_173406_create_sales_table', 6),
(12, '2026_03_31_175049_rename_penjualan_to_sales_transactions', 7),
(13, '2026_04_06_104800_create_riwayat_pembayaran_table', 8),
(14, '2026_04_07_073031_create_cash_flows_table', 9),
(15, '2026_04_09_000001_alter_user_licenses_nullable_user_id', 10),
(16, '2026_04_09_000002_add_super_admin_role_to_users', 10),
(17, '2026_04_09_000003_fix_user_licenses_status_column', 11),
(18, '2026_04_13_011000_create_missing_sessions_table', 12),
(19, '2026_04_13_092800_add_owner_id_to_cash_flows_table', 13),
(20, '2026_04_17_075405_add_polymorphic_to_payment_history_table', 14);

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment_history`
--

CREATE TABLE `payment_history` (
  `id` bigint UNSIGNED NOT NULL,
  `sales_transaction_id` bigint UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `referensi_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referensi_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payment_history`
--

INSERT INTO `payment_history` (`id`, `sales_transaction_id`, `tanggal`, `keterangan`, `amount`, `referensi_type`, `referensi_id`, `created_at`, `updated_at`) VALUES
(1, 3, '2026-04-06', 'Payment Off', 325000000.00, NULL, NULL, '2026-04-05 21:32:54', '2026-04-05 21:32:54'),
(2, 1, '2026-04-01', 'Angsuran Payment ke - 2', 21000000.00, NULL, NULL, '2026-04-07 23:17:17', '2026-04-07 23:17:17'),
(3, 8, '2026-04-15', 'Angsuran Payment ke - 1', 14950000.00, NULL, NULL, '2026-04-14 21:43:47', '2026-04-14 21:43:47'),
(4, 9, '2026-04-16', 'Pay Down Payment', 50000000.00, NULL, NULL, '2026-04-16 12:39:42', '2026-04-16 12:39:42'),
(5, 9, '2026-04-16', 'Angsuran Payment ke - 1', 20833333.33, NULL, NULL, '2026-04-16 12:40:04', '2026-04-16 12:40:04'),
(6, 9, '2026-04-16', 'Flexible Payment', 832333.00, NULL, NULL, '2026-04-16 12:41:12', '2026-04-16 12:41:12'),
(7, 9, '2026-04-16', 'Flexible Payment', 10000000.00, NULL, NULL, '2026-04-16 12:42:55', '2026-04-16 12:42:55'),
(8, 9, '2026-04-16', 'Flexible Payment', 10000000.00, NULL, NULL, '2026-04-16 12:50:33', '2026-04-16 12:50:33'),
(9, 9, '2026-04-16', 'Flexible Payment', 10000000.00, NULL, NULL, '2026-04-16 12:53:02', '2026-04-16 12:53:02'),
(10, 9, '2026-04-16', 'Flexible Payment', 1111111.00, NULL, NULL, '2026-04-16 12:53:25', '2026-04-16 12:53:25'),
(11, 9, '2026-04-16', 'Flexible Payment', 1000000.00, NULL, NULL, '2026-04-16 12:54:53', '2026-04-16 12:54:53'),
(12, 9, '2026-04-16', 'Angsuran Payment ke - 3', 8723222.66, NULL, NULL, '2026-04-16 13:48:12', '2026-04-16 13:48:12'),
(13, 9, '2026-04-16', 'Angsuran Payment ke - 4', 20394736.84, NULL, NULL, '2026-04-16 13:48:35', '2026-04-16 13:48:35'),
(14, 9, '2026-04-16', 'Flexible Payment', 20000000.00, NULL, NULL, '2026-04-16 13:49:00', '2026-04-16 13:49:00'),
(15, 9, '2026-04-16', 'Angsuran Payment ke - 5', 394736.84, NULL, NULL, '2026-04-16 13:49:09', '2026-04-16 13:49:09'),
(19, 15, '2026-04-17', 'Payment Off', 500000000.00, 'App\\Models\\SalesTransaction', 15, '2026-04-17 01:34:51', '2026-04-17 01:34:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'auth_token', '8a1ff92ddedcbcb06122530008fd9f1b65264c78970978053a5b6feded1e161a', '[\"*\"]', NULL, NULL, '2026-03-30 01:46:25', '2026-03-30 01:46:25'),
(2, 'App\\Models\\User', 1, 'auth_token', '85e652401458f846f36dcfbcf76fb398dd8afd04f13d17b956670a16a381fcca', '[\"*\"]', '2026-04-08 20:39:12', NULL, '2026-03-30 13:02:58', '2026-04-08 20:39:12'),
(3, 'App\\Models\\User', 5, 'auth_token', 'fbd142577e1481a265900f63becf4d7f00c65d9869fa762f0e9134e98d86ebea', '[\"*\"]', '2026-04-08 11:47:04', NULL, '2026-04-08 11:34:36', '2026-04-08 11:47:04'),
(4, 'App\\Models\\User', 6, 'auth_token', '20de7e06568ef06ff7f6692aedc42a302355543d2c40182703bb9ebb041ee741', '[\"*\"]', NULL, NULL, '2026-04-08 11:47:45', '2026-04-08 11:47:45'),
(5, 'App\\Models\\User', 1, 'auth_token', '8a8596eaed7f2730489d4c08af6e5a4a65ced88757b1e4b98485f3e09421bb8c', '[\"*\"]', NULL, NULL, '2026-04-08 19:48:58', '2026-04-08 19:48:58'),
(6, 'App\\Models\\User', 6, 'auth_token', 'eb2f8bca957681de457b52ee2ddb34e26fdc9b7e1083ea29655326e50176629a', '[\"*\"]', '2026-04-08 20:06:10', NULL, '2026-04-08 19:49:29', '2026-04-08 20:06:10'),
(7, 'App\\Models\\User', 1, 'auth_token', 'ce660f1a9c12f296698e1016188111c2afad3a02aed2235385120cb6c6da6c2f', '[\"*\"]', NULL, NULL, '2026-04-08 20:02:20', '2026-04-08 20:02:20'),
(8, 'App\\Models\\User', 6, 'auth_token', 'b8008709bd6c88f6f2e386c85797ce56907f8e4f80850a46d17c59077da4fa87', '[\"*\"]', '2026-04-08 20:07:07', NULL, '2026-04-08 20:06:04', '2026-04-08 20:07:07'),
(9, 'App\\Models\\User', 1, 'auth_token', '81cfe21fac4de6f0cf41d703f4043db88ebe6480e5b126c26eb7eb147af5889c', '[\"*\"]', '2026-04-08 20:40:43', NULL, '2026-04-08 20:08:56', '2026-04-08 20:40:43'),
(10, 'App\\Models\\User', 5, 'auth_token', '245675d653e7d68d2f6ecf51d1f9c447f266acb5e94e23f1964535dc579845c8', '[\"*\"]', '2026-04-08 20:29:26', NULL, '2026-04-08 20:29:04', '2026-04-08 20:29:26'),
(11, 'App\\Models\\User', 7, 'auth_token', '5e1858d8249eab6cee4a1f6600f5870b545aee4a2bd651d2f2b9f0d70d30b0f7', '[\"*\"]', '2026-04-08 20:42:51', NULL, '2026-04-08 20:29:53', '2026-04-08 20:42:51'),
(12, 'App\\Models\\User', 5, 'auth_token', '0c5ef6209ac40769c3d295ebb9c05d414bad41e59c5416d9c42367a30efa57ae', '[\"*\"]', NULL, NULL, '2026-04-12 11:27:33', '2026-04-12 11:27:33'),
(13, 'App\\Models\\User', 5, 'auth_token', '6f92dcd45834f5732090294236deaeaab274f968499ab2098e6e248d102bd418', '[\"*\"]', NULL, NULL, '2026-04-12 11:36:52', '2026-04-12 11:36:52'),
(14, 'App\\Models\\User', 5, 'auth_token', 'ecd1655b96057251deb33a46290ed3d27ab5fedd07a3e86085025be7f76944a5', '[\"*\"]', NULL, NULL, '2026-04-12 11:40:09', '2026-04-12 11:40:09'),
(15, 'App\\Models\\User', 1, 'auth_token', '65cfcc64f22b7d058b0f0c1d687455c4f3d029d28097553d6da73fdd7c788087', '[\"*\"]', NULL, NULL, '2026-04-12 19:10:55', '2026-04-12 19:10:55'),
(16, 'App\\Models\\User', 1, 'auth_token', 'fae2bde8bc73925bfbfc6a199dab89829795fd5fbc7cf402814d62555dfcc24b', '[\"*\"]', NULL, NULL, '2026-04-12 19:21:10', '2026-04-12 19:21:10'),
(17, 'App\\Models\\User', 1, 'auth_token', '589940de3438eae0a1d27d6909c00432ea5393f075a8f2e1dd719c7b6112976f', '[\"*\"]', NULL, NULL, '2026-04-12 19:23:39', '2026-04-12 19:23:39'),
(18, 'App\\Models\\User', 1, 'auth_token', 'ba392f2913a282f1644567ce8edf23bd9a3e4da2bed43b19eb977fdf4ac63f4e', '[\"*\"]', NULL, NULL, '2026-04-12 19:40:42', '2026-04-12 19:40:42'),
(19, 'App\\Models\\User', 1, 'auth_token', '886d1be21a472ab6edbf3daf426c449ec5ec53db6c58e61870fc026dd8363faa', '[\"*\"]', NULL, NULL, '2026-04-12 19:43:40', '2026-04-12 19:43:40'),
(20, 'App\\Models\\User', 1, 'auth_token', 'd1851175429dc15e998c4c68529b2d9ae21d3a16c82838f0afccb120d7fcaf20', '[\"*\"]', NULL, NULL, '2026-04-12 19:57:06', '2026-04-12 19:57:06'),
(21, 'App\\Models\\User', 1, 'auth_token', '33822b0aa5e65b4fcdea84a8dea196c28e24887c702fd4941b484753a06568aa', '[\"*\"]', NULL, NULL, '2026-04-12 19:58:18', '2026-04-12 19:58:18'),
(22, 'App\\Models\\User', 1, 'auth_token', 'd72d1aa0978375b169400b10cd1600982befb3dee28cacf346e652aba8810d99', '[\"*\"]', NULL, NULL, '2026-04-12 19:59:02', '2026-04-12 19:59:02'),
(23, 'App\\Models\\User', 1, 'auth_token', '846a48264077f53cae352d99b29d6dbb3f1f55a72f5aca41a1272c433e62e859', '[\"*\"]', NULL, NULL, '2026-04-12 20:10:01', '2026-04-12 20:10:01'),
(24, 'App\\Models\\User', 1, 'auth_token', '152db9bcbe780f9ececc69286ba9ec098f28660e66811a622129deece1659e80', '[\"*\"]', NULL, NULL, '2026-04-12 20:19:08', '2026-04-12 20:19:08'),
(25, 'App\\Models\\User', 1, 'auth_token', 'd85339e8f06aedd55c654774ab31c10a53f5caa10585b0f1e2a4d04f83544d60', '[\"*\"]', NULL, NULL, '2026-04-13 01:41:40', '2026-04-13 01:41:40'),
(26, 'App\\Models\\User', 1, 'auth_token', 'ceeebd54f075068a49fa1a13d0be64596b364028a6620de9f8ab4f849c122346', '[\"*\"]', '2026-04-13 01:44:54', NULL, '2026-04-13 01:44:21', '2026-04-13 01:44:54'),
(27, 'App\\Models\\User', 1, 'auth_token', 'ee10be4e05ca40bd73f4b5676d73e0063ccd197f0ed460900ccfd0ba8e34c013', '[\"*\"]', '2026-04-13 08:56:16', NULL, '2026-04-13 08:45:02', '2026-04-13 08:56:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `profile_perusahaan`
--

CREATE TABLE `profile_perusahaan` (
  `id` bigint UNSIGNED NOT NULL,
  `owner_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `npwp` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ttd_admin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_kaki_cetakan` text COLLATE utf8mb4_unicode_ci,
  `format_faktur` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `format_kuitansi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `profile_perusahaan`
--

INSERT INTO `profile_perusahaan` (`id`, `owner_id`, `name`, `npwp`, `email`, `telepon`, `alamat`, `logo`, `nama_ttd_admin`, `catatan_kaki_cetakan`, `format_faktur`, `format_kuitansi`, `created_at`, `updated_at`) VALUES
(1, 0, 'miawkav', '12.345.678.9-123.456', 'miawkav@gmail.com', NULL, NULL, 'logo/0AxYSht1p13dnVTY5X1e27jA4NqTuTGPN0oDqpod.webp', 'ahmadmiaw', 'miawkav@2026', '123/2026/03/0017', '321/2026/03/0017', '2026-03-30 13:19:39', '2026-03-30 13:19:39'),
(2, 1, 'aughkav', '12.345.678.9-123.456', 'aughkav@gmail.com', '082334210945', 'udin', 'logo/eaAZcPVzlNvdhTOuWQBEv8uUN2kqgkhwwq5eMniD.png', 'ahmadaugh', 'aughkav@2026', '123/2026/03/0018', '321/2026/03/0018', '2026-03-30 18:42:22', '2026-04-16 11:29:25'),
(3, 6, 'miawkav', '12.345.678.9-123.456', 'miawkav@gmail.com', NULL, NULL, 'logo/AJnMqaoUNmolUOX0pjAgyH8H6KqiQvIp4MwrcwcR.webp', 'ahmadmiaw', 'miawkav@2026', '123/2026/03/0017', '321/2026/03/0017', '2026-04-08 19:54:09', '2026-04-08 19:54:09'),
(4, 7, 'miawkav', '12.345.678.9-123.456', 'miawkav@gmail.com', NULL, NULL, 'logo/FoapM1cAtmaapKCudlP4cMOcMjiwFzUoL0E71JBX.webp', 'ahmadmiaw', 'miawkav@2026', '123/2026/03/0015', '321/2026/03/0015', '2026-04-08 20:30:56', '2026-04-08 20:30:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `project`
--

CREATE TABLE `project` (
  `id` bigint UNSIGNED NOT NULL,
  `owner_id` int NOT NULL,
  `nama_project` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lokasi` text COLLATE utf8mb4_unicode_ci,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `total_unit` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `project`
--

INSERT INTO `project` (`id`, `owner_id`, `nama_project`, `lokasi`, `catatan`, `total_unit`, `created_at`, `updated_at`) VALUES
(6, 1, 'grandmiawwwwww', 'sawojajar, malang', 'perumahan dengan lokasi strategis', 20, '2026-03-30 20:40:13', '2026-04-08 00:36:20'),
(8, 6, 'grandmiawmw', 'sawojajar, malang', 'perumahan dengan lokasi strategis', 20, '2026-04-08 19:56:13', '2026-04-08 19:56:13'),
(9, 7, 'grandmiawmw', 'sawojajar, malang', 'perumahan dengan lokasi strategis', 20, '2026-04-08 20:31:13', '2026-04-08 20:31:13'),
(10, 1, 'perumahanji', 'jl mannan', 'iii', 10, '2026-04-12 21:12:17', '2026-04-12 21:12:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales`
--

CREATE TABLE `sales` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `owner_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_sales` int NOT NULL DEFAULT '0',
  `total_revenue` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sales`
--

INSERT INTO `sales` (`id`, `user_id`, `owner_id`, `name`, `phone`, `unit_sales`, `total_revenue`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'Budi Sales', '08123456789', 6, 2475000000.00, '2026-04-01 00:15:13', '2026-04-17 00:41:25'),
(2, 9, 7, NULL, '08123456789', 0, 0.00, '2026-04-08 20:33:20', '2026-04-08 20:33:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales_transactions`
--

CREATE TABLE `sales_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `owner_id` bigint UNSIGNED NOT NULL,
  `nomor_transaksi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kavling_id` bigint UNSIGNED NOT NULL,
  `buyer_id` bigint UNSIGNED NOT NULL,
  `sales_id` bigint UNSIGNED NOT NULL,
  `metode_pembayaran` enum('cash_keras','angsuran_in_house','kpr_bank') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_booking` date DEFAULT NULL,
  `harga_dasar` decimal(15,2) DEFAULT NULL,
  `promo_diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `harga_netto` decimal(15,2) DEFAULT NULL,
  `biaya_ppjb` decimal(15,2) NOT NULL DEFAULT '0.00',
  `biaya_shm` decimal(15,2) NOT NULL DEFAULT '0.00',
  `biaya_lain` decimal(15,2) NOT NULL DEFAULT '0.00',
  `booking_fee` decimal(15,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) DEFAULT NULL,
  `sudah_termasuk_unit` tinyint(1) NOT NULL DEFAULT '0',
  `tenor` int DEFAULT NULL,
  `tanggal_jatuh_tempo` int DEFAULT NULL,
  `uang_muka_persen` decimal(5,2) DEFAULT NULL,
  `uang_muka_nominal` decimal(15,2) DEFAULT NULL,
  `estimasi_angsuran` decimal(15,2) DEFAULT NULL,
  `total_amount` int NOT NULL,
  `total_paid` int DEFAULT NULL,
  `total_flexible_paid` int DEFAULT NULL,
  `catatan_transaksi` text COLLATE utf8mb4_unicode_ci,
  `status_penjualan` enum('active','paid_off','cancel','refund') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `status_dp` enum('unpaid','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `status_kpr` enum('accepted','rejected') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_pelunasan` date DEFAULT NULL,
  `keterangan_batal` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sales_transactions`
--

INSERT INTO `sales_transactions` (`id`, `owner_id`, `nomor_transaksi`, `kavling_id`, `buyer_id`, `sales_id`, `metode_pembayaran`, `tanggal_booking`, `harga_dasar`, `promo_diskon`, `harga_netto`, `biaya_ppjb`, `biaya_shm`, `biaya_lain`, `booking_fee`, `grand_total`, `sudah_termasuk_unit`, `tenor`, `tanggal_jatuh_tempo`, `uang_muka_persen`, `uang_muka_nominal`, `estimasi_angsuran`, `total_amount`, `total_paid`, `total_flexible_paid`, `catatan_transaksi`, `status_penjualan`, `status_dp`, `status_kpr`, `tanggal_pelunasan`, `keterangan_batal`, `created_at`, `updated_at`) VALUES
(1, 1, 'TRX-20260402070717601', 1, 1, 1, 'angsuran_in_house', '2026-04-01', 500000000.00, 0.00, 500000000.00, 0.00, 0.00, 0.00, 0.00, 500000000.00, 0, 20, 25, NULL, 50000000.00, 37500000.00, 500000000, 125000000, 16500000, NULL, 'active', 'paid', NULL, NULL, NULL, '2026-04-02 00:07:17', '2026-04-07 23:17:17'),
(3, 1, 'TRX-20260405180036448', 2, 2, 1, 'cash_keras', '2026-04-01', 300000000.00, 25000000.00, 275000000.00, 0.00, 0.00, 50000000.00, 0.00, 325000000.00, 0, 24, 15, NULL, 50000000.00, NULL, 325000000, 325000000, NULL, NULL, 'refund', 'unpaid', NULL, '2026-04-06', NULL, '2026-04-05 11:00:36', '2026-04-08 00:18:18'),
(4, 1, 'TRX-20260407032320652', 3, 2, 1, 'cash_keras', '2026-04-01', 300000000.00, 25000000.00, 275000000.00, 0.00, 0.00, 50000000.00, 0.00, 325000000.00, 0, NULL, NULL, NULL, 50000000.00, NULL, 325000000, NULL, NULL, NULL, 'active', 'unpaid', NULL, NULL, NULL, '2026-04-06 20:23:20', '2026-04-06 20:23:20'),
(5, 7, 'TRX-20260409033629511', 6, 3, 2, 'angsuran_in_house', '2026-04-01', 300000000.00, 0.00, 300000000.00, 0.00, 0.00, 0.00, 0.00, 300000000.00, 0, 20, 25, NULL, 50000000.00, NULL, 300000000, NULL, NULL, NULL, 'active', 'unpaid', NULL, NULL, NULL, '2026-04-08 20:36:29', '2026-04-08 20:39:43'),
(8, 1, 'TRX-20260415044240613', 5, 2, 1, 'angsuran_in_house', '2026-04-15', 150000000.00, 0.00, 150000000.00, 0.00, 0.00, 0.00, 0.00, 150000000.00, 0, 10, 1, NULL, 500000.00, NULL, 150000000, 14950000, NULL, NULL, 'active', 'unpaid', NULL, NULL, NULL, '2026-04-14 21:42:40', '2026-04-14 21:43:47'),
(9, 1, 'TRX-20260416185558877', 2, 2, 1, 'angsuran_in_house', '2026-04-16', 300000000.00, 0.00, 300000000.00, 50000000.00, 0.00, 0.00, 150000000.00, 500000000.00, 0, 22, 1, NULL, 50000000.00, NULL, 500000000, 153289474, NULL, NULL, 'active', 'paid', NULL, NULL, NULL, '2026-04-16 11:55:58', '2026-04-16 14:09:52'),
(10, 1, '123/2026/03/0018', 7, 2, 1, 'cash_keras', '2026-04-16', 500000000.00, 0.00, 500000000.00, 0.00, 0.00, 0.00, 0.00, 500000000.00, 1, 1, 1, NULL, 0.00, NULL, 500000000, NULL, NULL, NULL, 'active', 'unpaid', NULL, NULL, NULL, '2026-04-16 14:25:43', '2026-04-16 14:25:43'),
(15, 1, 'TRX-20260417074125250', 8, 1, 1, 'cash_keras', '2026-04-17', 500000000.00, 0.00, 500000000.00, 0.00, 0.00, 0.00, 0.00, 500000000.00, 0, 1, 1, NULL, 0.00, NULL, 500000000, 500000000, NULL, NULL, 'paid_off', 'unpaid', NULL, '2026-04-17', NULL, '2026-04-17 00:41:25', '2026-04-17 01:34:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('11ea8PheyLr5gVGsBTXkysAh4OQ7NSbDYKiCImub', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJqN1o3eFRlbXF0OVVwZmlUTGt0ZUduMzRyeWZiRnZBYkdHMXgyZkxVIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776048025),
('5tpL5CAUmiOkChjrOKqxYA2aHeShNOYNk14b1XPH', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ3TXJRZ3dDOG05YVVZZ2VRdDVaS3ZhTndPeUZ2dnhVNHB0VkVabmVaIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776047780),
('8OQAWHP8hkh8aKcGq4i65UV7gc1bdaKDj6ckwerK', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJGZzZKc0gxVHUwSzhRQzQ5aFJYUW5JUng5VENsRzlpV2puMmxZbnVBIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9sb2dpbiIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776045340),
('AyRfk7z8ulIrhWKv5IxS69gzYH6iaUeJ07Czt1ux', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJKZjN3UnptaVZ5YUFrZUU1S2NvOUFWOFdjN2RRd1JjQkxyQXppb1U2IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776048198),
('KxmNvrUOfhI40qt3Nt6jAIf7huBiWxTNCJMqOVCq', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJTcFo0bTJDZzFRdVVGbXowMzRPOTBRanNBTWpnZEpCanI1d09laXJtIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1776049104),
('lUKMIANNrowXW4JLpPaQCK17AwJjfsLU2OR5mrcE', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIwOVdZbVJFZ2ZSV3lJQzI4eGJmUE9RSEJUOEJrZTVaQVdmMUZod005IiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1776048057),
('OFeoJc6maOKrUeDqqEBguTWlMzeUcKZuZX10y4fO', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJHZEU2N2NmMXo2SDRhdWZsZnN3RVRkamtHS1Z6aWZQS0o4a21ZREI3IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC8ud2VsbC1rbm93blwvYXBwc3BlY2lmaWNcL2NvbS5jaHJvbWUuZGV2dG9vbHMuanNvbiIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776049079),
('PQV32rZOSShczkamOO6ImhJ2t6mmOVdx14dliXnz', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ6RE5tazN3UWFYbWlaeVlNN09VbWJKdVh3N2xxeUp0WEJuRWZidG5pIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1776046872),
('qI7OeSLhpcCtO3cRtWplEaMkun4AIo0M9Rg85a5M', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJOdlZmOUtTNGlhbW12bTFPb1AwOFZGTzFhQmlWRWJDN2NzQ0xCaGxuIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcLy53ZWxsLWtub3duXC9hcHBzcGVjaWZpY1wvY29tLmNocm9tZS5kZXZ0b29scy5qc29uIiwicm91dGUiOm51bGx9fQ==', 1776049044),
('RdVDEEK9dWbV1zvh46VfbibzkKnSuWFbVGBd4Ggm', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ1NEpybDREbmZ0YTRQMXpCbE1ZRTVkTjdLd2d6RjBGRE1jODVRZ2dVIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1776046256),
('RRZlUd65kbLU3Ayn1xgksc2coUW7sUBqBztBiJon', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJwbXVvWjYyQmtmNDBxb055QTU5QTlRcHMzc3FvYkpXUTZES1RjQjZxIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1776049143),
('TUAITqvq7yPSHlCXg8mSntPrcbJSVooDdI4AswwQ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJuOWt0Q2s4cVZYTWZUT3FkNTNCdThSSFFBemJOdlFic2tPZ1NvN0t0IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776048310),
('UEhUp7haoobUAuhOvocmbgLNOQIeM3X24oWLoDhl', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJKNHllZDZPQ0tZVFBRUXB6Z3czTk9yZUEzb1YwZTYxQm5rdXE5SHZjIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcLy53ZWxsLWtub3duXC9hcHBzcGVjaWZpY1wvY29tLmNocm9tZS5kZXZ0b29scy5qc29uIiwicm91dGUiOm51bGx9fQ==', 1776049078),
('ufTJXbwnvBvRSJuhh3DggKQnQUbugtRApC00cJse', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJadGRXUm1HSHpxRFlnaGxNVDdyMnRTTk5EMXltU2JDblpMdFZKc1VFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC8ud2VsbC1rbm93blwvYXBwc3BlY2lmaWNcL2NvbS5jaHJvbWUuZGV2dG9vbHMuanNvbiIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776049082),
('v2OGSzIKLD5lIGcuMed05vdAsL2ThKSnKXZLSBPO', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJUQTBaRFlRTFJDdUxYcXhjcUNYQkVTWml4Mk1mYlhkamlsQ1NTZWljIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1776049037),
('VDNiX48LHS2gy8Rq6dfr5a4HAAyEeDk14bqzQVwr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJodUZnYjlOajhzWTM0eWpwUTVEV0xjN3VWYzFqZVdrd2M3T0RBY2x2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9wcm9qZWN0cyIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776049858),
('VW5Te8jZbD2J2EVhQMNW7kPxl8EkemmwenplOWUy', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJQMEQyemtrZ3p1TVVjUnpMRGdSMWxCaFh1T0ZZaWk2QTd4NHQ0UjY3IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776046852),
('W5aWYvkqjLSBdYRbbLLUpNFu6JfyYOUDuRDoDKF4', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJTNDhRaEI1N0l6Z0I0cjI5S3FKeU1VZ1MxRm1vdDR1dFQxdE1mUURPIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC8ud2VsbC1rbm93blwvYXBwc3BlY2lmaWNcL2NvbS5jaHJvbWUuZGV2dG9vbHMuanNvbiIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776049131),
('wniZCLT6pH8k07n9BfZ2tO4qAL3oxTXEF9MtBbwC', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJUekNYc0pSSXlRZEV5SmpPZWVkQnFXVVVhVXpkTGVoQUt3NFlXSWx4IiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1776047358),
('WzKTGiUIRHSYnuSDxMEAkJcokxRVgSD5jFqj50Py', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJFalhMdko3djdWd0VBYTJSQ3ROY0N3UkhCOHl0NU12QWR1U0x3RkdtIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1776047779),
('z5j1SKLD5i43j4R5B8PgzPy81EhZTYqjeiud41Fe', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJHRUtpRkR0WTV2dTdDeTdhcjMyWE9Oek9LdW9lcURyUnVkdERKSzBlIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcLy53ZWxsLWtub3duXC9hcHBzcGVjaWZpY1wvY29tLmNocm9tZS5kZXZ0b29scy5qc29uIiwicm91dGUiOm51bGx9fQ==', 1776049159),
('ZM4U3G8ZWDx7W2LIK2VMJLe25NJDaNXTgMkzKSIZ', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJGQ1ozYndFZDIyUjRGZmt2TU5iZ0xvN29ZT3BGYWphYXRzNmI4RXduIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1776048283);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'owner',
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `role`, `username`, `email`, `no_telepon`, `password`, `created_at`, `updated_at`, `last_login_at`, `last_login_ip`) VALUES
(1, 'owner', 'yusuf', 'yusuf@gmail.com', NULL, '$2y$12$YXuv11nkkrD8AhRjrNwd6eYNT.sh6VQYt4yRYNfNAK4G3yGZJ73SG', '2026-03-30 01:46:25', '2026-03-30 01:46:25', NULL, NULL),
(2, 'owner', 'yusuff', 'yusuff@gmail.com', NULL, '$2y$12$vGPutUBnMPGL4/Jq8cSo3OcSzljlrkmrFANdbXejwCns7mxn1ltNG', '2026-03-30 01:47:35', '2026-03-30 01:47:35', NULL, NULL),
(4, 'salesman', 'Budi Sales', 'sales@mail.com', NULL, '$2y$12$DlI3Td2/hKWN941vmdZwceWCZvgA8.t0SxmStkAgADNyyFj8vDsbu', '2026-04-01 00:15:13', '2026-04-01 00:15:13', NULL, NULL),
(5, 'super_admin', 'Super Admin', 'admin@simak.app', NULL, '$2y$12$X.pBP1/Y/60P2/BX/bH6h.KVlLeZ8A79z72EZZ9b5u9zObEZv/tnu', '2026-04-08 11:31:06', '2026-04-08 11:31:06', NULL, NULL),
(6, 'owner', 'yusufff', 'yusufff@gmail.com', NULL, '$2y$12$Syh/1zS.6ap5/WKM4YQC2u11qv53EeJLz6wcvRwW.InrHfNsmKVmO', '2026-04-08 11:47:45', '2026-04-08 11:47:45', NULL, NULL),
(7, 'owner', 'yusuffff', 'yusuffff@gmail.com', NULL, '$2y$12$QebUPVTOclM/URHolfTJkuQ6FMnw9jumjgC0lb98Ps0IEozCf2vwu', '2026-04-08 20:29:53', '2026-04-08 20:29:53', NULL, NULL),
(9, 'salesman', 'Budi Sales 33', 'salesss@mail.com', NULL, '$2y$12$nA/iwDQ9DLL0RnZ3xIWsruMb/6jhORDshfea1LTVqGDCJATI21Oui', '2026-04-08 20:33:20', '2026-04-08 20:33:20', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_licenses`
--

CREATE TABLE `user_licenses` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `license_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_type` enum('trial','basic','premium','enterprise') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trial',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `start_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_licenses`
--

INSERT INTO `user_licenses` (`id`, `user_id`, `license_key`, `note`, `license_type`, `status`, `start_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'DEMO123', NULL, 'basic', 'active', '2026-03-30', '2026-03-30 01:46:25', '2026-03-30 01:46:25'),
(3, 6, 'SIMAK-202604-ODCS56YE', 'Untuk client PT ABC', 'trial', 'active', '2026-04-08', '2026-04-08 11:47:04', '2026-04-08 11:47:45'),
(4, 7, 'SIMAK-202604-Y1RZFSXV', 'Untuk client PT ABC', 'trial', 'active', '2026-04-09', '2026-04-08 20:29:26', '2026-04-08 20:29:53'),
(6, NULL, 'SIMAK-202604-JC2UBNMS', 'untuk gw', 'trial', 'available', NULL, '2026-04-12 11:41:38', '2026-04-12 11:41:38'),
(7, NULL, 'SIMAK-202604-PRXPMWQM', 'untuk lu', 'trial', 'available', NULL, '2026-04-12 11:41:46', '2026-04-12 11:41:46');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `alokasi_pembayaran_fleksibel`
--
ALTER TABLE `alokasi_pembayaran_fleksibel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_alokasi` (`fleksible_payment_id`,`angsuran_id`),
  ADD KEY `alokasi_pembayaran_fleksibel_angsuran_id_foreign` (`angsuran_id`);

--
-- Indeks untuk tabel `angsuran`
--
ALTER TABLE `angsuran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `angsuran_penjualan_id_bulan_ke_unique` (`penjualan_id`,`bulan_ke`),
  ADD KEY `angsuran_status_index` (`status`);

--
-- Indeks untuk tabel `buyers`
--
ALTER TABLE `buyers`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cash_flows`
--
ALTER TABLE `cash_flows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cash_flows_referensi_type_referensi_id_index` (`referensi_type`,`referensi_id`),
  ADD KEY `cash_flows_owner_id_foreign` (`owner_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `fleksible_payments`
--
ALTER TABLE `fleksible_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembayaran_fleksibel_penjualan_id_foreign` (`sales_transaction_id`),
  ADD KEY `pembayaran_fleksibel_created_by_foreign` (`created_by`),
  ADD KEY `pembayaran_fleksibel_status_index` (`status`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kavling`
--
ALTER TABLE `kavling`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kavling_project_id_blok_nomor_unique` (`project_id`,`blok_nomor`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_history_sales_transaction_id_foreign` (`sales_transaction_id`),
  ADD KEY `payment_history_referensi_type_referensi_id_index` (`referensi_type`,`referensi_id`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indeks untuk tabel `profile_perusahaan`
--
ALTER TABLE `profile_perusahaan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_user_id_foreign` (`user_id`),
  ADD KEY `sales_owner_id_foreign` (`owner_id`);

--
-- Indeks untuk tabel `sales_transactions`
--
ALTER TABLE `sales_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `penjualan_nomor_transaksi_unique` (`nomor_transaksi`),
  ADD KEY `penjualan_kavling_id_foreign` (`kavling_id`),
  ADD KEY `penjualan_buyer_id_foreign` (`buyer_id`),
  ADD KEY `penjualan_sales_id_foreign` (`sales_id`),
  ADD KEY `penjualan_nomor_transaksi_index` (`nomor_transaksi`),
  ADD KEY `penjualan_status_penjualan_index` (`status_penjualan`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indeks untuk tabel `user_licenses`
--
ALTER TABLE `user_licenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_licenses_license_key_unique` (`license_key`),
  ADD KEY `user_licenses_user_id_foreign` (`user_id`),
  ADD KEY `user_licenses_license_key_index` (`license_key`),
  ADD KEY `user_licenses_status_index` (`status`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `alokasi_pembayaran_fleksibel`
--
ALTER TABLE `alokasi_pembayaran_fleksibel`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `angsuran`
--
ALTER TABLE `angsuran`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=300;

--
-- AUTO_INCREMENT untuk tabel `buyers`
--
ALTER TABLE `buyers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `cash_flows`
--
ALTER TABLE `cash_flows`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `fleksible_payments`
--
ALTER TABLE `fleksible_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kavling`
--
ALTER TABLE `kavling`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `profile_perusahaan`
--
ALTER TABLE `profile_perusahaan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `project`
--
ALTER TABLE `project`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `sales_transactions`
--
ALTER TABLE `sales_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `user_licenses`
--
ALTER TABLE `user_licenses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `alokasi_pembayaran_fleksibel`
--
ALTER TABLE `alokasi_pembayaran_fleksibel`
  ADD CONSTRAINT `alokasi_pembayaran_fleksibel_angsuran_id_foreign` FOREIGN KEY (`angsuran_id`) REFERENCES `angsuran` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alokasi_pembayaran_fleksibel_pembayaran_fleksibel_id_foreign` FOREIGN KEY (`fleksible_payment_id`) REFERENCES `fleksible_payments` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `angsuran`
--
ALTER TABLE `angsuran`
  ADD CONSTRAINT `angsuran_penjualan_id_foreign` FOREIGN KEY (`penjualan_id`) REFERENCES `sales_transactions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `cash_flows`
--
ALTER TABLE `cash_flows`
  ADD CONSTRAINT `cash_flows_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `fleksible_payments`
--
ALTER TABLE `fleksible_payments`
  ADD CONSTRAINT `pembayaran_fleksibel_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `pembayaran_fleksibel_penjualan_id_foreign` FOREIGN KEY (`sales_transaction_id`) REFERENCES `sales_transactions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kavling`
--
ALTER TABLE `kavling`
  ADD CONSTRAINT `kavling_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_sales_transaction_id_foreign` FOREIGN KEY (`sales_transaction_id`) REFERENCES `sales_transactions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sales_transactions`
--
ALTER TABLE `sales_transactions`
  ADD CONSTRAINT `penjualan_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `penjualan_kavling_id_foreign` FOREIGN KEY (`kavling_id`) REFERENCES `kavling` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `penjualan_sales_id_foreign` FOREIGN KEY (`sales_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `user_licenses`
--
ALTER TABLE `user_licenses`
  ADD CONSTRAINT `user_licenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
