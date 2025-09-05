<?php

return [

    'show_warnings' => false,
    'public_path'   => null,
    'convert_entities' => true,

    'options' => [

        // Directorios de fuentes y caché (deben existir y ser escribibles)
        'font_dir'   => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),

        // Directorio temporal
        'temp_dir' => sys_get_temp_dir(),

        // Chroot para seguridad (no poner '/')
        'chroot' => realpath(base_path()),

        // Protocolos permitidos
        'allowed_protocols' => [
            'file://'  => ['rules' => []],
            'http://'  => ['rules' => []],
            'https://' => ['rules' => []],
        ],

        // Log opcional
        'log_output_file' => null,

        // ✅ Incrusta subset de fuentes para reducir tamaño y asegurar render
        'enable_font_subsetting' => true,

        // Backend de render
        'pdf_backend' => 'CPDF',

        // Media
        'default_media_type' => 'print',

        // Tamaño/Orientación por defecto
        'default_paper_size'        => 'a4',
        'default_paper_orientation' => 'portrait',

        // Fuente por defecto
        'default_font' => 'DejaVu Sans',

        // ✅ DPI 300 para imágenes/medidas relativas (recomendado imprenta)
        'dpi' => 300,

        // PHP/JS embebido
        'enable_php'        => false,
        'enable_javascript' => true,

        // Acceso a recursos remotos (imágenes/CSS)
        'enable_remote' => true,

        // Ajuste de altura tipográfica
        'font_height_ratio' => 1.1,

        // Parser HTML5 (dompdf 2.x ya lo tiene por defecto)
        'enable_html5_parser' => true,
    ],
];
