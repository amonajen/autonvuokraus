<?php
require_once('db.php');

$sql = "SELECT s.aloituspvm, s.lopetuspvm, s.palautettu,
               a.etunimi, a.sukunimi,
               au.valmistaja, au.malli
        FROM sopimus s
        JOIN asiakas a ON s.asiakasid = a.id
        JOIN auto au ON s.autoid = au.id
        ORDER BY s.aloituspvm DESC";
$tulos = mysqli_query($yhteys, $sql);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Vuokraukset</title>
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>
    <h1>Vuokraukset</h1>
    <a class="back-button" href="../index.php">Takaisin</a>
    <table>
        <tr>
            <th>Asiakas</th>
            <th>Auto</th>
            <th>Alkaa</th>
            <th>Päättyy</th>
            <th>Tila</th>
        </tr>
        <?php while ($rivi = mysqli_fetch_assoc($tulos)) { ?>
            <tr>
                <td><?= htmlspecialchars($rivi['etunimi'] . " " . $rivi['sukunimi']) ?></td>
                <td><?= htmlspecialchars($rivi['valmistaja'] . " " . $rivi['malli']) ?></td>
                <td><?= date('d.m.Y', strtotime($rivi['aloituspvm'])) ?></td>
                <td><?= date('d.m.Y', strtotime($rivi['lopetuspvm'])) ?></td>
                <td>
                    <?php if ($rivi['palautettu'] === null) { ?>
                        Vuokrattu
                    <?php } else { ?>
                        Palautettu <?= date('d.m.Y', strtotime($rivi['palautettu'])) ?>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
