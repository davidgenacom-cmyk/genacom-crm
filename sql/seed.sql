-- Default admin — password: password (change immediately in production)
SET NAMES utf8mb4;

INSERT INTO users (email, password_hash, name, role) VALUES
(
  'admin@genacom.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'Genacom Admin',
  'admin'
);

-- Demo records (optional — remove on production if undesired)
INSERT INTO companies (name, website, phone, city, state, notes) VALUES
('Sample Client LLC', 'https://example.com', '(415) 555-0100', 'San Francisco', 'CA', 'Imported demo row.');

INSERT INTO contacts (company_id, first_name, last_name, email, phone, title, status, notes) VALUES
(1, 'Alex', 'Rivera', 'alex@example.com', '(415) 555-0101', 'Marketing Director', 'prospect', 'Met at networking event.');

INSERT INTO deals (company_id, contact_id, title, stage, value, expected_close, notes) VALUES
(1, 1, 'Website redesign — Phase 1', 'proposal', 12500.00, DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'Proposal sent.');

INSERT INTO activities (contact_id, deal_id, user_id, type, subject, body, created_at) VALUES
(1, 1, 1, 'call', 'Discovery call', 'Discussed scope and timeline.', NOW());
