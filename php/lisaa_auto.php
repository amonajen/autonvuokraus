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

    $kuva = $_FILES['kuva'] ?? [];
    $kuvavirhe = $kuva['error'] ?? UPLOAD_ERR_NO_FILE;
    $kuvapolku = '';
    if ($virhe === '' && $kuvavirhe !== UPLOAD_ERR_NO_FILE) {
        if ($kuvavirhe === UPLOAD_ERR_INI_SIZE || $kuvavirhe === UPLOAD_ERR_FORM_SIZE) {
            $virhe = "Kuvan koko saa olla enintään 500 000 tavua.";
        } elseif ($kuvavirhe !== UPLOAD_ERR_OK) {
            $virhe = "Kuvan lataaminen epäonnistui.";
        } elseif ($kuva['size'] > 500000) {
            $virhe = "Kuvan koko saa olla enintään 500 000 tavua.";
        } elseif (!preg_match('/^[A-Za-z0-9-]+$/', $malli)) {
            $virhe = "Kuvan kanssa mallin nimessä saa olla vain kirjaimia A–Z, numeroita ja yhdysmerkkejä.";
        } else {
            $tiedot = @getimagesize($kuva['tmp_name']);
            if ($tiedot === false || $tiedot[2] !== IMAGETYPE_PNG) {
                $virhe = "Valitse PNG-kuva.";
            } else {
                $kuvapolku = __DIR__ . '/../images/' . $malli . '.png';
            }
        }
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

        if ($stmt && mysqli_begin_transaction($yhteys)) {
            if (!mysqli_stmt_execute($stmt)) {
                $virhe = mysqli_stmt_errno($stmt) === 1062
                    ? "Rekisterinumero on jo käytössä."
                    : "Tallennus epäonnistui.";
            } elseif ($kuvapolku !== '' && !@move_uploaded_file($kuva['tmp_name'], $kuvapolku)) {
                $virhe = "Kuvan tallentaminen epäonnistui. Autoa ei lisätty.";
            } elseif (!mysqli_commit($yhteys)) {
                $virhe = "Tallennus epäonnistui.";
            } else {
                $viesti = "Auto lisättiin.";
                $rekno = '';
                $valmistaja = '';
                $malli = '';
                $onkoautomaatti = 0;
                $moottori = '';
                $istumapaikat = '';
                $hinta = '';
            }

            if ($virhe !== '') {
                mysqli_rollback($yhteys);
            }
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
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>
    <h1>Lisää auto</h1>
    <a class="back-button" href="../index.php">Takaisin</a>

    <?php if ($virhe != '') { ?><p class="viesti virhe"><?= htmlspecialchars($virhe) ?></p><?php } ?>
    <?php if ($viesti != '') { ?><p class="viesti"><?= htmlspecialchars($viesti) ?></p><?php } ?>

    <form method="post" enctype="multipart/form-data">
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
        <p><label for="kuva">Kuva (PNG):</label>
           <input type="file" id="kuva" name="kuva" accept="image/png"></p>
        <input type="submit" value="Lisää auto">
    </form>
</body>
</html>
