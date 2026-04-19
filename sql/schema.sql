-- Genacom CRM — MySQL 8+ / MariaDB 10.3+
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS deals;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(120) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE companies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  website VARCHAR(512) DEFAULT NULL,
  phone VARCHAR(64) DEFAULT NULL,
  city VARCHAR(120) DEFAULT NULL,
  state VARCHAR(64) DEFAULT NULL,
  notes TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_companies_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contacts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED DEFAULT NULL,
  first_name VARCHAR(120) NOT NULL,
  last_name VARCHAR(120) NOT NULL,
  email VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(64) DEFAULT NULL,
  title VARCHAR(120) DEFAULT NULL,
  status ENUM('lead','prospect','customer','inactive') NOT NULL DEFAULT 'lead',
  notes TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_contacts_company (company_id),
  KEY idx_contacts_status (status),
  CONSTRAINT fk_contacts_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE deals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED DEFAULT NULL,
  contact_id INT UNSIGNED DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  stage ENUM('qualification','proposal','negotiation','won','lost') NOT NULL DEFAULT 'qualification',
  value DECIMAL(14,2) NOT NULL DEFAULT 0,
  expected_close DATE DEFAULT NULL,
  notes TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_deals_company (company_id),
  KEY idx_deals_contact (contact_id),
  KEY idx_deals_stage (stage),
  CONSTRAINT fk_deals_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_deals_contact FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  contact_id INT UNSIGNED DEFAULT NULL,
  deal_id INT UNSIGNED DEFAULT NULL,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('call','email','meeting','note','task') NOT NULL DEFAULT 'note',
  subject VARCHAR(255) DEFAULT NULL,
  body TEXT,
  due_at DATETIME DEFAULT NULL,
  completed_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_act_contact (contact_id),
  KEY idx_act_deal (deal_id),
  KEY idx_act_user (user_id),
  CONSTRAINT fk_act_contact FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_act_deal FOREIGN KEY (deal_id) REFERENCES deals (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_act_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
