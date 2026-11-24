<?php
require_once('db.php');

$sql = "SELECT * FROM asiakas ORDER BY sukunimi";
$tulos = mysqli_query($yhteys, $sql);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Asiakkaat</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Asiakkaat</h1>
    <table>
        <tr>
            <th>Nimi</th>
            <th>Syntymäaika</th>
            <th>Osoite</th>
            <th>Puhelin</th>
            <th>Sähköposti</th>
        </tr>
        <?php while ($rivi = mysqli_fetch_assoc($tulos)) { ?>
            <tr>
                <td><?= htmlspecialchars($rivi['etunimi'] . " " . $rivi['sukunimi']) ?></td>
                <td><?= date('d.m.Y', strtotime($rivi['saika'])) ?></td>
                <td><?= htmlspecialchars($rivi['katuosoite'] . ", " . $rivi['postitoimipaikka']) ?></td>
                <td><?= htmlspecialchars($rivi['puhelin']) ?></td>
                <td><?= htmlspecialchars($rivi['email']) ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
