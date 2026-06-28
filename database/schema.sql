-- =============================================
-- HoanKiem LAB - Database Schema
-- Hệ thống quản lý sản xuất răng giả
-- =============================================

CREATE DATABASE IF NOT EXISTS hoankiemlab
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE hoankiemlab;

-- =============================================
-- 1. Chi nhánh / Cửa hàng
-- =============================================
CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    hotline VARCHAR(20),
    working_hours VARCHAR(255) DEFAULT '08:00 - 17:30',
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- 2. Người dùng (Admin, Nhân viên, Khách hàng)
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    activation_code VARCHAR(50) NULL,
    role ENUM('admin', 'staff', 'customer', 'shipper') NOT NULL DEFAULT 'customer',

    -- Thông tin cá nhân
    name VARCHAR(255) NOT NULL,
    avatar VARCHAR(500) NULL,
    birthday DATE NULL,
    gender ENUM('male', 'female', 'other') NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,

    -- Thông tin khách hàng (role=customer)
    clinic_name VARCHAR(255) NULL COMMENT 'Tên phòng khám',
    dentist_name VARCHAR(255) NULL COMMENT 'Tên bác sĩ',
    tax_code VARCHAR(50) NULL,
    credit_limit DECIMAL(15,2) DEFAULT 0,
    balance DECIMAL(15,2) DEFAULT 0 COMMENT 'Công nợ hiện tại',

    -- Thông tin nhân viên (role=staff)
    branch_id INT NULL,
    department VARCHAR(100) NULL,
    position VARCHAR(100) NULL,

    -- Cài đặt
    notify_promotion TINYINT(1) DEFAULT 1 COMMENT 'Bật/tắt thông báo khuyến mãi',
    notify_order TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    remember_token VARCHAR(255) NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    INDEX idx_role (role),
    INDEX idx_phone (phone)
) ENGINE=InnoDB;

-- =============================================
-- 3. Phân công nhân viên theo công đoạn
-- =============================================
CREATE TABLE production_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    step_name VARCHAR(100) NOT NULL COMMENT 'Tên công đoạn được phân',
    branch_id INT NULL,
    is_primary TINYINT(1) DEFAULT 1 COMMENT 'Công đoạn chính',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    INDEX idx_user_step (user_id, step_name)
) ENGINE=InnoDB;

-- =============================================
-- 4. Loại sản phẩm
-- =============================================
CREATE TABLE product_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'VD: Răng sứ kim loại, Toàn sứ...',
    category VARCHAR(100) NULL COMMENT 'Nhóm sản phẩm',
    description TEXT NULL,
    estimated_days INT DEFAULT 5 COMMENT 'Số ngày SX ước tính',
    base_price DECIMAL(15,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- 5. Đơn hàng
-- =============================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'DH-YYYYMMDD-XXX',
    customer_id INT NOT NULL,
    created_by INT NULL COMMENT 'Admin/Staff tạo đơn',
    branch_id INT NULL,

    -- Thời gian
    received_date DATE NOT NULL COMMENT 'Ngày nhận đơn',
    due_date DATE NOT NULL COMMENT 'Ngày hẹn trả',
    adjusted_due_date DATE NULL COMMENT 'Ngày trả đã chỉnh sửa',
    due_date_token VARCHAR(100) NULL COMMENT 'Token chỉnh ngày trả không cần login',

    -- Trạng thái
    priority ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
    production_status ENUM('pending', 'confirmed', 'in_production', 'qc_passed', 'ready') DEFAULT 'pending',
    delivery_status ENUM('none', 'waiting_pickup', 'shipping', 'delivered', 'pending_return', 'returned') DEFAULT 'none',
    overall_status ENUM('new', 'processing', 'completed', 'cancelled') DEFAULT 'new',

    -- Tài chính
    total_amount DECIMAL(15,2) DEFAULT 0,
    discount DECIMAL(15,2) DEFAULT 0,
    paid_amount DECIMAL(15,2) DEFAULT 0,
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',

    -- Ghi chú
    patient_name VARCHAR(255) NULL COMMENT 'Tên bệnh nhân',
    dentist_notes TEXT NULL COMMENT 'Ghi chú từ bác sĩ',
    admin_notes TEXT NULL COMMENT 'Ghi chú nội bộ',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    INDEX idx_order_code (order_code),
    INDEX idx_customer (customer_id),
    INDEX idx_status (overall_status, production_status, delivery_status),
    INDEX idx_due_date (due_date),
    INDEX idx_due_date_token (due_date_token)
) ENGINE=InnoDB;

-- =============================================
-- 6. Chi tiết đơn hàng (sản phẩm)
-- =============================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_type_id INT NULL,
    product_name VARCHAR(255) NOT NULL COMMENT 'Tên sản phẩm',
    tooth_numbers VARCHAR(100) NULL COMMENT '11,12,13...',
    shade VARCHAR(50) NULL COMMENT 'Màu răng: A1,A2,B1...',
    material_type VARCHAR(100) NULL,
    specifications TEXT NULL COMMENT 'Yêu cầu kỹ thuật',
    quantity INT DEFAULT 1,
    unit_price DECIMAL(15,2) DEFAULT 0,
    amount DECIMAL(15,2) DEFAULT 0,
    status ENUM('pending', 'in_production', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_type_id) REFERENCES product_types(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB;

-- =============================================
-- 7. Ảnh đính kèm đơn hàng
-- =============================================
CREATE TABLE order_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NULL COMMENT 'image|stl|document',
    description VARCHAR(255) NULL,
    uploaded_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- 8. Công đoạn sản xuất
-- =============================================
CREATE TABLE production_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL,
    order_id INT NOT NULL COMMENT 'Denormalized for quick query',
    step_number TINYINT NOT NULL COMMENT '1-8',
    step_name VARCHAR(100) NOT NULL COMMENT 'Cưa đai|Thiết kế|Nung sườn|...',
    assigned_to INT NULL COMMENT 'Nhân viên phụ trách',
    status ENUM('waiting', 'in_progress', 'completed', 'rework', 'skipped') DEFAULT 'waiting',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    estimated_hours DECIMAL(5,2) NULL,
    notes TEXT NULL,
    rework_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_item (order_item_id),
    INDEX idx_order (order_id),
    INDEX idx_assigned (assigned_to, status),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =============================================
-- 9. Nhật ký trạng thái đơn hàng
-- =============================================
CREATE TABLE order_status_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    from_status VARCHAR(50) NULL,
    to_status VARCHAR(50) NOT NULL,
    status_type ENUM('production', 'delivery', 'overall') DEFAULT 'overall',
    changed_by INT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB;

-- =============================================
-- 10. Phản hồi đơn hàng
-- =============================================
CREATE TABLE order_feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    customer_id INT NOT NULL,
    content TEXT NOT NULL COMMENT 'Nội dung phản hồi',
    rating TINYINT DEFAULT 5,
    images TEXT NULL,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    admin_reply TEXT NULL,
    replied_by INT NULL,
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB;

-- =============================================
-- 11. Ảnh phản hồi
-- =============================================
CREATE TABLE feedback_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feedback_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (feedback_id) REFERENCES order_feedbacks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- 12. Giao hàng
-- =============================================
CREATE TABLE deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method ENUM('pickup', 'courier', 'internal') DEFAULT 'internal',
    courier_name VARCHAR(255) NULL,
    tracking_number VARCHAR(255) NULL,
    scheduled_at TIMESTAMP NULL,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    status ENUM('waiting_pickup', 'shipping', 'delivered', 'pending_return', 'returned') DEFAULT 'waiting_pickup',
    delivered_by INT NULL,
    shipper_lat DECIMAL(10, 7) NULL,
    shipper_lng DECIMAL(10, 7) NULL,
    shipper_location_note VARCHAR(500) NULL,
    shipper_location_updated_at TIMESTAMP NULL,
    recipient_name VARCHAR(255) NULL,
    recipient_phone VARCHAR(20) NULL,
    delivery_address TEXT NULL,
    notes TEXT NULL,
    proof_photo VARCHAR(500) NULL COMMENT 'Ảnh xác nhận giao',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (delivered_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =============================================
-- 13. Lịch hẹn
-- =============================================
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    staff_id INT NULL,
    branch_id INT NULL,
    order_id INT NULL COMMENT 'Liên kết đơn hàng nếu có',
    appointment_date DATETIME NOT NULL,
    duration_minutes INT DEFAULT 30,
    type ENUM('consultation', 'delivery', 'checkup', 'impression', 'other') DEFAULT 'consultation',
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_date (appointment_date),
    INDEX idx_customer (customer_id),
    INDEX idx_staff (staff_id)
) ENGINE=InnoDB;

-- =============================================
-- 14. Nhà cung cấp
-- =============================================
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    material_types TEXT NULL COMMENT 'Loại vật liệu cung cấp',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- 15. Vật liệu
-- =============================================
CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category ENUM('metal', 'ceramic', 'acrylic', 'consumable', 'tool', 'other') DEFAULT 'other',
    unit VARCHAR(50) DEFAULT 'piece' COMMENT 'gram|ml|piece|set',
    current_stock DECIMAL(15,3) DEFAULT 0,
    min_stock DECIMAL(15,3) DEFAULT 0 COMMENT 'Mức tồn kho tối thiểu',
    unit_cost DECIMAL(15,2) DEFAULT 0,
    supplier_id INT NULL,
    expiry_date DATE NULL,
    storage_location VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_category (category),
    INDEX idx_low_stock (current_stock, min_stock)
) ENGINE=InnoDB;

-- =============================================
-- 16. Giao dịch vật liệu (nhập/xuất/điều chỉnh)
-- =============================================
CREATE TABLE material_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    type ENUM('import', 'export', 'adjust', 'return') NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    unit_cost DECIMAL(15,2) NULL,
    reference_id INT NULL COMMENT 'order_item_id hoặc PO id',
    reference_type VARCHAR(50) NULL COMMENT 'order|purchase_order',
    performed_by INT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_material (material_id),
    INDEX idx_type (type)
) ENGINE=InnoDB;

-- =============================================
-- 17. Bài viết / Tin tức
-- =============================================
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) NOT NULL UNIQUE,
    type ENUM('news', 'promotion', 'guide', 'announcement') DEFAULT 'news',
    summary TEXT NULL,
    content LONGTEXT NOT NULL,
    cover_image VARCHAR(500) NULL,
    show_hotline_button TINYINT(1) DEFAULT 1,
    is_published TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    view_count INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_type (type),
    INDEX idx_published (is_published, published_at)
) ENGINE=InnoDB;

-- =============================================
-- 18. Chương trình khuyến mãi
-- =============================================
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    code VARCHAR(50) NULL UNIQUE COMMENT 'Mã khuyến mãi',
    type ENUM('percent', 'fixed') DEFAULT 'percent',
    value DECIMAL(15,2) NOT NULL COMMENT 'Giá trị giảm (% hoặc VND)',
    min_order_amount DECIMAL(15,2) DEFAULT 0,
    max_discount DECIMAL(15,2) NULL COMMENT 'Giảm tối đa (cho type=percent)',
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    usage_limit INT NULL COMMENT 'Giới hạn lượt sử dụng',
    usage_count INT DEFAULT 0,
    description TEXT NULL,
    cover_image VARCHAR(500) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_code (code),
    INDEX idx_date (start_date, end_date),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- =============================================
-- 19. Thông báo
-- =============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('order_status', 'promotion', 'appointment', 'feedback', 'system') DEFAULT 'system',
    title VARCHAR(500) NOT NULL,
    content TEXT NULL,
    data JSON NULL COMMENT 'Dữ liệu bổ sung (order_id, link...)',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_type (type)
) ENGINE=InnoDB;

-- =============================================
-- 20. Thanh toán
-- =============================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    customer_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    method ENUM('cash', 'transfer', 'card', 'momo', 'other') DEFAULT 'cash',
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    transaction_code VARCHAR(100) NULL,
    notes TEXT NULL,
    confirmed_by INT NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB;

-- =============================================
-- 21. Cài đặt hệ thống
-- =============================================
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_group VARCHAR(50) DEFAULT 'general',
    description VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_group (setting_group)
) ENGINE=InnoDB;

-- =============================================
-- 22. Nhật ký hoạt động
-- =============================================
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    model_type VARCHAR(100) NULL,
    model_id INT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_model (model_type, model_id)
) ENGINE=InnoDB;

-- =============================================
-- DỮ LIỆU MẪU
-- =============================================

-- Cài đặt mặc định
INSERT INTO settings (setting_key, setting_value, setting_group, description) VALUES
('app_name', 'HoanKiem LAB', 'general', 'Tên ứng dụng'),
('hotline', '1900-xxxx', 'general', 'Số hotline'),
('company_name', 'Công ty TNHH HoanKiem LAB', 'general', 'Tên công ty'),
('company_address', 'Hà Nội, Việt Nam', 'general', 'Địa chỉ công ty'),
('company_email', 'info@hoankiemlab.com', 'general', 'Email công ty'),
('default_due_days', '7', 'order', 'Số ngày hẹn trả mặc định'),
('currency', 'VND', 'general', 'Đơn vị tiền tệ'),
('tax_rate', '0', 'finance', 'Thuế suất (%)'),
('sms_enabled', '0', 'notification', 'Bật/tắt SMS'),
('push_enabled', '0', 'notification', 'Bật/tắt Push notification');

-- Chi nhánh mẫu
INSERT INTO branches (name, address, phone, hotline) VALUES
('Chi nhánh Hoàn Kiếm', '123 Phố Huế, Hoàn Kiếm, Hà Nội', '024-1234-5678', '1900-xxxx'),
('Chi nhánh Cầu Giấy', '456 Xuân Thủy, Cầu Giấy, Hà Nội', '024-8765-4321', '1900-xxxx');

-- Loại sản phẩm mẫu
INSERT INTO product_types (name, category, estimated_days, base_price, sort_order) VALUES
('Răng sứ kim loại (PFM)', 'Răng sứ', 5, 1500000, 1),
('Răng sứ Zirconia', 'Răng sứ', 5, 3500000, 2),
('Răng toàn sứ E.max', 'Răng sứ', 5, 4000000, 3),
('Răng sứ Cercon', 'Răng sứ', 5, 3000000, 4),
('Hàm tháo lắp nhựa', 'Hàm tháo lắp', 7, 2000000, 5),
('Hàm tháo lắp khung kim loại', 'Hàm tháo lắp', 10, 5000000, 6),
('Mão tạm Acrylic', 'Răng tạm', 2, 300000, 7),
('Inlay/Onlay sứ', 'Phục hình', 5, 2500000, 8),
('Veneer sứ', 'Thẩm mỹ', 7, 5000000, 9),
('Implant Crown', 'Implant', 7, 6000000, 10);

-- Tài khoản Admin mặc định (password: admin123)
INSERT INTO users (phone, password, role, name, is_active) VALUES
('0901234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin HoanKiem', 1);

-- Nhân viên mẫu (password: staff123)
INSERT INTO users (phone, password, role, name, branch_id, department, position) VALUES
('0911111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Nguyễn Văn An', 1, 'Sản xuất', 'Kỹ thuật viên'),
('0911111112', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Trần Thị Bình', 1, 'Sản xuất', 'Kỹ thuật viên'),
('0911111113', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Lê Minh Cường', 1, 'Sản xuất', 'Kỹ thuật viên'),
('0911111114', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Phạm Thu Dung', 1, 'Sản xuất', 'Kỹ thuật viên');

-- Phân công nhân viên
INSERT INTO production_assignments (user_id, step_name, branch_id) VALUES
(2, 'Cưa đai', 1),
(2, 'Thiết kế', 1),
(3, 'Nung sườn', 1),
(3, 'Nguội sườn', 1),
(4, 'Đắp sứ', 1),
(4, 'Nguội sứ', 1),
(5, 'Stain màu', 1),
(5, 'Trả mẫu', 1);

-- Khách hàng mẫu (password: customer123)
INSERT INTO users (phone, password, role, name, clinic_name, dentist_name, address) VALUES
('0922222221', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Phòng khám Nha khoa Smile', 'Nha khoa Smile', 'BS. Nguyễn Thanh Hà', '789 Láng Hạ, Đống Đa, Hà Nội'),
('0922222222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Phòng khám Dr. Teeth', 'Dr. Teeth Dental', 'BS. Trần Văn Đức', '321 Kim Mã, Ba Đình, Hà Nội');

-- Shipper mẫu (password: password)
INSERT INTO users (phone, password, role, name, branch_id, department, position) VALUES
('0933333333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'shipper', 'Shipper Hoàn Kiếm', 1, 'Giao hàng', 'Shipper');

-- Vật liệu mẫu
INSERT INTO materials (code, name, category, unit, current_stock, min_stock, unit_cost) VALUES
('MT-001', 'Hợp kim Ni-Cr', 'metal', 'gram', 5000, 500, 150),
('MT-002', 'Hợp kim Co-Cr', 'metal', 'gram', 3000, 300, 250),
('MT-003', 'Zirconia Blank', 'ceramic', 'piece', 50, 10, 500000),
('MT-004', 'Bột sứ GC Initial', 'ceramic', 'gram', 200, 50, 5000),
('MT-005', 'Bột sứ Noritake', 'ceramic', 'gram', 150, 30, 6000),
('MT-006', 'E.max Press Ingot', 'ceramic', 'piece', 30, 5, 800000),
('MT-007', 'Thạch cao cứng', 'consumable', 'gram', 10000, 1000, 50),
('MT-008', 'Sáp nha khoa', 'consumable', 'gram', 2000, 200, 200),
('MT-009', 'Stain/Glaze', 'ceramic', 'ml', 100, 20, 15000),
('MT-010', 'Acrylic tự cứng', 'acrylic', 'gram', 3000, 500, 100);

-- Nhà cung cấp mẫu
INSERT INTO suppliers (name, contact_person, phone, email, material_types) VALUES
('Dental Supply Vietnam', 'Nguyễn Minh', '0909876543', 'contact@dentalsupply.vn', 'Kim loại, Sứ, Vật tư tiêu hao'),
('GC Asia Dental', 'Trần Hải', '0908765432', 'info@gcasia.com', 'Sứ GC Initial, Composite'),
('Dentsply Sirona VN', 'Lê Hương', '0907654321', 'vn@dentsply.com', 'Zirconia, CAD/CAM');

-- Đơn hàng mẫu
INSERT INTO orders (order_code, customer_id, created_by, branch_id, received_date, due_date, priority, production_status, delivery_status, overall_status, total_amount, patient_name, dentist_notes) VALUES
('DH-20260625-001', 6, 1, 1, '2026-06-25', '2026-07-02', 'normal', 'in_production', 'none', 'processing', 4500000, 'Nguyễn Văn Tùng', 'Răng số 11,21 sứ Zirconia màu A2, cần check bite'),
('DH-20260625-002', 7, 1, 1, '2026-06-25', '2026-06-30', 'urgent', 'confirmed', 'none', 'processing', 8000000, 'Trần Thị Mai', 'Cầu 4 đơn vị 24-27, PFM, màu A3'),
('DH-20260624-001', 6, 1, 1, '2026-06-24', '2026-07-01', 'normal', 'qc_passed', 'waiting_pickup', 'processing', 3500000, 'Lê Minh Hoàng', 'Mão sứ Zirconia răng 46, shade A1');

-- Order items mẫu
INSERT INTO order_items (order_id, product_type_id, product_name, tooth_numbers, shade, quantity, unit_price, amount, status) VALUES
(1, 2, 'Răng sứ Zirconia', '11,21', 'A2', 2, 3500000, 7000000, 'in_production'),
(2, 1, 'Răng sứ kim loại (PFM)', '24,25,26,27', 'A3', 4, 1500000, 6000000, 'pending'),
(3, 2, 'Răng sứ Zirconia', '46', 'A1', 1, 3500000, 3500000, 'completed');

-- Production steps cho đơn hàng 1
INSERT INTO production_steps (order_item_id, order_id, step_number, step_name, assigned_to, status, started_at, completed_at) VALUES
(1, 1, 1, 'Cưa đai', 2, 'completed', '2026-06-25 08:00:00', '2026-06-25 08:45:00'),
(1, 1, 2, 'Thiết kế', 2, 'completed', '2026-06-25 09:00:00', '2026-06-25 11:00:00'),
(1, 1, 3, 'Nung sườn', 3, 'completed', '2026-06-25 13:00:00', '2026-06-25 16:00:00'),
(1, 1, 4, 'Nguội sườn', 3, 'completed', '2026-06-25 16:30:00', '2026-06-25 17:00:00'),
(1, 1, 5, 'Đắp sứ', 4, 'in_progress', '2026-06-26 08:00:00', NULL),
(1, 1, 6, 'Nguội sứ', 4, 'waiting', NULL, NULL),
(1, 1, 7, 'Stain màu', 5, 'waiting', NULL, NULL),
(1, 1, 8, 'Trả mẫu', 5, 'waiting', NULL, NULL);

-- Bài viết mẫu
INSERT INTO articles (title, slug, type, summary, content, show_hotline_button, is_published, published_at, created_by) VALUES
('Chào mừng đến với HoanKiem LAB', 'chao-mung-hoankiemlab', 'announcement', 'Hệ thống quản lý sản xuất răng giả hiện đại', '<p>HoanKiem LAB là hệ thống quản lý toàn diện cho ngành sản xuất răng giả, giúp kết nối phòng khám nha khoa với phòng lab một cách hiệu quả nhất.</p>', 1, 1, NOW(), 1),
('Ưu đãi tháng 7: Giảm 10% răng sứ Zirconia', 'uu-dai-thang-7-zirconia', 'promotion', 'Chương trình ưu đãi đặc biệt tháng 7', '<p>Nhân dịp kỷ niệm thành lập, HoanKiem LAB giảm 10% cho tất cả đơn hàng răng sứ Zirconia trong tháng 7/2026.</p>', 1, 1, NOW(), 1);

-- Thông báo mẫu
INSERT INTO notifications (user_id, type, title, content) VALUES
(6, 'order_status', 'Đơn hàng DH-20260625-001 đang sản xuất', 'Đơn hàng của bạn đã được xác nhận và đang trong quá trình sản xuất.'),
(6, 'promotion', 'Ưu đãi tháng 7', 'Giảm 10% răng sứ Zirconia trong tháng 7/2026.');
