-- =============================================================
-- CAPK 2-1-1 Community Resource Directory
-- Database Schema
-- =============================================================

USE capk_db;

-- =============================================================
-- GROUP: organization_management
-- =============================================================

CREATE TABLE agencies (
  agency_id         INT PRIMARY KEY AUTO_INCREMENT,
  name              VARCHAR(255) NOT NULL,
  description       TEXT,
  website           VARCHAR(500),
  email             VARCHAR(255),
  phone_number      VARCHAR(50),
  tax_status        VARCHAR(50),
  legal_status      VARCHAR(100),
  ein               VARCHAR(20),
  incorporated_year INT,
  is_active         BOOLEAN,
  created_at        DATETIME,
  updated_at        DATETIME
);

CREATE TABLE accounts (
  aid           INT PRIMARY KEY AUTO_INCREMENT,
  agency_id     INT,
  email         VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  first_name    VARCHAR(100),
  last_name     VARCHAR(100),
  role          VARCHAR(20) NOT NULL,
  is_active     BOOLEAN,
  revoked_at    DATETIME,
  last_login_at DATETIME,
  created_at    DATETIME,
  updated_at    DATETIME,
  FOREIGN KEY (agency_id) REFERENCES agencies(agency_id)
);

-- =============================================================
-- GROUP: program_core
-- =============================================================

CREATE TABLE programs (
  pid              INT PRIMARY KEY AUTO_INCREMENT,
  agency_id        INT NOT NULL,
  name             VARCHAR(255) NOT NULL,
  description      TEXT,
  resource_number  VARCHAR(20),
  is_active        BOOLEAN,
  last_verified_at DATETIME,
  last_verified_by INT,
  created_at       DATETIME,
  updated_at       DATETIME,
  FOREIGN KEY (agency_id) REFERENCES agencies(agency_id)
);

CREATE TABLE program_details (
  id              INT PRIMARY KEY AUTO_INCREMENT,
  program_id      INT NOT NULL,
  attribute_name  VARCHAR(100),
  attribute_value TEXT,
  created_at      DATETIME,
  updated_at      DATETIME,
  FOREIGN KEY (program_id) REFERENCES programs(pid)
);

CREATE TABLE program_delivery (
  id                  INT PRIMARY KEY AUTO_INCREMENT,
  program_id          INT NOT NULL,
  eligibility         TEXT,
  application_process TEXT,
  payment_methods     VARCHAR(255),
  fees                VARCHAR(255),
  documents_required  TEXT,
  created_at          DATETIME,
  updated_at          DATETIME,
  FOREIGN KEY (program_id) REFERENCES programs(pid)
);

CREATE TABLE program_phones (
  id           INT PRIMARY KEY AUTO_INCREMENT,
  program_id   INT NOT NULL,
  phone_number VARCHAR(50) NOT NULL,
  phone_type   VARCHAR(50),
  is_primary   BOOLEAN,
  created_at   DATETIME,
  updated_at   DATETIME,
  FOREIGN KEY (program_id) REFERENCES programs(pid)
);

-- =============================================================
-- GROUP: locations_and_coverage
-- =============================================================

CREATE TABLE locations (
  lid                 INT PRIMARY KEY AUTO_INCREMENT,
  name                VARCHAR(255),
  address             VARCHAR(255),
  city                VARCHAR(120),
  county              VARCHAR(120),
  state               VARCHAR(60),
  zip                 VARCHAR(20),
  latitude            DECIMAL(10,7),
  longitude           DECIMAL(10,7),
  bus_service         VARCHAR(255),
  disabilities_access VARCHAR(255),
  parking_notes       VARCHAR(255),
  is_private          BOOLEAN,
  created_at          DATETIME,
  updated_at          DATETIME
);

CREATE TABLE program_locations (
  id          INT PRIMARY KEY AUTO_INCREMENT,
  program_id  INT NOT NULL,
  location_id INT NOT NULL,
  is_primary  BOOLEAN,
  site_name   VARCHAR(255),
  site_phone  VARCHAR(50),
  notes       VARCHAR(255),
  created_at  DATETIME,
  updated_at  DATETIME,
  FOREIGN KEY (program_id)  REFERENCES programs(pid),
  FOREIGN KEY (location_id) REFERENCES locations(lid)
);

CREATE TABLE program_location_hours (
  id                  INT PRIMARY KEY AUTO_INCREMENT,
  program_location_id INT NOT NULL,
  day_of_week         VARCHAR(10) NOT NULL,
  opening_time        TIME,
  closing_time        TIME,
  is_closed           BOOLEAN,
  notes               VARCHAR(255),
  FOREIGN KEY (program_location_id) REFERENCES program_locations(id)
);

CREATE TABLE program_service_areas (
  id         INT PRIMARY KEY AUTO_INCREMENT,
  program_id INT NOT NULL,
  state      VARCHAR(60),
  county     VARCHAR(120),
  city       VARCHAR(120),
  zip        VARCHAR(20),
  created_at DATETIME,
  FOREIGN KEY (program_id) REFERENCES programs(pid)
);

-- =============================================================
-- GROUP: taxonomy_and_languages
-- =============================================================

CREATE TABLE languages (
  id   INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(10) NOT NULL,
  name VARCHAR(100) NOT NULL
);

CREATE TABLE program_languages (
  id          INT PRIMARY KEY AUTO_INCREMENT,
  program_id  INT NOT NULL,
  language_id INT NOT NULL,
  FOREIGN KEY (program_id)  REFERENCES programs(pid),
  FOREIGN KEY (language_id) REFERENCES languages(id)
);

CREATE TABLE program_categories (
  id         INT PRIMARY KEY AUTO_INCREMENT,
  name       VARCHAR(255) NOT NULL,
  parent_id  INT,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE program_category_assignments (
  id          INT PRIMARY KEY AUTO_INCREMENT,
  program_id  INT NOT NULL,
  category_id INT NOT NULL,
  created_at  DATETIME,
  FOREIGN KEY (program_id)  REFERENCES programs(pid),
  FOREIGN KEY (category_id) REFERENCES program_categories(id)
);

-- =============================================================
-- GROUP: engagement_and_analytics
-- =============================================================

CREATE TABLE reviews (
  id                INT PRIMARY KEY AUTO_INCREMENT,
  program_id        INT,
  reviewed_by       INT,
  submitted_by_name VARCHAR(255),
  body              TEXT NOT NULL,
  rating            INT,
  status            VARCHAR(20) NOT NULL,
  submitted_at      DATETIME NOT NULL,
  reviewed_at       DATETIME,
  FOREIGN KEY (program_id)  REFERENCES programs(pid),
  FOREIGN KEY (reviewed_by) REFERENCES accounts(aid)
);

CREATE TABLE statistics (
  stat_id       INT PRIMARY KEY AUTO_INCREMENT,
  event_type    VARCHAR(50) NOT NULL,
  occurred_at   DATETIME NOT NULL,
  program_id    INT,
  agency_id     INT,
  location_id   INT,
  search_query  VARCHAR(255),
  search_state  VARCHAR(60),
  search_county VARCHAR(120),
  search_city   VARCHAR(120),
  search_zip    VARCHAR(20),
  FOREIGN KEY (program_id)  REFERENCES programs(pid),
  FOREIGN KEY (agency_id)   REFERENCES agencies(agency_id),
  FOREIGN KEY (location_id) REFERENCES locations(lid)
);

-- =============================================================
-- INVITE SYSTEM 
-- =============================================================

CREATE TABLE invitations (
  id         INT PRIMARY KEY AUTO_INCREMENT,
  token      VARCHAR(64)  NOT NULL UNIQUE,
  email      VARCHAR(255) NOT NULL,
  agency_id  INT          NOT NULL,
  role       VARCHAR(20)  NOT NULL DEFAULT 'agency',
  invited_by INT          NOT NULL,
  used_at    DATETIME     NULL,
  expires_at DATETIME     NOT NULL,
  created_at DATETIME     NOT NULL DEFAULT NOW(),
  FOREIGN KEY (agency_id)  REFERENCES agencies(agency_id),
  FOREIGN KEY (invited_by) REFERENCES accounts(aid)
);
