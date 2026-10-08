-- =====================================================================
--  BLACK COLLECTION - Clients & commandes
--  À exécuter dans phpMyAdmin (InfinityFree) > onglet SQL
-- =====================================================================

CREATE TABLE IF NOT EXISTS clients (
  id                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom                  VARCHAR(150) NOT NULL,
  telephone            VARCHAR(20)  NOT NULL,           -- format 225XXXXXXXXXX
  adresse              VARCHAR(255) NOT NULL,
  cree_le              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  modifie_le           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_clients_telephone (telephone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commandes (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  reference     VARCHAR(30)  NOT NULL,
  client_id     INT UNSIGNED NULL,
  nom_client    VARCHAR(150) NOT NULL,                  -- copie figée au moment de la commande
  telephone     VARCHAR(20)  NOT NULL,
  adresse       VARCHAR(255) NOT NULL,
  paiement      ENUM('wave','especes') NOT NULL,
  note          TEXT NULL,
  nb_articles   INT UNSIGNED NOT NULL DEFAULT 0,
  nb_parfums    INT UNSIGNED NOT NULL DEFAULT 0,
  total         INT UNSIGNED NOT NULL DEFAULT 0,
  statut        ENUM('nouvelle','confirmee','livree','annulee') NOT NULL DEFAULT 'nouvelle',
  cree_le       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_commandes_reference (reference),
  KEY idx_commandes_client (client_id),
  KEY idx_commandes_statut (statut),
  CONSTRAINT fk_commandes_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commande_items (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  commande_id  INT UNSIGNED NOT NULL,
  produit_id   INT UNSIGNED NULL,
  nom          VARCHAR(200) NOT NULL,
  type         VARCHAR(20)  NOT NULL,
  contenance   VARCHAR(50)  NULL,
  prix         INT UNSIGNED NOT NULL,                   -- prix unitaire au moment de la commande
  qte          INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_items_commande (commande_id),
  CONSTRAINT fk_items_commande FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
