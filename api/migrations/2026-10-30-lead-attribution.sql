-- Run once on an existing database (phpMyAdmin > SQL). Adds lead-type and traffic-source
-- columns to booking_enquiries. Existing rows keep their data; new columns stay NULL.
ALTER TABLE booking_enquiries
  MODIFY category VARCHAR(40) NULL,
  MODIFY phone VARCHAR(20) NULL,
  MODIFY source_url VARCHAR(500) NULL,
  ADD COLUMN lead_type VARCHAR(30) NOT NULL DEFAULT 'hero_booking' AFTER created_at,
  ADD COLUMN form_id VARCHAR(60) NULL AFTER lead_type,
  ADD COLUMN name VARCHAR(120) NULL AFTER form_id,
  ADD COLUMN email VARCHAR(190) NULL AFTER phone,
  ADD COLUMN message TEXT NULL AFTER email,
  ADD COLUMN source_path VARCHAR(255) NULL AFTER source_url,
  ADD COLUMN page_group VARCHAR(40) NULL AFTER source_path,
  ADD COLUMN channel VARCHAR(30) NULL AFTER page_group,
  ADD COLUMN source VARCHAR(80) NULL AFTER channel,
  ADD COLUMN medium VARCHAR(80) NULL AFTER source,
  ADD COLUMN campaign VARCHAR(150) NULL AFTER medium,
  ADD COLUMN gclid VARCHAR(255) NULL AFTER campaign,
  ADD COLUMN fbclid VARCHAR(255) NULL AFTER gclid,
  ADD COLUMN landing_path VARCHAR(255) NULL AFTER fbclid,
  ADD COLUMN referrer_host VARCHAR(190) NULL AFTER landing_path,
  ADD COLUMN first_channel VARCHAR(30) NULL AFTER referrer_host,
  ADD COLUMN first_source VARCHAR(80) NULL AFTER first_channel,
  ADD COLUMN visitor_id VARCHAR(40) NULL AFTER first_source,
  ADD COLUMN extra TEXT NULL AFTER visitor_id,
  ADD COLUMN lead_status ENUM('new','contacted','qualified','booked','lost') NOT NULL DEFAULT 'new' AFTER user_agent,
  ADD KEY idx_created (created_at),
  ADD KEY idx_channel (channel, source),
  ADD KEY idx_campaign (campaign),
  ADD KEY idx_source_path (source_path),
  ADD KEY idx_lead_type (lead_type),
  ADD KEY idx_phone (phone),
  ADD KEY idx_visitor (visitor_id);

-- Old rows have a source_url but no path; fill it in (the site's pages are all one level deep).
UPDATE booking_enquiries
   SET source_path = CONCAT('/', SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(source_url, '#', 1), '?', 1), '/', -1))
 WHERE source_url IS NOT NULL AND source_path IS NULL;
