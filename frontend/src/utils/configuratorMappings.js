// Mapeo de necesidades a IDs de la base de datos
// Basado en seeds.sql - daniel_map_needs y daniel_map_boosters

export const needsMapping = {
  // ===== GAMING (Super Category 5) =====
  // Indies (need_id: 2) - usa boosters 1, 2, 3
  'Indies-Optima': { need_id: 2, booster_id: 1 },        // Óptima (Base)
  'Indies-Mejorada': { need_id: 2, booster_id: 2 },      // Mejorada (1440p)
  'Indies-Premium': { need_id: 2, booster_id: 3 },       // Premium (4K/FPS)

  // E-Sports (need_id: 3) - usa boosters 1, 2, 3
  'E-sports-Optima': { need_id: 3, booster_id: 1 },
  'E-sports-Mejorada': { need_id: 3, booster_id: 2 },
  'E-sports-Premium': { need_id: 3, booster_id: 3 },

  // AAA (need_id: 4) - usa boosters 1, 2, 3
  'AAA-Optima': { need_id: 4, booster_id: 1 },
  'AAA-Mejorada': { need_id: 4, booster_id: 2 },
  'AAA-Premium': { need_id: 4, booster_id: 3 },

  // ===== AUDIOVISUALES (Super Category 6) =====
  // Edición de Audio (need_id: 5) - usa boosters 4, 5, 6
  'Edicion Audio/Musica-Entusiasta': { need_id: 5, booster_id: 4 },
  'Edicion Audio/Musica-Emprendedor': { need_id: 5, booster_id: 5 },
  'Edicion Audio/Musica-Profesional': { need_id: 5, booster_id: 6 },

  // Edición de Imagen (need_id: 6) - usa boosters 4, 5, 6
  'Edicion Imagen-Entusiasta': { need_id: 6, booster_id: 4 },
  'Edicion Imagen-Emprendedor': { need_id: 6, booster_id: 5 },
  'Edicion Imagen-Profesional': { need_id: 6, booster_id: 6 },

  // Edición de Video (need_id: 7) - usa boosters 4, 5, 6
  'Edicion video-Entusiasta': { need_id: 7, booster_id: 4 },
  'Edicion video-Emprendedor': { need_id: 7, booster_id: 5 },
  'Edicion video-Profesional': { need_id: 7, booster_id: 6 },

  // Modelado 3D (need_id: 8) - usa boosters 4, 5, 6
  'Modelado-Entusiasta': { need_id: 8, booster_id: 4 },
  'Modelado-Emprendedor': { need_id: 8, booster_id: 5 },
  'Modelado-Profesional': { need_id: 8, booster_id: 6 },

  // ===== WORKSTATION (Super Category 7) =====
  // Desarrollo de Software (need_id: 9) - usa boosters 4, 5, 6
  'Desarrollo de software-Entusiasta': { need_id: 9, booster_id: 4 },
  'Desarrollo de software-Emprendedor': { need_id: 9, booster_id: 5 },
  'Desarrollo de software-Profesional': { need_id: 9, booster_id: 6 },

  // Ciencia de Datos (need_id: 10) - usa boosters 4, 5, 6
  'Ciencia de datos-Entusiasta': { need_id: 10, booster_id: 4 },
  'Ciencia de datos-Emprendedor': { need_id: 10, booster_id: 5 },
  'Ciencia de datos-Profesional': { need_id: 10, booster_id: 6 },

  // Arquitectura e Ingeniería (need_id: 11) - usa boosters 4, 5, 6
  'Arquitectura e Ingenieria-Entusiasta': { need_id: 11, booster_id: 4 },
  'Arquitectura e Ingenieria-Emprendedor': { need_id: 11, booster_id: 5 },
  'Arquitectura e Ingenieria-Profesional': { need_id: 11, booster_id: 6 },

  // Desarrollo de Videojuegos (need_id: 12) - usa boosters 4, 5, 6
  'Desarrollo videojuegos-Entusiasta': { need_id: 12, booster_id: 4 },
  'Desarrollo videojuegos-Emprendedor': { need_id: 12, booster_id: 5 },
  'Desarrollo videojuegos-Profesional': { need_id: 12, booster_id: 6 },
};

// Mapeo de personalizaciones a IDs de COMPONENTES (tabla components)
// IMPORTANTE: El backend valida contra la tabla 'components', NO 'daniel_map_personalization'
// Ver backend/app/Routes/orders.php línea 75: Component::find($compId)
export const personalizationMapping = {
  // ===== ALMACENAMIENTO (de tabla components) =====
  // Basado en seeds.sql líneas 95-97
  '512GB': 501,  // 'SSD 512GB SATA' - component_id 501
  '1TB': 502,    // 'SSD 1TB NVMe Gen3' - component_id 502
  '2TB': 503,    // 'SSD 2TB NVMe Gen4' - component_id 503

  // ===== TAMAÑO DE GABINETE (de tabla components) =====
  // Basado en seeds.sql líneas 99-101
  'Pequeño': 701,  // 'Case Mini Tower Básico' - component_id 701
  'Normal': 702,   // 'Case Mid Tower Flujo Aire' - component_id 702
  'Grande': 703,   // 'Case Full Tower Premium' - component_id 703
};

// NOTAS IMPORTANTES:
// 1. Gaming usa boosters 1, 2, 3 (Óptima, Mejorada, Premium)
// 2. Audiovisuales y Workstation usan boosters 4, 5, 6 (Entusiasta, Emprendedor, Profesional)
// 3. Las personalizaciones se mapean a COMPONENTES FÍSICOS en la tabla 'components'
// 4. NO se usan los IDs de 'daniel_map_personalization' para crear pedidos
// 5. Los gabinetes (701-703) normalmente vienen en los kits estructurales, pero pueden ser personalizaciones
