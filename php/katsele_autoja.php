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
        <?php while ($rivi = mysqli_fetch_assoc($tulos)) {
            $kuva = basename($rivi['malli']) . '.png';
        ?>
            <tr>
                <td>
                    <?php if (file_exists(__DIR__ . '/../images/' . $kuva)) { ?>
                        <img src="../images/<?= rawurlencode($kuva) ?>" alt="Auton kuva" width="100">
                    <?php } ?>
                </td>
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
