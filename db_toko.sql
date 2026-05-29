-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 11 Des 2022 pada 06.11
-- Versi server: 10.5.17-MariaDB-cll-lve
-- Versi PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_toko`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `idBuku` varchar(10) NOT NULL,
  `idKategori` int(11) DEFAULT NULL,
  `idRak` int(11) DEFAULT NULL,
  `barcode` varchar(30) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `noisbn` varchar(50) DEFAULT NULL,
  `penulis` varchar(50) DEFAULT NULL,
  `penerbit` varchar(50) NOT NULL,
  `tahun` varchar(4) NOT NULL,
  `stock` int(11) UNSIGNED NOT NULL,
  `hargaPokok` int(11) NOT NULL,
  `hargaJual` int(11) NOT NULL,
  `disc` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`idBuku`, `idKategori`, `idRak`, `barcode`, `judul`, `noisbn`, `penulis`, `penerbit`, `tahun`, `stock`, `hargaPokok`, `hargaJual`, `disc`) VALUES
('BK00001', 2, 3, '9786230032998', 'Nasha Square Basic Series - Black', '9786230032998', 'Black', 'Shopee', '2021', 9, 16500, 34900, 0),
('BK00002', 2, 3, '9786230025945', 'Nasha Square Basic Series - Navy', '9786230025945', 'Navy', 'Shopee', '2021', 20, 16500, 34900, 0),
('BK00003', 2, 3, '9786230042485', 'Nasha Square Basic Series - Broken White', '9786230042485', 'Broken White', 'Shopee', '2021', 22, 16500, 34900, 0),
('BK00004', 2, 3, '9786230028328', 'Nasha Square Basic Series - Cloud', '9786230028328', 'Smoke Grey', 'Shopee', '2021', 20, 16500, 34900, 0),
('BK00005', 2, 3, '9786230010897', 'Nasha Square Basic Series - Flint', '9786230010897', 'Iron', 'Shopee', '2021', 13, 16500, 34900, 0),
('BK00006', 2, 3, '9786230052149', 'Nasha Square Basic Series - Anchor', '9786230052149', 'Charcoal', 'Shopee', '2021', 14, 16500, 34900, 0),
('BK00007', 2, 3, '9786230034152', 'Nasha Square Basic Series - Fossil', '9786230034152', 'Fog', 'Shopee', '2021', 0, 16500, 34900, 0),
('BK00008', 2, 3, '9786230071218', 'Nasha Square Basic Series - Graphite', '9786230071218', 'Oreo Cookies', 'Shopee', '2021', 18, 16500, 34900, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pembelian`
--

CREATE TABLE `detail_pembelian` (
  `idDetailPembelian` varchar(6) NOT NULL,
  `idPembelian` varchar(20) NOT NULL,
  `idBuku` varchar(10) DEFAULT NULL,
  `judul` varchar(100) NOT NULL,
  `hargaPokok` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `detail_pembelian`
--

INSERT INTO `detail_pembelian` (`idDetailPembelian`, `idPembelian`, `idBuku`, `judul`, `hargaPokok`, `jumlah`) VALUES
('P00001', 'P0001-170304-101222', 'BK00008', 'Nasha Square Basic Series - Graphite', 16500, 10),
('P00002', 'P0001-170304-101222', 'BK00007', 'Nasha Square Basic Series - Fossil', 16500, 10),
('P00003', 'P0001-170304-101222', 'BK00006', 'Nasha Square Basic Series - Anchor', 16500, 10),
('P00004', 'P0001-170304-101222', 'BK00001', 'Nasha Square Basic Series - Black', 16500, 10),
('P00005', 'P0001-170304-101222', 'BK00002', 'Nasha Square Basic Series - Navy', 16500, 10),
('P00006', 'P0001-170304-101222', 'BK00003', 'Nasha Square Basic Series - Broken White', 16500, 10),
('P00007', 'P0001-170304-101222', 'BK00004', 'Nasha Square Basic Series - Cloud', 16500, 10),
('P00008', 'P0001-170304-101222', 'BK00005', 'Nasha Square Basic Series - Flint', 16500, 10),
('P00009', 'P0002-170304-101222', 'BK00001', 'Nasha Square Basic Series - Black', 16500, 5),
('P00010', 'P0002-170304-101222', 'BK00002', 'Nasha Square Basic Series - Navy', 16500, 5),
('P00011', 'P0002-170304-101222', 'BK00003', 'Nasha Square Basic Series - Broken White', 16500, 5),
('P00012', 'P0002-170304-101222', 'BK00004', 'Nasha Square Basic Series - Cloud', 16500, 5),
('P00013', 'P0002-170304-101222', 'BK00005', 'Nasha Square Basic Series - Flint', 16500, 5),
('P00014', 'P0003-170305-101222', 'BK00008', 'Nasha Square Basic Series - Graphite', 16500, 15),
('P00015', 'P0003-170305-101222', 'BK00007', 'Nasha Square Basic Series - Fossil', 16500, 15),
('P00016', 'P0003-170305-101222', 'BK00006', 'Nasha Square Basic Series - Anchor', 16500, 15),
('P00017', 'P0004-170305-101222', 'BK00005', 'Nasha Square Basic Series - Flint', 16500, 10),
('P00018', 'P0004-170305-101222', 'BK00004', 'Nasha Square Basic Series - Cloud', 16500, 10),
('P00019', 'P0004-170305-101222', 'BK00003', 'Nasha Square Basic Series - Broken White', 16500, 10),
('P00020', 'P0004-170305-101222', 'BK00002', 'Nasha Square Basic Series - Navy', 16500, 10),
('P00021', 'P0004-170305-101222', 'BK00001', 'Nasha Square Basic Series - Black', 16500, 10);

--
-- Trigger `detail_pembelian`
--
DELIMITER $$
CREATE TRIGGER `update stock` BEFORE INSERT ON `detail_pembelian` FOR EACH ROW UPDATE buku a set a.stock = a.stock + new.jumlah where a.idBuku = new.idBuku
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `idDetailPenjualan` varchar(6) NOT NULL,
  `idPenjualan` varchar(20) NOT NULL,
  `idBuku` varchar(10) DEFAULT NULL,
  `judul` varchar(100) NOT NULL,
  `hargaPokok` int(11) NOT NULL,
  `hargaJual` int(11) NOT NULL,
  `disc` float NOT NULL,
  `ppn` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `detail_penjualan`
--

INSERT INTO `detail_penjualan` (`idDetailPenjualan`, `idPenjualan`, `idBuku`, `judul`, `hargaPokok`, `hargaJual`, `disc`, `ppn`, `jumlah`) VALUES
('T00001', 'T0001-170302-101222', 'BK00008', 'Nasha Square Basic Series - Graphite', 16500, 34900, 0, 6980, 2),
('T00002', 'T0001-170302-101222', 'BK00007', 'Nasha Square Basic Series - Fossil', 16500, 34900, 0, 3490, 1),
('T00003', 'T0001-170302-101222', 'BK00006', 'Nasha Square Basic Series - Anchor', 16500, 34900, 0, 10470, 3),
('T00004', 'T0002-170302-101222', 'BK00004', 'Nasha Square Basic Series - Cloud', 16500, 34900, 0, 17450, 5),
('T00005', 'T0002-170302-101222', 'BK00002', 'Nasha Square Basic Series - Navy', 16500, 34900, 0, 17450, 5),
('T00006', 'T0003-170302-101222', 'BK00008', 'Nasha Square Basic Series - Graphite', 16500, 34900, 0, 17450, 5),
('T00007', 'T0003-170302-101222', 'BK00001', 'Nasha Square Basic Series - Black', 16500, 34900, 0, 27920, 8),
('T00008', 'T0003-170302-101222', 'BK00003', 'Nasha Square Basic Series - Broken White', 16500, 34900, 0, 10470, 3),
('T00009', 'T0004-170303-101222', 'BK00007', 'Nasha Square Basic Series - Fossil', 16500, 34900, 0, 34900, 10),
('T00010', 'T0004-170303-101222', 'BK00006', 'Nasha Square Basic Series - Anchor', 16500, 34900, 0, 27920, 8),
('T00011', 'T0004-170303-101222', 'BK00005', 'Nasha Square Basic Series - Flint', 16500, 34900, 0, 41880, 12),
('T00012', 'T0005-170303-101222', 'BK00001', 'Nasha Square Basic Series - Black', 16500, 34900, 0, 27920, 8),
('T00013', 'T0005-170303-101222', 'BK00007', 'Nasha Square Basic Series - Fossil', 16500, 34900, 0, 48860, 14);

--
-- Trigger `detail_penjualan`
--
DELIMITER $$
CREATE TRIGGER `update_stok_buku` BEFORE INSERT ON `detail_penjualan` FOR EACH ROW UPDATE buku a set a.stock = a.stock - new.jumlah where idBuku = new.idBuku
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `distributor`
--

CREATE TABLE `distributor` (
  `idDist` varchar(10) NOT NULL,
  `namaDist` varchar(50) NOT NULL,
  `alamat` varchar(250) NOT NULL,
  `telepon` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `distributor`
--

INSERT INTO `distributor` (`idDist`, `namaDist`, `alamat`, `telepon`) VALUES
('DIS0001', 'JasmineJilbab', 'https://shopee.co.id/jasminejilbab', '085854749138'),
('DIS0002', 'Sally_scarf', 'https://shopee.co.id/sally_scarf', '0265213122'),
('DIS0003', 'RPM', 'Cilacap', '0265213123');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `idKategori` int(11) NOT NULL,
  `nama_kategori` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`idKategori`, `nama_kategori`) VALUES
(1, 'Pashmina Ceruty Babydoll'),
(2, 'Segi Empat Polycotton'),
(3, 'Bergo Diamond Italiano'),
(4, 'Masker Motif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_pengeluaran`
--

CREATE TABLE `kategori_pengeluaran` (
  `idKategoriPengeluaran` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `kategori_pengeluaran`
--

INSERT INTO `kategori_pengeluaran` (`idKategoriPengeluaran`, `nama`) VALUES
(1, 'Gaji Karyawan'),
(2, 'Pembelian Buku'),
(3, 'Bayar Pajak'),
(4, 'StokOpName'),
(5, 'Listrik Dan Air');

-- --------------------------------------------------------

--
-- Struktur dari tabel `opname`
--

CREATE TABLE `opname` (
  `idOpname` int(11) NOT NULL,
  `idBuku` varchar(10) DEFAULT NULL,
  `judul` varchar(100) NOT NULL,
  `tanggal` datetime NOT NULL,
  `stokSistem` int(11) NOT NULL,
  `stokNyata` int(11) NOT NULL,
  `hargaPokok` int(11) NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian`
--

CREATE TABLE `pembelian` (
  `idPembelian` varchar(20) NOT NULL,
  `idUser` varchar(10) DEFAULT NULL,
  `idDist` varchar(10) DEFAULT NULL,
  `namaUser` varchar(50) NOT NULL,
  `namaDist` varchar(50) NOT NULL,
  `total` int(11) NOT NULL,
  `tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `pembelian`
--

INSERT INTO `pembelian` (`idPembelian`, `idUser`, `idDist`, `namaUser`, `namaDist`, `total`, `tanggal`) VALUES
('P0001-170304-101222', '170304', 'DIS0001', 'Arya Saputra', 'JasmineJilbab', 825000, '2022-12-10 15:31:22'),
('P0002-170304-101222', '170304', 'DIS0002', 'Arya Saputra', 'Sally_scarf', 412500, '2022-12-10 15:33:09'),
('P0003-170305-101222', '170305', 'DIS0003', 'Arsya Saputra', 'RPM', 742500, '2022-12-10 15:33:55'),
('P0004-170305-101222', '170305', 'DIS0003', 'Arsya Saputra', 'RPM', 825000, '2022-12-10 15:34:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `idPengaturan` int(11) NOT NULL,
  `idUser` varchar(10) DEFAULT NULL,
  `nama_toko` varchar(20) NOT NULL,
  `alamat_toko` varchar(50) NOT NULL,
  `telepon_toko` varchar(15) NOT NULL,
  `logo` varchar(50) NOT NULL,
  `pakaiLogo` tinyint(1) DEFAULT 1,
  `ppn` double NOT NULL,
  `min_stok` int(11) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`idPengaturan`, `idUser`, `nama_toko`, `alamat_toko`, `telepon_toko`, `logo`, `pakaiLogo`, `ppn`, `min_stok`) VALUES
(1, '170301', 'Toko Buku Kita', 'Jl Argasari 1 No 4', '(0265) 9107713', '1635036266.png', 1, 10, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `idPengeluaran` varchar(20) NOT NULL,
  `idPembelian` varchar(20) DEFAULT NULL,
  `idOpname` int(11) DEFAULT NULL,
  `idPajak` varchar(21) DEFAULT NULL,
  `idKategoriPengeluaran` int(11) DEFAULT NULL,
  `idUser` varchar(10) DEFAULT NULL,
  `namaUser` varchar(50) DEFAULT NULL,
  `namaKategori` varchar(50) NOT NULL,
  `pengeluaran` int(11) NOT NULL,
  `keterangan` varchar(30) DEFAULT NULL,
  `tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `pengeluaran`
--

INSERT INTO `pengeluaran` (`idPengeluaran`, `idPembelian`, `idOpname`, `idPajak`, `idKategoriPengeluaran`, `idUser`, `namaUser`, `namaKategori`, `pengeluaran`, `keterangan`, `tanggal`) VALUES
('BOP001-170304-101222', 'P0001-170304-101222', NULL, NULL, 2, NULL, NULL, 'Pembelian Buku', 825000, 'JasmineJilbab', '2022-12-10 15:31:22'),
('BOP002-170304-101222', 'P0002-170304-101222', NULL, NULL, 2, NULL, NULL, 'Pembelian Buku', 412500, 'Sally_scarf', '2022-12-10 15:33:09'),
('BOP003-170305-101222', 'P0003-170305-101222', NULL, NULL, 2, NULL, NULL, 'Pembelian Buku', 742500, 'RPM', '2022-12-10 15:33:55'),
('BOP004-170305-101222', 'P0004-170305-101222', NULL, NULL, 2, NULL, NULL, 'Pembelian Buku', 825000, 'RPM', '2022-12-10 15:34:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `idPenjualan` varchar(20) NOT NULL,
  `idUser` varchar(10) DEFAULT NULL,
  `namaUser` varchar(50) NOT NULL,
  `total` int(11) NOT NULL,
  `ppn` int(11) NOT NULL,
  `tanggal` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`idPenjualan`, `idUser`, `namaUser`, `total`, `ppn`, `tanggal`, `status`) VALUES
('T0001-170302-101222', '170302', 'Erna Ratnasari', 230340, 20940, '2022-12-10 15:35:47', 1),
('T0002-170302-101222', '170302', 'Erna Ratnasari', 383900, 34900, '2022-12-10 15:36:07', 1),
('T0003-170302-101222', '170302', 'Erna Ratnasari', 614240, 55840, '2022-12-10 20:41:04', 1),
('T0004-170303-101222', '170303', 'Rindiani', 1151700, 104700, '2022-12-10 20:44:08', 1),
('T0005-170303-101222', '170303', 'Rindiani', 844580, 76780, '2022-12-10 20:45:06', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `ppn`
--

CREATE TABLE `ppn` (
  `idPajak` varchar(21) NOT NULL,
  `idPenjualan` varchar(20) DEFAULT NULL,
  `idUser` varchar(10) DEFAULT NULL,
  `jenis` varchar(100) NOT NULL,
  `nominal` int(11) NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  `tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `ppn`
--

INSERT INTO `ppn` (`idPajak`, `idPenjualan`, `idUser`, `jenis`, `nominal`, `keterangan`, `tanggal`) VALUES
('PPN0001-170302-101222', 'T0001-170302-101222', '170302', 'PPN Dikeluarkan', 20940, 'T0001', '2022-12-10 15:35:47'),
('PPN0002-170302-101222', 'T0002-170302-101222', '170302', 'PPN Dikeluarkan', 34900, 'T0002', '2022-12-10 15:36:07'),
('PPN0003-170302-101222', 'T0003-170302-101222', '170302', 'PPN Dikeluarkan', 55840, 'T0003', '2022-12-10 20:41:04'),
('PPN0004-170303-101222', 'T0004-170303-101222', '170303', 'PPN Dikeluarkan', 104700, 'T0004', '2022-12-10 20:44:08'),
('PPN0005-170303-101222', 'T0005-170303-101222', '170303', 'PPN Dikeluarkan', 76780, 'T0005', '2022-12-10 20:45:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rak`
--

CREATE TABLE `rak` (
  `idRak` int(11) NOT NULL,
  `nama_rak` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `rak`
--

INSERT INTO `rak` (`idRak`, `nama_rak`) VALUES
(1, 'Laser Cut'),
(2, 'Neci'),
(3, 'Jahit Tepi'),
(4, 'Motif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `idUser` varchar(10) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `alamat` varchar(250) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(64) NOT NULL,
  `hakAkses` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`idUser`, `nama`, `alamat`, `telepon`, `username`, `password`, `hakAkses`) VALUES
('170301', 'Aldi', 'Ciamis', '082121397663', 'aldi', '5cf15fc7e77e85f5d525727358c0ffc9', '1'),
('170302', 'Erna Ratnasari', 'Bandung', '0821213977632', 'erna', '035b3c6377652bd7d49b5d2e9a53ef40', '2'),
('170303', 'Rindiani', 'Bandung', '082727272', 'ririn', 'b84a4059d1af6c8b50bb7a28290dbd63', '2'),
('170304', 'Arya Saputra', 'Tasik', '082181412', 'arya', '5882985c8b1e2dce2763072d56a1d6e5', '3'),
('170305', 'Arsya Saputra', 'Tasik', '0821747121', 'arsya', '6582c680591052c3bed506891a0560be', '3');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`idBuku`),
  ADD KEY `fk_rakbuku` (`idRak`),
  ADD KEY `fk_kategoribuku` (`idKategori`);

--
-- Indeks untuk tabel `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD PRIMARY KEY (`idDetailPembelian`),
  ADD KEY `fk_detailpasok` (`idPembelian`),
  ADD KEY `fk_detailpasokbuku` (`idBuku`);

--
-- Indeks untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD PRIMARY KEY (`idDetailPenjualan`),
  ADD KEY `fk_transaksipenjualan` (`idPenjualan`),
  ADD KEY `fk_transaksibuku` (`idBuku`);

--
-- Indeks untuk tabel `distributor`
--
ALTER TABLE `distributor`
  ADD PRIMARY KEY (`idDist`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`idKategori`);

--
-- Indeks untuk tabel `kategori_pengeluaran`
--
ALTER TABLE `kategori_pengeluaran`
  ADD PRIMARY KEY (`idKategoriPengeluaran`);

--
-- Indeks untuk tabel `opname`
--
ALTER TABLE `opname`
  ADD PRIMARY KEY (`idOpname`),
  ADD KEY `fk_opname_buku` (`idBuku`);

--
-- Indeks untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`idPembelian`),
  ADD KEY `fk_pasok_user` (`idUser`),
  ADD KEY `fk_pasok_dist` (`idDist`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`idPengaturan`),
  ADD KEY `fk_pengaturan_user` (`idUser`);

--
-- Indeks untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`idPengeluaran`),
  ADD KEY `fk_pengeluaran_user` (`idUser`),
  ADD KEY `fk_kategori_pengeluaran` (`idKategoriPengeluaran`),
  ADD KEY `fk_pasok_pengeluaran` (`idPembelian`),
  ADD KEY `fk_pengeluaran_opname` (`idOpname`),
  ADD KEY `fk_pengeluaran_ppn` (`idPajak`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`idPenjualan`),
  ADD KEY `fk_penjualan_kasir` (`idUser`);

--
-- Indeks untuk tabel `ppn`
--
ALTER TABLE `ppn`
  ADD PRIMARY KEY (`idPajak`),
  ADD KEY `idUser` (`idUser`),
  ADD KEY `idPenjualan` (`idPenjualan`);

--
-- Indeks untuk tabel `rak`
--
ALTER TABLE `rak`
  ADD PRIMARY KEY (`idRak`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`idUser`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `idKategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `kategori_pengeluaran`
--
ALTER TABLE `kategori_pengeluaran`
  MODIFY `idKategoriPengeluaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `opname`
--
ALTER TABLE `opname`
  MODIFY `idOpname` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `idPengaturan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `rak`
--
ALTER TABLE `rak`
  MODIFY `idRak` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `fk_kategoribuku` FOREIGN KEY (`idKategori`) REFERENCES `kategori` (`idKategori`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_rakbuku` FOREIGN KEY (`idRak`) REFERENCES `rak` (`idRak`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD CONSTRAINT `fk_detailpasok` FOREIGN KEY (`idPembelian`) REFERENCES `pembelian` (`idPembelian`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detailpasokbuku` FOREIGN KEY (`idBuku`) REFERENCES `buku` (`idBuku`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `fk_transaksibuku` FOREIGN KEY (`idBuku`) REFERENCES `buku` (`idBuku`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaksipenjualan` FOREIGN KEY (`idPenjualan`) REFERENCES `penjualan` (`idPenjualan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `opname`
--
ALTER TABLE `opname`
  ADD CONSTRAINT `fk_opname_buku` FOREIGN KEY (`idBuku`) REFERENCES `buku` (`idBuku`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `fk_pasok_dist` FOREIGN KEY (`idDist`) REFERENCES `distributor` (`idDist`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pasok_user` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD CONSTRAINT `fk_pengaturan_user` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `fk_kategori_pengeluaran` FOREIGN KEY (`idKategoriPengeluaran`) REFERENCES `kategori_pengeluaran` (`idKategoriPengeluaran`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pasok_pengeluaran` FOREIGN KEY (`idPembelian`) REFERENCES `pembelian` (`idPembelian`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pengeluaran_opname` FOREIGN KEY (`idOpname`) REFERENCES `opname` (`idOpname`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pengeluaran_ppn` FOREIGN KEY (`idPajak`) REFERENCES `ppn` (`idPajak`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pengeluaran_user` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `fk_penjualan_kasir` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `ppn`
--
ALTER TABLE `ppn`
  ADD CONSTRAINT `ppn_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppn_ibfk_2` FOREIGN KEY (`idPenjualan`) REFERENCES `penjualan` (`idPenjualan`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
