<?php
return [
    // กำหนด path สำหรับฟอนต์
    'font_dir' => storage_path('fonts/'),
    'font_cache' => storage_path('fonts/'),
  
    
    'temp_dir' => sys_get_temp_dir(),
    'chroot' => realpath(base_path()),
    
    // กำหนดฟอนต์และการแมปปิ้ง
    'font_family_cache' => [
        'thai' => [
            'normal' => 'THSarabunNew',
            'bold' => 'THSarabunNew-Bold',
            'italic' => 'THSarabunNew-Italic',
            'bold_italic' => 'THSarabunNew-BoldItalic'
        ]
    ],
    

    
    // ตั้งค่าฟอนต์เริ่มต้น
    'default_font' => 'THSarabunNew',
    'defaultFont' => 'THSarabunNew',

    // ตั้งค่าอื่นๆ
    'font_height_ratio' => 1.1,
    'dpi' => 150,
    'defaultMediaType' => 'screen',
    'defaultPaperSize' => 'a4',
    
    // เปิดใช้งานฟีเจอร์ที่จำเป็น
    'is_remote_enabled' => true,
    'enable_remote' => true,
    'enable_javascript' => true,
    'font_subsetting' => true,
    
    // อนุญาต protocols
    'allowed_protocols' => [
        'file://' => ['rules' => []],
        'http://' => ['rules' => []],
        'https://' => ['rules' => []]
    ],
];