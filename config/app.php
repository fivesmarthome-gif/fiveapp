<?php
/**
 * Application Configuration
 * HoanKiem LAB - Dental Lab Management System
 */

return [
    'name'      => 'HoanKiem LAB',
    'version'   => '1.0.0',
    'debug'     => true,
    'timezone'  => 'Asia/Ho_Chi_Minh',
    'locale'    => 'vi',

    // Base URL (adjust if needed)
    'base_url'  => '/HoanKiemLAB',
    'base_path' => dirname(__DIR__),

    // Upload settings
    'upload_dir'       => 'public/uploads',
    'max_upload_size'  => 10 * 1024 * 1024, // 10MB
    'allowed_images'   => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'allowed_files'    => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'stl', 'dcm'],

    // Session
    'session_lifetime'  => 86400 * 30, // 30 days (persistent login)
    'session_name'      => 'hklab_session',

    // Pagination
    'per_page' => 15,

    // Production Steps (8 công đoạn)
    'production_steps' => [
        1 => ['name' => 'Cưa đai',    'icon' => 'fa-cut',          'color' => '#6366f1', 'estimated_hours' => 0.5],
        2 => ['name' => 'Thiết kế',    'icon' => 'fa-pencil-ruler', 'color' => '#8b5cf6', 'estimated_hours' => 2],
        3 => ['name' => 'Nung sườn',   'icon' => 'fa-fire',         'color' => '#ef4444', 'estimated_hours' => 3],
        4 => ['name' => 'Nguội sườn',  'icon' => 'fa-snowflake',    'color' => '#06b6d4', 'estimated_hours' => 1],
        5 => ['name' => 'Đắp sứ',     'icon' => 'fa-layer-group',  'color' => '#f59e0b', 'estimated_hours' => 2],
        6 => ['name' => 'Nguội sứ',    'icon' => 'fa-temperature-low', 'color' => '#3b82f6', 'estimated_hours' => 1.5],
        7 => ['name' => 'Stain màu',   'icon' => 'fa-palette',      'color' => '#ec4899', 'estimated_hours' => 1],
        8 => ['name' => 'Trả mẫu',    'icon' => 'fa-box',           'color' => '#10b981', 'estimated_hours' => 0.5],
    ],

    // Order priorities
    'priorities' => [
        'normal'    => ['label' => 'Thường',    'color' => '#6b7280', 'badge' => 'secondary'],
        'urgent'    => ['label' => 'Gấp',       'color' => '#f59e0b', 'badge' => 'warning'],
        'emergency' => ['label' => 'Khẩn cấp',  'color' => '#ef4444', 'badge' => 'danger'],
    ],

    // Production statuses
    'production_statuses' => [
        'pending'       => ['label' => 'Chờ xác nhận',  'color' => '#6b7280', 'icon' => 'fa-clock'],
        'confirmed'     => ['label' => 'Đã xác nhận',   'color' => '#3b82f6', 'icon' => 'fa-check-circle'],
        'in_production' => ['label' => 'Đang sản xuất', 'color' => '#f59e0b', 'icon' => 'fa-cog'],
        'qc_passed'     => ['label' => 'QC đạt',        'color' => '#8b5cf6', 'icon' => 'fa-shield-check'],
        'ready'         => ['label' => 'Sẵn sàng giao', 'color' => '#10b981', 'icon' => 'fa-box-check'],
    ],

    // Delivery statuses
    'delivery_statuses' => [
        'none'           => ['label' => 'Chưa giao',           'color' => '#6b7280', 'icon' => 'fa-minus-circle'],
        'waiting_pickup' => ['label' => 'Chờ lấy hàng',        'color' => '#f59e0b', 'icon' => 'fa-box'],
        'shipping'       => ['label' => 'Đang vận chuyển',     'color' => '#3b82f6', 'icon' => 'fa-truck'],
        'delivered'      => ['label' => 'Giao hàng thành công', 'color' => '#10b981', 'icon' => 'fa-check-double'],
        'pending_return' => ['label' => 'Chờ duyệt hoàn',      'color' => '#ef4444', 'icon' => 'fa-undo'],
        'returned'       => ['label' => 'Hoàn thành công',      'color' => '#6366f1', 'icon' => 'fa-exchange-alt'],
    ],

    // Shade options
    'shades' => ['A1','A2','A3','A3.5','A4','B1','B2','B3','B4','C1','C2','C3','C4','D2','D3','D4','BL1','BL2','BL3','BL4'],
];
