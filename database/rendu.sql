CREATE DATABASE IF NOT EXISTS plusoumoins CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE plusoumoins;

DROP TABLE IF EXISTS partie;
DROP TABLE IF EXISTS joueur;
DROP TABLE IF EXISTS niveau;

-- Qui joue
CREATE TABLE joueur (
    id_joueur INT NOT NULL AUTO_INCREMENT,
    pseudo VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,  -- mot de passe hashé avec password_hash()
    PRIMARY KEY (id_joueur),
    UNIQUE (pseudo)                      -- deux joueurs ne peuvent pas avoir le même pseudo
);

-- Les règles du jeu : une ligne = un niveau
CREATE TABLE niveau (
    id_niveau INT NOT NULL AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    nombre_min INT NOT NULL,             -- début de la plage du nombre secret
    nombre_max INT NOT NULL,             -- fin de la plage du nombre secret
    essais_max INT NOT NULL,             -- nombre d'essais autorisés
    PRIMARY KEY (id_niveau)
);

-- Chaque partie terminée (gagnée ou perdue)
CREATE TABLE partie (
    id_partie INT NOT NULL AUTO_INCREMENT,
    id_joueur INT NOT NULL,              -- quel joueur
    id_niveau INT NOT NULL,              -- quel niveau
    nombre_secret INT NOT NULL,
    essais_utilises INT NOT NULL,
    gagnee BOOLEAN NOT NULL,             -- 1 = gagnée, 0 = perdue
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,          -- la durée se calcule avec date_fin - date_debut
    PRIMARY KEY (id_partie),
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur),
    FOREIGN KEY (id_niveau) REFERENCES niveau(id_niveau)
);

-- Les 4 niveaux du sujet
INSERT INTO niveau (nom, nombre_min, nombre_max, essais_max) VALUES
('Facile', 1, 50, 10),
('Normal', 1, 100, 8),
('Difficile', 1, 500, 10),
('Expert', 1, 1000, 10);
