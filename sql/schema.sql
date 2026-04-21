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

-- Muuttaa hintatekijää asiakkaan maksuhistorian perusteella:
--   1.30 jos erääntyneitä laskuja maksamatta
--   1.10 jos kaikki maksettu mutta karhulasku (lasku nro. 3+) viimeisen 2 vuoden aikana
--   1.00 muuten
CREATE OR REPLACE FUNCTION laske_hintatekija_asiakkaalle(p_asiakas_id INT)
RETURNS NUMERIC AS $$
DECLARE
    maksamattomia INT;
    karhu_viimeisen_2v INT;
BEGIN
    SELECT COUNT(*) INTO maksamattomia
    FROM Lasku l
    JOIN Sopimus s ON l.sopimus_id = s.sopimus_id
    JOIN Tyokohde tk ON s.kohde_id = tk.kohde_id
    WHERE tk.asiakas_id = p_asiakas_id
      AND l.maksupaiva IS NULL;

    IF maksamattomia > 0 THEN
        RETURN 1.30;
    END IF;

    SELECT COUNT(*) INTO karhu_viimeisen_2v
    FROM (
        WITH RECURSIVE lasku_ketju AS (
            SELECT lasku_id, edellinen_lasku_id, sopimus_id, Pvm, 1 AS lasku_nro
            FROM Lasku
            WHERE edellinen_lasku_id IS NULL
            UNION ALL
            SELECT l.lasku_id, l.edellinen_lasku_id, l.sopimus_id, l.Pvm, lk.lasku_nro + 1
            FROM Lasku l
            JOIN lasku_ketju lk ON l.edellinen_lasku_id = lk.lasku_id
        )
        SELECT lk.lasku_id
        FROM lasku_ketju lk
        JOIN Sopimus s ON lk.sopimus_id = s.sopimus_id
        JOIN Tyokohde tk ON s.kohde_id = tk.kohde_id
        WHERE tk.asiakas_id = p_asiakas_id
          AND lk.lasku_nro >= 3
          AND lk.Pvm >= CURRENT_DATE - INTERVAL '2 years'
    ) karhu_laskut;

    IF karhu_viimeisen_2v > 0 THEN
        RETURN 1.10;
    END IF;

    RETURN 1.00;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trg_fn_hintatekija_sopimus_tarvike()
RETURNS TRIGGER AS $$
DECLARE
    v_asiakas_id INT;
    v_tyyppi VARCHAR(50);
BEGIN
    SELECT tk.asiakas_id, s.tyyppi
    INTO v_asiakas_id, v_tyyppi
    FROM Sopimus s
    JOIN Tyokohde tk ON s.kohde_id = tk.kohde_id
    WHERE s.sopimus_id = NEW.sopimus_id;

    IF v_tyyppi != 'Urakka' THEN
        RETURN NEW;
    END IF;

    NEW.hintatekija := NEW.hintatekija * laske_hintatekija_asiakkaalle(v_asiakas_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trg_fn_hintatekija_sopimus_suoritus()
RETURNS TRIGGER AS $$
DECLARE
    v_asiakas_id INT;
    v_tyyppi VARCHAR(50);
BEGIN
    SELECT tk.asiakas_id, s.tyyppi
    INTO v_asiakas_id, v_tyyppi
    FROM Sopimus s
    JOIN Tyokohde tk ON s.kohde_id = tk.kohde_id
    WHERE s.sopimus_id = NEW.sopimus_id;

    IF v_tyyppi != 'Urakka' THEN
        RETURN NEW;
    END IF;

    NEW.hintatekija := NEW.hintatekija * laske_hintatekija_asiakkaalle(v_asiakas_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_hintatekija_tarvike
BEFORE INSERT OR UPDATE ON Sopimus_tarvike
FOR EACH ROW EXECUTE FUNCTION trg_fn_hintatekija_sopimus_tarvike();

CREATE TRIGGER trg_hintatekija_suoritus
BEFORE INSERT OR UPDATE ON Sopimus_suoritus
FOR EACH ROW EXECUTE FUNCTION trg_fn_hintatekija_sopimus_suoritus();
