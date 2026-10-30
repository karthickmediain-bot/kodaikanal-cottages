-- Every lead from every page and form lands here: one row per submission.
--   lead_type     hero_booking, trip_enquiry, contact_form, page_quote_form, cab_request,
--                 whatsapp_click, call_click, other
--   source_path   the page the form was on; page_group is its section of the site
--   channel       Paid Search, Paid Social, Paid Other, Organic Search, Organic Social,
--                 Email, Referral or Direct; source/medium e.g. google/cpc, meta/paid_social
--   first_*       how the visitor first found the site (the plain columns are the visit
--                 that led to this submission)
--   extra         JSON for the rare stuff: form fields (vehicle, pickup, trip type, stay type,
--                 traveller type, service interest) and tracking (other UTM tags and click ids,
--                 landing url, session id, device)
-- Existing databases: run migrations/2026-10-30-lead-attribution.sql instead of this block.
CREATE TABLE IF NOT EXISTS booking_enquiries (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lead_type     VARCHAR(30)  NOT NULL DEFAULT 'hero_booking',
  form_id       VARCHAR(60)  NULL,

  name          VARCHAR(120) NULL,
  phone         VARCHAR(20)  NULL,
  email         VARCHAR(190) NULL,
  message       TEXT         NULL,
  category      VARCHAR(40)  NULL,
  check_in      DATE         NULL,
  check_out     DATE         NULL,
  guests        SMALLINT UNSIGNED NULL,

  source_url    VARCHAR(500) NULL,
  source_path   VARCHAR(255) NULL,
  page_group    VARCHAR(40)  NULL,

  channel       VARCHAR(30)  NULL,
  source        VARCHAR(80)  NULL,
  medium        VARCHAR(80)  NULL,
  campaign      VARCHAR(150) NULL,
  gclid         VARCHAR(255) NULL,
  fbclid        VARCHAR(255) NULL,
  landing_path  VARCHAR(255) NULL,
  referrer_host VARCHAR(190) NULL,
  first_channel VARCHAR(30)  NULL,
  first_source  VARCHAR(80)  NULL,

  visitor_id    VARCHAR(40)  NULL,
  extra         TEXT         NULL,
  ip            VARCHAR(45)  NULL,
  user_agent    VARCHAR(255) NULL,
  lead_status   ENUM('new','contacted','qualified','booked','lost') NOT NULL DEFAULT 'new',
  email_sent    TINYINT(1)   NOT NULL DEFAULT 0,

  KEY idx_ip_created (ip, created_at),
  KEY idx_created (created_at),
  KEY idx_channel (channel, source),
  KEY idx_campaign (campaign),
  KEY idx_source_path (source_path),
  KEY idx_lead_type (lead_type),
  KEY idx_phone (phone),
  KEY idx_visitor (visitor_id)
) CHARACTER SET utf8mb4;

-- Blog team accounts. Created automatically by blog-lib.php on first visit to /blog-admin/;
-- listed here for reference.
CREATE TABLE IF NOT EXISTS blog_users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(80)  NOT NULL,
  email         VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  active        TINYINT(1)   NOT NULL DEFAULT 1,
  last_login    DATETIME     NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_blog_users_email (email)
) CHARACTER SET utf8mb4;
