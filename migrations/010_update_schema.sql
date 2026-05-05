-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 14-04-2025 a las 21:04:51
-- Versión del servidor: 8.0.41-0ubuntu0.20.04.1
-- Versión de PHP: 7.4.3-4ubuntu2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `botcanchero`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `api_token`
--

CREATE TABLE `api_token` (
  `id` int NOT NULL,
  `token` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `api_token`
--

INSERT INTO `api_token` (`id`, `token`) VALUES
(1, 'FF3316F6722CBD663A607ED012BF13FFAA13DC13F7D8A242AAC95807B2F4B8A5');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `booking`
--

CREATE TABLE `booking` (
  `id` int NOT NULL,
  `id_customer` int NOT NULL,
  `id_field` int NOT NULL,
  `day_booking` int NOT NULL,
  `time_booking` int NOT NULL,
  `date_booking` date DEFAULT NULL,
  `user` int NOT NULL DEFAULT '0',
  `paymet` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `booking`
--

INSERT INTO `booking` (`id`, `id_customer`, `id_field`, `day_booking`, `time_booking`, `date_booking`, `user`, `paymet`, `status`) VALUES
(1, 1, 66, 1, 23, '2023-12-04', 1, NULL, 3),
(232, 2, 66, 1, 22, '2023-12-04', 1, NULL, 3),
(233, 3, 66, 1, 22, '2023-12-04', 21, NULL, 2),
(234, 4, 66, 1, 24, '2023-12-04', 1, NULL, 3),
(236, 2, 66, 1, 21, '2023-12-04', 1, NULL, 3),
(237, 8, 67, 2, 17, '2023-12-12', 1, NULL, 3),
(285, 2, 67, 1, 21, '2023-12-11', 21, NULL, 3),
(286, 6, 67, 1, 23, '2023-12-11', 21, NULL, 3),
(287, 6, 66, 1, 21, '2023-12-11', 21, NULL, 3),
(288, 6, 67, 1, 20, '2023-12-11', 1, NULL, 3),
(289, 10, 67, 1, 24, '2023-12-11', 1, NULL, 2),
(290, 1, 67, 5, 20, '2023-12-15', 51, NULL, 2),
(291, 1, 67, 5, 23, '2023-12-15', 51, NULL, 1),
(292, 11, 67, 5, 23, '2023-12-15', 51, NULL, 2),
(293, 13, 67, 4, 23, '2023-12-14', 51, NULL, 1),
(294, 13, 67, 7, 23, '2023-12-17', 51, NULL, 2),
(295, 14, 67, 7, 23, '2023-12-17', 1, NULL, 3),
(296, 10, 67, 7, 24, '2023-12-24', 1, NULL, 2),
(297, 10, 67, 1, 24, '2024-11-04', 1, NULL, 3),
(298, 8, 67, 1, 21, '2024-11-04', 1, NULL, 3),
(299, 10, 67, 1, 22, '2024-11-04', 1, NULL, 2),
(300, 15, 67, 1, 22, '2024-11-04', 1, NULL, 3),
(301, 16, 67, 1, 23, '2024-11-04', 1, NULL, 3),
(302, 17, 67, 1, 24, '2024-11-04', 1, NULL, 3),
(303, 16, 67, 1, 23, '2024-11-04', 1, NULL, 3),
(304, 18, 67, 1, 24, '2024-11-04', 1, NULL, 3),
(305, 10, 67, 2, 15, '2024-11-05', 1, NULL, 3),
(306, 10, 67, 2, 15, '2024-11-05', 1, NULL, 3),
(307, 10, 67, 2, 17, '2024-11-05', 1, NULL, 3),
(308, 19, 67, 7, 23, '2024-11-10', 1, NULL, 3),
(309, 10, 66, 3, 23, '2024-11-06', 1, NULL, 3),
(310, 10, 66, 1, 22, '2024-11-11', 1, NULL, 2),
(311, 10, 67, 5, 23, '2024-11-08', 1, NULL, 2),
(312, 10, 66, 1, 21, '2024-11-11', 1, NULL, 3),
(313, 19, 66, 1, 23, '2024-11-11', 1, NULL, 3),
(314, 10, 66, 1, 21, '2024-11-18', 1, NULL, 2),
(315, 10, 67, 3, 23, '2024-11-13', 1, NULL, 3),
(316, 18, 67, 4, 20, '2024-11-14', 1, NULL, 3),
(317, 10, 66, 1, 22, '2024-12-23', 1, NULL, 3),
(318, 10, 66, 3, 23, '2025-01-15', 1, NULL, 3),
(319, 10, 66, 1, 22, '2025-01-20', 1, NULL, 3),
(320, 10, 66, 4, 18, '2025-01-16', 21, NULL, 2),
(321, 10, 66, 4, 18, '2025-01-16', 1, NULL, 3),
(322, 10, 66, 1, 23, '2025-01-20', 1, NULL, 3),
(323, 10, 66, 3, 23, '2025-04-09', 1, NULL, 3);

--
-- Disparadores `booking`
--
DELIMITER $$
CREATE TRIGGER `new_booking` AFTER INSERT ON `booking` FOR EACH ROW BEGIN
    SET time_zone = '-03:00';
    INSERT INTO booking_logs(fecha, hora, users, id_reserva, log) VALUES (NOW(), NOW(), NEW.user, NEW.Id, 5);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_reserva` AFTER UPDATE ON `booking` FOR EACH ROW BEGIN
    DECLARE valor INT;
    IF new.status = 2 THEN
        SET valor = 2;
    ELSEIF new.date_booking != old.date_booking  THEN
        SET valor = 6;
    ELSEIF new.time_booking != old.time_booking  THEN
        SET valor = 6;
    ELSE
        SET valor = 4;
    END IF;

    INSERT INTO booking_logs(fecha, hora, users, id_reserva, log)
    VALUES (NOW(), NOW(), new.user, new.id, valor);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `booking_logs`
--

CREATE TABLE `booking_logs` (
  `id` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `users` int NOT NULL,
  `id_reserva` int NOT NULL,
  `log` int NOT NULL,
  `data_new` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `data_old` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `booking_logs`
--

INSERT INTO `booking_logs` (`id`, `fecha`, `hora`, `users`, `id_reserva`, `log`, `data_new`, `data_old`) VALUES
(426, '2023-12-04', '13:46:05', 50, 1, 5, NULL, NULL),
(427, '2023-12-04', '13:46:28', 21, 232, 5, NULL, NULL),
(428, '2023-12-04', '13:48:57', 50, 233, 5, NULL, NULL),
(429, '2023-12-04', '16:50:45', 50, 233, 2, NULL, NULL),
(430, '2023-12-04', '13:54:03', 50, 234, 5, NULL, NULL),
(431, '2023-12-04', '13:56:32', 50, 235, 5, NULL, NULL),
(432, '2023-12-04', '16:57:00', 50, 235, 2, NULL, NULL),
(433, '2023-12-04', '16:57:35', 50, 234, 4, NULL, NULL),
(434, '2023-12-04', '16:58:11', 50, 234, 6, NULL, NULL),
(435, '2023-12-04', '16:07:34', 21, 236, 5, NULL, NULL),
(436, '2023-12-04', '19:07:50', 21, 233, 2, NULL, NULL),
(437, '2023-12-04', '17:56:29', 21, 237, 5, NULL, NULL),
(438, '2023-12-04', '17:56:50', 21, 238, 5, NULL, NULL),
(439, '2023-12-04', '20:57:06', 21, 238, 2, NULL, NULL),
(440, '2023-12-04', '17:58:52', 21, 239, 5, NULL, NULL),
(441, '2023-12-04', '21:10:34', 21, 237, 2, NULL, NULL),
(442, '2023-12-04', '21:22:07', 21, 239, 2, NULL, NULL),
(443, '2023-12-04', '18:23:13', 21, 240, 5, NULL, NULL),
(444, '2023-12-04', '18:23:25', 21, 241, 5, NULL, NULL),
(445, '2023-12-04', '18:40:42', 21, 242, 5, NULL, NULL),
(446, '2023-12-04', '18:41:05', 21, 243, 5, NULL, NULL),
(447, '2023-12-04', '18:41:22', 21, 244, 5, NULL, NULL),
(448, '2023-12-04', '21:41:52', 21, 244, 2, NULL, NULL),
(449, '2023-12-04', '18:42:19', 21, 245, 5, NULL, NULL),
(450, '2023-12-04', '19:10:02', 51, 246, 5, NULL, NULL),
(451, '2023-12-04', '19:40:32', 51, 247, 5, NULL, NULL),
(452, '2023-12-05', '10:25:57', 21, 249, 5, NULL, NULL),
(453, '2023-12-05', '13:26:28', 21, 249, 2, NULL, NULL),
(454, '2023-12-05', '10:56:25', 1, 250, 5, NULL, NULL),
(455, '2023-12-05', '10:57:57', 1, 251, 5, NULL, NULL),
(456, '2023-12-05', '10:59:06', 1, 252, 5, NULL, NULL),
(457, '2023-12-05', '14:00:01', 1, 234, 4, NULL, NULL),
(458, '2023-12-05', '14:00:01', 1, 247, 4, NULL, NULL),
(459, '2023-12-05', '11:13:07', 1, 253, 5, NULL, NULL),
(460, '2023-12-05', '11:18:53', 1, 254, 5, NULL, NULL),
(461, '2023-12-05', '11:23:11', 1, 255, 5, NULL, NULL),
(462, '2023-12-05', '11:24:27', 21, 256, 5, NULL, NULL),
(463, '2023-12-05', '11:30:10', 1, 257, 5, NULL, NULL),
(464, '2023-12-05', '14:34:09', 1, 257, 2, NULL, NULL),
(465, '2023-12-05', '11:54:08', 1, 258, 5, NULL, NULL),
(466, '2023-12-05', '15:00:01', 1, 258, 4, NULL, NULL),
(467, '2023-12-05', '15:20:31', 1, 258, 4, NULL, NULL),
(468, '2023-12-05', '15:21:36', 1, 258, 2, NULL, NULL),
(469, '2023-12-05', '12:29:46', 1, 259, 5, NULL, NULL),
(470, '2023-12-05', '15:50:19', 1, 259, 2, NULL, NULL),
(471, '2023-12-05', '15:44:31', 1, 260, 5, NULL, NULL),
(472, '2023-12-05', '16:27:33', 1, 261, 5, NULL, NULL),
(473, '2023-12-05', '16:45:22', 1, 262, 5, NULL, NULL),
(474, '2023-12-05', '22:00:01', 1, 261, 4, NULL, NULL),
(475, '2023-12-05', '23:00:01', 1, 260, 4, NULL, NULL),
(476, '2023-12-06', '00:00:01', 1, 236, 4, NULL, NULL),
(477, '2023-12-06', '01:00:02', 1, 232, 4, NULL, NULL),
(478, '2023-12-06', '02:00:01', 1, 1, 4, NULL, NULL),
(479, '2023-12-06', '02:00:01', 1, 246, 4, NULL, NULL),
(480, '2023-12-06', '02:00:01', 1, 262, 4, NULL, NULL),
(481, '2023-12-06', '22:57:29', 51, 258, 2, NULL, NULL),
(482, '2023-12-06', '20:11:02', 51, 263, 5, NULL, NULL),
(483, '2023-12-07', '15:41:36', 21, 243, 6, NULL, NULL),
(484, '2023-12-07', '19:51:26', 21, 264, 5, NULL, NULL),
(485, '2023-12-08', '02:00:01', 1, 264, 4, NULL, NULL),
(486, '2023-12-10', '19:41:34', 21, 265, 5, NULL, NULL),
(487, '2023-12-10', '20:24:43', 21, 266, 5, NULL, NULL),
(488, '2023-12-10', '20:41:36', 21, 267, 5, NULL, NULL),
(489, '2023-12-10', '21:11:33', 51, 268, 5, NULL, NULL),
(490, '2023-12-10', '21:35:18', 1, 269, 5, NULL, NULL),
(491, '2023-12-11', '01:00:01', 21, 269, 4, NULL, NULL),
(492, '2023-12-11', '01:18:42', 21, 269, 4, NULL, NULL),
(493, '2023-12-11', '01:30:01', 21, 269, 4, NULL, NULL),
(494, '2023-12-10', '22:43:15', 1, 270, 5, NULL, NULL),
(495, '2023-12-11', '02:00:01', 21, 268, 4, NULL, NULL),
(496, '2023-12-10', '23:04:46', 1, 271, 5, NULL, NULL),
(497, '2023-12-10', '23:57:49', 1, 272, 5, NULL, NULL),
(498, '2023-12-11', '03:00:01', 21, 242, 4, NULL, NULL),
(499, '2023-12-11', '03:00:01', 21, 245, 4, NULL, NULL),
(500, '2023-12-11', '03:00:01', 21, 265, 4, NULL, NULL),
(501, '2023-12-11', '00:06:01', 1, 273, 5, NULL, NULL),
(502, '2023-12-11', '00:12:36', 1, 274, 5, NULL, NULL),
(503, '2023-12-11', '00:20:48', 21, 275, 5, NULL, NULL),
(504, '2023-12-11', '03:25:15', 21, 275, 2, NULL, NULL),
(505, '2023-12-11', '00:25:35', 21, 276, 5, NULL, NULL),
(506, '2023-12-11', '03:25:53', 21, 276, 2, NULL, NULL),
(507, '2023-12-11', '00:26:24', 21, 277, 5, NULL, NULL),
(508, '2023-12-11', '03:26:30', 21, 277, 6, NULL, NULL),
(509, '2023-12-11', '00:29:55', 1, 237, 5, NULL, NULL),
(510, '2023-12-11', '00:47:10', 21, 278, 5, NULL, NULL),
(511, '2023-12-11', '00:53:06', 21, 279, 5, NULL, NULL),
(512, '2023-12-11', '00:57:58', 21, 280, 5, NULL, NULL),
(513, '2023-12-11', '04:00:01', 21, 280, 4, NULL, NULL),
(514, '2023-12-11', '01:06:19', 21, 281, 5, NULL, NULL),
(515, '2023-12-11', '01:21:25', 21, 282, 5, NULL, NULL),
(516, '2023-12-11', '01:27:04', 21, 283, 5, NULL, NULL),
(517, '2023-12-11', '04:30:01', 21, 283, 4, NULL, NULL),
(518, '2023-12-11', '01:31:13', 21, 284, 5, NULL, NULL),
(519, '2023-12-11', '15:51:30', 21, 285, 5, NULL, NULL),
(520, '2023-12-11', '16:56:53', 21, 286, 5, NULL, NULL),
(521, '2023-12-11', '17:23:39', 21, 287, 5, NULL, NULL),
(522, '2023-12-11', '17:35:18', 21, 288, 5, NULL, NULL),
(523, '2023-12-11', '18:36:30', 1, 289, 5, NULL, NULL),
(524, '2023-12-11', '21:39:58', 1, 289, 2, NULL, NULL),
(525, '2023-12-11', '23:00:01', 1, 288, 4, NULL, NULL),
(526, '2023-12-14', '23:19:51', 1, 237, 4, NULL, NULL),
(527, '2023-12-14', '23:20:12', 21, 285, 4, NULL, NULL),
(528, '2023-12-14', '23:20:22', 21, 286, 4, NULL, NULL),
(529, '2023-12-14', '23:20:30', 21, 287, 4, NULL, NULL),
(530, '2023-12-14', '20:26:33', 51, 290, 5, NULL, NULL),
(531, '2023-12-14', '23:27:04', 51, 290, 6, NULL, NULL),
(532, '2023-12-14', '23:27:21', 51, 290, 2, NULL, NULL),
(533, '2023-12-14', '20:27:50', 51, 291, 5, NULL, NULL),
(534, '2023-12-14', '20:48:12', 51, 292, 5, NULL, NULL),
(535, '2023-12-14', '23:49:29', 51, 292, 6, NULL, NULL),
(536, '2023-12-14', '23:50:09', 51, 292, 2, NULL, NULL),
(537, '2023-12-14', '20:50:43', 51, 293, 5, NULL, NULL),
(538, '2023-12-14', '20:57:31', 51, 294, 5, NULL, NULL),
(539, '2023-12-14', '23:59:21', 51, 294, 6, NULL, NULL),
(540, '2023-12-14', '23:59:51', 51, 294, 2, NULL, NULL),
(541, '2023-12-14', '21:32:34', 1, 295, 5, NULL, NULL),
(542, '2023-12-17', '01:00:40', 1, 296, 5, NULL, NULL),
(543, '2023-12-17', '04:03:51', 1, 296, 2, NULL, NULL),
(544, '2024-11-04', '12:49:58', 1, 297, 5, NULL, NULL),
(545, '2024-11-04', '14:04:27', 1, 298, 5, NULL, NULL),
(546, '2024-11-04', '16:37:22', 1, 299, 5, NULL, NULL),
(547, '2024-11-04', '16:40:37', 1, 297, 2, NULL, NULL),
(548, '2024-11-04', '17:29:43', 1, 300, 5, NULL, NULL),
(549, '2024-11-04', '17:31:10', 1, 301, 5, NULL, NULL),
(550, '2024-11-04', '19:35:16', 1, 302, 5, NULL, NULL),
(551, '2024-11-04', '19:51:53', 1, 303, 5, NULL, NULL),
(552, '2024-11-04', '21:14:20', 1, 304, 5, NULL, NULL),
(553, '2024-11-04', '21:56:17', 1, 305, 5, NULL, NULL),
(554, '2024-11-04', '22:00:59', 1, 306, 5, NULL, NULL),
(555, '2024-11-04', '22:40:43', 1, 307, 5, NULL, NULL),
(556, '2024-11-05', '01:05:38', 1, 308, 5, NULL, NULL),
(557, '2024-11-05', '23:55:10', 1, 309, 5, NULL, NULL),
(558, '2024-11-06', '00:08:21', 1, 310, 5, NULL, NULL),
(559, '2024-11-06', '00:12:09', 1, 310, 2, NULL, NULL),
(560, '2024-11-08', '16:11:25', 1, 299, 2, NULL, NULL),
(561, '2024-11-08', '16:39:49', 1, 311, 5, NULL, NULL),
(562, '2024-11-08', '16:45:03', 1, 312, 5, NULL, NULL),
(563, '2024-11-08', '16:48:44', 1, 311, 2, NULL, NULL),
(564, '2024-11-08', '17:17:20', 1, 309, 4, NULL, NULL),
(565, '2024-11-08', '17:17:31', 1, 307, 4, NULL, NULL),
(566, '2024-11-08', '17:17:38', 1, 306, 4, NULL, NULL),
(567, '2024-11-08', '17:17:43', 1, 305, 4, NULL, NULL),
(568, '2024-11-08', '17:17:55', 1, 297, 4, NULL, NULL),
(569, '2024-11-08', '17:18:01', 1, 298, 4, NULL, NULL),
(570, '2024-11-08', '17:18:06', 1, 300, 4, NULL, NULL),
(571, '2024-11-08', '17:18:10', 1, 301, 4, NULL, NULL),
(572, '2024-11-08', '17:18:13', 1, 302, 4, NULL, NULL),
(573, '2024-11-08', '17:18:17', 1, 303, 4, NULL, NULL),
(574, '2024-11-08', '17:18:21', 1, 304, 4, NULL, NULL),
(575, '2024-11-08', '23:00:02', 1, 295, 4, NULL, NULL),
(576, '2024-11-10', '23:00:01', 1, 308, 4, NULL, NULL),
(577, '2024-11-11', '07:29:51', 1, 313, 5, NULL, NULL),
(578, '2024-11-11', '16:54:20', 1, 314, 5, NULL, NULL),
(579, '2024-11-11', '20:31:48', 1, 314, 2, NULL, NULL),
(580, '2024-11-11', '21:00:01', 1, 312, 4, NULL, NULL),
(581, '2024-11-11', '23:00:01', 1, 313, 4, NULL, NULL),
(582, '2024-11-11', '23:11:21', 1, 315, 5, NULL, NULL),
(583, '2024-11-13', '23:00:01', 1, 315, 4, NULL, NULL),
(584, '2024-11-14', '11:51:03', 1, 316, 5, NULL, NULL),
(585, '2024-11-14', '20:00:01', 1, 316, 4, NULL, NULL),
(586, '2024-12-20', '20:37:48', 1, 317, 5, NULL, NULL),
(587, '2024-12-23', '22:00:01', 1, 317, 4, NULL, NULL),
(588, '2025-01-15', '08:58:46', 1, 318, 5, NULL, NULL),
(589, '2025-01-15', '22:32:32', 1, 319, 5, NULL, NULL),
(590, '2025-01-15', '23:00:01', 1, 318, 4, NULL, NULL),
(591, '2025-01-16', '10:07:59', 21, 320, 5, NULL, NULL),
(592, '2025-01-16', '10:12:39', 21, 320, 2, NULL, NULL),
(593, '2025-01-16', '10:14:37', 1, 321, 5, NULL, NULL),
(594, '2025-01-16', '18:00:01', 1, 321, 4, NULL, NULL),
(595, '2025-01-20', '19:09:37', 1, 322, 5, NULL, NULL),
(596, '2025-01-20', '22:00:01', 1, 319, 4, NULL, NULL),
(597, '2025-01-20', '23:00:01', 1, 322, 4, NULL, NULL),
(598, '2025-04-05', '10:21:29', 1, 323, 5, NULL, NULL),
(599, '2025-04-09', '23:00:01', 1, 323, 4, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `booking_status`
--

CREATE TABLE `booking_status` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `color` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `color_hex` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `booking_status`
--

INSERT INTO `booking_status` (`id`, `name`, `color`, `color_hex`) VALUES
(1, 'Activo', 'success', '#50CD89'),
(2, 'Cancelado', 'danger', '#F1416C'),
(3, 'Completado', 'secondary', '#E4E6EF'),
(4, 'Actualizado', 'info', '#E4E6EF'),
(5, 'Agendado', 'success', '#50CD89'),
(6, 'Re Agendado', 'warning', '#50CD89'),
(7, 'Pendiente', 'info', '#50CD89');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `city`
--

CREATE TABLE `city` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_provincia` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `city`
--

INSERT INTO `city` (`id`, `name`, `id_provincia`) VALUES
(1, 'La Plata', 1),
(2, 'Resistencia', 3),
(3, 'Saenz Peña', 3),
(4, 'Fontana', 3),
(5, 'Quitilipi', 3),
(6, 'Barranqueras ', 3),
(7, 'Corrientes', 6),
(8, 'Santo Tomé', 6),
(9, 'Goya', 6),
(10, 'Paso de los Libres', 6),
(11, 'Santa Ana', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `customers`
--

CREATE TABLE `customers` (
  `id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `phone`, `email`) VALUES
(1, 'Pablo', '3625293513', ''),
(2, 'Matias Luciano Garcia', '03624646461', 'matiasgarcia444@gmail.com'),
(3, 'Hector Espíndola', '3624852029', 'hector.espindola@gmail.com'),
(4, 'Alejandro Bernard', '03624244049', 'alejandro.bernard@nbch.com.ar'),
(5, 'Alejandro', '03625000000', 'alejandro@gmail.com'),
(6, 'Matias Luciano Garcia', '+543625293513', 'matiasgarcia444@gmail.com'),
(7, 'Hay Equipo F5', '+5493624758573', ''),
(8, 'Mauricio Ayala', '5493795054759', ''),
(9, 'Angel Morales', '50757501012', ''),
(10, 'Matias Garcia', '5493625293513', ''),
(11, 'Papa Francisco', '1782561093', ''),
(12, 'patricia bullrich', '3795054759', ''),
(13, 'Bob Esponjaaa', '993624999000666', ''),
(14, 'Belén Benitez', '5493624008320', ''),
(15, 'Franco Perez', '5493624716607', ''),
(16, 'Andrés Nicolás', '5493625225614', ''),
(17, 'Julian Pinto', '5493624265632', ''),
(18, 'Leandro Machuca', '5493624108921', ''),
(19, 'Gustavo Arias', '5493624887558', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `error_log`
--

CREATE TABLE `error_log` (
  `id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `field_schedule`
--

CREATE TABLE `field_schedule` (
  `id` int NOT NULL,
  `id_court` int NOT NULL,
  `id_schedule` int NOT NULL,
  `day` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mobbex_ops`
--

CREATE TABLE `mobbex_ops` (
  `id` int NOT NULL,
  `checkout_currency` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `checkout_total` decimal(10,2) DEFAULT NULL,
  `checkout_uid` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `childs_entity_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `childs_entity_uid` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_source_cardholder_identification` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_source_cardholder_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_expiration_month` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_expiration_year` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_installment_amount` decimal(10,2) DEFAULT NULL,
  `payment_installment_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_installment_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_transaction_authorizationCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paymenttransaction_batchNo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paymenttransaction_resultCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paymenttransaction_retrievalReferenceNo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paymenttransaction_ticketNo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paymenttransaction_transactionId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status_message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status_resultCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status_view` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_total` decimal(10,2) DEFAULT NULL,
  `customer_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_identification` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entity_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entity_uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment`
--

CREATE TABLE `payment` (
  `id` int NOT NULL,
  `payment_id` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cardholder_identification_number` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cardholder_identification_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `card_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_created` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_last_updated` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiration_month` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiration_year` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `first_six_digits` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_four_digits` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `charges_details_mounts_original` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `charges_details_mounts_refunded` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `charges_details_mounts_amounts_original_1` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `charges_details_mounts_amounts_refunded_1` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `charges_details_mounts_amounts_original_2` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `charges_details_mounts_amounts_refunded_2` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metadata_mov_detail` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metadata_mov_financial_entity` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metadata_mov_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metadata_tax_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metadata_tax_status` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metadata_user_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metadata_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `coupon_amount` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_approved` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_of_expiration` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `marketplace_owner` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notification_url` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `order_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `order_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_type_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `statement_descriptor` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_detail` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taxes_amount` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transaction_amount` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transaction_amount_refunded` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `external_reference` varchar(250) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `payment`
--

INSERT INTO `payment` (`id`, `payment_id`, `ip_address`, `cardholder_identification_number`, `cardholder_identification_type`, `card_name`, `date_created`, `date_last_updated`, `expiration_month`, `expiration_year`, `first_six_digits`, `last_four_digits`, `charges_details_mounts_original`, `charges_details_mounts_refunded`, `charges_details_mounts_amounts_original_1`, `charges_details_mounts_amounts_refunded_1`, `charges_details_mounts_amounts_original_2`, `charges_details_mounts_amounts_refunded_2`, `metadata_mov_detail`, `metadata_mov_financial_entity`, `metadata_mov_type`, `metadata_tax_id`, `metadata_tax_status`, `metadata_user_id`, `metadata_type`, `coupon_amount`, `currency_id`, `date_approved`, `date_of_expiration`, `marketplace_owner`, `notification_url`, `order_id`, `order_type`, `payment_method_id`, `payment_type_id`, `statement_descriptor`, `status`, `status_detail`, `taxes_amount`, `transaction_amount`, `transaction_amount_refunded`, `external_reference`) VALUES
(93, '68548673738', '152.168.116.124', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '0', '0.99', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2023-12-10T23:29:20.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '14006346321', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '11.7', '0', '3e3a92b86fcb48caa6a27facd5c3ec4f'),
(94, '68594444204', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '547.56', '0', '395.93', '0', '140.4', '0', 'tax_withholding_sirtac_noinsc', 'chaco', 'expense', '78764327819', 'applied', '177481545', 'tax', '0', 'ARS', '2023-12-11T17:36:07.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '14030805378', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '4680', '0', 'd3d3a7e84c4040fd8ce041473a112261'),
(95, '68594444204', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '547.56', '547.56', '395.93', '395.93', '140.4', '140.4', 'tax_withholding_sirtac_noinsc', 'chaco', 'expense', '78764327819', 'applied', '177481545', 'tax', '0', 'ARS', '2023-12-11T17:36:07.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '14030805378', 'mercadopago', 'account_money', 'account_money', '', 'refunded', 'refunded', '0', '4680', '4680', 'd3d3a7e84c4040fd8ce041473a112261'),
(96, '68797199664', '186.122.104.29', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '0', '0.99', '0', '0.35', '0', 'tax_withholding_sirtac_noinsc', 'chaco', 'expense', '78939740060', 'applied', '177481545', 'tax', '0', 'ARS', '2023-12-14T20:32:06.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '14117873615', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '11.7', '0', '82c5e2de90e24a228639f1ef0055e59c'),
(97, '68941093694', '181.9.201.124', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.1', '0', '0.04', '0', 'tax_withholding_sirtac_noinsc', 'chaco', 'expense', '79021570064', 'applied', '177481545', 'tax', '0', 'ARS', '2023-12-17T00:00:20.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '14186003034', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', 'd5b9f3abf0a04637a20aeb03fc291d6f'),
(98, '68941093694', '181.9.201.124', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0.14', '0.1', '0.1', '0.04', '0.04', 'tax_withholding_sirtac_noinsc', 'chaco', 'expense', '79021570064', 'applied', '177481545', 'tax', '0', 'ARS', '2023-12-17T00:00:20.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '14186003034', 'mercadopago', 'account_money', 'account_money', '', 'refunded', 'refunded', '0', '1.17', '1.17', 'd5b9f3abf0a04637a20aeb03fc291d6f'),
(99, '92048564393', '181.9.212.113', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91717665601', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T11:46:27.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24617641190', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '67600bda5ed8474297a33e47fcc9dbaa'),
(100, '92370312326', '181.9.212.113', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91827104766', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T11:49:32.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24617758650', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '00cd59919eb643218155cbbacae0e775'),
(101, '92056188755', '98.97.134.239', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91830422938', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T13:03:55.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24620618772', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '794e9782bd224a8da1a5a1a52eb1613b'),
(102, '92390526042', '181.9.212.113', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91726542263', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T15:37:03.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24611127079', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '2603f4b96b554ab692ce5eeda95e56bf'),
(103, '92370312326', '181.9.212.113', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0.14', '0.09', '0.09', '0.01', '0.01', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91827104766', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T11:49:32.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24617758650', 'mercadopago', 'account_money', 'account_money', '', 'refunded', 'refunded', '0', '1.17', '1.17', '00cd59919eb643218155cbbacae0e775'),
(104, '92394173430', '98.97.134.239', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '6714', '1.37', '0', '0.89', '0', '0.07', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91728085465', 'applied', '339941974', 'tax', '0', 'ARS', '', '', '339941974', 'https://botcanchero.com/mpay-hook', '24612456015', 'mercadopago', 'visa', 'credit_card', 'MERPAGO*BOTCANCHERO', 'rejected', 'cc_rejected_insufficient_amount', '0', '11.7', '0', '15964fcc3536457999f4b759f83a8ec8'),
(105, '92395578492', '190.138.161.50', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '0', '0.89', '0', '0.07', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91728624541', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T16:29:33.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24612996109', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '11.7', '0', '2f79bd46c470453fba24204a53792876'),
(106, '92395693256', '181.9.213.127', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '0', '0.89', '0', '0.07', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91838102436', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T16:31:07.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24627408866', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '11.7', '0', 'db2327d314154e2abe00c482b788a8a8'),
(107, '92394690436', '98.97.134.239', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '0', '0.89', '0', '0.07', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91728230739', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T16:20:38.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24626994710', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '11.7', '0', '15964fcc3536457999f4b759f83a8ec8'),
(108, '92407327118', '181.9.213.233', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '0', '0.89', '0', '0.07', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91844091128', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T18:30:44.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24632575618', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '11.7', '0', 'be93f42b708d463b999de4834e628509'),
(109, '92407327118', '181.9.213.233', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '1.37', '0.89', '0.89', '0.07', '0.07', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91844091128', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T18:30:44.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24632575618', 'mercadopago', 'account_money', 'account_money', '', 'refunded', 'refunded', '0', '11.7', '11.7', 'be93f42b708d463b999de4834e628509'),
(110, '92086194503', '186.123.181.139', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91734842305', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T18:34:53.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24618372067', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '7ce9326065354fe1b10d02b8edf4ba20'),
(111, '92086741597', '181.9.213.233', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '0', '0.89', '0', '0.07', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91844613622', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T18:41:03.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24633013698', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '11.7', '0', 'dbd86670f61a478592ce049ce0530204'),
(112, '92086741597', '181.9.213.233', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '1.37', '0.89', '0.89', '0.07', '0.07', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91844613622', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T18:41:03.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24633013698', 'mercadopago', 'account_money', 'account_money', '', 'refunded', 'refunded', '0', '11.7', '11.7', 'dbd86670f61a478592ce049ce0530204'),
(113, '92409388384', '181.9.213.127', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '0', '0.89', '0', '0.07', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91845165640', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T18:51:22.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24633469412', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '11.7', '0', '0f19ff99562443afb7228b92e4ac34af'),
(114, '92095346241', '45.191.80.102', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '1.37', '0', '0.89', '0', '0.07', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91740067621', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T20:13:53.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24622604301', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '11.7', '0', 'ba8bbc242f374c9694768e1bc1477a8f'),
(115, '92098571175', '186.123.180.182', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91741688583', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T20:55:29.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24624021281', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', 'b067ebe9e77a4ae8b59010bbd517cd77'),
(116, '92098571175', '186.123.180.182', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0.14', '0.09', '0.09', '0.01', '0.01', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91741688583', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T20:55:29.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24624021281', 'mercadopago', 'account_money', 'account_money', '', 'refunded', 'refunded', '0', '1.17', '1.17', 'b067ebe9e77a4ae8b59010bbd517cd77'),
(117, '92420546244', '186.123.180.182', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91741906171', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T21:00:32.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24638528276', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '372c1a54de26432baa8c73f4c77c95b5'),
(118, '92423030524', '186.123.180.182', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91852631506', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-04T21:40:29.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24639613224', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '241aa63225d343bebd2aac89b890d82b'),
(119, '92107208157', '186.122.104.174', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91855670254', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-05T00:05:24.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24641964574', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '2ba06cd6878347b1910780bfe98bb434'),
(120, '92208250513', '186.123.180.182', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2024-11-05T22:55:07.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24666395645', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '1e17ef78ed8649cb98e81210af2dbd5f'),
(121, '92208688947', '186.123.180.182', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '958.23', '0', '623.26', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2024-11-05T23:07:47.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24681021134', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '8190', '0', '33d46bf0ecae4cd1a29665abe3370c18'),
(122, '92208688947', '186.123.180.182', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '958.23', '958.23', '623.26', '623.26', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2024-11-05T23:07:47.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24681021134', 'mercadopago', 'account_money', 'account_money', '', 'refunded', 'refunded', '0', '8190', '8190', '33d46bf0ecae4cd1a29665abe3370c18'),
(123, '92810045880', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '91915161969', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-08T15:39:03.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24772372303', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '936cdd8c48534c80abd3f96de9fc582a'),
(124, '92810649952', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '95.82', '0', '62.33', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2024-11-08T15:44:47.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24787187618', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '819', '0', '5a216b15e6334f95aaeac6df60012e27'),
(125, '92708774391', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '95.82', '0', '62.33', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2024-11-11T06:29:39.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24865967731', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '819', '0', '8f41281a41914b9c9d4638bd82b6fc77'),
(126, '92764241301', '186.123.180.182', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '27.38', '0', '17.81', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2024-11-11T15:53:39.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24885727253', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '234', '0', 'b2a19fa531d24d4e9499a90e67655901'),
(127, '93123289176', '186.123.180.182', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '92060114659', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-11T22:11:13.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '24915368818', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', 'dfd91884d352478cbe683b8ddc5bc7cb'),
(128, '93361919748', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.09', '0', '0.01', '0', 'tax_withholding_collector', 'debitos_creditos', 'expense', '92160442193', 'applied', '339941974', 'tax', '0', 'ARS', '2024-11-14T10:50:35.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '25002854262', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '13e06821e34046a5a831b1a42a78e5cd'),
(129, '96670621731', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '6.84', '0', '4.45', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2024-12-20T19:37:18.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '26373587059', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '58.5', '0', '381e18b7ce9b411cac65855593093224'),
(130, '99376429172', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '6.84', '0', '4.45', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2025-01-15T07:58:17.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '27290416045', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '58.5', '0', '7b7ae6ccf22d4f7c8f9e8c892a3ad267'),
(131, '99093964275', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '6.84', '0', '4.45', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2025-01-15T21:32:01.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '27321465147', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '58.5', '0', 'b65ecbf6f8c94bb1966b19019abdb21e'),
(132, '99124004911', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '6.84', '0', '4.45', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2025-01-16T09:14:19.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '27330756289', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '58.5', '0', '68dc23b2fad64691844ce6493782e63d'),
(133, '99542342357', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '6.84', '0', '4.45', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2025-01-20T18:09:18.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '27492771745', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '58.5', '0', '4146eebc64824f7db8eaedf0558afb1c'),
(134, '106981891561', '', '99999999', 'DNI', 'fulano', '\"3023-10-27T17:17:55.000-04:00\"', '\"3023-10-27T17:17:55.000-04:00\"', '99', '9999', '999999', '9999', '0.14', '0', '0.04', '0', '0', '0', 'tax_withholding_payer', 'debitos_creditos', 'expense', '99999999999', 'applied', '999999999', 'tax', '0', 'ARS', '2025-04-05T09:21:24.000-04:00', '', '339941974', 'https://botcanchero.com/mpay-hook', '30120818870', 'mercadopago', 'account_money', 'account_money', '', 'approved', 'accredited', '0', '1.17', '0', '290909621154405098ca7b47aafd8338');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_app_web`
--

CREATE TABLE `payment_app_web` (
  `id` int NOT NULL,
  `payment_date` date DEFAULT NULL,
  `amount_payment` int NOT NULL,
  `method_payment` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `id_booking` int DEFAULT NULL,
  `id_field` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `payment_app_web`
--

INSERT INTO `payment_app_web` (`id`, `payment_date`, `amount_payment`, `method_payment`, `id_booking`, `id_field`) VALUES
(38, '2023-12-11', 10, 'efectivo', 285, 67),
(39, '2023-12-11', 10, 'mercado_pago', 286, 67),
(40, '2023-12-11', 1, 'efectivo', 287, 66),
(41, '2023-12-11', 10, 'efectivo', 288, 67),
(42, '2023-12-14', 10, 'efectivo', 291, 67),
(43, '2023-12-14', 10, 'efectivo', 293, 67),
(44, '2023-12-14', 10, 'efectivo', 294, 67);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_preference`
--

CREATE TABLE `payment_preference` (
  `client_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `collector_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `coupon_code` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `coupon_labels` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_created` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_of_expiration` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiration_date_from` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiration_date_to` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expires` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `external_reference` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `init_point` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `internal_metadata` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `items_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `items_category_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `items_currency_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `items_description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `items_picture_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `items_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `items_quantity` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `items_unit_price` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `marketplace` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `marketplace_fee` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notification_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `operation_type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payer_area_code` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payer_number` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payer_address_zip_code` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payer_address_street_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payer_address_street_number` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `identification_number` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `identification_type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `surname` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_purchase` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `statement_descriptor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_amount` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_updated` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `payment_preference`
--

INSERT INTO `payment_preference` (`client_id`, `collector_id`, `coupon_code`, `coupon_labels`, `date_created`, `date_of_expiration`, `expiration_date_from`, `expiration_date_to`, `expires`, `external_reference`, `id`, `init_point`, `internal_metadata`, `items_id`, `items_category_id`, `items_currency_id`, `items_description`, `items_picture_url`, `items_title`, `items_quantity`, `items_unit_price`, `marketplace`, `marketplace_fee`, `notification_url`, `operation_type`, `payer_area_code`, `payer_number`, `payer_address_zip_code`, `payer_address_street_name`, `payer_address_street_number`, `email`, `identification_number`, `identification_type`, `name`, `surname`, `last_purchase`, `statement_descriptor`, `total_amount`, `last_updated`) VALUES
('4739156168928171', '177481545', '', '', '2023-12-04T17:55:37.711-04:00', '', '2023-12-04T18:55:37.319-03:00', '2023-12-04T19:55:37.320-03:00', '1', '67a2a1932fea49eebd0db551125547c8', '177481545-5299f40e-d34c-482b-b473-45da0310272d', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-5299f40e-d34c-482b-b473-45da0310272d', '', 'a489d568c91343679442f7284bf1066b', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 04122023\n\n                        Hora: 22:00 - 23:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_916345-MLB73220183325_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', 'Mauricio', 'Ayala', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-04T18:11:59.882-04:00', '', '2023-12-04T19:11:59.427-03:00', '2023-12-04T20:11:59.427-03:00', '1', '971305f1b9f14aea997867db60dbd91c', '177481545-96a3b78d-61f8-4003-a999-ab6cddd938c4', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-96a3b78d-61f8-4003-a999-ab6cddd938c4', '', '63a56c8e68694f91a996a683c76649cd', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 04122023\n\n                        Hora: 23:00 - 00:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_916345-MLB73220183325_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n                        ', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'arcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-05T10:29:15.443-04:00', '', '2023-12-05T11:29:15.078-03:00', '2023-12-05T12:29:15.078-03:00', '1', '3f5ad009eb134af08f64997db4999192', '177481545-0ad4f353-24c6-4c32-9575-ed9f0284563d', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-0ad4f353-24c6-4c32-9575-ed9f0284563d', '', 'ea444d48e59a4bcf8f7d7bd24ec14c6d', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 05122023\n\n                        Hora: 22:00 - 23:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_838307-MLB73168296848_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n                        ', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'arcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-05T10:50:43.756-04:00', '', '2023-12-05T11:50:43.341-03:00', '2023-12-05T12:50:43.342-03:00', '1', '7a8402fbfd72480e91e4a7bef475f251', '177481545-ba2e4dc6-cb1c-48c9-bc87-3556e8dcc5ee', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-ba2e4dc6-cb1c-48c9-bc87-3556e8dcc5ee', '', 'e323d6cd72bd48db8ae4608442d88651', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 05122023\n\n                        Hora: 23:00 - 00:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_838307-MLB73168296848_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n                        ', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'arcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-05T11:28:52.143-04:00', '', '2023-12-05T12:28:51.750-03:00', '2023-12-05T13:28:51.750-03:00', '1', 'c7d99bfed92d436284e4417195802af6', '177481545-a29c88ce-e705-41f0-8155-ef3355f37ee2', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-a29c88ce-e705-41f0-8155-ef3355f37ee2', '', 'eb5461a04f064f27b4de76cb8944b9cd', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 05122023\n\n                        Hora: 18:00 - 19:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_838307-MLB73168296848_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', 'Mauricio', 'Ayala', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-05T14:14:37.422-04:00', '', '2023-12-05T15:14:37.100-03:00', '2023-12-05T16:14:37.100-03:00', '1', '5770305135d344d6ab36ed42b6564eb2', '177481545-93f753d3-7179-43a5-84a4-64dca973f4f2', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-93f753d3-7179-43a5-84a4-64dca973f4f2', '', 'cf2f61d4900648988133e8aa85cb8c37', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 05122023\n\n                        Hora: 19:00 - 20:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_838307-MLB73168296848_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n                        ', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'arcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-05T14:29:14.653-04:00', '', '2023-12-05T15:29:14.279-03:00', '2023-12-05T16:29:14.279-03:00', '1', '383206bef5994a9e90590d8971969b81', '177481545-5dc36043-771d-4985-be1b-613b6d16f340', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-5dc36043-771d-4985-be1b-613b6d16f340', '', 'e9ed1562dc7a45cca820a86ddaa18bc5', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 05122023\n\n                        Hora: 19:00 - 20:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_838307-MLB73168296848_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n                        ', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'arcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-05T14:43:36.877-04:00', '', '2023-12-05T15:43:36.542-03:00', '2023-12-05T16:43:36.542-03:00', '1', '75187c3a8217456e8bf6b3992052b105', '177481545-eeba5b20-487f-4d6a-869a-1e873f091124', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-eeba5b20-487f-4d6a-869a-1e873f091124', '', 'f082844f4ae44de3996affe1cce301f3', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 05122023\n\n                        Hora: 19:00 - 20:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_838307-MLB73168296848_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n                        ', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'arcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-05T15:26:39.302-04:00', '', '2023-12-05T16:26:38.925-03:00', '2023-12-05T17:26:38.925-03:00', '1', 'e97501a793454b16b2bf792ec70e9516', '177481545-c88dbd5b-3c5a-461c-8e3a-b9350d831431', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-c88dbd5b-3c5a-461c-8e3a-b9350d831431', '', '4756e0237c214dc4b820a1f136bc9c88', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 05122023\n\n                        Hora: 18:00 - 19:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_838307-MLB73168296848_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', 'Mauricio', 'Ayala', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-05T15:44:28.357-04:00', '', '2023-12-05T16:44:28.043-03:00', '2023-12-05T17:44:28.043-03:00', '1', '53ca68bdd2ac46739ed994c4bed197a7', '177481545-63b4d259-f7f8-4cb3-ba65-edafa9a886be', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-63b4d259-f7f8-4cb3-ba65-edafa9a886be', '', '3e70fea5232f44c9b5d931e7b8b45c60', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 05122023\n\n                        Hora: 22:00 - 23:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_838307-MLB73168296848_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', 'Mauricio', 'Ayala', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-06T16:01:12.960-04:00', '', '2023-12-06T17:01:12.596-03:00', '2023-12-06T18:01:12.596-03:00', '1', 'e5b08c16657f432cb3df2d49b6fb44cb', '177481545-cbc5feee-61f1-44c1-9dd4-c20120c9b91d', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-cbc5feee-61f1-44c1-9dd4-c20120c9b91d', '', 'e486c11828194e818a4b1081756e1455', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 11122023\n\n                        Hora: 18:00 - 19:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_838307-MLB73168296848_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', 'Mauricio', 'Ayala', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-10T20:32:43.444-04:00', '', '2023-12-10T21:32:43.082-03:00', '2023-12-10T22:32:43.083-03:00', '1', 'd35fc2ce84d145969210556581ce8dd7', '177481545-7f9b504e-0599-4418-8aed-dd428809c33b', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-7f9b504e-0599-4418-8aed-dd428809c33b', '', '069f45c9fd854f0399c92ac92befe9be', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 10122023\n\n                        Hora: 23:00 - 00:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_830468-MLB73341106619_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', 'Mauricio', 'Ayala', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-10T21:42:20.580-04:00', '', '2023-12-10T22:42:20.215-03:00', '2023-12-10T23:42:20.215-03:00', '1', '299ff9f8ef2449068f23794a07ef5808', '177481545-5dd1822e-f907-4e86-b467-8f8a10be6f86', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-5dd1822e-f907-4e86-b467-8f8a10be6f86', '', '7b4bf3e8121a461285b2328577442d3c', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 12122023\n\n                        Hora: 18:00 - 19:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_830468-MLB73341106619_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '379', '5054759', '', '', '', '', '', '', 'patricia', 'bullrich', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-10T22:03:52.060-04:00', '', '2023-12-10T23:03:51.635-03:00', '2023-12-11T00:03:51.636-03:00', '1', '3232b872ffdb46308177513d8867ecf6', '177481545-7e75ce3b-34ce-4df4-90ac-1aca36424994', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-7e75ce3b-34ce-4df4-90ac-1aca36424994', '', 'bd934bbac2934e2cbaa9bcc42eb4fa5f', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 12122023\n\n                        Hora: 19:00 - 20:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_830468-MLB73341106619_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', 'Mauricio', 'Ayala', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-10T22:56:54.679-04:00', '', '2023-12-10T23:56:54.319-03:00', '2023-12-11T00:56:54.319-03:00', '1', '204795f8b92e4417a415787f811b2f6a', '177481545-a2a61e58-d9ff-4330-814f-9e8b6b04ce30', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-a2a61e58-d9ff-4330-814f-9e8b6b04ce30', '', 'acb50077d1f247fd958311cec424717d', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 12122023\n\n                        Hora: 14:00 - 15:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_830468-MLB73341106619_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'Garcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-10T23:05:06.517-04:00', '', '2023-12-11T00:05:06.183-03:00', '2023-12-11T01:05:06.183-03:00', '1', '574cf84b357442538deea745e6ff0c8e', '177481545-b2a83f91-f8d5-482a-8a65-3c1b2f7c7467', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-b2a83f91-f8d5-482a-8a65-3c1b2f7c7467', '', 'be7f329cff3a4cabb107030d19f022f8', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 11122023\n\n                        Hora: 21:00 - 22:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_830468-MLB73341106619_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'Garcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-10T23:11:41.792-04:00', '', '2023-12-11T00:11:41.451-03:00', '2023-12-11T01:11:41.451-03:00', '1', '6ae0515183324d87aa8d5516d0681e44', '177481545-2559b87b-638b-460f-a152-a3863591339c', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-2559b87b-638b-460f-a152-a3863591339c', '', '71483ae302314e0e90c9d035dbfc5262', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 11122023\n\n                        Hora: 19:00 - 20:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_830468-MLB73341106619_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'Garcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-10T23:29:00.491-04:00', '', '2023-12-11T00:29:00.163-03:00', '2023-12-11T01:29:00.163-03:00', '1', '3e3a92b86fcb48caa6a27facd5c3ec4f', '177481545-71bfe10a-03ed-49ca-a5de-348e5a1de284', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-71bfe10a-03ed-49ca-a5de-348e5a1de284', '', 'afa1c7a1782f4ddd97e859a5b7364c21', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 12122023\n\n                        Hora: 16:00 - 17:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_830468-MLB73341106619_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', 'Mauricio', 'Ayala', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-11T17:33:56.310-04:00', '', '2023-12-11T18:33:55.946-03:00', '2023-12-11T19:33:55.947-03:00', '1', 'd3d3a7e84c4040fd8ce041473a112261', '177481545-37b559ac-e4da-4053-aa11-1dfaa859a29a', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-37b559ac-e4da-4053-aa11-1dfaa859a29a', '', '89d11657fbcb4becb13bd7eb02986d46', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 11122023\n\n                        Hora: 23:00 - 00:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_682431-MLB73277433374_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '4680', 'MP-MKT-4739156168928171', '547.56', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'Garcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-14T20:27:40.505-04:00', '', '2023-12-14T21:27:40.124-03:00', '2023-12-14T22:27:40.124-03:00', '1', 'f3753c141cc8436bb52642f35daa6255', '177481545-8dab4f23-679b-4fc0-8da2-366d0f60275d', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-8dab4f23-679b-4fc0-8da2-366d0f60275d', '', 'eaa8e65e98fa435cb748a276700293a3', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 17122023\n\n                        Hora: 22:00 - 23:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_856130-MLB73405218945_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'Garcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-14T20:31:40.114-04:00', '', '2023-12-14T21:31:39.755-03:00', '2023-12-14T22:31:39.755-03:00', '1', '82c5e2de90e24a228639f1ef0055e59c', '177481545-dc862ac1-7f1c-4128-b445-462f15ddfa3b', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-dc862ac1-7f1c-4128-b445-462f15ddfa3b', '', 'c74f60c484c34d76ace6a1a0863462c7', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 17122023\n\n                        Hora: 22:00 - 23:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_856130-MLB73405218945_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624008320', '', '', '', '', '', '', 'Belén', 'Benitez', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-16T23:58:06.260-04:00', '', '2023-12-17T00:58:05.978-03:00', '2023-12-17T01:58:05.978-03:00', '1', 'd5b9f3abf0a04637a20aeb03fc291d6f', '177481545-65c49a01-954f-408b-aa9a-bec448e9e03f', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-65c49a01-954f-408b-aa9a-bec448e9e03f', '', '764ae4fdfd4a4d8cbde8aff91fdb746d', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 24122023\n\n                        Hora: 23:00 - 00:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_790309-MLB73360853678_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n                        ', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'arcia', '', 'BotCanchero', '', ''),
('4739156168928171', '177481545', '', '', '2023-12-17T00:30:04.790-04:00', '', '2023-12-17T01:30:04.429-03:00', '2023-12-17T02:30:04.429-03:00', '1', '1df2abc1bc814da58bf3d4ceb142907d', '177481545-0d158e7d-9836-4204-af4c-73927e92d226', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=177481545-0d158e7d-9836-4204-af4c-73927e92d226', '', '69caf182dff742c7ae3e79cf529280be', 'entertainment', 'ARS', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        Dia: 24122023\n\n                        Hora: 23:00 - 00:00\n                        ', 'https://http2.mlstatic.com/D_NQ_NP_790309-MLB73360853678_122023-F.jpg', '\n                        Reservar Cancha: Test Total Ingreso\n\n                        ', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', 'Matias', 'Garcia', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T11:39:48.968-04:00', '', '', '2024-11-04T13:39:48.482-03:00', '1', '67600bda5ed8474297a33e47fcc9dbaa', '339941974-f2efda82-0dfc-4851-a306-0f8c20c6f182', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-f2efda82-0dfc-4851-a306-0f8c20c6f182', '', '6da529ca749441e7af59ee061a6313fd', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 04112024\nHora: 23:00 - 00:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '93', '625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T11:49:03.873-04:00', '', '', '2024-11-04T13:49:03.473-03:00', '1', '00cd59919eb643218155cbbacae0e775', '339941974-b8f31647-9201-4e11-af38-78393bebc85f', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-b8f31647-9201-4e11-af38-78393bebc85f', '', 'f8f757e64afc453998ea9d0dc03d7447', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 04112024\nHora: 23:00 - 00:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T13:03:33.169-04:00', '', '', '2024-11-04T15:03:32.690-03:00', '1', '794e9782bd224a8da1a5a1a52eb1613b', '339941974-e14fe58d-c715-4fa7-8f6e-9bb414484ccb', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-e14fe58d-c715-4fa7-8f6e-9bb414484ccb', '', '6668a1464e26408caed3c5b4fd43ddf2', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 04112024\nHora: 20:00 - 21:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T15:36:27.496-04:00', '', '', '2024-11-04T17:36:26.874-03:00', '1', '2603f4b96b554ab692ce5eeda95e56bf', '339941974-b0707db4-78f1-4b2b-bdf6-51dd7e55eeca', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-b0707db4-78f1-4b2b-bdf6-51dd7e55eeca', '', 'c9371f8fc15941c198ec58806ac662f1', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 04112024\nHora: 21:00 - 22:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T16:14:44.273-04:00', '', '', '2024-11-04T18:14:43.644-03:00', '1', '15964fcc3536457999f4b759f83a8ec8', '339941974-a84af09e-c4a3-4585-b2e2-ee1afdadb23e', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-a84af09e-c4a3-4585-b2e2-ee1afdadb23e', '', 'b1eead3cd8b042e7a6c6a258dbc1951a', 'entertainment', 'ARS', ' Reservar Cancha: Test Total Ingreso\nDia: 04112024\n Hora: 23:00 - 00:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T16:28:48.522-04:00', '', '', '2024-11-04T18:28:47.934-03:00', '1', '2f79bd46c470453fba24204a53792876', '339941974-64825e66-cb4f-4377-9c00-2f09129f67a4', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-64825e66-cb4f-4377-9c00-2f09129f67a4', '', '24127892ab1f4af98a98f3984c09ccf5', 'entertainment', 'ARS', ' Reservar Cancha: Test Total Ingreso\nDia: 04112024\n Hora: 21:00 - 22:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624716607', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T16:30:15.454-04:00', '', '', '2024-11-04T18:30:14.936-03:00', '1', 'db2327d314154e2abe00c482b788a8a8', '339941974-a30d24fa-7d97-4cf4-8f70-5417eef95637', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-a30d24fa-7d97-4cf4-8f70-5417eef95637', '', '08ced00ad0db4533abbe8cbf2a16df9e', 'entertainment', 'ARS', ' Reservar Cancha: Test Total Ingreso\nDia: 04112024\n Hora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625225614', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T18:30:19.723-04:00', '', '', '2024-11-04T20:30:19.306-03:00', '1', 'be93f42b708d463b999de4834e628509', '339941974-bf3dc7dd-759f-4f4e-b5e1-2a3fee3a2f03', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-bf3dc7dd-759f-4f4e-b5e1-2a3fee3a2f03', '', '2d95a7ca2e5e4fb3a4df51c118c77a7b', 'entertainment', 'ARS', ' Reservar Cancha: Test Total Ingreso\nDia: 04112024\n Hora: 23:00 - 00:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624873909', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T18:32:33.434-04:00', '', '', '2024-11-04T20:32:33.044-03:00', '1', '014d2872885341b69af83216acd0733f', '339941974-8b800148-ca42-4741-85e8-0f0f7de6d33e', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-8b800148-ca42-4741-85e8-0f0f7de6d33e', '', '26982964257b416995047736d649f936', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 04112024\nHora: 23:00 - 00:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625180250', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T18:34:22.042-04:00', '', '', '2024-11-04T20:34:21.533-03:00', '1', '7ce9326065354fe1b10d02b8edf4ba20', '339941974-354c5d50-ea0d-4c1b-9695-e113437d24df', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-354c5d50-ea0d-4c1b-9695-e113437d24df', '', 'f2f1497d31634dbbb62ef3a62d4d6ebf', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 04112024\nHora: 23:00 - 00:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624265632', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T18:40:26.316-04:00', '', '', '2024-11-04T20:40:25.903-03:00', '1', 'dbd86670f61a478592ce049ce0530204', '339941974-e7be117d-d223-4af8-bd73-c3cecc221a2e', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-e7be117d-d223-4af8-bd73-c3cecc221a2e', '', '69ac4b5c8a554659a9fa9a881dac3de2', 'entertainment', 'ARS', ' Reservar Cancha: Test Total Ingreso\nDia: 04112024\n Hora: 23:00 - 00:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624873909', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T18:50:58.903-04:00', '', '', '2024-11-04T20:50:58.487-03:00', '1', '0f19ff99562443afb7228b92e4ac34af', '339941974-81dd6503-bfad-41c6-8dc5-900269eb168e', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-81dd6503-bfad-41c6-8dc5-900269eb168e', '', 'b1dbe3e0821f418299c6a8618af97f33', 'entertainment', 'ARS', ' Reservar Cancha: Test Total Ingreso\nDia: 04112024\n Hora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625225614', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T20:13:25.444-04:00', '', '', '2024-11-04T22:13:25.045-03:00', '1', 'ba8bbc242f374c9694768e1bc1477a8f', '339941974-0091ca2a-5e3a-4e38-877c-83bc84c46e4b', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-0091ca2a-5e3a-4e38-877c-83bc84c46e4b', '', '8729afe3983044cea6490f327514b920', 'entertainment', 'ARS', ' Reservar Cancha: Test Total Ingreso\nDia: 04112024\n Hora: 23:00 - 00:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '11.7', 'MP-MKT-4739156168928171', '1.3689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624108921', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T20:54:33.328-04:00', '', '', '2024-11-04T22:54:32.962-03:00', '1', 'b067ebe9e77a4ae8b59010bbd517cd77', '339941974-809a38f9-c4db-4d2c-b540-dda1e2df7bdf', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-809a38f9-c4db-4d2c-b540-dda1e2df7bdf', '', '783d081e3cb34369a20d63c8c88c5128', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 05112024\nHora: 14:00 - 15:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T21:00:05.206-04:00', '', '', '2024-11-04T23:00:04.829-03:00', '1', '372c1a54de26432baa8c73f4c77c95b5', '339941974-498be220-dd81-460d-9e1d-e780257074c4', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-498be220-dd81-460d-9e1d-e780257074c4', '', '8efcae8ecfb541f5b442b33f14f45dc5', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 05112024\nHora: 14:00 - 15:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-04T21:39:49.396-04:00', '', '', '2024-11-04T23:39:49.012-03:00', '1', '241aa63225d343bebd2aac89b890d82b', '339941974-f7be7c04-e38c-43d7-b765-ffd87d4097b3', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-f7be7c04-e38c-43d7-b765-ffd87d4097b3', '', '1bf44121976c4295a46d105c030806ac', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 05112024\nHora: 16:00 - 17:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-05T00:04:43.832-04:00', '', '', '2024-11-05T02:04:43.455-03:00', '1', '2ba06cd6878347b1910780bfe98bb434', '339941974-6b28a120-7e28-476c-a187-10c6a9a92a1b', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-6b28a120-7e28-476c-a187-10c6a9a92a1b', '', 'f3c8cac1a058420b951324e635b6a3fc', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 10112024\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_855636-MLC80149923718_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624887558', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-05T22:54:16.319-04:00', '', '', '2024-11-06T00:54:15.937-03:00', '1', '1e17ef78ed8649cb98e81210af2dbd5f', '44387438-3a425b66-6945-41e6-a4c5-da8673313692', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-3a425b66-6945-41e6-a4c5-da8673313692', '', 'c453465ad39d4aaea67e450ac4943147', 'entertainment', 'ARS', ' Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 06112024\n Hora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_618091-MLB80195209548_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-05T23:04:06.720-04:00', '', '', '2024-11-06T01:04:06.318-03:00', '1', '33d46bf0ecae4cd1a29665abe3370c18', '44387438-807144de-bbbe-42e8-8e6e-2fe8b1d22e0c', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-807144de-bbbe-42e8-8e6e-2fe8b1d22e0c', '', '5e7b751ae448494da887c1fde3afdb97', 'entertainment', 'ARS', ' Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 11112024\n Hora: 21:00 - 22:00', 'https://http2.mlstatic.com/D_NQ_NP_618091-MLB80195209548_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '8190', 'MP-MKT-4739156168928171', '958.23', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-08T15:22:15.930-04:00', '', '', '2024-11-08T17:22:15.512-03:00', '1', '936cdd8c48534c80abd3f96de9fc582a', '339941974-505078d4-d11f-49c5-aff5-f1685ae21c5d', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-505078d4-d11f-49c5-aff5-f1685ae21c5d', '', '824df804ddbb4734bd78f2a2bab195f8', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 08112024\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_636604-MLB80510161549_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-08T15:44:10.655-04:00', '', '', '2024-11-08T17:44:10.252-03:00', '1', '5a216b15e6334f95aaeac6df60012e27', '44387438-793d93ce-4ef1-42d7-a023-0767fbe18853', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-793d93ce-4ef1-42d7-a023-0767fbe18853', '', '8888da0c68094606a380f340fe9ac736', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 11112024\nHora: 20:00 - 21:00', 'https://http2.mlstatic.com/D_NQ_NP_636604-MLB80510161549_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '819', 'MP-MKT-4739156168928171', '95.823', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-11T06:28:09.732-04:00', '', '', '2024-11-11T08:28:09.324-03:00', '1', '8f41281a41914b9c9d4638bd82b6fc77', '44387438-be64cc09-2bdd-4879-9ad6-87847668bd56', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-be64cc09-2bdd-4879-9ad6-87847668bd56', '', '9e8665278cb74ed3a1d6102b1bbd46b2', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 11112024\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_972595-MLA80556980529_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '819', 'MP-MKT-4739156168928171', '95.823', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624887558', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-11T06:36:10.329-04:00', '', '', '2024-11-11T08:36:09.962-03:00', '1', '80cf98c5ad23454a833bf7c2a84de455', '44387438-0f43b35d-cfa4-4b55-87ec-ef29b781e1f2', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-0f43b35d-cfa4-4b55-87ec-ef29b781e1f2', '', 'd7c0b3df43054ae1b8fadff851e15f9f', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 11112024\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_972595-MLA80556980529_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '819', 'MP-MKT-4739156168928171', '95.823', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624887558', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-11T15:52:37.425-04:00', '', '', '2024-11-11T17:52:37.051-03:00', '1', 'b2a19fa531d24d4e9499a90e67655901', '44387438-d8996551-f7b5-458d-a514-f21627f1fd1b', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-d8996551-f7b5-458d-a514-f21627f1fd1b', '', '7148d6d1b6d9486d87392be0687feaf5', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 18112024\nHora: 20:00 - 21:00', 'https://http2.mlstatic.com/D_NQ_NP_601593-MLA80333762870_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '234', 'MP-MKT-4739156168928171', '27.378', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-11T19:38:14.381-04:00', '', '', '2024-11-11T21:38:13.951-03:00', '1', 'd99b5c0263ec4b8b846623b18a5f7f10', '44387438-797993c6-f7e5-44b7-99b6-211153a0ec4b', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-797993c6-f7e5-44b7-99b6-211153a0ec4b', '', 'da7f6a62eebf45fc85bec5453d174d4a', 'entertainment', 'ARS', ' Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 11112024\n Hora: 23:00 - 00:00', 'https://http2.mlstatic.com/D_NQ_NP_601593-MLA80333762870_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '2340', 'MP-MKT-4739156168928171', '273.78', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624887558', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-11T22:09:38.858-04:00', '', '', '2024-11-12T00:09:38.444-03:00', '1', 'dfd91884d352478cbe683b8ddc5bc7cb', '339941974-2595158d-bf6e-42e2-9ca6-322c4f76c9e4', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-2595158d-bf6e-42e2-9ca6-322c4f76c9e4', '', '37c3dc258bbc4e79a53e5ee1fec5a75a', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 13112024\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_601593-MLA80333762870_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-11T22:38:24.851-04:00', '', '', '2024-11-11T23:39:24.440-03:00', '1', 'fbf0605b690341c58c41632b898eea8b', '339941974-89fc7f2c-3c28-46b9-bb27-19ec2129fc26', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-89fc7f2c-3c28-46b9-bb27-19ec2129fc26', '', 'd2ab5aeb4cba4702a935460303cb7072', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 13112024\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_601593-MLA80333762870_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-11T23:05:05.832-04:00', '', '', '2024-12-23T16:05:05.454-03:00', '1', 'd71292c351bf48379b1b40e7c41a17ec', '44387438-83b4ccc4-9290-436d-acdb-335710c0f08b', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-83b4ccc4-9290-436d-acdb-335710c0f08b', '', '4ddbe3d5843746e7b9526dc14c7baf0f', 'entertainment', 'ARS', ' Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 14112024\n Hora: 19:00 - 20:00', 'https://http2.mlstatic.com/D_NQ_NP_601593-MLA80333762870_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '2340', 'MP-MKT-4739156168928171', '273.78', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3795054759', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-12T17:09:35.309-04:00', '', '', '2024-11-12T18:10:34.888-03:00', '1', '63f6fcf0050749a6af6818c54116177b', '339941974-da3bea79-5011-4ce4-b6b2-ed4fb3a068be', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-da3bea79-5011-4ce4-b6b2-ed4fb3a068be', '', 'b9a68ed033ba450b8d4022e2c3f0d033', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 12112024\nHora: 23:00 - 00:00', 'https://http2.mlstatic.com/D_NQ_NP_601593-MLA80333762870_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-13T00:10:42.356-04:00', '', '', '2024-11-13T01:15:41.925-03:00', '1', 'ce13ad7207454dde87a0f7fe8a6ce473', '44387438-21159914-cbc5-4e16-ad94-f6ae8fdf8255', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-21159914-cbc5-4e16-ad94-f6ae8fdf8255', '', '5d35beff7eb349068166ff96a9250534', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 13112024\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_601593-MLA80333762870_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '234', 'MP-MKT-4739156168928171', '27.378', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624887558', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '339941974', '', '', '2024-11-14T10:50:10.506-04:00', '', '', '2024-11-14T11:55:10.043-03:00', '1', '13e06821e34046a5a831b1a42a78e5cd', '339941974-e52910fc-68ed-45cc-a44e-260c502c5b7f', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=339941974-e52910fc-68ed-45cc-a44e-260c502c5b7f', '', '9312bd69414c4b6982f298a6c7c08666', 'entertainment', 'ARS', 'Reservar Cancha: Test Total Ingreso\nDia: 14112024\nHora: 19:00 - 20:00', 'https://http2.mlstatic.com/D_NQ_NP_916589-MLA80396007706_112024-F.jpg', 'Reservar Cancha: Test Total Ingreso', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624108921', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-14T11:52:20.089-04:00', '', '', '2024-11-14T12:57:19.674-03:00', '1', '1866dd648512466c88b9f6da07bc8914', '44387438-d9746e23-0457-4e19-a044-db13736242bf', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-d9746e23-0457-4e19-a044-db13736242bf', '', '9317fa526e8047578d9dcf8ce1626cc8', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 20112024\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_916589-MLA80396007706_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '234', 'MP-MKT-4739156168928171', '27.378', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624759880', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-14T12:22:49.253-04:00', '', '', '2024-11-14T13:27:48.829-03:00', '1', '931ad866ab0847adbba9ed0715f3aebf', '44387438-1e3262cc-db04-4002-b62e-b1c07945cc94', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-1e3262cc-db04-4002-b62e-b1c07945cc94', '', '37f5132d2fc1451c9e91db9a55e1403b', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 27112024\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_916589-MLA80396007706_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '234', 'MP-MKT-4739156168928171', '27.378', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624331661', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-14T12:31:12.042-04:00', '', '', '2024-11-14T13:36:11.631-03:00', '1', '12ea1ea6d87847e7b6fa793f589bede5', '44387438-c50ebcbf-cf03-4084-82d5-018a8d95a2c0', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-c50ebcbf-cf03-4084-82d5-018a8d95a2c0', '', 'da4a9ef79c7244d5aba70c43bff2d86d', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 18112024\nHora: 20:00 - 21:00', 'https://http2.mlstatic.com/D_NQ_NP_916589-MLA80396007706_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '234', 'MP-MKT-4739156168928171', '27.378', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624666785', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-14T12:33:18.160-04:00', '', '', '2024-11-14T13:38:17.781-03:00', '1', '14da30c3a4eb40e484c2a20bfb16301d', '44387438-b437ff08-5aae-4bfd-ad72-58dc44a0d029', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-b437ff08-5aae-4bfd-ad72-58dc44a0d029', '', 'dd2704a01efe469282d1f47243c40de9', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 14112024\nHora: 19:00 - 20:00', 'https://http2.mlstatic.com/D_NQ_NP_916589-MLA80396007706_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '234', 'MP-MKT-4739156168928171', '27.378', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624366525', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-11-14T12:37:18.136-04:00', '', '', '2025-06-10T21:37:17.620-03:00', '1', '7caf68660ba644d09b96fcd46b793d62', '44387438-e59f0a67-a203-4109-923d-e2c9b9cb9b56', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-e59f0a67-a203-4109-923d-e2c9b9cb9b56', '', 'c0f5600540fc4276a79dda5aaa8f4f5a', 'entertainment', 'ARS', ' Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 14112024\n Hora: 19:00 - 20:00', 'https://http2.mlstatic.com/D_NQ_NP_916589-MLA80396007706_112024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '2340', 'MP-MKT-4739156168928171', '273.78', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624666785', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', '');
INSERT INTO `payment_preference` (`client_id`, `collector_id`, `coupon_code`, `coupon_labels`, `date_created`, `date_of_expiration`, `expiration_date_from`, `expiration_date_to`, `expires`, `external_reference`, `id`, `init_point`, `internal_metadata`, `items_id`, `items_category_id`, `items_currency_id`, `items_description`, `items_picture_url`, `items_title`, `items_quantity`, `items_unit_price`, `marketplace`, `marketplace_fee`, `notification_url`, `operation_type`, `payer_area_code`, `payer_number`, `payer_address_zip_code`, `payer_address_street_name`, `payer_address_street_number`, `email`, `identification_number`, `identification_type`, `name`, `surname`, `last_purchase`, `statement_descriptor`, `total_amount`, `last_updated`) VALUES
('', '44387438', '', '', '2024-12-11T00:10:05.295-04:00', '', '', '2024-12-11T01:15:04.912-03:00', '1', 'dbe4e80f80504b7fa5960640e5b7b5ce', '44387438-bea130db-1d35-4484-b086-9c9142b01501', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-bea130db-1d35-4484-b086-9c9142b01501', '', 'c135afc852fe4c0fa03dbaa9ad564189', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 18122024\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_633391-MLA81010833912_122024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '234', 'MP-MKT-4739156168928171', '27.378', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-12-11T00:13:00.890-04:00', '', '', '2024-12-11T01:18:00.555-03:00', '1', '8c064071ea504d40a90f879698da4eea', '44387438-3b1f5a38-82a3-4b01-8565-68d1c0a58eb0', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-3b1f5a38-82a3-4b01-8565-68d1c0a58eb0', '', 'fe1a87d497b04efdb1102d615f56796f', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 16122024\nHora: 20:00 - 21:00', 'https://http2.mlstatic.com/D_NQ_NP_633391-MLA81010833912_122024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '234', 'MP-MKT-4739156168928171', '27.378', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3624887558', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-12-20T17:43:06.831-04:00', '', '', '2024-12-20T18:48:06.435-03:00', '1', '1f5fae12e00d4b1b85243322d3c01b5f', '44387438-aa23d1d8-bba9-453d-af8c-c2bec4e22439', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-aa23d1d8-bba9-453d-af8c-c2bec4e22439', '', '7d8240ca9e414926b78a243603fd8e02', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 01012025\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_954850-MLB81513346205_122024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '58.5', 'MP-MKT-4739156168928171', '6.8445', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2024-12-20T19:36:55.409-04:00', '', '', '2024-12-20T20:41:55.042-03:00', '1', '381e18b7ce9b411cac65855593093224', '44387438-f313788f-3a62-47a8-8c3c-3dd8feb43969', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-f313788f-3a62-47a8-8c3c-3dd8feb43969', '', 'e6bc559a98664fd1aa65bfc396e0c886', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 23122024\nHora: 21:00 - 22:00', 'https://http2.mlstatic.com/D_NQ_NP_954850-MLB81513346205_122024-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '58.5', 'MP-MKT-4739156168928171', '6.8445', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2025-01-15T07:57:52.965-04:00', '', '', '2025-01-15T09:02:52.615-03:00', '1', '7b7ae6ccf22d4f7c8f9e8c892a3ad267', '44387438-50e620ea-3016-410f-b868-1e58fcce1552', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-50e620ea-3016-410f-b868-1e58fcce1552', '', '7b428735055f48958a2e3b89b1fbfc9f', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 15012025\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_650920-MLA81940661415_012025-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '58.5', 'MP-MKT-4739156168928171', '6.8445', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2025-01-15T21:31:39.287-04:00', '', '', '2025-01-15T22:36:38.897-03:00', '1', 'b65ecbf6f8c94bb1966b19019abdb21e', '44387438-ec23fc5c-a2e6-43c8-adb7-aada847e01e7', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-ec23fc5c-a2e6-43c8-adb7-aada847e01e7', '', '0d6745fe12e741a081e2ba1e33215b71', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 20012025\nHora: 21:00 - 22:00', 'https://http2.mlstatic.com/D_NQ_NP_650920-MLA81940661415_012025-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '58.5', 'MP-MKT-4739156168928171', '6.8445', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2025-01-16T09:13:44.446-04:00', '', '', '2025-01-16T10:18:44.076-03:00', '1', '68dc23b2fad64691844ce6493782e63d', '44387438-d69a6600-dd4b-4f8b-a753-5d6cb8177ccc', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-d69a6600-dd4b-4f8b-a753-5d6cb8177ccc', '', '638d5ff4dcbc43f7a208a4afcdbe89d0', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 16012025\nHora: 17:00 - 18:00', 'https://http2.mlstatic.com/D_NQ_NP_650920-MLA81940661415_012025-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '58.5', 'MP-MKT-4739156168928171', '6.8445', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2025-01-20T18:04:33.864-04:00', '', '', '2025-01-20T19:09:33.381-03:00', '1', '4146eebc64824f7db8eaedf0558afb1c', '44387438-47625224-6d89-46ea-896b-580d5ed3039a', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-47625224-6d89-46ea-896b-580d5ed3039a', '', '448142d4e60c4243830424a6f49e6a0d', 'entertainment', 'ARS', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 20012025\nHora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_606085-MLA81757787648_012025-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '58.5', 'MP-MKT-4739156168928171', '6.8445', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', ''),
('', '44387438', '', '', '2025-04-05T09:20:35.704-04:00', '', '', '2025-10-30T18:20:35.282-03:00', '1', '290909621154405098ca7b47aafd8338', '44387438-6dccbcd3-2e59-4f53-8c76-2349c95ddc83', 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=44387438-6dccbcd3-2e59-4f53-8c76-2349c95ddc83', '', '85eaa516bac04777b744ac7daac52a7a', 'entertainment', 'ARS', ' Reservar Cancha: Hay Equipo F5 - Sede Avalos\nDia: 09042025\n Hora: 22:00 - 23:00', 'https://http2.mlstatic.com/D_NQ_NP_710287-MLA83545201763_042025-F.jpg', 'Reservar Cancha: Hay Equipo F5 - Sede Avalos', '1', '1.17', 'MP-MKT-4739156168928171', '0.13689', 'https://botcanchero.com/mpay-hook', 'regular_payment', '549', '3625293513', '', '', '', '', '', '', '', '', '', 'BotCanchero', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `price_ranges`
--

CREATE TABLE `price_ranges` (
  `id` int NOT NULL,
  `id_field` int DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `price_ranges`
--

INSERT INTO `price_ranges` (`id`, `id_field`, `start_time`, `end_time`, `price`) VALUES
(1, 1, '00:00:00', '23:59:59', 4000.00),
(2, 66, '00:00:00', '23:59:59', 500.00),
(3, 67, '00:00:00', '23:59:59', 10.00),
(4, 68, '00:00:00', '23:59:59', 1.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `province`
--

CREATE TABLE `province` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `acronym` varchar(10) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `province`
--

INSERT INTO `province` (`id`, `name`, `acronym`) VALUES
(1, 'Buenos Aires', 'BA'),
(2, 'Catamarca', 'CT'),
(3, 'Chaco', 'CH'),
(4, 'Chubut', 'CB'),
(5, 'Córdoba', 'CC'),
(6, 'Corrientes', 'CR'),
(7, 'Entre Ríos', 'ER'),
(8, 'Formosa', 'FO'),
(9, 'Jujuy', 'JY'),
(10, 'La Pampa', 'LP'),
(11, 'La Rioja', 'LR'),
(12, 'Mendoza', 'MZ'),
(13, 'Misiones', 'MN'),
(14, 'Neuquén', 'NQ'),
(15, 'Río Negro', 'RN'),
(16, 'Salta', 'SA'),
(17, 'San Juan', 'SJ'),
(18, 'San Luis', 'SL'),
(19, 'Santa Cruz', 'SC'),
(20, 'Santa Fe', 'SF'),
(21, 'Santiago del Estero', 'SE'),
(22, 'Tierra del Fuego, Antártida e Islas del Atlántico Sur', 'TF'),
(23, 'Tucumán', 'TM');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules`
--

CREATE TABLE `schedules` (
  `id` int NOT NULL,
  `hour` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `time` time NOT NULL,
  `hour12` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `schedules`
--

INSERT INTO `schedules` (`id`, `hour`, `time`, `hour12`) VALUES
(1, '00:00 - 01:00', '00:00:00', '12:00 AM - 01:00 AM'),
(2, '01:00 - 02:00', '01:00:00', '01:00 AM - 02:00 AM'),
(3, '02:00 - 03:00', '02:00:00', '02:00 AM - 03:00 AM'),
(4, '03:00 - 04:00', '03:00:00', '03:00 AM - 04:00 AM'),
(5, '04:00 - 05:00', '04:00:00', '04:00 AM - 05:00 AM'),
(6, '05:00 - 06:00', '05:00:00', '05:00 AM - 06:00 AM'),
(7, '06:00 - 07:00', '06:00:00', '06:00 AM - 07:00 AM'),
(8, '07:00 - 08:00', '07:00:00', '07:00 AM - 08:00 AM'),
(9, '08:00 - 09:00', '08:00:00', '08:00 AM - 09:00 AM'),
(10, '09:00 - 10:00', '09:00:00', '09:00 AM - 10:00 AM'),
(11, '10:00 - 11:00', '10:00:00', '10:00 AM - 11:00 AM'),
(12, '11:00 - 12:00', '11:00:00', '11:00 AM - 12:00 PM'),
(13, '12:00 - 13:00', '12:00:00', '12:00 PM - 01:00 PM'),
(14, '13:00 - 14:00', '13:00:00', '01:00 PM - 02:00 PM'),
(15, '14:00 - 15:00', '14:00:00', '02:00 PM - 03:00 PM'),
(16, '15:00 - 16:00', '15:00:00', '03:00 PM - 04:00 PM'),
(17, '16:00 - 17:00', '16:00:00', '04:00 PM - 05:00 PM'),
(18, '17:00 - 18:00', '17:00:00', '05:00 PM - 06:00 PM'),
(19, '18:00 - 19:00', '18:00:00', '06:00 PM - 07:00 PM'),
(20, '19:00 - 20:00', '19:00:00', '07:00 PM - 08:00 PM'),
(21, '20:00 - 21:00', '20:00:00', '08:00 PM - 09:00 PM'),
(22, '21:00 - 22:00', '21:00:00', '09:00 PM - 10:00 PM'),
(23, '22:00 - 23:00', '22:00:00', '10:00 PM - 11:00 PM'),
(24, '23:00 - 00:00', '23:00:00', '11:00 PM - 12:00 AM');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_day`
--

CREATE TABLE `schedules_day` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `schedules_day`
--

INSERT INTO `schedules_day` (`id`, `name`) VALUES
(1, 'Lunes'),
(2, 'Martes'),
(3, 'Miércoles'),
(4, 'Jueves'),
(5, 'Viernes'),
(6, 'Sábado'),
(7, 'Domingo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_field`
--

CREATE TABLE `schedules_field` (
  `id` int NOT NULL,
  `id_field` int NOT NULL,
  `id_schedule` int NOT NULL,
  `id_day` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `schedules_field`
--

INSERT INTO `schedules_field` (`id`, `id_field`, `id_schedule`, `id_day`) VALUES
(672, 63, 1, 1),
(673, 63, 4, 1),
(675, 63, 24, 3),
(690, 64, 20, 5),
(691, 64, 21, 5),
(692, 64, 23, 5),
(693, 64, 24, 5),
(694, 62, 20, 7),
(695, 62, 21, 7),
(696, 62, 23, 7),
(697, 62, 24, 7),
(698, 63, 21, 7),
(699, 63, 24, 7),
(702, 64, 19, 7),
(703, 64, 21, 7),
(704, 64, 23, 7),
(705, 64, 24, 7),
(711, 64, 5, 2),
(712, 64, 14, 2),
(713, 64, 20, 2),
(714, 64, 21, 2),
(715, 64, 23, 2),
(716, 64, 24, 2),
(727, 64, 1, 3),
(728, 64, 23, 3),
(729, 64, 24, 3),
(730, 64, 16, 1),
(731, 64, 17, 1),
(732, 64, 18, 1),
(733, 64, 20, 1),
(734, 64, 21, 1),
(735, 64, 22, 1),
(736, 64, 23, 1),
(737, 64, 24, 1),
(744, 64, 17, 4),
(745, 64, 19, 4),
(746, 64, 20, 4),
(747, 64, 21, 4),
(748, 64, 22, 4),
(749, 64, 23, 4),
(750, 64, 24, 4),
(751, 65, 16, 4),
(752, 65, 17, 4),
(753, 62, 16, 4),
(754, 62, 17, 4),
(755, 62, 20, 4),
(756, 62, 23, 4),
(757, 64, 21, 6),
(758, 62, 21, 6),
(759, 65, 21, 6),
(763, 66, 21, 1),
(764, 66, 22, 1),
(765, 66, 23, 1),
(766, 66, 24, 1),
(791, 67, 15, 2),
(792, 67, 17, 2),
(793, 67, 19, 2),
(794, 67, 20, 2),
(795, 67, 23, 2),
(796, 67, 24, 2),
(797, 67, 23, 7),
(798, 67, 24, 7),
(799, 67, 17, 5),
(800, 67, 20, 5),
(801, 67, 23, 5),
(802, 67, 20, 4),
(803, 67, 21, 4),
(804, 67, 23, 4),
(805, 67, 24, 4),
(806, 67, 19, 1),
(807, 67, 21, 1),
(808, 67, 22, 1),
(809, 67, 23, 1),
(810, 67, 24, 1),
(811, 67, 17, 3),
(812, 67, 20, 3),
(813, 67, 23, 3),
(814, 66, 23, 3),
(816, 66, 18, 4),
(817, 66, 20, 4),
(848, 66, 17, 7),
(849, 66, 23, 7),
(854, 68, 16, 1),
(855, 68, 17, 1),
(856, 68, 18, 1),
(857, 68, 19, 1),
(858, 68, 21, 1),
(859, 68, 22, 1),
(860, 68, 23, 1),
(861, 68, 24, 1),
(862, 68, 14, 4),
(863, 68, 15, 4),
(864, 68, 16, 4),
(865, 68, 17, 4),
(866, 68, 18, 4),
(867, 68, 19, 4),
(868, 68, 21, 4),
(869, 68, 22, 4),
(870, 68, 23, 4),
(871, 68, 24, 4),
(872, 68, 16, 6),
(873, 68, 17, 6),
(874, 68, 18, 6),
(875, 68, 19, 6),
(876, 68, 21, 6),
(877, 68, 22, 6),
(878, 68, 23, 6),
(879, 68, 24, 6),
(880, 68, 16, 2),
(881, 68, 17, 2),
(882, 68, 18, 2),
(883, 68, 19, 2),
(884, 68, 21, 2),
(885, 68, 22, 2),
(886, 68, 23, 2),
(887, 68, 24, 2),
(888, 68, 16, 3),
(889, 68, 17, 3),
(890, 68, 18, 3),
(891, 68, 19, 3),
(892, 68, 21, 3),
(893, 68, 22, 3),
(894, 68, 23, 3),
(895, 68, 24, 3),
(896, 68, 16, 5),
(897, 68, 17, 5),
(898, 68, 18, 5),
(899, 68, 19, 5),
(900, 68, 21, 5),
(901, 68, 22, 5),
(902, 68, 23, 5),
(903, 68, 24, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `services_soccer`
--

CREATE TABLE `services_soccer` (
  `id_service` int NOT NULL,
  `name_service` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `services_soccer`
--

INSERT INTO `services_soccer` (`id_service`, `name_service`) VALUES
(1, 'Zona de Parrillas'),
(2, 'Bar con Wifi'),
(3, 'Vestuarios'),
(4, 'Servicio de Emergencia'),
(5, 'Estacionamiento'),
(6, 'Escuela de Fútbol'),
(7, 'Cumpleaños');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soccer_field`
--

CREATE TABLE `soccer_field` (
  `id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `latitude` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `length` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `threshold` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `tax_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token_mercadopago` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_province` int NOT NULL,
  `id_city` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `soccer_field`
--

INSERT INTO `soccer_field` (`id`, `full_name`, `phone`, `address`, `latitude`, `length`, `logo`, `threshold`, `status`, `tax_id`, `token_mercadopago`, `id_province`, `id_city`) VALUES
(1, 'COMPLEJO SARMIENTO FUTBOL 5/6', '37951650592323232323', 'Av. Sarmiento esquina, Tte. Cundom 3400, Corrientes', '-2.748534171000', '-5.883100748000', '', 5, 0, '11111111111', 'APP_USR-4739156168928171-120419-1109ef7e3b92a14b0d68931dc04e3f01-177481545', 1, 1),
(66, 'Hay Equipo F5 - Sede Avalos', '549362 4655304', 'Av. Avalos 650, H3500BZT Resistencia, Chaco, Argentina', '-27.439607575999503', '-58.988179736606696', '48dd69af-baf5-41b8-a740-3dfd11030250.jfif', 2, 1, NULL, 'APP_USR-4739156168928171-110414-b1d1b5eb5b5388dfce7349e7c90fba55-44387438', 3, 2),
(67, 'Test Total Ingreso', '549333333333', 'Av. Avalos 650, H3500BZT Resistencia, Chaco, Argentina', '-34.54645425538717', '-58.78587562698162', 'WhatsApp Image 2024-12-11 at 00.30.30.jpeg', 2, 1, NULL, 'APP_USR-4739156168928171-110410-ca5e08012beef6b70569053dc5a7528f-339941974', 6, 7),
(68, 'La Chacra', '+5493624608705', 'Av. Vélez Sarsfield 2150 2028, H3508 Resistencia, Chaco', '-27.44138879863452', '-58.96147907252306', 'bg.png', 1, 1, NULL, NULL, 3, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soccer_field_services`
--

CREATE TABLE `soccer_field_services` (
  `id` int NOT NULL,
  `id_field` int DEFAULT NULL,
  `id_service_field` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `soccer_field_services`
--

INSERT INTO `soccer_field_services` (`id`, `id_field`, `id_service_field`) VALUES
(20, 67, 1),
(21, 67, 3),
(22, 67, 4),
(23, 68, 1),
(24, 68, 2),
(25, 68, 3),
(26, 68, 4),
(27, 68, 5),
(28, 68, 6),
(29, 68, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones`
--

CREATE TABLE `transacciones` (
  `id` int NOT NULL,
  `id_booking` int NOT NULL DEFAULT '0',
  `data_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `currency` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `timeout` int NOT NULL,
  `created` bigint NOT NULL,
  `split_entityType` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `split_tax_id` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `split_uid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `split_reference` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `split_hold` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `split_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `split_percentage` int NOT NULL,
  `split_total` decimal(10,2) NOT NULL,
  `split_fee` decimal(10,2) NOT NULL,
  `split_refundFee` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `date_create` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `id_field` int NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rol` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'canchero',
  `status` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `full_name`, `phone`, `id_field`, `password`, `email`, `avatar`, `rol`, `status`) VALUES
(1, 'botCanchero', '000000', 1, '$2y$12$ryDoqP1kNtgBGL6.mwgRM.pLcBUhEvpbnPm2q/5GqXMvV/HmVVbFC', 'botcancheo', NULL, 'botCanchero', 1),
(21, 'SuperAdmin', '000', 1, '$2y$12$oy65bEEfppjavVl8STBlSeO5hWjJn3iKCrlYdbMojXFWI60hF6CmW', 'admin', NULL, 'superAdmin', 1),
(51, 'Matias Luciano Garcia', '+543625293513', 67, '$2y$12$yHcC8MgCrngmN3wGbhtL3ef/YLuJKa1GChqrqemAOtJYPcELUq2u6', 'matiasgarcia444@gmail.com', NULL, 'canchero', 1),
(52, 'Mauricio Ayala', '+5493795054759', 66, '$2y$12$4ZYqmkT6oZYZtVAwPGf15uQ6BgPFkOFBfYG0M.jTlosR.TSlC40gG', 'mauricioadolfosys@gmail.com', NULL, 'canchero', 1),
(53, 'Pablo Alarcon', '5493624608705', 68, '$2y$12$w0al2cIBvzkPRcC.o4.Ny.JsKnna9E7YcDPOSi2e7x0kFo6GYwb.e', 'cr.alarconpablo@gmail.com', NULL, 'canchero', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int NOT NULL,
  `data_id` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `id_notification` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `id_canchero` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `method_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_create` datetime NOT NULL,
  `id_booking` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vouchers`
--

INSERT INTO `vouchers` (`id`, `data_id`, `id_notification`, `id_canchero`, `method_name`, `date_create`, `id_booking`) VALUES
(137, '68548673738', '109111630210', '177481545', 'Mercado Pago', '2023-12-11 00:29:20', 237),
(138, '68594444204', '109101970423', '177481545', 'Mercado Pago', '2023-12-11 18:36:08', 289),
(139, '68594444204', '109125664678', '177481545', 'Mercado Pago', '2023-12-11 18:40:11', NULL),
(140, '68797199664', '109195685788', '177481545', 'Mercado Pago', '2023-12-14 21:32:06', 295),
(141, '68941093694', '109220161047', '177481545', 'Mercado Pago', '2023-12-17 01:00:20', 296),
(142, '68941093694', '109244307072', '177481545', 'Mercado Pago', '2023-12-17 01:04:04', NULL),
(143, '123456', '123456', '339941974', 'Mercado Pago', '2024-11-04 10:34:32', NULL),
(144, '654321', '123456', '339941974', 'Mercado Pago', '2024-11-04 10:36:08', NULL),
(145, '123456', '123456', '339941974', 'Mercado Pago', '2024-11-04 10:55:03', NULL),
(146, '92048564393', '116824243325', '339941974', 'Mercado Pago', '2024-11-04 12:46:28', NULL),
(147, '92370312326', '116874020738', '339941974', 'Mercado Pago', '2024-11-04 12:49:32', 297),
(148, '92056188755', '116825847457', '339941974', 'Mercado Pago', '2024-11-04 14:03:56', 298),
(149, '92390526042', '116828925275', '339941974', 'Mercado Pago', '2024-11-04 16:37:04', 299),
(150, '92370312326', '116878739424', '339941974', 'Mercado Pago', '2024-11-04 16:40:50', NULL),
(151, '92394173430', '116829753033', '339941974', 'Mercado Pago', '2024-11-04 17:16:00', NULL),
(152, '92395578492', '116830076103', '339941974', 'Mercado Pago', '2024-11-04 17:29:34', 300),
(153, '92395693256', '116830059257', '339941974', 'Mercado Pago', '2024-11-04 17:31:07', 301),
(154, '92394690436', '116880022234', '339941974', 'Mercado Pago', '2024-11-04 17:40:17', NULL),
(155, '92407327118', '116882532630', '339941974', 'Mercado Pago', '2024-11-04 19:30:44', 302),
(156, '92407327118', '116832803829', '339941974', 'Mercado Pago', '2024-11-04 19:31:20', NULL),
(157, '92086194503', '116882563690', '339941974', 'Mercado Pago', '2024-11-04 19:34:53', 302),
(158, '92086741597', '116882751002', '339941974', 'Mercado Pago', '2024-11-04 19:41:03', 303),
(159, '92086741597', '116833056673', '339941974', 'Mercado Pago', '2024-11-04 19:41:27', NULL),
(160, '92409388384', '116882923882', '339941974', 'Mercado Pago', '2024-11-04 19:51:22', 303),
(161, '92095346241', '116885316828', '339941974', 'Mercado Pago', '2024-11-04 21:13:54', 304),
(162, '92098571175', '116886450344', '339941974', 'Mercado Pago', '2024-11-04 21:55:30', NULL),
(163, '92098571175', '116886464614', '339941974', 'Mercado Pago', '2024-11-04 21:56:24', NULL),
(164, '92420546244', '116886487720', '339941974', 'Mercado Pago', '2024-11-04 22:00:32', 306),
(165, '92423030524', '116837602825', '339941974', 'Mercado Pago', '2024-11-04 22:40:29', 307),
(166, '92107208157', '116889396414', '339941974', 'Mercado Pago', '2024-11-05 01:05:24', 308),
(167, '92208250513', '116911650216', '44387438', 'Mercado Pago', '2024-11-05 23:55:07', 309),
(168, '92208688947', '116861941651', '44387438', 'Mercado Pago', '2024-11-06 00:07:47', 310),
(169, '92208688947', '116862049293', '44387438', 'Mercado Pago', '2024-11-06 00:12:22', NULL),
(170, '92810045880', '116974594024', '339941974', 'Mercado Pago', '2024-11-08 16:39:03', 311),
(171, '92810649952', '116974696608', '44387438', 'Mercado Pago', '2024-11-08 16:44:47', 312),
(172, '92708774391', '117033367260', '44387438', 'Mercado Pago', '2024-11-11 07:29:40', 313),
(173, '92764241301', '117043503478', '44387438', 'Mercado Pago', '2024-11-11 16:53:39', 314),
(174, '93123289176', '117051571608', '339941974', 'Mercado Pago', '2024-11-11 23:11:13', 315),
(175, '93361919748', '117105243644', '339941974', 'Mercado Pago', '2024-11-14 11:50:36', 316),
(176, '96670621731', '117897672073', '44387438', 'Mercado Pago', '2024-12-20 20:37:19', 317),
(177, '99376429172', '118457288757', '44387438', 'Mercado Pago', '2025-01-15 08:58:17', 318),
(178, '99093964275', '118530036710', '44387438', 'Mercado Pago', '2025-01-15 22:32:01', 319),
(179, '99124004911', '118537454438', '44387438', 'Mercado Pago', '2025-01-16 10:14:19', 321),
(180, '99542342357', '118580373087', '44387438', 'Mercado Pago', '2025-01-20 19:09:19', 322),
(181, '106981891561', '120306537937', '44387438', 'Mercado Pago', '2025-04-05 10:21:24', 323);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `api_token`
--
ALTER TABLE `api_token`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `UsuarioPlayerId` (`id_customer`),
  ADD KEY `CanchaAsignada` (`id_field`),
  ADD KEY `time_booking` (`time_booking`),
  ADD KEY `user` (`user`),
  ADD KEY `status` (`status`),
  ADD KEY `day_booking` (`day_booking`);

--
-- Indices de la tabla `booking_logs`
--
ALTER TABLE `booking_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users` (`users`),
  ADD KEY `id_reserva` (`id_reserva`),
  ADD KEY `log` (`log`);

--
-- Indices de la tabla `booking_status`
--
ALTER TABLE `booking_status`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_provincia` (`id_provincia`) USING BTREE;

--
-- Indices de la tabla `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `error_log`
--
ALTER TABLE `error_log`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `field_schedule`
--
ALTER TABLE `field_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mobbex_ops`
--
ALTER TABLE `mobbex_ops`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `checkout_uid` (`checkout_uid`);

--
-- Indices de la tabla `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indices de la tabla `payment_app_web`
--
ALTER TABLE `payment_app_web`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `price_ranges`
--
ALTER TABLE `price_ranges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_field` (`id_field`);

--
-- Indices de la tabla `province`
--
ALTER TABLE `province`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `schedules_day`
--
ALTER TABLE `schedules_day`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `schedules_field`
--
ALTER TABLE `schedules_field`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_day` (`id_day`),
  ADD KEY `id_field` (`id_field`),
  ADD KEY `id_schedule` (`id_schedule`);

--
-- Indices de la tabla `services_soccer`
--
ALTER TABLE `services_soccer`
  ADD PRIMARY KEY (`id_service`);

--
-- Indices de la tabla `soccer_field`
--
ALTER TABLE `soccer_field`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_city` (`id_city`),
  ADD KEY `id_province` (`id_province`);

--
-- Indices de la tabla `soccer_field_services`
--
ALTER TABLE `soccer_field_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_field` (`id_field`),
  ADD KEY `id_service_field` (`id_service_field`);

--
-- Indices de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `CanchaAsignada` (`id_field`);

--
-- Indices de la tabla `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `api_token`
--
ALTER TABLE `api_token`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=324;

--
-- AUTO_INCREMENT de la tabla `booking_logs`
--
ALTER TABLE `booking_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=600;

--
-- AUTO_INCREMENT de la tabla `booking_status`
--
ALTER TABLE `booking_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `city`
--
ALTER TABLE `city`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `error_log`
--
ALTER TABLE `error_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `field_schedule`
--
ALTER TABLE `field_schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mobbex_ops`
--
ALTER TABLE `mobbex_ops`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de la tabla `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT de la tabla `payment_app_web`
--
ALTER TABLE `payment_app_web`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `price_ranges`
--
ALTER TABLE `price_ranges`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `province`
--
ALTER TABLE `province`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `schedules_field`
--
ALTER TABLE `schedules_field`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=906;

--
-- AUTO_INCREMENT de la tabla `services_soccer`
--
ALTER TABLE `services_soccer`
  MODIFY `id_service` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `soccer_field`
--
ALTER TABLE `soccer_field`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de la tabla `soccer_field_services`
--
ALTER TABLE `soccer_field_services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`time_booking`) REFERENCES `schedules` (`id`),
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`user`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `booking_ibfk_4` FOREIGN KEY (`status`) REFERENCES `booking_status` (`id`),
  ADD CONSTRAINT `booking_ibfk_5` FOREIGN KEY (`id_field`) REFERENCES `soccer_field` (`id`),
  ADD CONSTRAINT `booking_ibfk_6` FOREIGN KEY (`day_booking`) REFERENCES `schedules_day` (`id`);

--
-- Filtros para la tabla `city`
--
ALTER TABLE `city`
  ADD CONSTRAINT `city_ibfk_1` FOREIGN KEY (`id_provincia`) REFERENCES `province` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `price_ranges`
--
ALTER TABLE `price_ranges`
  ADD CONSTRAINT `price_ranges_ibfk_1` FOREIGN KEY (`id_field`) REFERENCES `soccer_field` (`id`);

--
-- Filtros para la tabla `schedules_field`
--
ALTER TABLE `schedules_field`
  ADD CONSTRAINT `schedules_field_ibfk_1` FOREIGN KEY (`id_day`) REFERENCES `schedules_day` (`id`),
  ADD CONSTRAINT `schedules_field_ibfk_2` FOREIGN KEY (`id_field`) REFERENCES `soccer_field` (`id`),
  ADD CONSTRAINT `schedules_field_ibfk_3` FOREIGN KEY (`id_schedule`) REFERENCES `schedules` (`id`);

--
-- Filtros para la tabla `soccer_field`
--
ALTER TABLE `soccer_field`
  ADD CONSTRAINT `soccer_field_ibfk_1` FOREIGN KEY (`id_city`) REFERENCES `city` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `soccer_field_ibfk_2` FOREIGN KEY (`id_province`) REFERENCES `province` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `soccer_field_services`
--
ALTER TABLE `soccer_field_services`
  ADD CONSTRAINT `soccer_field_services_ibfk_1` FOREIGN KEY (`id_field`) REFERENCES `soccer_field` (`id`),
  ADD CONSTRAINT `soccer_field_services_ibfk_2` FOREIGN KEY (`id_service_field`) REFERENCES `services_soccer` (`id_service`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;