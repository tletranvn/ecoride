
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    pseudo VARCHAR(50),
    credits INT DEFAULT 20,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NULL,
    apiToken VARCHAR(255),
    userType VARCHAR(50),
    isActive BOOLEAN DEFAULT TRUE,
    photo VARCHAR(255)
);

CREATE TABLE vehicule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    marque VARCHAR(100),
    modele VARCHAR(100),
    immatriculation VARCHAR(20),
    typeEnergie VARCHAR(50),
    preferChien BOOLEAN DEFAULT FALSE,
    preferFumeur BOOLEAN DEFAULT FALSE,
    places INT,
    preferencesLibres TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES user(id) ON DELETE CASCADE
);

CREATE TABLE trajet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chauffeur_id INT NOT NULL,
    vehicule_id INT NOT NULL,
    villeDepart VARCHAR(100),
    villeArrivee VARCHAR(100),
    dateDepart DATETIME,
    placesTotal INT,
    placesRestantes INT,
    prix DECIMAL(10,2),
    isEcoCertifie BOOLEAN DEFAULT FALSE,
    duree INT,
    isStarted BOOLEAN DEFAULT FALSE,
    isCompleted BOOLEAN DEFAULT FALSE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NULL,
    FOREIGN KEY (chauffeur_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicule_id) REFERENCES vehicule(id) ON DELETE CASCADE
);

CREATE TABLE participation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trajet_id INT NOT NULL,
    creditsUtilises INT,
    trajetValide BOOLEAN DEFAULT FALSE,
    commentaire TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (trajet_id) REFERENCES trajet(id) ON DELETE CASCADE
);

CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    auteur_id INT NOT NULL,
    cible_id INT NOT NULL,
    trajet_id INT NOT NULL,
    note INT CHECK (note >= 1 AND note <= 5),
    commentaire TEXT,
    isValidated BOOLEAN DEFAULT FALSE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NULL,
    FOREIGN KEY (auteur_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (cible_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (trajet_id) REFERENCES trajet(id) ON DELETE CASCADE
);

CREATE TABLE employe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    poste VARCHAR(50),
    dateEmbauche DATE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);

CREATE TABLE administrateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    niveau VARCHAR(50),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
