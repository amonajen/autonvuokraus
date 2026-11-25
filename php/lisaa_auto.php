<?php
require_once('db.php');

$virhe = '';
$viesti = '';
$rekno = '';
$valmistaja = '';
$malli = '';
$onkoautomaatti = 0;
$moottori = '';
$istumapaikat = '';
$hinta = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rekno = trim($_POST['rekno'] ?? '');
    $valmistaja = trim($_POST['valmistaja'] ?? '');
    $malli = trim($_POST['malli'] ?? '');
    $onkoautomaatti = isset($_POST['onkoautomaatti']) ? 1 : 0;
    $moottori = trim($_POST['moottori'] ?? '');
    $istumapaikat = trim($_POST['istumapaikat'] ?? '');
    $hinta = trim($_POST['hinta'] ?? '');

    if ($rekno == '' || $valmistaja == '' || $malli == '' ||
        $moottori == '' || $istumapaikat == '' || $hinta == '') {
        $virhe = "Täytä kaikki kentät.";
    } elseif (filter_var($istumapaikat, FILTER_VALIDATE_INT) === false || $istumapaikat < 1 || $istumapaikat > 9) {
        $virhe = "Istumapaikkoja pitää olla 1–9.";
    } elseif (!is_numeric($hinta) || $hinta < 0.01) {
        $virhe = "Hinnan on oltava vähintään 0,01 euroa.";
    }

    if ($virhe == '') {
        $sql = "SELECT COUNT(*) AS lkm FROM auto WHERE rekno = ?";
        $stmt = mysqli_prepare($yhteys, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $rekno);
        }

        if ($stmt && mysqli_stmt_execute($stmt)) {
            $rivi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            if ($rivi['lkm'] > 0) {
                $virhe = "Rekisterinumero on jo käytössä.";
            }
        } else {
            $virhe = "Tallennus epäonnistui.";
        }
    }

    if ($virhe == '') {
        $sql = "INSERT INTO auto (rekno, valmistaja, malli, onkoautomaatti, moottori, istumapaikat, hinta)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($yhteys, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssisid", $rekno, $valmistaja, $malli,
                $onkoautomaatti, $moottori, $istumapaikat, $hinta);
        }

        if ($stmt && mysqli_stmt_execute($stmt)) {
            $viesti = "Auto lisättiin.";
            $rekno = '';
            $valmistaja = '';
            $malli = '';
            $onkoautomaatti = 0;
            $moottori = '';
            $istumapaikat = '';
            $hinta = '';
        } elseif ($stmt && mysqli_stmt_errno($stmt) === 1062) {
            $virhe = "Rekisterinumero on jo käytössä.";
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
    <title>Lisää auto</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Lisää auto</h1>

    <?php if ($virhe != '') { ?><p class="viesti virhe"><?= htmlspecialchars($virhe) ?></p><?php } ?>
    <?php if ($viesti != '') { ?><p class="viesti"><?= htmlspecialchars($viesti) ?></p><?php } ?>

    <form method="post">
        <p><label for="rekno">Rekisterinumero:</label>
           <input type="text" id="rekno" name="rekno" value="<?= htmlspecialchars($rekno) ?>"></p>
        <p><label for="valmistaja">Valmistaja:</label>
           <input type="text" id="valmistaja" name="valmistaja" value="<?= htmlspecialchars($valmistaja) ?>"></p>
        <p><label for="malli">Malli:</label>
           <input type="text" id="malli" name="malli" value="<?= htmlspecialchars($malli) ?>"></p>
        <p><label for="onkoautomaatti">Automaatti:</label>
           <input type="checkbox" id="onkoautomaatti" name="onkoautomaatti" value="1" <?= $onkoautomaatti ? 'checked' : '' ?>></p>
        <p><label for="moottori">Moottori:</label>
           <input type="text" id="moottori" name="moottori" value="<?= htmlspecialchars($moottori) ?>"></p>
        <p><label for="istumapaikat">Istumapaikat:</label>
           <input type="number" id="istumapaikat" name="istumapaikat" min="1" max="9" value="<?= htmlspecialchars($istumapaikat) ?>"></p>
        <p><label for="hinta">Hinta/päivä:</label>
           <input type="number" id="hinta" name="hinta" min="0.01" step="0.01" value="<?= htmlspecialchars($hinta) ?>"></p>
        <input type="submit" value="Lisää auto">
    </form>
</body>
</html>
