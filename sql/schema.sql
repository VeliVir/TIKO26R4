DROP TABLE IF EXISTS Sopimus_suoritus CASCADE;
DROP TABLE IF EXISTS Sopimus_tarvike CASCADE;
DROP TABLE IF EXISTS Lasku CASCADE;
DROP TABLE IF EXISTS Sopimus CASCADE;
DROP TABLE IF EXISTS Tyokohde CASCADE;
DROP TABLE IF EXISTS Asiakas CASCADE;
DROP TABLE IF EXISTS Tarvike CASCADE;
DROP TABLE IF EXISTS Toimittaja CASCADE;
DROP TABLE IF EXISTS Suoritus CASCADE;

CREATE TABLE Asiakas (
    asiakas_id SERIAL PRIMARY KEY,
    etunimi VARCHAR(50) NOT NULL,
    sukunimi VARCHAR(50) NOT NULL,
    osoite VARCHAR(100),
    puhelinnro VARCHAR(50),
    sahkoposti VARCHAR(50),
    luotu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    muokattu TIMESTAMP
);

CREATE TABLE Tyokohde (
    kohde_id SERIAL PRIMARY KEY,
    asiakas_id INT NOT NULL REFERENCES Asiakas(asiakas_id),
    nimi VARCHAR(50) NOT NULL,
    osoite VARCHAR(100),
    luotu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    muokattu TIMESTAMP
);
 
CREATE TABLE Sopimus (
    sopimus_id SERIAL PRIMARY KEY,
    kohde_id INT NOT NULL REFERENCES Tyokohde(kohde_id),
    tyyppi VARCHAR(50),
    osia_laskussa INT,
    luotu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    muokattu TIMESTAMP
);
 
CREATE TABLE Lasku (
    lasku_id SERIAL PRIMARY KEY,
    sopimus_id INT NOT NULL REFERENCES Sopimus(sopimus_id),
    edellinen_lasku_id INT REFERENCES Lasku(lasku_id),
    Pvm DATE NOT NULL,
    erapaiva DATE NOT NULL,
    maksupaiva DATE
);

CREATE TABLE Toimittaja (
    toimittaja_id SERIAL PRIMARY KEY,
    nimi VARCHAR(50),
    osoite VARCHAR(100)
);

CREATE TABLE Tarvike (
    tarvike_id SERIAL PRIMARY KEY,
    toimittaja_id INT NOT NULL REFERENCES Toimittaja(toimittaja_id),
    nimi VARCHAR(50),
    merkki VARCHAR(50),
    yksikko VARCHAR(50),
    hankintahinta NUMERIC(12,2),
    varastossa INT,
    alv NUMERIC(12,2) DEFAULT 1.24,
    luotu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    muokattu TIMESTAMP ,
    poistettu TIMESTAMP
);

CREATE TABLE Sopimus_tarvike (
    sopimus_id INT NOT NULL REFERENCES Sopimus(sopimus_id),
    tarvike_id INT NOT NULL REFERENCES Tarvike(tarvike_id),
    maara NUMERIC(12,3),
    hintatekija NUMERIC(12,2) DEFAULT 1.00,
    PRIMARY KEY (sopimus_id, tarvike_id)
);

CREATE TABLE Suoritus (
    suoritus_id SERIAL PRIMARY KEY,
    nimi VARCHAR(50),
    hinta NUMERIC(12,2)
);

CREATE TABLE Sopimus_suoritus (
    suoritus_id INT NOT NULL REFERENCES Suoritus(suoritus_id),
    sopimus_id INT NOT NULL REFERENCES Sopimus(sopimus_id),
    tyomaara_tunneilla NUMERIC(12,3),
    hintatekija NUMERIC(12,2) DEFAULT 1.00,
    urakka_hinta NUMERIC(12,2),
    PRIMARY KEY (sopimus_id, suoritus_id)
);

CREATE OR REPLACE FUNCTION paivita_muokattu_sarake()
RETURNS TRIGGER AS $$
BEGIN
    NEW.muokattu = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER trg_paivita_muokattu_asiakas
BEFORE UPDATE ON Asiakas
FOR EACH ROW EXECUTE FUNCTION paivita_muokattu_sarake();

CREATE TRIGGER trg_paivita_muokattu_tyokohde
BEFORE UPDATE ON Tyokohde
FOR EACH ROW EXECUTE FUNCTION paivita_muokattu_sarake();

CREATE TRIGGER trg_paivita_muokattu_sopimus
BEFORE UPDATE ON Sopimus
FOR EACH ROW EXECUTE FUNCTION paivita_muokattu_sarake();

CREATE TRIGGER trg_paivita_muokattu_tarvike
BEFORE UPDATE ON Tarvike
FOR EACH ROW EXECUTE FUNCTION paivita_muokattu_sarake();
