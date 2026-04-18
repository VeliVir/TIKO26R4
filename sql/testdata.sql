INSERT INTO Toimittaja (nimi, osoite) VALUES 
('How-data', 'Datumpolku 69, 33720 Tampere'),
('Moponet', 'Kaarnankatu 2, Joensuu'),
('Tärsky Pub', 'Homeenkatu 420, Tampere'),
('Junk Co', 'Tietokantakuja 1, Aitolahti');

INSERT INTO Asiakas (etunimi, sukunimi, osoite, puhelinnro, sahkoposti) VALUES 
('Jaska', 'Hosunen', 'Mesikuja 10, Susimetsä', '040 123 4567', 'jaska.hosunen@gmail.com'),
('Lissu', 'Jokinen', 'Nurmitie 5, Aitolahti', '050 987 6543', 'lissu.jokinen@hotmail.com'),
('Masa', 'Näsänen', 'Masalantie 12, Kangasala', '044 321 7689', 'masa.nasanen@tuni.fi');

INSERT INTO Tyokohde (asiakas_id, nimi, osoite) VALUES 
(1, 'Susimetsän asunto', 'Mesikuja 10, Susimetsä'),
(2, 'Nurmitien asunto', 'Nurmitie 5, Aitolahti'),
(2, 'Huitsinnevan toimisto', 'Kasinopolku 11, Huitsinneva'),
(3, 'Puotonkorven tehdas', 'Puotonkorventie 1, Puotonkorpi'),
(3, 'Masalantien asunto', 'Masalantie 12, Kangasala'),
(3, 'Presidentinlinna', 'Mariankatu 2, Helsinki');

INSERT INTO Tarvike (toimittaja_id, nimi, merkki, yksikko, hankintahinta, varastossa) VALUES 
(1, 'USB-kaapeli', 'Deltaco', 'kpl', 4.00, 24),
(2, 'Sähköjohto', 'Harju', 'metri', 1.00, 28),
(3, 'Opaskirja', 'Apustajat', 'kpl', 8.00, 18),
(2, 'Pistorasia', 'Jussi', 'kpl', 4.00, 10),
(2, 'Maakaapeli', 'Kaapelsson', 'metri', 4.00, 300),
(4, 'Sähkökeskus', 'Junker', 'kpl', 300.00, 3),
(4, 'Palohälytin', 'Incendium', 'kpl', 4.00, 15);

INSERT INTO Suoritus (nimi, hinta) VALUES 
('Urakka', NULL),
('Suunnittelu', 55.00),
('Työ', 45.00),
('Aputyö', 35.00);

INSERT INTO Sopimus (kohde_id, tyyppi, osia_laskussa, luotu) VALUES 
(1, 'Urakka', 1, '2025-09-01'),
(2, 'Tuntihinta', 1, '2026-01-02'),
(4, 'Tuntihinta', 1, '2026-01-03'),
(3, 'Urakka', 1, '2026-02-01'),
(5, 'Tuntihinta', 1, '2026-02-02'),
(6, 'Urakka', 1, '2026-02-06');

INSERT INTO Lasku (sopimus_id, edellinen_lasku_id, Pvm, erapaiva, maksupaiva) VALUES 
(1, NULL, '2025-10-01', '2025-10-15', NULL),
(1, 1, '2025-10-25', '2025-11-10', NULL),
(1, 2, '2025-11-27', '2025-12-13', '2025-12-01'),
(2, NULL, '2026-02-01', '2026-02-15', '2026-02-15'),
(3, NULL, '2026-02-01', '2026-02-15', NULL),
(3, 5, '2026-02-15', '2026-03-01', NULL),
(3, 6, '2026-03-05', '2026-03-20', NULL),
(4, NULL, '2026-03-01', '2026-03-15', NULL),
(5, NULL, '2026-03-01', '2026-03-15', NULL);

INSERT INTO Sopimus_tarvike (sopimus_id, tarvike_id, maara, hintatekija) VALUES 
(1, 1, 1, 1.24),
(2, 2, 3, (1.0 - 0.10) * 1.24),
(2, 3, 1, 1.10),
(2, 4, 1, (1.0 - 0.20) * 1.24),
(3, 5, 100, (1.0 - 0.10) * 1.24),
(3, 6, 1, (1.0 - 0.05) * 1.24),
(4, 7, 2, 1.24),
(5, 2, 3, 1.24), 
(5, 4, 1, 1.24),
(6, 4, 3, 1.24);

INSERT INTO Sopimus_suoritus (sopimus_id, suoritus_id, tyomaara_tunneilla, hintatekija, urakka_hinta) VALUES 
(1, 1, NULL, 1.24, 100.00),
(2, 2, 3, 0.90, NULL),
(2, 3, 12, 1.00, NULL),
(3, 2, 25, 0.80, NULL),
(3, 3, 7, 0.90, NULL),
(3, 4, 3, 1.00, NULL),
(4, 1, NULL, 1.12, 50.00),
(5, 2, 3, 1.00, NULL),
(5, 3, 12, 1.00, NULL),
(6, 1, NULL, 1.12, 5004.21);