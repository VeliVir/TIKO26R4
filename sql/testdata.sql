INSERT INTO Toimittaja (nimi, osoite) VALUES 
('Toimittaja Oy', 'Teollisuustie 1, Vantaa'),
('Rakennusmateriaali Ltd', 'Kivimiehentie 4, Espoo'),
('ToolHouse', 'Varastokatu 12, Kerava');

INSERT INTO Asiakas (etunimi, sukunimi, osoite, puhelinnro, sahkoposti) VALUES 
('Seppo', 'Tärsky', 'Mannerheimintie 10, Helsinki', '040 123 4567', 'seppo.tarsky@tuni.fi'),
('Marko', 'Junkkari', 'Aurakatu 5, Turku', '050 987 6543', 'marko.junkkari@tuni.fi'),
('Matti', 'Meikäläinen', 'Isokatu 12, Oulu', '044 321 7689', 'matti.meikalainen@example.com'),
('Laura', 'Lehtonen', 'Logistiikkatie 8, Espoo', '045 111 2233', 'laura.lehtonen@testi.fi');

INSERT INTO Tyokohde (asiakas_id, nimi, osoite) VALUES 
(1, 'Helsingin toimisto', 'Mannerheimintie 10, Helsinki'),
(1, 'Tampereen varasto', 'Hatanpään valtatie 18, Tampere'),
(2, 'Turun pääkonttori', 'Aurakatu 5, Turku'),
(3, 'Oulun toimipiste', 'Isokatu 12, Oulu'),
(1, 'Jyväskylän tehdas', 'Tehtaankatu 15, Jyväskylä'),
(4, 'Espoon logistiikkakeskus', 'Logistiikkatie 8, Espoo');

INSERT INTO Tarvike (toimittaja_id, nimi, merkki, yksikko, hankintahinta, varastossa) VALUES 
(1, 'Ruuvimeisseli', 'ProTool', 'kpl', 5.50, 24),
(2, 'Poranterä 10mm', 'DrillPro', 'kpl', 2.80, 120),
(3, 'Työkalupakki', 'MasterBox', 'kpl', 35.00, 18);

INSERT INTO Suoritus (nimi, hinta) VALUES 
('Työ', 65.00),
('Suunnittelu', 85.00),
('Asennus', 70.00);

INSERT INTO Sopimus (kohde_id, tyyppi, osia_laskussa, luotu) VALUES 
(1, 'Urakka', 3, '2026-04-01'),
(2, 'Tuntihinta', 1, '2026-03-12'),
(3, 'Urakka', 2, '2026-04-08');

INSERT INTO Lasku (sopimus_id, Pvm, erapaiva, maksupaiva) VALUES 
(1, '2026-04-01', '2026-05-01', NULL),
(2, '2026-03-15', '2026-04-15', '2026-04-10'),
(3, '2026-04-08', '2026-05-08', NULL);

INSERT INTO Sopimus_tarvike (sopimus_id, tarvike_id, maara, hintatekija) VALUES 
(1, 1, 2, 1.1),
(2, 2, 10, 1.0);

INSERT INTO Sopimus_suoritus (sopimus_id, suoritus_id, tyomaara_tunneilla, hintatekija) VALUES 
(1, 1, 5, 1.2),
(2, 2, 2, 1.3);