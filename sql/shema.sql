-- =====================================================================
--  BLACK COLLECTION - Base de données complète (MySQL 8 / MariaDB 10)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS black_collection
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE black_collection;

-- 1. ADMINISTRATEURS
CREATE TABLE IF NOT EXISTS admins (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom                VARCHAR(100) NOT NULL,
  email              VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe       VARCHAR(255) NOT NULL,
  derniere_connexion DATETIME NULL,
  cree_le            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. TENTATIVES DE CONNEXION
CREATE TABLE IF NOT EXISTS tentatives_connexion (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email    VARCHAR(150) NOT NULL,
  ip       VARCHAR(45)  NOT NULL,
  reussie  TINYINT(1)   NOT NULL DEFAULT 0,
  cree_le  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_date (ip, cree_le),
  INDEX idx_email_date (email, cree_le)
) ENGINE=InnoDB;

-- 3. CATEGORIES
CREATE TABLE IF NOT EXISTS categories (
  id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom    VARCHAR(80)  NOT NULL,
  slug   VARCHAR(100) NOT NULL UNIQUE,
  type   ENUM('parfum','vetement') NOT NULL DEFAULT 'parfum',
  ordre  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- 4. PRODUITS (parfums et vêtements)
CREATE TABLE IF NOT EXISTS produits (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type              ENUM('parfum','vetement') NOT NULL DEFAULT 'parfum',
  nom               VARCHAR(150) NOT NULL,
  slug              VARCHAR(170) NOT NULL UNIQUE,
  categorie_id      INT UNSIGNED NULL,
  style             VARCHAR(100) NULL,
  notes_olfactives  TEXT NULL,
  description       TEXT NULL,
  contenance        VARCHAR(30)  NULL,
  prix              INT UNSIGNED NOT NULL,
  disponibilite     ENUM('en_stock','rupture') NOT NULL DEFAULT 'en_stock',
  tailles           VARCHAR(100) NULL,
  couleurs          VARCHAR(100) NULL,
  mis_en_avant      TINYINT(1) NOT NULL DEFAULT 0,
  visible           TINYINT(1) NOT NULL DEFAULT 1,
  cree_le           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  modifie_le        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_produit_categorie FOREIGN KEY (categorie_id)
    REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_type_visible (type, visible),
  INDEX idx_categorie (categorie_id),
  INDEX idx_une (mis_en_avant, visible)
) ENGINE=InnoDB;

-- 5. IMAGES DES PRODUITS
CREATE TABLE IF NOT EXISTS produit_images (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  produit_id  INT UNSIGNED NOT NULL,
  chemin      VARCHAR(255) NOT NULL,
  principale  TINYINT(1) NOT NULL DEFAULT 0,
  ordre       INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_image_produit FOREIGN KEY (produit_id)
    REFERENCES produits(id) ON DELETE CASCADE,
  INDEX idx_produit (produit_id)
) ENGINE=InnoDB;

-- 6. PARAMETRES DU SITE
CREATE TABLE IF NOT EXISTS parametres (
  cle     VARCHAR(50) PRIMARY KEY,
  valeur  TEXT NULL
) ENGINE=InnoDB;

-- DONNEES DE DEPART
INSERT IGNORE INTO categories (nom, slug, type, ordre) VALUES
  ('Parfums Homme', 'homme', 'parfum', 1),
  ('Parfums Femme', 'femme', 'parfum', 2);

INSERT IGNORE INTO parametres (cle, valeur) VALUES
  ('nom_marque',        'Black Collection'),
  ('slogan',            'L''élégance a son côté sombre.'),
  ('whatsapp',          '2250554971592'),
  ('wave_business',     ''),
  ('livraison_minimum', '5'),
  ('livraison_zone',    'Abidjan'),
  ('instagram',         ''),
  ('tiktok',            ''),
  ('facebook',          ''),
  ('adresse',           '');