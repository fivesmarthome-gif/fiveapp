-- HoanKiemLAB shipper role and live location fields
-- Apply this migration when upgrading an existing database.

ALTER TABLE users
MODIFY COLUMN role ENUM('admin','staff','customer','shipper') NOT NULL DEFAULT 'customer';

ALTER TABLE deliveries
ADD COLUMN shipper_lat DECIMAL(10,7) NULL AFTER delivered_by,
ADD COLUMN shipper_lng DECIMAL(10,7) NULL AFTER shipper_lat,
ADD COLUMN shipper_location_note VARCHAR(500) NULL AFTER shipper_lng,
ADD COLUMN shipper_location_updated_at TIMESTAMP NULL AFTER shipper_location_note;

INSERT INTO users (phone, password, role, name, branch_id, department, position, is_active)
SELECT '0933333333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'shipper', 'Shipper Hoàn Kiếm', 1, 'Giao hàng', 'Shipper', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE phone = '0933333333');
