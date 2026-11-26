<?php
require_once('db.php');
require_once('viestit.php');

$virhe = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = filter_var($_POST['autoid'] ?? 0, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        $virhe = "Valitse poistettava auto.";
    } elseif (mysqli_begin_transaction($yhteys)) {
        $lukitus = mysqli_prepare($yhteys, "SELECT id FROM auto WHERE id = ? FOR UPDATE");
        $tarkistus = mysqli_prepare($yhteys, "SELECT COUNT(*) AS lkm FROM sopimus WHERE autoid = ?");
        $poisto = mysqli_prepare($yhteys, "DELETE FROM auto WHERE id = ?");
        $virhe = "Poistaminen epäonnistui.";

        if ($lukitus && $tarkistus && $poisto) {
            mysqli_stmt_bind_param($lukitus, "i", $id);
            mysqli_stmt_bind_param($tarkistus, "i", $id);
            mysqli_stmt_bind_param($poisto, "i", $id);

            if (mysqli_stmt_execute($lukitus) && mysqli_stmt_store_result($lukitus)) {
                if (mysqli_stmt_num_rows($lukitus) === 0) {
                    $virhe = "Autoa ei löytynyt.";
                } elseif (mysqli_stmt_execute($tarkistus)) {
                    $rivi = mysqli_fetch_assoc(mysqli_stmt_get_result($tarkistus));

                    if ($rivi['lkm'] > 0) {
                        $virhe = "Autoa ei voi poistaa, koska sillä on vuokrauksia.";
                    } elseif (mysqli_stmt_execute($poisto) && mysqli_stmt_affected_rows($poisto) === 1
                        && mysqli_commit($yhteys)) {
                        header("Location: poista_auto.php?viesti=auto_poistettu", true, 303);
                        exit();
                    }
                }
            }
        }

        if ($virhe != '') {
            mysqli_rollback($yhteys);
        }
    } else {
        $virhe = "Poistaminen epäonnistui.";
    }
}

$tulos = mysqli_query($yhteys, "SELECT id, rekno, valmistaja, malli FROM auto");
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Poista auto</title>
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>
    <h1>Poista auto</h1>
    <a class="back-button" href="../index.php">Takaisin</a>

    <?php if ($virhe != '') { ?><p class="viesti virhe"><?= htmlspecialchars($virhe) ?></p><?php } ?>
    <?php if ($viesti != '') { ?><p class="viesti"><?= htmlspecialchars($viesti) ?></p><?php } ?>

    <form method="post">
        <p>
            <label for="autoid">Auto:</label>
            <select id="autoid" name="autoid">
                <option value="0">-- Valitse --</option>
                <?php while ($rivi = mysqli_fetch_assoc($tulos)) { ?>
                    <option value="<?= (int)$rivi['id'] ?>">
                        <?= htmlspecialchars($rivi['rekno'] . " - " . $rivi['valmistaja'] . " " . $rivi['malli']) ?>
                    </option>
                <?php } ?>
            </select>
        </p>
        <input type="submit" value="Poista auto">
    </form>
</body>
</html>
