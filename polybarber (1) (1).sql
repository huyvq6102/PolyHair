-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th12 04, 2025 lúc 02:38 PM
-- Phiên bản máy phục vụ: 8.0.30
-- Phiên bản PHP: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `polybarber`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('Chờ xử lý','Đã xác nhận','Đang thực hiện','Hoàn thành','Đã hủy','Chưa thanh toán','Đã thanh toán') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `employee_id`, `status`, `start_at`, `end_at`, `note`, `cancellation_reason`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 3, 'Đã thanh toán', '2025-12-08 03:30:00', '2025-12-09 17:20:00', NULL, NULL, '2025-12-04 07:23:28', '2025-12-04 07:26:25', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `appointment_details`
--

CREATE TABLE `appointment_details` (
  `id` bigint UNSIGNED NOT NULL,
  `appointment_id` bigint UNSIGNED DEFAULT NULL,
  `service_variant_id` bigint UNSIGNED DEFAULT NULL,
  `combo_id` bigint UNSIGNED DEFAULT NULL,
  `combo_item_id` bigint UNSIGNED DEFAULT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `price_snapshot` decimal(10,2) DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `status` enum('Chờ','Xác nhận','Hoàn thành','Hủy') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `appointment_details`
--

INSERT INTO `appointment_details` (`id`, `appointment_id`, `service_variant_id`, `combo_id`, `combo_item_id`, `employee_id`, `price_snapshot`, `duration`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 18, NULL, NULL, 3, 150000.00, 60, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(2, 1, 17, NULL, NULL, 3, 100000.00, 30, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(3, 1, 18, NULL, NULL, 3, 150000.00, 60, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(4, 1, 19, NULL, NULL, 3, 120000.00, 45, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(5, 1, 20, NULL, NULL, 3, 250000.00, 50, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(6, 1, 21, NULL, NULL, 3, 300000.00, 60, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(7, 1, 22, NULL, NULL, 3, 499000.00, 60, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(8, 1, 23, NULL, NULL, 3, 399000.00, 50, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(9, 1, 24, NULL, NULL, 3, 599000.00, 90, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(10, 1, 25, NULL, NULL, 3, 449000.00, 90, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(11, 1, 26, NULL, NULL, 3, 499000.00, 60, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(12, 1, 27, NULL, NULL, 3, 549000.00, 90, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(13, 1, 28, NULL, NULL, 3, 499000.00, 90, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(14, 1, 29, NULL, NULL, 3, 449000.00, 120, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(15, 1, 30, NULL, NULL, 3, 549000.00, 180, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(16, 1, 17, NULL, NULL, 3, 100000.00, 30, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(17, 1, 18, NULL, NULL, 3, 150000.00, 60, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(18, 1, 19, NULL, NULL, 3, 120000.00, 45, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(19, 1, 20, NULL, NULL, 3, 250000.00, 50, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(20, 1, 21, NULL, NULL, 3, 300000.00, 60, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(21, 1, 22, NULL, NULL, 3, 499000.00, 60, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(22, 1, 23, NULL, NULL, 3, 399000.00, 50, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(23, 1, 24, NULL, NULL, 3, 599000.00, 90, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(24, 1, 25, NULL, NULL, 3, 449000.00, 90, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(25, 1, 26, NULL, NULL, 3, 499000.00, 60, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(26, 1, 27, NULL, NULL, 3, 549000.00, 90, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(27, 1, 28, NULL, NULL, 3, 499000.00, 90, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(28, 1, 29, NULL, NULL, 3, 449000.00, 120, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(29, 1, 30, NULL, NULL, 3, 549000.00, 180, 'Chờ', NULL, '2025-12-04 07:23:28', '2025-12-04 07:23:28'),
(30, 1, NULL, NULL, NULL, 3, 80000.00, 60, 'Chờ', 'Cắt tóc nam', '2025-12-04 07:23:28', '2025-12-04 07:23:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `appointment_logs`
--

CREATE TABLE `appointment_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `appointment_id` bigint UNSIGNED DEFAULT NULL,
  `status_from` enum('Chờ xử lý','Đã xác nhận','Đang thực hiện','Hoàn thành','Đã hủy') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_to` enum('Chờ xử lý','Đã xác nhận','Đang thực hiện','Hoàn thành','Đã hủy') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modified_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `appointment_logs`
--

INSERT INTO `appointment_logs` (`id`, `appointment_id`, `status_from`, `status_to`, `modified_by`, `created_at`) VALUES
(1, 1, NULL, 'Chờ xử lý', 1, '2025-12-04 14:23:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel_cache_appointment_email_sent_1', 'b:1;', 1764858508);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `images` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `combos`
--

CREATE TABLE `combos` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `owner_service_id` bigint UNSIGNED DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` enum('Hoạt động','Vô hiệu hóa') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `combos`
--

INSERT INTO `combos` (`id`, `name`, `slug`, `description`, `image`, `category_id`, `owner_service_id`, `price`, `status`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'PolyCombo', 'polycombo-69318da4ce6f3', NULL, '1764856541_kiem-tra-va-hoan-thien.jpg', 5, NULL, 119000.00, 'Hoạt động', 0, '2025-12-04 06:33:24', '2025-12-04 06:55:41', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `combo_items`
--

CREATE TABLE `combo_items` (
  `id` bigint UNSIGNED NOT NULL,
  `combo_id` bigint UNSIGNED DEFAULT NULL,
  `service_id` bigint UNSIGNED DEFAULT NULL,
  `service_variant_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `price_override` decimal(10,2) DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `combo_items`
--

INSERT INTO `combo_items` (`id`, `combo_id`, `service_id`, `service_variant_id`, `quantity`, `price_override`, `notes`, `created_at`, `updated_at`) VALUES
(35, 1, 10, NULL, 1, NULL, NULL, '2025-12-04 07:37:54', '2025-12-04 07:37:54'),
(36, 1, 12, NULL, 1, NULL, NULL, '2025-12-04 07:37:54', '2025-12-04 07:37:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `id` bigint UNSIGNED NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_product` bigint UNSIGNED NOT NULL,
  `id_user` bigint UNSIGNED NOT NULL,
  `approve` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `rating` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `employees`
--

CREATE TABLE `employees` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('Nam','Nữ','Khác') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `position` enum('Stylist','Barber','Shampooer','Receptionist') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level` enum('Intern','Junior','Middle','Senior') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience_years` tinyint DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Đang làm việc','Nghỉ phép','Vô hiệu hóa') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `avatar`, `gender`, `dob`, `position`, `level`, `experience_years`, `bio`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, NULL, NULL, NULL, 'Stylist', 'Senior', 5, 'Chuyên gia cắt tóc và tạo kiểu với hơn 5 năm kinh nghiệm', 'Đang làm việc', '2025-12-01 10:36:47', '2025-12-01 10:36:47', NULL),
(2, 3, NULL, NULL, NULL, 'Barber', 'Middle', 3, 'Thợ cắt tóc nam chuyên nghiệp', 'Đang làm việc', '2025-12-01 10:36:48', '2025-12-01 10:36:48', NULL),
(3, 4, NULL, NULL, NULL, 'Stylist', 'Junior', 1, 'Nhân viên mới, nhiệt tình và chăm chỉ', 'Đang làm việc', '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(4, 5, NULL, NULL, NULL, 'Shampooer', 'Middle', 2, 'Chuyên viên gội đầu và chăm sóc tóc', 'Đang làm việc', '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `employee_skills`
--

CREATE TABLE `employee_skills` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `service_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `employee_skills`
--

INSERT INTO `employee_skills` (`id`, `employee_id`, `service_id`) VALUES
(1, 4, 11),
(2, 4, 15),
(3, 4, 21),
(4, 3, 10),
(5, 3, 9),
(6, 3, 15),
(7, 3, 16),
(8, 2, 13),
(9, 2, 15),
(10, 2, 14),
(11, 2, 19),
(12, 1, 10),
(13, 1, 20),
(14, 1, 17),
(15, 1, 18);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `evaluates`
--

CREATE TABLE `evaluates` (
  `id` bigint UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint NOT NULL DEFAULT '0',
  `id_user` bigint UNSIGNED NOT NULL,
  `id_appointment` bigint UNSIGNED DEFAULT NULL,
  `id_service` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
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
-- Cấu trúc bảng cho bảng `jobs`
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
-- Cấu trúc bảng cho bảng `job_batches`
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
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_01_15_000001_create_roles_table', 1),
(5, '2025_01_15_000002_update_users_table', 1),
(6, '2025_01_15_000003_create_employees_table', 1),
(7, '2025_01_15_000004_create_skills_table', 1),
(8, '2025_01_15_000005_create_employee_skills_table', 1),
(9, '2025_01_15_000006_create_service_categories_table', 1),
(10, '2025_01_15_000007_create_services_table', 1),
(11, '2025_01_15_000008_create_service_variants_table', 1),
(12, '2025_01_15_000009_create_variant_attributes_table', 1),
(13, '2025_01_15_000010_create_combos_table', 1),
(14, '2025_01_15_000011_create_combo_items_table', 1),
(15, '2025_01_15_000012_create_working_shifts_table', 1),
(16, '2025_01_15_000013_create_working_schedule_table', 1),
(17, '2025_01_15_000014_create_appointments_table', 1),
(18, '2025_01_15_000015_create_appointment_details_table', 1),
(19, '2025_01_15_000016_create_appointment_logs_table', 1),
(20, '2025_01_15_000019_create_reviews_table', 1),
(21, '2025_01_15_000020_create_notifications_table', 1),
(22, '2025_01_15_000021_create_payments_table', 1),
(23, '2025_01_22_000001_change_employee_skills_to_services', 1),
(24, '2025_01_23_000001_change_working_schedule_status_enum', 1),
(25, '2025_11_11_165910_create_categories_table', 1),
(26, '2025_11_11_165931_create_types_table', 1),
(27, '2025_11_11_165954_create_products_table', 1),
(28, '2025_11_11_170040_create_word_time_table', 1),
(29, '2025_11_11_170147_create_orders_table', 1),
(30, '2025_11_11_170208_create_order_details_table', 1),
(31, '2025_11_11_170259_create_settings_table', 1),
(32, '2025_11_11_170325_create_news_table', 1),
(33, '2025_11_11_170359_create_comments_table', 1),
(34, '2025_11_11_170416_create_contacts_table', 1),
(35, '2025_11_11_170434_create_evaluates_table', 1),
(36, '2025_11_17_134709_refactor_services_module', 1),
(37, '2025_11_19_072124_add_deleted_at_to_categories_table', 1),
(38, '2025_11_21_071511_add_service_code_to_services_table', 1),
(39, '2025_11_22_094608_create_password_reset_otps_table', 1),
(40, '2025_11_22_095750_add_remember_token_to_users_table', 1),
(41, '2025_11_23_093134_drop_skills_table', 1),
(42, '2025_11_23_161006_remove_unique_constraint_from_users_email', 1),
(43, '2025_11_23_162049_remove_unique_constraint_from_users_phone', 1),
(44, '2025_11_24_125500_add_service_code_to_services_table', 1),
(45, '2025_11_28_000001_create_promotions_table', 1),
(46, '2025_11_28_000002_create_promotion_service_table', 1),
(47, '2025_11_28_160000_add_order_id_to_payments_table', 1),
(48, '2025_11_28_160500_add_invoice_code_to_payments_table', 1),
(49, '2025_11_28_163000_add_timestamps_to_payments_table', 1),
(50, '2025_11_29_154311_add_images_and_is_hidden_to_reviews_table', 2),
(51, '2025_11_28_000003_create_promotion_usages_table', 3),
(52, '2025_11_28_000003_drop_status_column_from_working_schedule_table', 3),
(53, '2025_12_04_141608_add_missing_columns_to_promotions_table', 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `news`
--

CREATE TABLE `news` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `images` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_user` bigint UNSIGNED NOT NULL,
  `views` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `type` enum('appointment','promotion','feedback','system') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` int DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `id_user` bigint UNSIGNED NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ lấy hàng',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `id` bigint UNSIGNED NOT NULL,
  `id_order` bigint UNSIGNED NOT NULL,
  `id_product` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_otps`
--

CREATE TABLE `password_reset_otps` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `appointment_id` bigint UNSIGNED DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `price` double DEFAULT NULL,
  `VAT` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` enum('cash','online') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `payments`
--

INSERT INTO `payments` (`id`, `invoice_code`, `user_id`, `appointment_id`, `order_id`, `price`, `VAT`, `total`, `created_by`, `payment_type`, `created_at`, `updated_at`) VALUES
(1, 'INV-20251204-CEWOMV', 1, 1, NULL, 11052000, 1105200, 12157200, 'Administrator', 'cash', '2025-12-04 07:26:25', '2025-12-04 07:26:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale` decimal(10,2) NOT NULL DEFAULT '0.00',
  `images` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_category` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `views` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `discount_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `discount_percent` tinyint UNSIGNED NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `apply_scope` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'service',
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `per_user_limit` int UNSIGNED DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `promotions`
--

INSERT INTO `promotions` (`id`, `code`, `name`, `description`, `discount_type`, `discount_percent`, `discount_amount`, `apply_scope`, `min_order_amount`, `max_discount_amount`, `per_user_limit`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'JQK-1', 'Khuyến mãi chào mừng PolyHair', NULL, 'percent', 12, NULL, 'service', NULL, 50000.00, 1, '2025-12-05', '2025-12-12', 'active', '2025-12-04 07:17:34', '2025-12-04 07:17:34', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotion_service`
--

CREATE TABLE `promotion_service` (
  `id` bigint UNSIGNED NOT NULL,
  `promotion_id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED DEFAULT NULL,
  `combo_id` bigint UNSIGNED DEFAULT NULL,
  `service_variant_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `promotion_service`
--

INSERT INTO `promotion_service` (`id`, `promotion_id`, `service_id`, `combo_id`, `service_variant_id`, `created_at`, `updated_at`) VALUES
(1, 1, 21, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(2, 1, 20, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(3, 1, 19, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(4, 1, 18, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(5, 1, 17, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(6, 1, 16, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(7, 1, 15, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(8, 1, 14, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(9, 1, 13, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(10, 1, 12, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(11, 1, 11, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(12, 1, 10, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(13, 1, 9, NULL, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(14, 1, NULL, 1, NULL, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(15, 1, NULL, NULL, 17, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(16, 1, NULL, NULL, 18, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(17, 1, NULL, NULL, 19, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(18, 1, NULL, NULL, 20, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(19, 1, NULL, NULL, 21, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(20, 1, NULL, NULL, 22, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(21, 1, NULL, NULL, 23, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(22, 1, NULL, NULL, 24, '2025-12-04 07:17:34', '2025-12-04 07:17:34'),
(23, 1, NULL, NULL, 25, '2025-12-04 07:17:34', '2025-12-04 07:17:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotion_usages`
--

CREATE TABLE `promotion_usages` (
  `id` bigint UNSIGNED NOT NULL,
  `promotion_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `appointment_id` bigint UNSIGNED DEFAULT NULL,
  `used_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `appointment_id` bigint UNSIGNED DEFAULT NULL,
  `service_id` bigint UNSIGNED DEFAULT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `images` json DEFAULT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Quản trị viên', '2025-12-01 10:36:20', '2025-12-01 10:36:20'),
(2, 'nhân viên', 'Nhân viên', '2025-12-01 10:36:20', '2025-12-01 10:36:20'),
(3, 'khách hàng', 'Khách hàng', '2025-12-01 10:36:20', '2025-12-01 10:36:20');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `services`
--

CREATE TABLE `services` (
  `id` bigint UNSIGNED NOT NULL,
  `service_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_price` decimal(10,2) DEFAULT NULL,
  `base_duration` int UNSIGNED DEFAULT NULL,
  `status` enum('Hoạt động','Vô hiệu hóa') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `services`
--

INSERT INTO `services` (`id`, `service_code`, `category_id`, `name`, `slug`, `description`, `image`, `base_price`, `base_duration`, `status`, `sort_order`, `is_featured`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, 'DV000001', 5, 'Cắt tóc trẻ em', NULL, NULL, '1764688396_cat-toc-tre-em.jpg', 30000.00, NULL, 'Hoạt động', 0, 0, '2025-12-02 08:03:47', '2025-12-02 08:13:16', NULL),
(10, 'DV000010', 5, 'Cắt tóc nam', NULL, NULL, '1764688047_cat-toc.jpg', 80000.00, NULL, 'Hoạt động', 0, 0, '2025-12-02 08:07:27', '2025-12-02 08:07:27', NULL),
(11, 'DV000011', 5, 'Cắt tóc nữ', NULL, NULL, '1764691499_cat-toc-2.jpg', NULL, NULL, 'Hoạt động', 0, 0, '2025-12-02 08:28:38', '2025-12-02 09:04:59', NULL),
(12, 'DV000012', 5, 'Gội thư giãn cổ vai gáy', NULL, NULL, '1764785417_z3770073394122_f33e0adea63fab1e68934c9c7dc32018-1.jpg', 139000.00, NULL, 'Hoạt động', 0, 0, '2025-12-02 08:45:49', '2025-12-03 11:10:17', NULL),
(13, 'DV000013', 8, 'Chăm sóc da Ultrawhite', NULL, NULL, '1764691299_csd1.PNG', 55000.00, NULL, 'Hoạt động', 0, 0, '2025-12-02 08:56:41', '2025-12-02 09:01:39', NULL),
(14, 'DV000014', 8, 'Tẩy da chết sủi bọt Hàn Quốc', NULL, NULL, '1764691398_csd2.PNG', 35000.00, NULL, 'Hoạt động', 0, 0, '2025-12-02 08:58:27', '2025-12-02 09:03:18', NULL),
(15, 'DV000015', 8, 'Đánh bay mụn cám lột mụn full face', NULL, NULL, '1764691642_csd3.PNG', 49000.00, NULL, 'Hoạt động', 0, 0, '2025-12-02 09:07:22', '2025-12-02 09:07:22', NULL),
(16, 'DV000016', 9, 'Uốn tóc nam', NULL, NULL, '1764834132_cac-kieu-uon-toc-nam-26.jpg', NULL, NULL, 'Hoạt động', 0, 0, '2025-12-02 09:33:24', '2025-12-04 00:42:12', NULL),
(17, 'DV000017', 9, 'Uốn tóc nữ cơ bản', NULL, NULL, '1764786034_uon-toc-nu.jpg', NULL, NULL, 'Hoạt động', 0, 0, '2025-12-03 11:20:34', '2025-12-03 11:20:34', NULL),
(18, 'DV000018', 9, 'Uốn tóc nữ Hàn Quốc', NULL, NULL, '1764824424_toc-xoan-mai-thua-hq.jpg', NULL, NULL, 'Hoạt động', 0, 0, '2025-12-03 22:00:24', '2025-12-03 22:00:24', NULL),
(19, 'DV000019', 9, 'Uốn tóc nữ cá tính', NULL, NULL, '1764824538_toc-mullet-layer-xoan.jpg', NULL, NULL, 'Hoạt động', 0, 0, '2025-12-03 22:02:18', '2025-12-03 22:02:18', NULL),
(20, 'DV000020', 10, 'Nhuộm tóc nam', NULL, NULL, '1764834666_bang-mau-nhuom-toc-nam-8.jpg', NULL, NULL, 'Hoạt động', 0, 0, '2025-12-03 22:05:21', '2025-12-04 00:51:06', NULL),
(21, 'DV000021', 5, 'Gội đầu', NULL, NULL, '1764855082_young-woman-having-hair-washed-in-salon-e1576678689352.jpg', 40000.00, NULL, 'Hoạt động', 0, 0, '2025-12-04 06:31:22', '2025-12-04 06:31:22', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `service_categories`
--

CREATE TABLE `service_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `slug`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Cắt tóc', 'cat-toc', 'Dịch vụ cắt tóc chuyên nghiệp', 0, 1, '2025-12-01 10:44:14', '2025-12-02 07:57:40', '2025-12-02 07:57:40'),
(2, 'Nhuộm tóc', NULL, 'Dịch vụ nhuộm tóc đa dạng màu sắc', 0, 1, '2025-12-01 10:44:14', '2025-12-02 07:57:46', '2025-12-02 07:57:46'),
(3, 'Uốn tóc', NULL, 'Dịch vụ uốn tóc tạo kiểu', 0, 1, '2025-12-01 10:44:14', '2025-12-02 07:57:49', '2025-12-02 07:57:49'),
(4, 'Chăm sóc tóc', 'cham-soc-toc', 'Dịch vụ chăm sóc và phục hồi tóc', 0, 1, '2025-12-01 10:44:14', '2025-12-02 07:57:43', '2025-12-02 07:57:43'),
(5, 'Cắt - Gội - Xả thư giãn', 'cat-goi-xa-thu-gian', NULL, 0, 1, '2025-12-02 08:00:42', '2025-12-02 08:00:42', NULL),
(6, 'Gội dưỡng sinh thư giãn - Relax', 'goi-duong-sinh-thu-gian-relax', NULL, 0, 1, '2025-12-02 08:01:32', '2025-12-02 08:01:32', NULL),
(7, 'Dịch vụ khác', 'dich-vu-khac', NULL, 0, 1, '2025-12-02 08:02:16', '2025-12-02 08:02:16', NULL),
(8, 'Chăm sóc da cơ bản', 'cham-soc-da-co-ban', NULL, 0, 1, '2025-12-02 08:02:34', '2025-12-02 08:02:34', NULL),
(9, 'Uốn định hình nếp tóc', 'uon-dinh-hinh-nep-toc', NULL, 0, 1, '2025-12-02 08:02:51', '2025-12-02 08:02:51', NULL),
(10, 'Nhuộm tóc - Dưỡng tóc', 'nhuom-toc-duong-toc', NULL, 0, 1, '2025-12-02 08:03:21', '2025-12-02 08:03:21', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `service_variants`
--

CREATE TABLE `service_variants` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `service_variants`
--

INSERT INTO `service_variants` (`id`, `service_id`, `name`, `sku`, `price`, `duration`, `is_default`, `is_active`, `sort_order`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(17, 11, 'Cắt tóc nữ cơ bản', NULL, 100000.00, 30, 0, 1, 0, NULL, '2025-12-02 08:28:38', '2025-12-02 08:28:38', NULL),
(18, 11, 'Cắt tóc nữ tạo kiểu', NULL, 150000.00, 60, 0, 1, 0, NULL, '2025-12-02 08:28:38', '2025-12-02 08:28:38', NULL),
(19, 11, 'Cắt tóc nữ tạo kiểu', NULL, 120000.00, 45, 0, 1, 0, NULL, '2025-12-02 08:28:38', '2025-12-02 08:28:38', NULL),
(20, 16, 'Uốn cơ bản', NULL, 250000.00, 50, 0, 1, 0, NULL, '2025-12-02 09:33:24', '2025-12-02 09:33:24', NULL),
(21, 16, 'Uốn tóc con sâu', NULL, 300000.00, 60, 0, 1, 0, NULL, '2025-12-02 09:33:24', '2025-12-02 09:33:24', NULL),
(22, 17, 'Uốn tóc nữ xoăn sóng', NULL, 499000.00, 60, 0, 1, 0, NULL, '2025-12-03 11:20:34', '2025-12-03 11:20:34', NULL),
(23, 17, 'Uốn tóc nữ sóng lơi', NULL, 399000.00, 50, 0, 1, 0, NULL, '2025-12-03 11:20:34', '2025-12-03 11:20:34', NULL),
(24, 18, 'Uốn tóc xoăn mái thưa Hàn Quốc', NULL, 599000.00, 90, 0, 1, 0, NULL, '2025-12-03 22:00:24', '2025-12-03 22:00:24', NULL),
(25, 18, 'Uốn tóc xoăn Hippie cá tính', NULL, 449000.00, 90, 0, 1, 0, NULL, '2025-12-03 22:00:24', '2025-12-03 22:00:24', NULL),
(26, 18, 'Tóc xoăn sóng ngang vai', NULL, 499000.00, 60, 0, 1, 0, NULL, '2025-12-03 22:00:24', '2025-12-03 22:00:24', NULL),
(27, 19, 'Tóc Layer Mullet Xoăn', NULL, 549000.00, 90, 0, 1, 0, NULL, '2025-12-03 22:02:18', '2025-12-03 22:02:18', NULL),
(28, 20, 'Nhuộm nâu trà sữa', NULL, 499000.00, 90, 0, 1, 0, NULL, '2025-12-03 22:05:21', '2025-12-03 22:05:21', NULL),
(29, 20, 'Nhuộm xám khói', NULL, 449000.00, 120, 0, 1, 0, NULL, '2025-12-03 22:05:21', '2025-12-03 22:05:21', NULL),
(30, 20, 'Nhuộm bạch kim', NULL, 549000.00, 180, 0, 1, 0, NULL, '2025-12-03 22:05:21', '2025-12-03 22:05:21', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
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
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('uwyk1N0rMl9QbSSqPvthzO7UjYhyG62AsogUonYl', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNnJ0a0xDaHVza0ZHT1JmSTZ5ek1DaDBMTlIySlZ4QXZvczFGMGpQYyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMzOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBwb2ludG1lbnQiO3M6NToicm91dGUiO3M6MjM6InNpdGUuYXBwb2ludG1lbnQuY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1764859136);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ico` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `introduce` text COLLATE utf8mb4_unicode_ci,
  `slogan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `types`
--

CREATE TABLE `types` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `images` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('Nam','Nữ','Khác') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `status` enum('Hoạt động','Vô hiệu hóa','Cấm') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `avatar`, `gender`, `dob`, `status`, `role_id`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Administrator', 'admin@example.com', '0123456789', NULL, NULL, NULL, 'Hoạt động', 1, '$2y$12$iY4pnFkyHx30ZJOdN5P3A.ffdlpXFO4yW9/FadOuAXRLTlRy.blF6', NULL, '2025-12-01 10:36:47', '2025-12-01 10:36:47', NULL),
(2, 'Nguyễn Văn An', 'nguyenvanan@example.com', '0912345678', NULL, NULL, NULL, 'Hoạt động', 2, '$2y$12$MSIoUwARUvf0Sy1.LojzDO9gosHYd2RqD8W0fDzy.DgFvprsWBhv6', NULL, '2025-12-01 10:36:47', '2025-12-01 10:36:47', NULL),
(3, 'Trần Thị Bình', 'tranthibinh@example.com', '0923456789', NULL, NULL, NULL, 'Hoạt động', 2, '$2y$12$y/CBciYnIfm3V3z6.9CArubNPMAgD4iyEiqkuBoI2.45IJGwc7hRC', NULL, '2025-12-01 10:36:48', '2025-12-01 10:36:48', NULL),
(4, 'Lê Văn Cường', 'levancuong@example.com', '0934567890', NULL, NULL, NULL, 'Hoạt động', 2, '$2y$12$uEKhmDL3ZdWBt/Al2hBAyul88TqWwBZZC7JkONBgDXKtHnccKmT2q', NULL, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(5, 'Phạm Thị Dung', 'phamthidung@example.com', '0945678901', NULL, NULL, NULL, 'Hoạt động', 2, '$2y$12$ZZTQQVH2p5wM1OQOZvUnj.ZlBtq0Y8lCVeXB19VuDs03452G3CCZ2', NULL, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `variant_attributes`
--

CREATE TABLE `variant_attributes` (
  `id` bigint UNSIGNED NOT NULL,
  `service_variant_id` bigint UNSIGNED DEFAULT NULL,
  `attribute_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_value` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `variant_attributes`
--

INSERT INTO `variant_attributes` (`id`, `service_variant_id`, `attribute_name`, `attribute_value`, `created_at`, `updated_at`) VALUES
(13, 18, 'Tóc', 'Dài', '2025-12-03 11:03:49', '2025-12-03 11:03:49'),
(14, 18, 'Tóc', 'Trung bình', '2025-12-03 11:03:49', '2025-12-03 11:03:49'),
(15, 19, 'Tóc', 'Ngắn', '2025-12-03 11:03:49', '2025-12-03 11:03:49'),
(16, 22, 'Tóc', 'Dài', '2025-12-03 11:20:34', '2025-12-03 11:20:34'),
(17, 22, 'Tóc', 'Trung bình', '2025-12-03 11:20:34', '2025-12-03 11:20:34'),
(18, 23, 'Tóc', 'Dài', '2025-12-03 11:20:34', '2025-12-03 11:20:34'),
(19, 24, 'Tóc', 'Dài', '2025-12-03 22:00:24', '2025-12-03 22:00:24'),
(20, 25, 'Tóc', 'Dài', '2025-12-03 22:00:24', '2025-12-03 22:00:24'),
(23, 20, 'Tóc', 'Trung bình', '2025-12-04 00:42:12', '2025-12-04 00:42:12'),
(24, 20, 'Tóc', 'Dài', '2025-12-04 00:42:12', '2025-12-04 00:42:12'),
(25, 21, 'Tóc', 'Ngắn', '2025-12-04 00:42:12', '2025-12-04 00:42:12'),
(26, 21, 'Tóc', 'Trung bình', '2025-12-04 00:42:12', '2025-12-04 00:42:12'),
(29, 28, 'Tóc', 'Ngắn', '2025-12-04 00:51:06', '2025-12-04 00:51:06'),
(30, 28, 'Tóc', 'Trung bình', '2025-12-04 00:51:06', '2025-12-04 00:51:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `word_time`
--

CREATE TABLE `word_time` (
  `id` bigint UNSIGNED NOT NULL,
  `time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `word_time`
--

INSERT INTO `word_time` (`id`, `time`, `created_at`, `updated_at`) VALUES
(1, '07:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(2, '07:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(3, '08:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(4, '08:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(5, '09:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(6, '09:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(7, '10:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(8, '10:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(9, '11:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(10, '11:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(11, '12:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(12, '12:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(13, '13:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(14, '13:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(15, '14:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(16, '14:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(17, '15:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(18, '15:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(19, '16:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(20, '16:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(21, '17:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(22, '17:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(23, '18:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(24, '18:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(25, '19:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(26, '19:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(27, '20:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(28, '20:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(29, '21:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(30, '21:30:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02'),
(31, '22:00:00', '2025-12-04 07:23:02', '2025-12-04 07:23:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `working_schedule`
--

CREATE TABLE `working_schedule` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `shift_id` bigint UNSIGNED DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_handover` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `working_schedule`
--

INSERT INTO `working_schedule` (`id`, `employee_id`, `work_date`, `shift_id`, `image`, `is_handover`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '2025-12-01', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(2, 1, '2025-12-01', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(3, 1, '2025-12-02', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(4, 1, '2025-12-02', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(5, 1, '2025-12-03', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(6, 1, '2025-12-03', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(7, 1, '2025-12-04', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(8, 1, '2025-12-04', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(9, 1, '2025-12-05', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(10, 1, '2025-12-05', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(11, 1, '2025-12-08', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(12, 1, '2025-12-08', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(13, 1, '2025-12-09', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(14, 1, '2025-12-09', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(15, 1, '2025-12-10', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(16, 1, '2025-12-10', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(17, 1, '2025-12-11', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(18, 1, '2025-12-11', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(19, 1, '2025-12-12', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(20, 1, '2025-12-12', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(21, 1, '2025-12-15', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(22, 1, '2025-12-15', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(23, 1, '2025-12-16', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(24, 1, '2025-12-16', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(25, 1, '2025-12-17', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(26, 1, '2025-12-17', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(27, 1, '2025-12-18', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(28, 1, '2025-12-18', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(29, 1, '2025-12-19', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(30, 1, '2025-12-19', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(31, 1, '2025-12-22', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(32, 1, '2025-12-22', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(33, 1, '2025-12-23', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(34, 1, '2025-12-23', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(35, 1, '2025-12-24', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(36, 1, '2025-12-24', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(37, 1, '2025-12-25', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(38, 1, '2025-12-25', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(39, 1, '2025-12-26', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(40, 1, '2025-12-26', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(41, 1, '2025-12-29', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(42, 1, '2025-12-29', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(43, 1, '2025-12-30', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(44, 1, '2025-12-30', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(45, 1, '2025-12-31', 1, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(46, 1, '2025-12-31', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(47, 2, '2025-12-01', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(48, 2, '2025-12-01', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(49, 2, '2025-12-02', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(50, 2, '2025-12-02', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(51, 2, '2025-12-03', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(52, 2, '2025-12-03', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(53, 2, '2025-12-04', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(54, 2, '2025-12-04', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(55, 2, '2025-12-05', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(56, 2, '2025-12-05', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(57, 2, '2025-12-08', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(58, 2, '2025-12-08', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(59, 2, '2025-12-09', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(60, 2, '2025-12-09', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(61, 2, '2025-12-10', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(62, 2, '2025-12-10', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(63, 2, '2025-12-11', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(64, 2, '2025-12-11', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(65, 2, '2025-12-12', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(66, 2, '2025-12-12', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(67, 2, '2025-12-15', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(68, 2, '2025-12-15', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(69, 2, '2025-12-16', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(70, 2, '2025-12-16', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(71, 2, '2025-12-17', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(72, 2, '2025-12-17', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(73, 2, '2025-12-18', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(74, 2, '2025-12-18', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(75, 2, '2025-12-19', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(76, 2, '2025-12-19', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(77, 2, '2025-12-22', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(78, 2, '2025-12-22', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(79, 2, '2025-12-23', 2, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(80, 2, '2025-12-23', 3, NULL, 0, '2025-12-01 10:36:49', '2025-12-01 10:36:49', NULL),
(81, 2, '2025-12-24', 2, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(82, 2, '2025-12-24', 3, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(83, 2, '2025-12-25', 2, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(84, 2, '2025-12-25', 3, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(85, 2, '2025-12-26', 2, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(86, 2, '2025-12-26', 3, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(87, 2, '2025-12-29', 2, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(88, 2, '2025-12-29', 3, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(89, 2, '2025-12-30', 2, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(90, 2, '2025-12-30', 3, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(91, 2, '2025-12-31', 2, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(92, 2, '2025-12-31', 3, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(93, 3, '2025-12-01', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(94, 3, '2025-12-03', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(95, 3, '2025-12-05', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(96, 3, '2025-12-08', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(97, 3, '2025-12-10', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(98, 3, '2025-12-12', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(99, 3, '2025-12-15', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(100, 3, '2025-12-17', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(101, 3, '2025-12-19', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(102, 3, '2025-12-22', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(103, 3, '2025-12-24', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(104, 3, '2025-12-26', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(105, 3, '2025-12-29', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(106, 3, '2025-12-31', 4, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(107, 4, '2025-12-02', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(108, 4, '2025-12-04', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(109, 4, '2025-12-06', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(110, 4, '2025-12-09', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(111, 4, '2025-12-11', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(112, 4, '2025-12-13', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(113, 4, '2025-12-16', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(114, 4, '2025-12-18', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(115, 4, '2025-12-20', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(116, 4, '2025-12-23', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(117, 4, '2025-12-25', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(118, 4, '2025-12-27', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL),
(119, 4, '2025-12-30', 1, NULL, 0, '2025-12-01 10:36:50', '2025-12-01 10:36:50', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `working_shifts`
--

CREATE TABLE `working_shifts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `working_shifts`
--

INSERT INTO `working_shifts` (`id`, `name`, `start_time`, `end_time`, `duration`, `created_at`, `updated_at`) VALUES
(1, 'Ca sáng', '07:00:00', '12:00:00', 300, '2025-12-01 10:36:47', '2025-12-01 10:36:47'),
(2, 'Ca chiều', '12:00:00', '17:00:00', 300, '2025-12-01 10:36:47', '2025-12-01 10:36:47'),
(3, 'Ca tối', '17:00:00', '22:00:00', 300, '2025-12-01 10:36:47', '2025-12-01 10:36:47'),
(4, 'Ca cả ngày', '07:00:00', '22:00:00', 900, '2025-12-01 10:36:47', '2025-12-01 10:36:47');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointments_user_id_foreign` (`user_id`),
  ADD KEY `appointments_employee_id_foreign` (`employee_id`);

--
-- Chỉ mục cho bảng `appointment_details`
--
ALTER TABLE `appointment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_details_appointment_id_foreign` (`appointment_id`),
  ADD KEY `appointment_details_service_variant_id_foreign` (`service_variant_id`),
  ADD KEY `appointment_details_employee_id_foreign` (`employee_id`),
  ADD KEY `appointment_details_combo_id_foreign` (`combo_id`),
  ADD KEY `appointment_details_combo_item_id_foreign` (`combo_item_id`);

--
-- Chỉ mục cho bảng `appointment_logs`
--
ALTER TABLE `appointment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_logs_appointment_id_foreign` (`appointment_id`),
  ADD KEY `appointment_logs_modified_by_foreign` (`modified_by`);

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `combos`
--
ALTER TABLE `combos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `combos_slug_unique` (`slug`),
  ADD KEY `combos_category_id_foreign` (`category_id`),
  ADD KEY `combos_owner_service_id_foreign` (`owner_service_id`);

--
-- Chỉ mục cho bảng `combo_items`
--
ALTER TABLE `combo_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `combo_items_combo_id_foreign` (`combo_id`),
  ADD KEY `combo_items_service_id_foreign` (`service_id`),
  ADD KEY `combo_items_service_variant_id_foreign` (`service_variant_id`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_id_product_foreign` (`id_product`),
  ADD KEY `comments_id_user_foreign` (`id_user`);

--
-- Chỉ mục cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employees_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `employee_skills`
--
ALTER TABLE `employee_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_skills_employee_id_foreign` (`employee_id`),
  ADD KEY `employee_skills_service_id_foreign` (`service_id`);

--
-- Chỉ mục cho bảng `evaluates`
--
ALTER TABLE `evaluates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluates_id_user_foreign` (`id_user`),
  ADD KEY `evaluates_id_appointment_foreign` (`id_appointment`),
  ADD KEY `evaluates_id_service_foreign` (`id_service`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id_user_foreign` (`id_user`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_id_user_foreign` (`id_user`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_details_id_order_foreign` (`id_order`),
  ADD KEY `order_details_id_product_foreign` (`id_product`);

--
-- Chỉ mục cho bảng `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_reset_otps_email_otp_index` (`email`,`otp`),
  ADD KEY `password_reset_otps_phone_otp_index` (`phone`,`otp`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_invoice_code_unique` (`invoice_code`),
  ADD KEY `payments_user_id_foreign` (`user_id`),
  ADD KEY `payments_appointment_id_foreign` (`appointment_id`),
  ADD KEY `payments_order_id_foreign` (`order_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_id_category_foreign` (`id_category`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promotions_code_unique` (`code`);

--
-- Chỉ mục cho bảng `promotion_service`
--
ALTER TABLE `promotion_service`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promotion_service_promotion_id_service_id_unique` (`promotion_id`,`service_id`),
  ADD UNIQUE KEY `promotion_service_promotion_id_combo_id_unique` (`promotion_id`,`combo_id`),
  ADD UNIQUE KEY `promotion_service_promotion_id_service_variant_id_unique` (`promotion_id`,`service_variant_id`),
  ADD KEY `promotion_service_service_id_foreign` (`service_id`),
  ADD KEY `promotion_service_combo_id_foreign` (`combo_id`),
  ADD KEY `promotion_service_service_variant_id_foreign` (`service_variant_id`);

--
-- Chỉ mục cho bảng `promotion_usages`
--
ALTER TABLE `promotion_usages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promotion_usages_promotion_id_foreign` (`promotion_id`),
  ADD KEY `promotion_usages_user_id_foreign` (`user_id`),
  ADD KEY `promotion_usages_appointment_id_foreign` (`appointment_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_appointment_id_foreign` (`appointment_id`),
  ADD KEY `reviews_service_id_foreign` (`service_id`),
  ADD KEY `reviews_employee_id_foreign` (`employee_id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_slug_unique` (`slug`),
  ADD UNIQUE KEY `services_service_code_unique` (`service_code`),
  ADD KEY `services_category_id_foreign` (`category_id`);

--
-- Chỉ mục cho bảng `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_categories_slug_unique` (`slug`);

--
-- Chỉ mục cho bảng `service_variants`
--
ALTER TABLE `service_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_variants_service_id_foreign` (`service_id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- Chỉ mục cho bảng `variant_attributes`
--
ALTER TABLE `variant_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variant_attributes_service_variant_id_foreign` (`service_variant_id`);

--
-- Chỉ mục cho bảng `word_time`
--
ALTER TABLE `word_time`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `working_schedule`
--
ALTER TABLE `working_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `working_schedule_employee_id_foreign` (`employee_id`),
  ADD KEY `working_schedule_shift_id_foreign` (`shift_id`);

--
-- Chỉ mục cho bảng `working_shifts`
--
ALTER TABLE `working_shifts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `appointment_details`
--
ALTER TABLE `appointment_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `appointment_logs`
--
ALTER TABLE `appointment_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `combos`
--
ALTER TABLE `combos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `combo_items`
--
ALTER TABLE `combo_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `employee_skills`
--
ALTER TABLE `employee_skills`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `evaluates`
--
ALTER TABLE `evaluates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT cho bảng `news`
--
ALTER TABLE `news`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `promotion_service`
--
ALTER TABLE `promotion_service`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `promotion_usages`
--
ALTER TABLE `promotion_usages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `service_variants`
--
ALTER TABLE `service_variants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `types`
--
ALTER TABLE `types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `variant_attributes`
--
ALTER TABLE `variant_attributes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `word_time`
--
ALTER TABLE `word_time`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `working_schedule`
--
ALTER TABLE `working_schedule`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT cho bảng `working_shifts`
--
ALTER TABLE `working_shifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `appointment_details`
--
ALTER TABLE `appointment_details`
  ADD CONSTRAINT `appointment_details_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_details_combo_id_foreign` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointment_details_combo_item_id_foreign` FOREIGN KEY (`combo_item_id`) REFERENCES `combo_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointment_details_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointment_details_service_variant_id_foreign` FOREIGN KEY (`service_variant_id`) REFERENCES `service_variants` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `appointment_logs`
--
ALTER TABLE `appointment_logs`
  ADD CONSTRAINT `appointment_logs_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_logs_modified_by_foreign` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `combos`
--
ALTER TABLE `combos`
  ADD CONSTRAINT `combos_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `combos_owner_service_id_foreign` FOREIGN KEY (`owner_service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `combo_items`
--
ALTER TABLE `combo_items`
  ADD CONSTRAINT `combo_items_combo_id_foreign` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `combo_items_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `combo_items_service_variant_id_foreign` FOREIGN KEY (`service_variant_id`) REFERENCES `service_variants` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_id_product_foreign` FOREIGN KEY (`id_product`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `employee_skills`
--
ALTER TABLE `employee_skills`
  ADD CONSTRAINT `employee_skills_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_skills_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `evaluates`
--
ALTER TABLE `evaluates`
  ADD CONSTRAINT `evaluates_id_appointment_foreign` FOREIGN KEY (`id_appointment`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluates_id_service_foreign` FOREIGN KEY (`id_service`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluates_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_id_order_foreign` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_id_product_foreign` FOREIGN KEY (`id_product`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_id_category_foreign` FOREIGN KEY (`id_category`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `promotion_service`
--
ALTER TABLE `promotion_service`
  ADD CONSTRAINT `promotion_service_combo_id_foreign` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotion_service_promotion_id_foreign` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotion_service_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotion_service_service_variant_id_foreign` FOREIGN KEY (`service_variant_id`) REFERENCES `service_variants` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `promotion_usages`
--
ALTER TABLE `promotion_usages`
  ADD CONSTRAINT `promotion_usages_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotion_usages_promotion_id_foreign` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotion_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `service_variants`
--
ALTER TABLE `service_variants`
  ADD CONSTRAINT `service_variants_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `variant_attributes`
--
ALTER TABLE `variant_attributes`
  ADD CONSTRAINT `variant_attributes_service_variant_id_foreign` FOREIGN KEY (`service_variant_id`) REFERENCES `service_variants` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `working_schedule`
--
ALTER TABLE `working_schedule`
  ADD CONSTRAINT `working_schedule_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `working_schedule_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `working_shifts` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
