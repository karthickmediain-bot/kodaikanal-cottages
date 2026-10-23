CREATE TABLE IF NOT EXISTS booking_enquiries (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category   VARCHAR(40)  NOT NULL,
  check_in   DATE         NULL,
  check_out  DATE         NULL,
  guests     SMALLINT UNSIGNED NULL,
  phone      VARCHAR(20)  NOT NULL,
  source_url VARCHAR(255) NULL,
  ip         VARCHAR(45)  NULL,
  user_agent VARCHAR(255) NULL,
  email_sent TINYINT(1)   NOT NULL DEFAULT 0,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_ip_created (ip, created_at)
) CHARACTER SET utf8mb4;
