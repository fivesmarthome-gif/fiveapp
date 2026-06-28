-- HoanKiemLAB delivery + feedback updates
-- Apply this migration when upgrading an existing database created from the old schema.

ALTER TABLE deliveries
MODIFY COLUMN status ENUM('pending','shipped','delivered','return_pending','returned','waiting_pickup','shipping','pending_return')
DEFAULT 'pending';

UPDATE deliveries SET status = 'waiting_pickup' WHERE status = 'pending' OR status IS NULL;
UPDATE deliveries SET status = 'shipping' WHERE status = 'shipped';
UPDATE deliveries SET status = 'pending_return' WHERE status = 'return_pending';

ALTER TABLE deliveries
MODIFY COLUMN status ENUM('waiting_pickup','shipping','delivered','pending_return','returned')
DEFAULT 'waiting_pickup';

ALTER TABLE order_feedbacks
ADD COLUMN rating TINYINT DEFAULT 5 AFTER content,
ADD COLUMN images TEXT NULL AFTER rating;
