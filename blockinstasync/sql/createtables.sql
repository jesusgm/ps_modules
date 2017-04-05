-- phpMyAdmin SQL Dump
-- version 4.6.4deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 03, 2017 at 10:07 PM
-- Server version: 5.7.17-0ubuntu0.16.10.1
-- PHP Version: 7.0.15-0ubuntu0.16.10.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prestashop`
--

-- --------------------------------------------------------

--
-- Table structure for table `ps_instagramsync_images`
--

CREATE TABLE `ps_instagramsync_images` (
  `instagramsync_images_id` int(11) NOT NULL,
  `shown` tinyint(1) NOT NULL DEFAULT '1',
  `caption` text CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `instagram_id` varchar(255) NOT NULL,
  `instagram_link` varchar(255) NOT NULL,
  `instagram_user_name` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `likes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tabla para guardar info sobre las imagenes de instagram';

-- --------------------------------------------------------

--
-- Table structure for table `ps_instagramsync_image_product`
--

CREATE TABLE `ps_instagramsync_image_product` (
  `id_is_image_product` int(11) NOT NULL,
  `id_instagramsync_images` int(11) NOT NULL,
  `id_product` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tabla que relaciona imagenes de instagram con productos';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ps_instagramsync_images`
--
ALTER TABLE `ps_instagramsync_images`
  ADD PRIMARY KEY (`instagramsync_images_id`);

--
-- Indexes for table `ps_instagramsync_image_product`
--
ALTER TABLE `ps_instagramsync_image_product`
  ADD PRIMARY KEY (`id_is_image_product`),
  ADD KEY `id_instagramsync_images` (`id_instagramsync_images`,`id_product`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ps_instagramsync_images`
--
ALTER TABLE `ps_instagramsync_images`
  MODIFY `instagramsync_images_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;
--
-- AUTO_INCREMENT for table `ps_instagramsync_image_product`
--
ALTER TABLE `ps_instagramsync_image_product`
  MODIFY `id_is_image_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
