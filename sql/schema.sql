CREATE DATABASE IF NOT EXISTS `emmanuel-bissa-portfolio`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `emmanuel-bissa-portfolio`;

CREATE TABLE IF NOT EXISTS admin(
    id INT AUTO_INCREMENT PRIMARY KEY,
    lastname VARCHAR(255) NOT NULL,
    firstname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS informations(
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(1000),
    profile_image LONGTEXT,
    linkedin_link VARCHAR(255),
    github_link VARCHAR(255),
    facebook_link VARCHAR(255),
    instagram_link VARCHAR(255),
    twitter_link VARCHAR(255),
    last_update DATE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS job_titles(
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_information INT NOT NULL,
    title VARCHAR(255),
    display_order INT DEFAULT 0,
    CONSTRAINT fk_job_titles_id_information
        FOREIGN KEY (id_information) REFERENCES informations(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS educations(
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_name VARCHAR(255) NOT NULL,
    sector VARCHAR(255) NOT NULL,
    year VARCHAR(255) NOT NULL,
    description VARCHAR(1000) NOT NULL,
    display_order INT DEFAULT 0,
    last_update DATE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(1000) NOT NULL,
    display_order INT DEFAULT 0,
    last_update DATE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projets(
    id INT AUTO_INCREMENT PRIMARY KEY,
    icon VARCHAR(255) DEFAULT 'bx bx-laptop',
    title VARCHAR(255) NOT NULL,
    description VARCHAR(1000) NOT NULL,
    code_source_link VARCHAR(255) NOT NULL,
    action_link VARCHAR(255) NOT NULL,
    action_name VARCHAR(50) DEFAULT 'visiter',
    display_order INT DEFAULT 0,
    last_update DATE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS langage_projet(
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_projet INT NOT NULL ,
    langage VARCHAR(255) NOT NULL,
    CONSTRAINT fk_langage_projet_id_projet
        FOREIGN KEY (id_projet) REFERENCES projets(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS certificats(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_path LONGTEXT NOT NULL,
    display_order INT DEFAULT 0,
    last_update DATE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web3forms(
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key VARCHAR(255),
    last_update DATE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS skills(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    percentage INT NOT NULL DEFAULT 80,
    display_order INT DEFAULT 0,
    last_update DATE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admin(lastname, firstname, email, password_hash) 
VALUES ('Bissa', 'Emmanuel', 'emmanuelbissa0000@gmail.com','$2y$10$4gfOZ.swgKjsyS6ludlPcuuc9eqFctX1GRdQrMRLAIcafgv4yTHQ6');

INSERT INTO skills (name, percentage, display_order, last_update) VALUES
('HTML / CSS / BOOTSTRAP', 95, 1, NOW()),
('REACT', 85, 2, NOW()),
('PHP / MYSQL / SQLITE', 80, 3, NOW()),
('GODOT (GD.SCRIPT)', 80, 4, NOW()),
('GDEVELOP 5', 95, 5, NOW()),
('NODE.JS', 85, 6, NOW());