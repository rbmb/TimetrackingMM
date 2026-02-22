-- TimeTracking MM Database Schema

CREATE DATABASE IF NOT EXISTS timetracking_mm
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE timetracking_mm;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Workstations reference table
CREATE TABLE IF NOT EXISTS workstations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    label VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Time entries table
-- Each half-day can optionally be split into 4 hourly slots
-- slot: 0 = whole morning, 1 = whole afternoon
-- slot: 1-4 = morning hours, 5-8 = afternoon hours (when detailed)
CREATE TABLE IF NOT EXISTS time_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    entry_date DATE NOT NULL,
    period ENUM('morning','afternoon') NOT NULL,
    slot TINYINT NOT NULL DEFAULT 0 COMMENT '0=whole half-day, 1-4=hourly detail within half-day',
    workstation_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (workstation_id) REFERENCES workstations(id),
    UNIQUE KEY uk_entry (user_id, entry_date, period, slot)
) ENGINE=InnoDB;

-- Insert default workstations
INSERT INTO workstations (code, label, sort_order) VALUES
    ('poste1', 'Poste 1', 1),
    ('poste2', 'Poste 2', 2),
    ('poste3', 'Poste 3', 3),
    ('poste4', 'Poste 4', 4),
    ('poste5', 'Poste 5', 5),
    ('poste6', 'Poste 6', 6),
    ('poste7', 'Poste 7', 7),
    ('poste8', 'Poste 8', 8),
    ('volant', 'Volant', 9),
    ('automateAB', 'Automate AB', 10),
    ('automateCD', 'Automate CD', 11),
    ('controle_automate', 'Contrôle automate', 12),
    ('frigo', 'Frigo', 13),
    ('magasin_manuel', 'Magasin manuel', 14),
    ('vaccin', 'Vaccin', 15),
    ('volume', 'Volume', 16),
    ('autre', 'Autre', 17);

-- Insert a default admin user (password: admin123 - change in production!)
-- Password hash for 'admin123' using PHP password_hash with PASSWORD_BCRYPT
INSERT INTO users (username, password_hash, display_name, is_admin) VALUES
    ('admin', '$2y$12$/pUw3NSn4ssIbAfIEYeD2OhQAMXddRoewrTPpOOeSVMS.4v/xsrwO', 'Administrateur', 1);
