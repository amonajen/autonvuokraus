<?php
require_once('db.php');

$virhe = '';
$viesti = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sopimusid = (int)($_POST['sopimusid'] ?? 0);

    if ($sopimusid <= 0) {
        $virhe = "Valitse palautettava vuokraus.";
    } elseif (mysqli_begin_transaction($yhteys)) {
        $sql = "SELECT id, autoid FROM sopimus WHERE id = ? AND palautettu IS NULL FOR UPDATE";
        $stmt = mysqli_prepare($yhteys, $sql);
        $sopimus = null;

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $sopimusid);
            if (mysqli_stmt_execute($stmt)) {
                $sopimus = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            }
        }

        if (!$sopimus) {
            $virhe = "Palauttamatonta vuokrausta ei löytynyt.";
        } else {
            $tanaan = date('Y-m-d');
            $autoid = (int)$sopimus['autoid'];
            $palautus = mysqli_prepare($yhteys, "UPDATE sopimus SET palautettu = ? WHERE id = ?");
            $paivitys = mysqli_prepare($yhteys, "UPDATE auto SET onkovapaa = 1 WHERE id = ?");
            $virhe = "Palautus epäonnistui.";

            if ($palautus && $paivitys) {
                mysqli_stmt_bind_param($palautus, "si", $tanaan, $sopimusid);
                mysqli_stmt_bind_param($paivitys, "i", $autoid);

                if (mysqli_stmt_execute($palautus) && mysqli_stmt_execute($paivitys)
                    && mysqli_stmt_affected_rows($paivitys) === 1 && mysqli_commit($yhteys)) {
                    $virhe = '';
                    $viesti = "Auto palautettiin.";
                }
            }
        }

        if ($virhe != '') {
            mysqli_rollback($yhteys);
        }
    } else {
        $virhe = "Palautus epäonnistui.";
    }
}

$sql = "SELECT s.id, s.lopetuspvm, a.etunimi, a.sukunimi, au.rekno, au.malli
        FROM sopimus s
        JOIN asiakas a ON s.asiakasid = a.id
        JOIN auto au ON s.autoid = au.id
        WHERE s.palautettu IS NULL";
$tulos = mysqli_query($yhteys, $sql);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Palauta auto</title>
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>
    <h1>Palauta auto</h1>
    <a class="back-button" href="../index.php">Takaisin</a>

    <?php if ($virhe != '') { ?><p class="viesti virhe"><?= htmlspecialchars($virhe) ?></p><?php } ?>
    <?php if ($viesti != '') { ?><p class="viesti"><?= htmlspecialchars($viesti) ?></p><?php } ?>

    <form method="post">
        <p>
            <label for="sopimusid">Vuokraus:</label>
            <select id="sopimusid" name="sopimusid">
                <option value="0">-- Valitse --</option>
                <?php while ($rivi = mysqli_fetch_assoc($tulos)) { ?>
                    <option value="<?= (int)$rivi['id'] ?>">
                        <?= htmlspecialchars($rivi['etunimi'] . " " . $rivi['sukunimi'] . " - " . $rivi['rekno'] . " " . $rivi['malli']) ?>
                        (<?= date('d.m.Y', strtotime($rivi['lopetuspvm'])) ?>)
                    </option>
                <?php } ?>
            </select>
        </p>
        <input type="submit" value="Palauta auto">
    </form>
</body>
</html>
