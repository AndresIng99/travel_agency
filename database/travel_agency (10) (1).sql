-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-06-2025 a las 23:23:59
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `travel_agency`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `biblioteca_actividades`
--

CREATE TABLE `biblioteca_actividades` (
  `id` int(11) NOT NULL,
  `idioma` varchar(5) NOT NULL DEFAULT 'es',
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `imagen1` varchar(255) DEFAULT NULL,
  `imagen2` varchar(255) DEFAULT NULL,
  `imagen3` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `biblioteca_actividades`
--

INSERT INTO `biblioteca_actividades` (`id`, `idioma`, `nombre`, `descripcion`, `ubicacion`, `latitud`, `longitud`, `imagen1`, `imagen2`, `imagen3`, `activo`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'es', 'Tour Eiffel', 'Visita guiada a la Torre Eiffel con subida incluida', 'París, Francia', 48.85840000, 2.29450000, NULL, NULL, NULL, 0, 1, '2025-06-24 16:12:22', '2025-06-24 21:38:12'),
(2, 'es', 'Coliseo Romano', 'Entrada y visita guiada al Coliseo de Roma', 'Roma, Italia', 41.89020000, 12.49220000, NULL, NULL, NULL, 0, 1, '2025-06-24 16:12:22', '2025-06-24 21:38:14'),
(3, 'es', 'Visita Guiada al Museo del Louvre', 'Visita de 3 horas al museo más visitado del mundo. Recorrido por las obras maestras: Mona Lisa, Venus de Milo, Victoria de Samotracia. Guía experto en arte e historia, grupos reducidos de máximo 15 personas.', 'Rue de Rivoli, Quartier Saint-Merri, 4th Arrondissement, París, Francia metropolitana, 75004, Francia', 48.85721600, 2.35264400, 'http://localhost/travel_agency/assets/uploads/biblioteca/actividades/2025/06/actividades_3_imagen1_1750801971.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/actividades/2025/06/actividades_3_imagen2_1750801971.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/actividades/2025/06/actividades_3_imagen3_1750801971.jpg', 1, 1, '2025-06-24 21:52:51', '2025-06-24 21:52:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `biblioteca_alojamientos`
--

CREATE TABLE `biblioteca_alojamientos` (
  `id` int(11) NOT NULL,
  `idioma` varchar(5) NOT NULL DEFAULT 'es',
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `tipo` enum('hotel','camping','casa_huespedes','crucero','lodge','atipico','campamento','camping_car','tren') NOT NULL,
  `categoria` int(11) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `biblioteca_alojamientos`
--

INSERT INTO `biblioteca_alojamientos` (`id`, `idioma`, `nombre`, `descripcion`, `ubicacion`, `latitud`, `longitud`, `tipo`, `categoria`, `imagen`, `sitio_web`, `activo`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'es', 'Hotel París Centro', 'Hotel 4 estrellas en el centro de París', 'París, Francia', 48.85660000, 2.35220000, 'hotel', 4, NULL, NULL, 0, 1, '2025-06-24 16:12:22', '2025-06-24 21:38:03'),
(2, 'es', 'Camping Costa Brava', 'Camping familiar cerca de la playa', 'Costa Brava, España', 41.97940000, 3.04410000, 'camping', 3, NULL, NULL, 0, 1, '2025-06-24 16:12:22', '2025-06-24 21:38:08'),
(3, 'es', 'Hotel Ritz París Plaza Vendôme', 'Hotel icónico de 5 estrellas en el corazón de París, frente a la Place Vendôme. Habitaciones elegantes con vista a los jardines de las Tullerías, spa de lujo, restaurante estrella Michelin.', 'Le Ritz, Place Vendôme, Quartier Vendôme, 1st Arrondissement, París, Francia metropolitana, 75001, Francia', 48.86796900, 2.32884500, 'hotel', 5, 'http://localhost/travel_agency/assets/uploads/biblioteca/alojamientos/2025/06/alojamientos_3_imagen_1750801789.jpg', 'https://www.ritzparis.com', 1, 1, '2025-06-24 21:49:49', '2025-06-24 21:49:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `biblioteca_dias`
--

CREATE TABLE `biblioteca_dias` (
  `id` int(11) NOT NULL,
  `idioma` varchar(5) NOT NULL DEFAULT 'es',
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `imagen1` varchar(255) DEFAULT NULL,
  `imagen2` varchar(255) DEFAULT NULL,
  `imagen3` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `biblioteca_dias`
--

INSERT INTO `biblioteca_dias` (`id`, `idioma`, `titulo`, `descripcion`, `ubicacion`, `latitud`, `longitud`, `imagen1`, `imagen2`, `imagen3`, `activo`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'es', 'Día en París', 'Recorrido completo por los principales monumentos de París', 'París, Francia', 48.85660000, 2.35220000, 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_1_dia-en-paris_imagen1_20250624161620.png', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_1_dia-en-paris_imagen2_20250624161620.png', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_1_dia-en-paris_imagen3_20250624161620.png', 0, 1, '2025-06-24 16:12:22', '2025-06-24 21:37:55'),
(2, 'es', 'Día en Roma', 'Visita al Coliseo, Foro Romano y Vaticano', 'Roma, Italia', 41.90280000, 12.49640000, NULL, NULL, NULL, 0, 1, '2025-06-24 16:12:22', '2025-06-24 21:37:57'),
(3, 'es', 'Día en París', 'paris', 'París, Francia', 48.85660000, 2.35220000, 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_3_imagen1_1750801038.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_3_imagen2_1750801038.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_3_imagen3_1750801038.jpg', 0, 1, '2025-06-24 21:05:38', '2025-06-24 21:37:51'),
(4, 'es', 'Día Completo en París - Monumentos Históricos', 'Recorrido por los principales monumentos de París: Torre Eiffel, Arco del Triunfo, Champs-Élysées y crucero por el Sena. Incluye almuerzo en restaurante típico francés y tiempo libre para compras.', 'Paris, Jeu-les-Bois, Châteauroux, Indre, Centro, Francia metropolitana, 36120, Francia', 46.69394800, 1.81111900, 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen1_1750801432.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen2_1750801432.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen3_1750801432.jpg', 1, 1, '2025-06-24 21:43:09', '2025-06-24 21:43:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `biblioteca_transportes`
--

CREATE TABLE `biblioteca_transportes` (
  `id` int(11) NOT NULL,
  `idioma` varchar(5) NOT NULL DEFAULT 'es',
  `medio` enum('bus','avion','coche','barco','tren') NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `lugar_salida` varchar(255) DEFAULT NULL,
  `lugar_llegada` varchar(255) DEFAULT NULL,
  `lat_salida` decimal(10,8) DEFAULT NULL,
  `lng_salida` decimal(11,8) DEFAULT NULL,
  `lat_llegada` decimal(10,8) DEFAULT NULL,
  `lng_llegada` decimal(11,8) DEFAULT NULL,
  `duracion` varchar(50) DEFAULT NULL,
  `distancia_km` decimal(8,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `biblioteca_transportes`
--

INSERT INTO `biblioteca_transportes` (`id`, `idioma`, `medio`, `titulo`, `descripcion`, `lugar_salida`, `lugar_llegada`, `lat_salida`, `lng_salida`, `lat_llegada`, `lng_llegada`, `duracion`, `distancia_km`, `activo`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'es', 'avion', 'Vuelo París-Roma', 'Vuelo directo París Charles de Gaulle a Roma Fiumicino', 'París CDG, Francia', 'Roma FCO, Italia', 49.00970000, 2.54790000, 41.80030000, 12.23890000, '2 horas 15 minutos', 1105.00, 0, 1, '2025-06-24 16:12:22', '2025-06-24 21:38:19'),
(2, 'es', 'avion', 'Vuelo París - Roma', 'Este es el transporte', 'Charles de Gaulle International Airport, Tremblay-en-France, Le Raincy, Sena-Saint Denis, Isla de Francia, Francia metropolitana, 93290, Francia', 'Rome–Fiumicino Airport, Via Leonardo da Vinci, Fiumicino, Roma Capitale, Lacio, 00054, Italia', 49.00689100, 2.57108200, 41.81539100, 12.22648500, '4 horas', 2500.00, 1, 1, '2025-06-25 15:04:54', '2025-06-25 15:04:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company_settings`
--

CREATE TABLE `company_settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL DEFAULT 'Travel Agency',
  `logo_url` varchar(255) DEFAULT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `admin_primary_color` varchar(7) DEFAULT '#e53e3e',
  `admin_secondary_color` varchar(7) DEFAULT '#fd746c',
  `agent_primary_color` varchar(7) DEFAULT '#667eea',
  `agent_secondary_color` varchar(7) DEFAULT '#764ba2',
  `login_bg_color` varchar(7) DEFAULT '#667eea',
  `login_secondary_color` varchar(7) DEFAULT '#764ba2',
  `default_language` varchar(5) DEFAULT 'es',
  `session_timeout` int(11) DEFAULT 60,
  `max_file_size` int(11) DEFAULT 10,
  `backup_frequency` enum('daily','weekly','monthly','never') DEFAULT 'weekly',
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_name`, `logo_url`, `background_image`, `admin_primary_color`, `admin_secondary_color`, `agent_primary_color`, `agent_secondary_color`, `login_bg_color`, `login_secondary_color`, `default_language`, `session_timeout`, `max_file_size`, `backup_frequency`, `maintenance_mode`, `created_at`, `updated_at`) VALUES
(1, 'Travel Agency', 'http://localhost/travel_agency/assets/uploads/config/logo_685b02181fde7.png', NULL, '#454545', '#454545', '#667eea', '#764ba2', '#908f8e', '#525151', 'es', 60, 10, 'weekly', 0, '2025-06-24 19:30:40', '2025-06-25 15:01:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config_uploads`
--

CREATE TABLE `config_uploads` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `upload_type` enum('logo','background','general') NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `config_uploads`
--

INSERT INTO `config_uploads` (`id`, `filename`, `original_name`, `file_path`, `file_size`, `mime_type`, `upload_type`, `uploaded_by`, `created_at`) VALUES
(1, 'logo_685afea058e4c_1750793888.png', 'Captura de pantalla 2025-06-20 133948.png', '/assets/uploads/config/logo_685afea058e4c_1750793888.png', 467712, 'image/png', 'logo', 1, '2025-06-24 19:38:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programa_dias`
--

CREATE TABLE `programa_dias` (
  `id` int(11) NOT NULL,
  `solicitud_id` int(11) NOT NULL,
  `dia_numero` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `fecha_dia` date DEFAULT NULL,
  `imagen1` varchar(500) DEFAULT NULL,
  `imagen2` varchar(500) DEFAULT NULL,
  `imagen3` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programa_dias`
--

INSERT INTO `programa_dias` (`id`, `solicitud_id`, `dia_numero`, `titulo`, `descripcion`, `ubicacion`, `fecha_dia`, `imagen1`, `imagen2`, `imagen3`, `created_at`, `updated_at`) VALUES
(2, 24, 1, 'Día Completo en París - Monumentos Históricos', 'Recorrido por los principales monumentos de París: Torre Eiffel, Arco del Triunfo, Champs-Élysées y crucero por el Sena. Incluye almuerzo en restaurante típico francés y tiempo libre para compras.', 'Paris, Jeu-les-Bois, Châteauroux, Indre, Centro, Francia metropolitana, 36120, Francia', NULL, 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen1_1750801432.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen2_1750801432.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen3_1750801432.jpg', '2025-06-26 23:23:31', '2025-06-26 23:23:31'),
(5, 25, 1, 'Día Completo en París - Monumentos Históricos', 'Recorrido por los principales monumentos de París: Torre Eiffel, Arco del Triunfo, Champs-Élysées y crucero por el Sena. Incluye almuerzo en restaurante típico francés y tiempo libre para compras.', 'Paris, Jeu-les-Bois, Châteauroux, Indre, Centro, Francia metropolitana, 36120, Francia', NULL, 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen1_1750801432.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen2_1750801432.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen3_1750801432.jpg', '2025-06-27 00:31:50', '2025-06-27 00:31:50'),
(6, 24, 2, 'Día Completo en París - Monumentos Históricos', 'Recorrido por los principales monumentos de París: Torre Eiffel, Arco del Triunfo, Champs-Élysées y crucero por el Sena. Incluye almuerzo en restaurante típico francés y tiempo libre para compras.', 'Paris, Jeu-les-Bois, Châteauroux, Indre, Centro, Francia metropolitana, 36120, Francia', NULL, 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen1_1750801432.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen2_1750801432.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen3_1750801432.jpg', '2025-06-27 00:56:50', '2025-06-27 00:56:50'),
(7, 24, 3, 'Día Completo en París - Monumentos Históricos', 'Recorrido por los principales monumentos de París: Torre Eiffel, Arco del Triunfo, Champs-Élysées y crucero por el Sena. Incluye almuerzo en restaurante típico francés y tiempo libre para compras.', 'Paris, Jeu-les-Bois, Châteauroux, Indre, Centro, Francia metropolitana, 36120, Francia', NULL, 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen1_1750801432.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen2_1750801432.jpg', 'http://localhost/travel_agency/assets/uploads/biblioteca/dias/2025/06/dias_4_imagen3_1750801432.jpg', '2025-06-27 00:58:22', '2025-06-27 00:58:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programa_dias_servicios`
--

CREATE TABLE `programa_dias_servicios` (
  `id` int(11) NOT NULL,
  `programa_dia_id` int(11) NOT NULL,
  `tipo_servicio` enum('actividad','transporte','alojamiento') NOT NULL,
  `biblioteca_item_id` int(11) NOT NULL,
  `orden` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `servicio_principal_id` int(11) DEFAULT NULL COMMENT 'ID del servicio principal (NULL si es principal)',
  `es_alternativa` tinyint(1) DEFAULT 0 COMMENT '0=Principal, 1=Alternativa',
  `orden_alternativa` int(11) DEFAULT 0 COMMENT 'Orden dentro de las alternativas (0 para principal)',
  `notas_alternativa` text DEFAULT NULL COMMENT 'Notas específicas de esta alternativa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programa_dias_servicios`
--

INSERT INTO `programa_dias_servicios` (`id`, `programa_dia_id`, `tipo_servicio`, `biblioteca_item_id`, `orden`, `created_at`, `servicio_principal_id`, `es_alternativa`, `orden_alternativa`, `notas_alternativa`) VALUES
(1, 2, 'actividad', 3, 1, '2025-06-26 23:43:22', NULL, 0, 0, NULL),
(4, 2, 'actividad', 3, 4, '2025-06-27 00:00:04', NULL, 0, 0, NULL),
(5, 2, 'actividad', 3, 5, '2025-06-27 00:00:08', NULL, 0, 0, NULL),
(8, 2, 'actividad', 3, 8, '2025-06-27 00:02:21', NULL, 0, 0, NULL),
(9, 5, 'transporte', 2, 1, '2025-06-27 00:31:54', NULL, 0, 0, NULL),
(10, 5, 'actividad', 3, 2, '2025-06-27 00:49:02', NULL, 0, 0, NULL),
(13, 2, 'transporte', 2, 9, '2025-06-27 01:09:12', NULL, 0, 0, NULL),
(14, 2, 'transporte', 2, 9, '2025-06-27 01:09:24', 13, 1, 1, NULL),
(15, 2, 'transporte', 2, 9, '2025-06-27 01:09:47', 13, 1, 2, NULL),
(16, 2, 'alojamiento', 3, 10, '2025-06-27 01:13:04', NULL, 0, 0, NULL),
(17, 2, 'actividad', 3, 11, '2025-06-27 01:17:07', NULL, 0, 0, NULL),
(18, 2, 'transporte', 2, 12, '2025-06-27 01:17:47', NULL, 0, 0, NULL),
(19, 2, 'alojamiento', 3, 13, '2025-06-27 01:18:23', NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programa_personalizacion`
--

CREATE TABLE `programa_personalizacion` (
  `id` int(11) NOT NULL,
  `solicitud_id` int(11) NOT NULL,
  `titulo_programa` varchar(200) DEFAULT NULL,
  `idioma_predeterminado` varchar(5) DEFAULT 'es',
  `foto_portada` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programa_personalizacion`
--

INSERT INTO `programa_personalizacion` (`id`, `solicitud_id`, `titulo_programa`, `idioma_predeterminado`, `foto_portada`, `created_at`, `updated_at`) VALUES
(24, 24, 'Conociendo París si si si', 'es', 'http://localhost/travel_agency/assets/uploads/programa/2025/06/programa_24_cover_1750980909.jpg', '2025-06-26 20:58:26', '2025-06-26 23:35:09'),
(25, 25, 'Conocer España', 'es', 'http://localhost/travel_agency/assets/uploads/programa/2025/06/programa_25_cover_1750984293.jpg', '2025-06-27 00:31:33', '2025-06-27 00:31:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programa_precios`
--

CREATE TABLE `programa_precios` (
  `id` int(11) NOT NULL,
  `solicitud_id` int(11) NOT NULL,
  `moneda` varchar(3) DEFAULT 'USD',
  `precio_por_persona` decimal(10,2) DEFAULT NULL,
  `precio_total` decimal(10,2) DEFAULT NULL,
  `noches_incluidas` int(11) DEFAULT 0,
  `precio_incluye` text DEFAULT NULL,
  `precio_no_incluye` text DEFAULT NULL,
  `condiciones_generales` text DEFAULT NULL,
  `movilidad_reducida` tinyint(1) DEFAULT 0,
  `info_pasaporte` text DEFAULT NULL,
  `info_seguros` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programa_precios`
--

INSERT INTO `programa_precios` (`id`, `solicitud_id`, `moneda`, `precio_por_persona`, `precio_total`, `noches_incluidas`, `precio_incluye`, `precio_no_incluye`, `condiciones_generales`, `movilidad_reducida`, `info_pasaporte`, `info_seguros`, `created_at`, `updated_at`) VALUES
(1, 24, 'COP', 2500000.00, 2500000.00, 3, 'NADA ¿Qué incluye el precio?', 'NADA ¿Qué NO incluye?', 'NADA Condiciones generales', 0, 'NADA Información de pasaporte', 'NADA Información de SEGUROS', '2025-06-27 01:19:51', '2025-06-27 01:19:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programa_solicitudes`
--

CREATE TABLE `programa_solicitudes` (
  `id` int(11) NOT NULL,
  `id_solicitud` varchar(50) DEFAULT NULL,
  `nombre_viajero` varchar(100) NOT NULL,
  `apellido_viajero` varchar(100) NOT NULL,
  `destino` varchar(200) NOT NULL,
  `fecha_llegada` date NOT NULL,
  `fecha_salida` date NOT NULL,
  `numero_pasajeros` int(11) NOT NULL DEFAULT 1,
  `acompanamiento` varchar(50) DEFAULT 'sin-acompanamiento',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programa_solicitudes`
--

INSERT INTO `programa_solicitudes` (`id`, `id_solicitud`, `nombre_viajero`, `apellido_viajero`, `destino`, `fecha_llegada`, `fecha_salida`, `numero_pasajeros`, `acompanamiento`, `user_id`, `created_at`, `updated_at`) VALUES
(24, 'SOL2025001', 'Andres Fernando', 'Pineda Guerra', 'París', '2025-06-28', '2025-07-10', 1, 'guide', 1, '2025-06-26 20:58:26', '2025-06-26 23:21:47'),
(25, 'SOL2025002', 'Manuel Medrano', 'Dannand', 'España', '2025-06-27', '2025-07-01', 1, 'guide', 1, '2025-06-27 00:31:33', '2025-06-27 15:07:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','agent') NOT NULL DEFAULT 'agent',
  `active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@travelagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin', 1, '2025-06-27 16:05:50', '2025-06-24 15:58:30', '2025-06-27 21:05:50'),
(2, 'agente1', 'agente@travelagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Agente de Viajes', 'agent', 1, '2025-06-25 15:46:20', '2025-06-24 15:58:30', '2025-06-25 20:46:20'),
(3, 'andres.pineda.guerra', 'andrespineda@travelagency.com', '$2y$10$2jrKrjfD1hR/AWn2sDvgJOofwwqgirEAr8zWyfFPaCqfqWcA570GG', 'Andres Pineda Guerra', 'admin', 0, NULL, '2025-06-24 17:38:53', '2025-06-24 18:41:29'),
(4, 'jheshua.dannand', 'jheshua.dannand@travelagency.com', '$2y$10$nObJPm540OLjjweRmm0.quNndbn0pBfcuKjNHXwEmpOFEQYKiKnNC', 'jheshua dannand', 'agent', 1, NULL, '2025-06-24 18:35:17', '2025-06-24 18:35:17');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `biblioteca_actividades`
--
ALTER TABLE `biblioteca_actividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `biblioteca_alojamientos`
--
ALTER TABLE `biblioteca_alojamientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `biblioteca_dias`
--
ALTER TABLE `biblioteca_dias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `biblioteca_transportes`
--
ALTER TABLE `biblioteca_transportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `config_uploads`
--
ALTER TABLE `config_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indices de la tabla `programa_dias`
--
ALTER TABLE `programa_dias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_solicitud_dia` (`solicitud_id`,`dia_numero`);

--
-- Indices de la tabla `programa_dias_servicios`
--
ALTER TABLE `programa_dias_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_servicio_principal` (`servicio_principal_id`),
  ADD KEY `idx_es_alternativa` (`es_alternativa`),
  ADD KEY `idx_orden_alternativa` (`orden_alternativa`);

--
-- Indices de la tabla `programa_personalizacion`
--
ALTER TABLE `programa_personalizacion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_solicitud` (`solicitud_id`);

--
-- Indices de la tabla `programa_precios`
--
ALTER TABLE `programa_precios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_solicitud_precio` (`solicitud_id`);

--
-- Indices de la tabla `programa_solicitudes`
--
ALTER TABLE `programa_solicitudes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_id_solicitud` (`id_solicitud`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `biblioteca_actividades`
--
ALTER TABLE `biblioteca_actividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `biblioteca_alojamientos`
--
ALTER TABLE `biblioteca_alojamientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `biblioteca_dias`
--
ALTER TABLE `biblioteca_dias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `biblioteca_transportes`
--
ALTER TABLE `biblioteca_transportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `config_uploads`
--
ALTER TABLE `config_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `programa_dias`
--
ALTER TABLE `programa_dias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `programa_dias_servicios`
--
ALTER TABLE `programa_dias_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `programa_personalizacion`
--
ALTER TABLE `programa_personalizacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `programa_precios`
--
ALTER TABLE `programa_precios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `programa_solicitudes`
--
ALTER TABLE `programa_solicitudes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `biblioteca_actividades`
--
ALTER TABLE `biblioteca_actividades`
  ADD CONSTRAINT `biblioteca_actividades_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `biblioteca_alojamientos`
--
ALTER TABLE `biblioteca_alojamientos`
  ADD CONSTRAINT `biblioteca_alojamientos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `biblioteca_dias`
--
ALTER TABLE `biblioteca_dias`
  ADD CONSTRAINT `biblioteca_dias_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `biblioteca_transportes`
--
ALTER TABLE `biblioteca_transportes`
  ADD CONSTRAINT `biblioteca_transportes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `config_uploads`
--
ALTER TABLE `config_uploads`
  ADD CONSTRAINT `config_uploads_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
