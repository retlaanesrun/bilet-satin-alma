PRAGMA foreign_keys=ON;

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK(role IN ('admin','firm_admin','user')),
  firm_id INTEGER,
  credit INTEGER NOT NULL DEFAULT 0, -- Kuruş bazlı tutar
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS firms (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS trips (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  firm_id INTEGER NOT NULL,
  origin TEXT NOT NULL,
  destination TEXT NOT NULL,
  departure_ts INTEGER NOT NULL, -- epoch seconds
  price INTEGER NOT NULL, -- kuruş
  seat_count INTEGER NOT NULL DEFAULT 40,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tickets (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  trip_id INTEGER NOT NULL,
  seat_no INTEGER NOT NULL,
  price_paid INTEGER NOT NULL,
  status TEXT NOT NULL CHECK(status IN ('active','cancelled')) DEFAULT 'active',
  purchased_at TEXT NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
  UNIQUE(trip_id, seat_no)
);

CREATE TABLE IF NOT EXISTS coupons (
  code TEXT PRIMARY KEY,
  percent INTEGER NOT NULL CHECK(percent BETWEEN 1 AND 100),
  usage_limit INTEGER NOT NULL,
  used_count INTEGER NOT NULL DEFAULT 0,
  expires_at INTEGER NOT NULL
);

-- Seed data
INSERT OR IGNORE INTO firms (id, name) VALUES (1, 'ChatBus'), (2, 'Nursena Lines');

-- admin user (password 'admin123')
INSERT OR IGNORE INTO users (id, name, email, password_hash, role, credit) VALUES
(1,'Admin','admin@example.com','{ADMIN_HASH}','admin',0);

-- sample firm admin (password 'firm123')
INSERT OR IGNORE INTO users (id, name, email, password_hash, role, firm_id, credit) VALUES
(2,'Firma Yetkilisi','firma@example.com','{FIRM_HASH}','firm_admin',1,0);

-- sample user (password 'user123')
INSERT OR IGNORE INTO users (id, name, email, password_hash, role, credit) VALUES
(3,'Yolcu','user@example.com','{USER_HASH}','user',500000); -- 5000.00 TL
