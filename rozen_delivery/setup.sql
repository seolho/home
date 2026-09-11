CREATE TABLE IF NOT EXISTS delivery_shipments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  carrier_name VARCHAR(50) NOT NULL DEFAULT '로젠택배',
  tracking_no VARCHAR(80) NOT NULL,
  send_status ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
  sent_at DATETIME NULL,
  send_count INT NOT NULL DEFAULT 0,
  last_error TEXT NULL,
  message_key VARCHAR(120) NULL,
  ref_key VARCHAR(120) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_phone (phone),
  KEY idx_tracking (tracking_no),
  KEY idx_status (send_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_send_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  shipment_id BIGINT UNSIGNED NOT NULL,
  success TINYINT(1) NOT NULL DEFAULT 0,
  message_key VARCHAR(120) NULL,
  ref_key VARCHAR(120) NULL,
  request_json MEDIUMTEXT NULL,
  response_json MEDIUMTEXT NULL,
  error_message TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_shipment (shipment_id),
  CONSTRAINT fk_delivery_log_ship FOREIGN KEY (shipment_id) REFERENCES delivery_shipments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
