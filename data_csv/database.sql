/* commande d'execution
	mysql --local-infile=1 -u root -p < database.sql 
*/
/* =========================================================
   WORLD DATABASE – CREATION + IMPORT CSV
   ========================================================= */

/* ---------- Sécurité / compatibilité ---------- */
SET GLOBAL local_infile = 1;

/* ---------- Suppression si existe ---------- */
DROP DATABASE IF EXISTS world;

/* ---------- Création base ---------- */
CREATE DATABASE world
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE world;

/* =========================================================
   TABLE: country
   ========================================================= */
CREATE TABLE country (
    Code CHAR(3) NOT NULL,
    Name VARCHAR(100),
    Continent VARCHAR(50),
    Region VARCHAR(50),
    SurfaceArea FLOAT,
    IndepYear INT,
    Population INT,
    LifeExpectancy FLOAT,
    GNP FLOAT,
    GNPOld FLOAT,
    LocalName VARCHAR(100),
    GovernmentForm VARCHAR(100),
    HeadOfState VARCHAR(100),
    Capital INT,
    Code2 CHAR(2),
    PRIMARY KEY (Code)
) ENGINE=InnoDB;

/* =========================================================
   TABLE: city
   ========================================================= */
CREATE TABLE city (
    ID INT NOT NULL,
    Name VARCHAR(100),
    CountryCode CHAR(3),
    District VARCHAR(100),
    Population INT,
    PRIMARY KEY (ID),
    INDEX idx_countrycode (CountryCode)
) ENGINE=InnoDB;

/* =========================================================
   TABLE: countrylanguage
   ========================================================= */
CREATE TABLE countrylanguage (
    CountryCode CHAR(3),
    Language VARCHAR(50),
    IsOfficial CHAR(1),
    Percentage FLOAT,
    PRIMARY KEY (CountryCode, Language)
) ENGINE=InnoDB;

/* =========================================================
   IMPORT CSV FILES
   ========================================================= */

/* ---------- COUNTRY ---------- */
LOAD DATA LOCAL INFILE 'country.csv'
INTO TABLE country
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES;

/* ---------- CITY ---------- */
LOAD DATA LOCAL INFILE 'city.csv'
INTO TABLE city
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES;

/* ---------- COUNTRY LANGUAGE ---------- */
LOAD DATA LOCAL INFILE 'countrylanguage.csv'
INTO TABLE countrylanguage
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES;

/* =========================================================
   FOREIGN KEYS (optionnel mais recommandé)
   ========================================================= */
ALTER TABLE city
ADD CONSTRAINT fk_city_country
FOREIGN KEY (CountryCode)
REFERENCES country(Code);

ALTER TABLE countrylanguage
ADD CONSTRAINT fk_language_country
FOREIGN KEY (CountryCode)
REFERENCES country(Code);

ALTER TABLE country
ADD CONSTRAINT fk_country_capital
FOREIGN KEY (Capital)
REFERENCES city(ID);

/* =========================================================
   VERIFICATION
   ========================================================= */
SELECT COUNT(*) AS nb_pays FROM country;
SELECT COUNT(*) AS nb_villes FROM city;
SELECT COUNT(*) AS nb_langues FROM countrylanguage;

