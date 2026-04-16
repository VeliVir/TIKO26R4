# Tietokantaohjelmointi 2026 ryhmä 4

## Asennusohjeet

### 1. Konfiguraatiotiedoston luominen
Sovellus tarvitsee toimiakseen `config.php`-tiedoston, joka sisältää tietokantayhteyden tiedot. Tietoturvasyistä tätä tiedostoa ei tallenneta versionhallintaan.

1. Kopioi projektin juuresta löytyvän `temp_config.php`-tiedoston sisältö.
2. Luo uusi tiedosto nimeltä `config.php`.
3. Tallenna `config.php` projektin **juurikansioon**.

### 2. Tietokantatunnusten haku
Hae henkilökohtaiset tunnuksesi yliopiston palvelimelta:

1. Avaa yhteys esim. PuTTY:llä.
2. Lue tunnukset: `cat database.txt`
3. Päivitä tiedot `config.php`-tiedostoon:
   - **user ja dbname**: oma käyttäjätunnuksesi
   - **password**: database.txt:stä löytyvä salasana

Tarkemmat ohjeet kurssimateriaalissa `Tietokantajärjestelmät-kurssilta tutut palvelinohjeet`

### 3. Tietokannan alustus
Sovellus sisältää alustusskriptin, joka luo taulut ja syöttää testidatan.

1. Aja tiedoston init_db.php skripti selaimella eduVPN käyttäen.
2. Nauti.