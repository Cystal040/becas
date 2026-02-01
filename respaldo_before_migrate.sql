-- Tabla: estudiante
DROP TABLE IF EXISTS `estudiante`;
CREATE TABLE `estudiante` (
  `id_estudiante` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_estudiante`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `estudiante` (`id_estudiante`,`nombre`,`apellido`,`cedula`,`correo`,`password`,`fecha_registro`) VALUES ('5','Gabriel Fernando','Mendez Colmenares','31072441','gabrielcolm1910@gmail.com','$2y$10$xMqY.LBl0TexlyON3qQrHeWxiomfoedzT/KwUztWtFMM6tmUSHCAy','2025-12-19 11:58:36');

-- Tabla: tipo_documento
DROP TABLE IF EXISTS `tipo_documento`;
CREATE TABLE `tipo_documento` (
  `id_tipo_documento` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_documento` varchar(100) DEFAULT NULL,
  `formato_permitido` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_tipo_documento`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tipo_documento` (`id_tipo_documento`,`nombre_documento`,`formato_permitido`) VALUES ('1','Constancia de inscripción','');
INSERT INTO `tipo_documento` (`id_tipo_documento`,`nombre_documento`,`formato_permitido`) VALUES ('2','Récord académico','');
INSERT INTO `tipo_documento` (`id_tipo_documento`,`nombre_documento`,`formato_permitido`) VALUES ('3','Cédula','');
INSERT INTO `tipo_documento` (`id_tipo_documento`,`nombre_documento`,`formato_permitido`) VALUES ('4','RIF','');
INSERT INTO `tipo_documento` (`id_tipo_documento`,`nombre_documento`,`formato_permitido`) VALUES ('5','Foto tipo carnet','');

