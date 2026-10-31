-- Chrysalide : tables MySQL.
-- À exécuter UNE FOIS dans phpMyAdmin (IONOS : Bases de données → ouvrir la base → onglet SQL).
-- Ne pas déposer ce fichier sur le site.

CREATE TABLE IF NOT EXISTS chry_users (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(80)  NOT NULL,
  code_hash  CHAR(64)     NOT NULL UNIQUE,          -- SHA-256 du code personnel
  cats       TEXT         NULL,                     -- catégories ajoutées (JSON)
  cats_ts    BIGINT       NOT NULL DEFAULT 0,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chry_notes (
  user_id    INT UNSIGNED NOT NULL,
  id         VARCHAR(40)  NOT NULL,                 -- identifiant créé par le téléphone
  data       MEDIUMTEXT   NOT NULL,                 -- la fiche en JSON (photo comprise)
  ts         BIGINT       NOT NULL,                 -- date de dernière modification (ms)
  deleted    TINYINT(1)   NOT NULL DEFAULT 0,
  rev        BIGINT       NOT NULL,                 -- numéro de version côté serveur
  PRIMARY KEY (user_id, id),
  KEY by_rev (user_id, rev),
  FOREIGN KEY (user_id) REFERENCES chry_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chry_counter (
  k TINYINT NOT NULL PRIMARY KEY,
  v BIGINT  NOT NULL
) ENGINE=InnoDB;
INSERT IGNORE INTO chry_counter (k, v) VALUES (1, 0);
