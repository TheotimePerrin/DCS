CREATE DATABASE IF NOT EXISTS campus_it
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE campus_it;

-- =====================================================
-- TABLE : application
-- =====================================================

CREATE TABLE application (
    app_id INT UNSIGNED NOT NULL,
    nom VARCHAR(80) NOT NULL,
    
    PRIMARY KEY (app_id),
    UNIQUE KEY uk_application_nom (nom)
) ENGINE=InnoDB;


-- =====================================================
-- TABLE : ressource
-- =====================================================

CREATE TABLE ressource (
    res_id INT UNSIGNED NOT NULL,
    nom VARCHAR(30) NOT NULL,
    unite VARCHAR(10) NOT NULL,
    
    PRIMARY KEY (res_id),
    UNIQUE KEY uk_ressource_nom (nom)
) ENGINE=InnoDB;


-- =====================================================
-- TABLE : consommation
-- =====================================================

CREATE TABLE consommation (
    conso_id INT UNSIGNED NOT NULL,
    app_id INT UNSIGNED NOT NULL,
    res_id INT UNSIGNED NOT NULL,
    mois DATE NOT NULL,
    volume DECIMAL(10,2) NOT NULL,
    
    PRIMARY KEY (conso_id),
    
    KEY idx_conso_app (app_id),
    KEY idx_conso_res (res_id),
    KEY idx_conso_mois (mois),
    KEY idx_conso_unique (app_id, res_id, mois),
    
    CONSTRAINT fk_conso_application
        FOREIGN KEY (app_id)
        REFERENCES application(app_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_conso_ressource
        FOREIGN KEY (res_id)
        REFERENCES ressource(res_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB;