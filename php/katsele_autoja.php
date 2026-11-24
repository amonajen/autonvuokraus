<?php
require_once('db.php');

$sql = "SELECT * FROM auto";
$tulos = mysqli_query($yhteys, $sql);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Autot</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Autot</h1>
    <table>
        <tr>
            <th>Kuva</th>
            <th>Rekisterinumero</th>
            <th>Valmistaja</th>
            <th>Malli</th>
            <th>Automaatti</th>
            <th>Moottori</th>
            <th>Istumapaikat</th>
            <th>Hinta/päivä</th>
            <th>Vapaa</th>
        </tr>
        <?php while ($rivi = mysqli_fetch_assoc($tulos)) { ?>
            <tr>
                <td><img src="../images/<?= htmlspecialchars($rivi['malli']) ?>.png" alt="Auton kuva" width="100"></td>
                <td><?= htmlspecialchars($rivi['rekno']) ?></td>
                <td><?= htmlspecialchars($rivi['valmistaja']) ?></td>
                <td><?= htmlspecialchars($rivi['malli']) ?></td>
                <td><?= $rivi['onkoautomaatti'] ? 'Kyllä' : 'Ei' ?></td>
                <td><?= htmlspecialchars($rivi['moottori']) ?></td>
                <td><?= (int)$rivi['istumapaikat'] ?></td>
                <td><?= htmlspecialchars($rivi['hinta']) ?> €</td>
                <td><?= $rivi['onkovapaa'] ? 'Kyllä' : 'Ei' ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
