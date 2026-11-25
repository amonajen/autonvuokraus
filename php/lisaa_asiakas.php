<?php
require_once('db.php');

$virhe = '';
$viesti = '';
$etunimi = '';
$sukunimi = '';
$katuosoite = '';
$postitoimipaikka = '';
$puhelin = '';
$saika = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $etunimi = trim($_POST['etunimi'] ?? '');
    $sukunimi = trim($_POST['sukunimi'] ?? '');
    $katuosoite = trim($_POST['katuosoite'] ?? '');
    $postitoimipaikka = trim($_POST['postitoimipaikka'] ?? '');
    $puhelin = trim($_POST['puhelin'] ?? '');
    $saika = trim($_POST['saika'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($etunimi == '' || $sukunimi == '' || $katuosoite == '' ||
        $postitoimipaikka == '' || $puhelin == '' || $saika == '' || $email == '') {
        $virhe = "Täytä kaikki kentät.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $virhe = "Sähköpostiosoite ei ole oikean muotoinen.";
    } elseif ($saika > date('Y-m-d')) {
        $virhe = "Syntymäaika ei voi olla tulevaisuudessa.";
    }

    if ($virhe == '') {
        $sql = "INSERT INTO asiakas (etunimi, sukunimi, katuosoite, postitoimipaikka, puhelin, saika, email)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($yhteys, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssss", $etunimi, $sukunimi, $katuosoite,
                $postitoimipaikka, $puhelin, $saika, $email);
        }

        if ($stmt && mysqli_stmt_execute($stmt)) {
            $viesti = "Asiakas lisättiin.";
            $etunimi = '';
            $sukunimi = '';
            $katuosoite = '';
            $postitoimipaikka = '';
            $puhelin = '';
            $saika = '';
            $email = '';
        } else {
            $virhe = "Tallennus epäonnistui.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Lisää asiakas</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Lisää asiakas</h1>

    <?php if ($virhe != '') { ?><p class="viesti virhe"><?= htmlspecialchars($virhe) ?></p><?php } ?>
    <?php if ($viesti != '') { ?><p class="viesti"><?= htmlspecialchars($viesti) ?></p><?php } ?>

    <form method="post">
        <p><label for="etunimi">Etunimi:</label>
           <input type="text" id="etunimi" name="etunimi" value="<?= htmlspecialchars($etunimi) ?>"></p>
        <p><label for="sukunimi">Sukunimi:</label>
           <input type="text" id="sukunimi" name="sukunimi" value="<?= htmlspecialchars($sukunimi) ?>"></p>
        <p><label for="katuosoite">Katuosoite:</label>
           <input type="text" id="katuosoite" name="katuosoite" value="<?= htmlspecialchars($katuosoite) ?>"></p>
        <p><label for="postitoimipaikka">Postitoimipaikka:</label>
           <input type="text" id="postitoimipaikka" name="postitoimipaikka" value="<?= htmlspecialchars($postitoimipaikka) ?>"></p>
        <p><label for="puhelin">Puhelin:</label>
           <input type="tel" id="puhelin" name="puhelin" value="<?= htmlspecialchars($puhelin) ?>"></p>
        <p><label for="saika">Syntymäaika:</label>
           <input type="date" id="saika" name="saika" value="<?= htmlspecialchars($saika) ?>"></p>
        <p><label for="email">Sähköposti:</label>
           <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>"></p>
        <input type="submit" value="Lisää asiakas">
    </form>
</body>
</html>
