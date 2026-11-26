-- Autonvuokraus: taulut ja yksi esimerkkirivi kustakin

-- Näin käytät tiedostoa:
-- 1. Luo ensin tyhjä tietokanta phpMyAdminissa (Uusi - anna nimeksi autonvuokraus).
-- 2. Valitse tietokanta vasemmalta.
-- 3. Valitse Tuonti (Import), valitse tämä tiedosto ja paina Suorita.

-- Tiedostossa ei ole CREATE DATABASE- eikä USE-riviä tarkoituksella: tietokanta tehdään ensin käsin.

-- TEE ITSE: lisää tietoja niin, että autoja on 7, asiakkaita 6 ja vuokrauksia 4.
-- Voit kirjoittaa lisää INSERT-lauseita tähän tiedostoon tai lisätä rivit suoraan phpMyAdminissa (taulun Lisää-välilehti).
-- Tee ainakin kaksi autoa vuokratuksi: auto.onkovapaa = 0 ja sopimus, jossa palautettu on NULL.
-- Voit myös tehdä taulut itse phpMyAdminissa (Luo taulu) tämän tiedoston mallin mukaan.

-- Huom: tiedosto poistaa ensin vanhat taulut auto, asiakas ja sopimus ja tekee ne uudestaan.
-- Tuonti nollaa siis näiden taulujen sisällön.

-- Sopimuksen vierasavaimet yhdistävät vuokrauksen asiakkaaseen ja autoon.
-- PHP:n poistotarkistukset näyttävät käyttäjälle selkeän virheilmoituksen.

SET NAMES utf8mb4;

DROP TABLE IF EXISTS sopimus;
DROP TABLE IF EXISTS asiakas;
DROP TABLE IF EXISTS auto;

-- Taulu auto: yksi rivi per vuokra-auto
-- onkoautomaatti: 1 = automaatti, 0 = manuaali
-- onkovapaa: 1 = vapaana, 0 = vuokrattu
CREATE TABLE auto (
  id INT NOT NULL AUTO_INCREMENT,
  rekno VARCHAR(10) NOT NULL,
  valmistaja VARCHAR(100) NOT NULL,
  malli VARCHAR(100) NOT NULL,
  onkoautomaatti TINYINT(1) NOT NULL DEFAULT 0,
  moottori VARCHAR(50) NOT NULL,
  istumapaikat INT NOT NULL,
  hinta DECIMAL(6,2) NOT NULL,
  onkovapaa TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY rekno (rekno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Taulu asiakas: yksi rivi per asiakas
-- saika = syntymäaika (muoto VVVV-KK-PP)
CREATE TABLE asiakas (
  id INT NOT NULL AUTO_INCREMENT,
  etunimi VARCHAR(100) NOT NULL,
  sukunimi VARCHAR(100) NOT NULL,
  katuosoite VARCHAR(150) NOT NULL,
  postitoimipaikka VARCHAR(100) NOT NULL,
  puhelin VARCHAR(20) NOT NULL,
  saika DATE NOT NULL,
  email VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Taulu sopimus: yksi rivi per vuokraus
-- asiakasid viittaa taulun asiakas sarakkeeseen id
-- autoid viittaa taulun auto sarakkeeseen id
-- palautettu: päivä jona auto palautettiin, NULL = auto on vielä vuokrattuna
CREATE TABLE sopimus (
  id INT NOT NULL AUTO_INCREMENT,
  asiakasid INT NOT NULL,
  autoid INT NOT NULL,
  aloituspvm DATE NOT NULL,
  lopetuspvm DATE NOT NULL,
  palautettu DATE NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY asiakasid (asiakasid),
  KEY autoid (autoid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE sopimus
  ADD CONSTRAINT fk_sopimus_asiakas FOREIGN KEY (asiakasid) REFERENCES asiakas(id),
  ADD CONSTRAINT fk_sopimus_auto FOREIGN KEY (autoid) REFERENCES auto(id);

-- Esimerkkirivit (yksi kustakin taulusta). Lisää loput itse.
INSERT INTO auto (rekno, valmistaja, malli, onkoautomaatti, moottori, istumapaikat, hinta, onkovapaa) VALUES
('ABC-313', 'Toyota', 'Corolla', 1, 'bensa', 5, 101.00, 1);

INSERT INTO asiakas (etunimi, sukunimi, katuosoite, postitoimipaikka, puhelin, saika, email) VALUES
('Liisa', 'Meikäläinen', 'Kotikatu 11 A 3', '00100 Helsinki', '0501234567', '2004-10-08', 'liisa.meikalainen@example.com');

-- Vuokraus: asiakas 1 on vuokrannut auton 1 ja palauttanut sen (palautettu ei ole NULL)
INSERT INTO sopimus (asiakasid, autoid, aloituspvm, lopetuspvm, palautettu) VALUES
(1, 1, '2026-11-01', '2026-11-05', '2026-11-05');

-- TEE ITSE: lisää 6 autoa, 5 asiakasta ja 3 vuokrausta lisää.
-- Muista: avoimessa vuokrauksessa palautettu on NULL ja auton onkovapaa on 0.

START TRANSACTION;

INSERT INTO auto
    (id, rekno, valmistaja, malli, onkoautomaatti, moottori, istumapaikat, hinta)
VALUES
    (2, 'BAC-212', 'BMW', '330e', 1, 'hybridi', 5, 121.00),
    (3, 'DAC-812', 'BMW', '520d', 1, 'diesel', 5, 123.00),
    (4, 'CAB-312', 'BMW', '120i', 1, 'bensa', 5, 112.00),
    (5, 'ACB-412', 'Ford', 'Focus', 1, 'diesel', 5, 104.00),
    (6, 'BCB-512', 'Ford', 'Fiesta', 0, 'bensa', 5, 98.00),
    (7, 'DDD-321', 'Hyundai', 'Kona', 1, 'sähkö', 5, 123.00);

INSERT INTO asiakas
    (id, etunimi, sukunimi, katuosoite, postitoimipaikka, puhelin, saika, email)
VALUES
    (2, 'Kalevi', 'Kallio', 'Esimerkkikatu 2', '04200 Kerava', '0400000002', '1995-05-16', 'kalevi@example.com'),
    (3, 'Matti', 'Mäki', 'Esimerkkikatu 3', '04400 Järvenpää', '0400000003', '1989-09-03', 'matti@example.com'),
    (4, 'Aino', 'Laine', 'Esimerkkikatu 4', '04130 Sipoo', '0400000004', '1998-02-12', 'aino@example.com'),
    (5, 'Emilia', 'Niemi', 'Esimerkkikatu 5', '01300 Vantaa', '0400000005', '2000-07-21', 'emilia@example.com'),
    (6, 'Oskari', 'Salmi', 'Esimerkkikatu 6', '00100 Helsinki', '0400000006', '1992-11-25', 'oskari@example.com');

INSERT INTO sopimus
    (asiakasid, autoid, aloituspvm, lopetuspvm, palautettu)
VALUES
    (2, 2, '2026-11-22', '2026-11-26', NULL),
    (3, 3, '2026-11-24', '2026-11-29', NULL),
    (4, 4, '2026-11-10', '2026-11-12', '2026-11-12');

UPDATE auto
SET onkovapaa = 0
WHERE id IN (2, 3);

COMMIT;
