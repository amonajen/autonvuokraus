<?php
require_once('db.php');

$virhe = '';
$auto = null;
$tallennetaan = $_SERVER['REQUEST_METHOD'] === 'POST';
$id = (int)filter_input($tallennetaan ? INPUT_POST : INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id > 0) {
    $stmt = mysqli_prepare($yhteys, "SELECT * FROM auto WHERE id = ?");
    if ($stmt && mysqli_stmt_bind_param($stmt, "i", $id) && mysqli_stmt_execute($stmt)) {
        $auto = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    } else {
        $virhe = "Auton tietojen hakeminen epäonnistui.";
    }
}

if ($virhe === '' && !$auto) {
    $virhe = "Autoa ei löytynyt.";
}

if ($auto && $tallennetaan) {
    $auto['rekno'] = trim((string)filter_input(INPUT_POST, 'rekno'));
    $auto['valmistaja'] = trim((string)filter_input(INPUT_POST, 'valmistaja'));
    $auto['malli'] = trim((string)filter_input(INPUT_POST, 'malli'));
    $auto['onkoautomaatti'] = isset($_POST['onkoautomaatti']) ? 1 : 0;
    $auto['moottori'] = trim((string)filter_input(INPUT_POST, 'moottori'));
    $auto['istumapaikat'] = trim((string)filter_input(INPUT_POST, 'istumapaikat'));
    $auto['hinta'] = trim((string)filter_input(INPUT_POST, 'hinta'));

    if ($auto['rekno'] === '' || $auto['valmistaja'] === '' || $auto['malli'] === '' ||
        $auto['moottori'] === '' || $auto['istumapaikat'] === '' || $auto['hinta'] === '') {
        $virhe = "Täytä kaikki kentät.";
    } elseif (filter_var($auto['istumapaikat'], FILTER_VALIDATE_INT) === false ||
        $auto['istumapaikat'] < 1 || $auto['istumapaikat'] > 9) {
        $virhe = "Istumapaikkoja pitää olla 1–9.";
    } elseif (!is_numeric($auto['hinta']) || $auto['hinta'] < 0.01 || $auto['hinta'] > 9999.99) {
        $virhe = "Hinnan on oltava 0,01–9 999,99 euroa.";
    }

    if ($virhe === '') {
        $stmt = mysqli_prepare($yhteys, "SELECT id FROM auto WHERE rekno = ? AND id <> ?");
        if (!$stmt || !mysqli_stmt_bind_param($stmt, "si", $auto['rekno'], $id) || !mysqli_stmt_execute($stmt)) {
            $virhe = "Tallennus epäonnistui.";
        } elseif (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
            $virhe = "Rekisterinumero on jo käytössä.";
        }
    }

    if ($virhe === '') {
        $sql = "UPDATE auto SET rekno = ?, valmistaja = ?, malli = ?, onkoautomaatti = ?,
                moottori = ?, istumapaikat = ?, hinta = ? WHERE id = ?";
        $stmt = mysqli_prepare($yhteys, $sql);

        if ($stmt && mysqli_stmt_bind_param($stmt, "sssisidi", $auto['rekno'], $auto['valmistaja'],
            $auto['malli'], $auto['onkoautomaatti'], $auto['moottori'], $auto['istumapaikat'], $auto['hinta'], $id)
            && mysqli_stmt_execute($stmt)) {
            header("Location: katsele_autoja.php?viesti=auto_muokattu", true, 303);
            exit();
        }

        $virhe = $stmt && mysqli_stmt_errno($stmt) === 1062
            ? "Rekisterinumero on jo käytössä."
            : "Tallennus epäonnistui.";
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Muokkaa autoa</title>
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>
    <h1>Muokkaa autoa</h1>
    <a class="back-button" href="katsele_autoja.php">Takaisin</a>
    <?php if ($virhe !== '') { ?><p class="viesti virhe"><?= htmlspecialchars($virhe) ?></p><?php } ?>

    <?php if ($auto) { ?>
        <form method="post" action="muokkaa_auto.php">
            <input type="hidden" name="id" value="<?= $id ?>">
            <p><label for="rekno">Rekisterinumero:</label>
               <input type="text" id="rekno" name="rekno" maxlength="10" value="<?= htmlspecialchars($auto['rekno']) ?>"></p>
            <p><label for="valmistaja">Valmistaja:</label>
               <input type="text" id="valmistaja" name="valmistaja" maxlength="100" value="<?= htmlspecialchars($auto['valmistaja']) ?>"></p>
            <p><label for="malli">Malli:</label>
               <input type="text" id="malli" name="malli" maxlength="100" value="<?= htmlspecialchars($auto['malli']) ?>"></p>
            <p><label for="onkoautomaatti">Automaatti:</label>
               <input type="checkbox" id="onkoautomaatti" name="onkoautomaatti" value="1" <?= $auto['onkoautomaatti'] ? 'checked' : '' ?>></p>
            <p><label for="moottori">Moottori:</label>
               <input type="text" id="moottori" name="moottori" maxlength="50" value="<?= htmlspecialchars($auto['moottori']) ?>"></p>
            <p><label for="istumapaikat">Istumapaikat:</label>
               <input type="number" id="istumapaikat" name="istumapaikat" min="1" max="9" value="<?= htmlspecialchars($auto['istumapaikat']) ?>"></p>
            <p><label for="hinta">Hinta/päivä:</label>
               <input type="number" id="hinta" name="hinta" min="0.01" max="9999.99" step="0.01" value="<?= htmlspecialchars($auto['hinta']) ?>"></p>
            <input type="submit" value="Tallenna">
        </form>
    <?php } ?>
</body>
</html>
