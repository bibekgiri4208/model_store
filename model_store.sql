-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2026 at 03:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `model_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Hypercars', 'hypercars'),
(2, 'Supercars', 'supercars'),
(3, 'Classic & Vintage', 'classic-vintage'),
(4, 'Motorsport', 'motorsport');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(20) DEFAULT 'esewa',
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `status` enum('Pending','Processing','Shipped','Completed','Cancelled') DEFAULT 'Pending',
  `transaction_uuid` varchar(100) DEFAULT NULL,
  `esewa_ref_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `full_name`, `phone`, `address`, `city`, `total_amount`, `payment_method`, `payment_status`, `status`, `transaction_uuid`, `esewa_ref_id`, `created_at`) VALUES
(1, NULL, 'Bibek', '9876543210', 'naya gau pokhara', 'pokhara', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-1787053672-3947', NULL, '2026-08-18 11:47:52'),
(2, NULL, 'bibek giri', '9876543210', 'naya gau pokhara', 'pokhara', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-1787053696-6090', NULL, '2026-08-18 11:48:16'),
(3, 2, 'bibek giri', '9876543210', 'naya gau pokhara', 'pokhara', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-3-1787053848', NULL, '2026-08-18 11:50:48'),
(4, 2, 'bibeke', '9876543219', 'naya gau', 'pokhara', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-4-1787053871', NULL, '2026-08-18 11:51:11'),
(5, 2, 'Cailin Bryant', '+1 (305) 493-5662', 'Est placeat sit des', 'Et harum qui volupta', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-5-1787054048', NULL, '2026-08-18 11:54:08'),
(6, 2, 'Bell Shaw', '+1 (888) 589-1072', 'Itaque aut minim eiu', 'Aliquip rerum obcaec', 22275.00, 'cod', 'pending', 'Pending', 'ORD-6-1787054410', NULL, '2026-08-18 12:00:10'),
(7, 2, 'Quin Daugherty', '+1 (491) 686-2419', 'Eius cumque qui volu', 'Omnis consectetur a', 30375.00, 'esewa', 'pending', 'Pending', 'ORD-7-1787054425', NULL, '2026-08-18 12:00:25'),
(8, 2, 'Rama Petersen', '+1 (275) 689-7567', 'Accusamus quae autem', 'Facilis non magnam l', 22275.00, 'esewa', 'completed', 'Processing', 'ORD-8-1787054496', '000GGBU', '2026-08-18 12:01:36'),
(9, 1, 'Ashton Berg', '+1 (444) 172-1842', 'Qui tempora quis vol', 'Veniam perferendis', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-9-1787061338', NULL, '2026-08-18 13:55:38'),
(10, 1, 'Ashton Berg', '+1 (444) 172-1842', 'Qui tempora quis vol', 'Veniam perferendis', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-10-1787061361', NULL, '2026-08-18 13:56:01'),
(11, 2, 'Luke Thomas', '+1 (804) 184-9196', 'Recusandae Eu quo u', 'Optio doloremque cu', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-11-1787062188', NULL, '2026-08-18 14:09:48'),
(12, 2, 'Luke Thomas', '+1 (804) 184-9196', 'Recusandae Eu quo u', 'Optio doloremque cu', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-12-1787062379', NULL, '2026-08-18 14:12:59'),
(13, 2, 'Ruby Calhoun', '+1 (678) 408-1708', 'Exercitation quaerat', 'Ut nesciunt ab anim', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-13-1787062619', NULL, '2026-08-18 14:16:59'),
(14, 2, 'Gareth Mclean', '+1 (146) 904-6297', 'Aliquid anim ab in e', 'Corporis dolor qui i', 23625.00, 'esewa', 'pending', 'Pending', 'ORD-14-1787063816', NULL, '2026-08-18 14:36:56'),
(15, 2, 'Demetrius Spence', '+1 (354) 978-5393', 'Omnis accusantium eo', 'Dolorum consequatur', 2399.00, 'esewa', 'completed', 'Shipped', 'ORD-15-1787064052', '000GGCO', '2026-08-18 14:40:52'),
(16, NULL, 'Jakeem Hayden', '+1 (432) 277-8705', 'Obcaecati est consec', 'Adipisicing similiqu', 2199.00, 'cod', 'pending', 'Pending', 'ORD-16-1787067381', NULL, '2026-08-18 15:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(1, 1, 6, 1, 23625.00),
(2, 2, 6, 1, 23625.00),
(3, 3, 6, 1, 23625.00),
(4, 4, 6, 1, 23625.00),
(5, 5, 6, 1, 23625.00),
(6, 6, 5, 1, 22275.00),
(7, 7, 4, 1, 30375.00),
(8, 8, 5, 1, 22275.00),
(9, 9, 6, 1, 23625.00),
(10, 10, 6, 1, 23625.00),
(11, 11, 6, 1, 23625.00),
(12, 12, 6, 1, 23625.00),
(13, 13, 6, 1, 23625.00),
(14, 14, 6, 1, 23625.00),
(15, 15, 6, 1, 2399.00),
(16, 16, 8, 1, 2199.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `scale` varchar(10) DEFAULT '1:18',
  `type` varchar(20) DEFAULT 'Diecast',
  `image_url` varchar(255) DEFAULT 'assets/images/placeholder.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `title`, `description`, `price`, `stock`, `scale`, `type`, `image_url`, `created_at`) VALUES
(1, 2, 'Porsche 911 GT3 RS (992)', 'Precision 1:18 scale model featuring active rear wing detail and opening doors.', 2499.00, 12, '1:18', 'Diecast', 'https://images.unsplash.com/photo-1614162692292-7ac56d7f7f1e?auto=format&fit=crop&w=1000&q=80', '2026-08-18 11:47:45'),
(2, 3, 'Ferrari F40 Competizione', 'Iconic 1:18 replica showcasing twin-turbo V8 engine details and pop-up headlights.', 2850.00, 5, '1:18', 'Diecast', 'https://images.unsplash.com/photo-1691488607965-61ae154c1ab4?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxmZXJyYXJpJTIwZjQwfGVufDB8fHx8MTc4NzA2NTQzMnww', '2026-08-18 11:47:45'),
(3, 1, 'Lamborghini Aventador SVJ', 'Aggressive aerodynamics recreated in 1:18 scale featuring functional scissor doors.', 2650.00, 8, '1:18', 'Diecast', 'https://images.unsplash.com/photo-1519245659620-e859806a8d3b?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxsYW1ib3JnaGluaSUyMGF2ZW50YWRvcnxlbnwwfHx8fDE3ODcwNjU1MjZ8MA', '2026-08-18 11:47:45'),
(4, 1, 'McLaren Senna GT', 'Track-focused hypercar model with intricate rear diffuser detailing and carbon accents.', 2999.00, 4, '1:18', 'Diecast', 'https://images.unsplash.com/photo-1617814086906-d847a8bc6fca?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxtY2xhcmVufGVufDB8fHx8MTc4NzA2NTUyN3ww', '2026-08-18 11:47:45'),
(5, 3, 'Nissan Skyline GT-R R34', 'Legendary JDM classic replicated with detailed engine bay and gold Brembo calipers.', 2250.00, 15, '1:18', 'Diecast', 'https://images.unsplash.com/photo-1611859266238-4b98091d9d9b?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxuaXNzYW4lMjBza3lsaW5lJTIwZ3RyfGVufDB8fHx8MTc4NzA2NTUyOHww', '2026-08-18 11:47:45'),
(6, 4, 'Audi R8 LMS GT3', '1:18 FIA GT3 racing spec replica complete with full sponsor livery print.', 2399.00, 9, '1:18', 'Diecast', 'https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxhdWRpJTIwcjh8ZW58MHx8fHwxNzg3MDY1NTI5fDA', '2026-08-18 11:47:45'),
(7, 3, 'Lamborghini Countach LP400', 'Iconic wedge-shaped V12 supercar from the 1970s, faithfully reproduced with opening doors and detailed engine bay.', 2799.00, 12, '1:18', 'Diecast', 'https://images.unsplash.com/photo-1654938900760-1419ee86bc1d?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxsYW1ib3JnaGluaSUyMGNvdW50YWNofGVufDB8fHx8MTc4NzA2NTUzMHww', '2026-08-18 14:50:53'),
(8, 2, 'Toyota Supra MK4', 'The legendary 2JZ-powered Supra in diecast form, finished with crisp tampering and rolling wheels.', 2199.00, 15, '1:64', 'Diecast', 'https://images.unsplash.com/photo-1603811478698-0b1d6256f79a?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHx0b3lvdGElMjBzdXByYXxlbnwwfHx8fDE3ODcwNjU1MzF8MA', '2026-08-18 14:50:53'),
(9, 4, 'BMW M4 GT3', 'Race-bred GT3 machine in full racing livery, featuring fine resin cast detail and aero package.', 2450.00, 10, '1:43', 'Resin', 'https://images.unsplash.com/photo-1683638287438-7710b87ffb41?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxibXclMjBtNHxlbnwwfHx8fDE3ODcwNjU1MzJ8MA', '2026-08-18 14:50:53'),
(10, 2, 'Mercedes-AMG GT R', 'The Beast of the Green Hell reproduced with precision — opening scissor doors and detailed cockpit.', 2699.00, 11, '1:18', 'Diecast', 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxtZXJjZWRlcyUyMGFtZyUyMGd0fGVufDB8fHx8MTc4NzA2NTUzM3ww', '2026-08-18 14:50:53'),
(11, 2, 'Chevrolet Corvette C8', 'Mid-engine American icon in 1:43 scale with sharp body lines and a spot-on rear wing.', 2550.00, 13, '1:43', 'Diecast', 'https://images.unsplash.com/photo-1619405399517-d7fce0f13302?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxjaGV2cm9sZXQlMjBjb3J2ZXR0ZXxlbnwwfHx8fDE3ODcwNjU1MzR8MA', '2026-08-18 14:50:53'),
(12, 2, 'Ford Mustang Shelby GT500', 'Supercharged 5.2L V8 muscle legend, diecast with opening doors, hood and functional suspension.', 2325.00, 14, '1:18', 'Diecast', 'https://images.unsplash.com/photo-1625231334168-35067f8853ed?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxmb3JkJTIwbXVzdGFuZyUyMGd0NTAwfGVufDB8fHx8MTc4NzA2NTUzNXww', '2026-08-18 14:50:53'),
(13, 1, 'Porsche 911 Turbo S (992)', 'Pocket-sized Turbo S with wide-body arches and the unmistakable 911 silhouette.', 2899.00, 9, '1:64', 'Diecast', 'https://images.unsplash.com/photo-1597858520171-563a8e8b9925?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxwb3JzY2hlJTIwOTExJTIwdHVyYm98ZW58MHx8fHwxNzg3MDY1NTM3fDA', '2026-08-18 14:50:53'),
(14, 3, 'Aston Martin DB5', 'The legendary 007 grand tourer in polished silver, complete with detailed interior and wire wheels.', 2999.00, 8, '1:18', 'Diecast', 'https://images.unsplash.com/photo-1573593612929-45d67798b534?auto=format&fit=crop&w=1000&q=80&ixid=M3w5NzB8MHwxfHNlYXJjaHwxfHxhc3RvbiUyMG1hcnRpbnxlbnwwfHx8fDE3ODcwNjU1MzZ8MA', '2026-08-18 14:50:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Bibek Giri', 'giribibek0101@gmail.com', '$2y$10$zEmjzXsMuTZaPFVUejsVgOUwKka8WF9XhJ1Nd8jw6nPNKQGy.Qu1q', 'admin', '2026-08-18 11:47:45'),
(2, 'Jester', 'jester@gmail.com', '$2y$10$OEIqM0qGIltJnbFm8qTCUO9NuofZpxhxBYoBsQwx.Qnn0oUsreaxe', 'user', '2026-08-18 11:47:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_uuid` (`transaction_uuid`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
