<?php
require_once('db.php');

$virhe = '';
$viesti = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = filter_var($_POST['sopimusid'] ?? 0, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        $virhe = "Valitse poistettava vuokraus.";
    } elseif (mysqli_begin_transaction($yhteys)) {
        $sql = "SELECT autoid, palautettu FROM sopimus WHERE id = ? FOR UPDATE";
        $stmt = mysqli_prepare($yhteys, $sql);
        $sopimus = null;

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                $sopimus = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            }
        }

        if (!$sopimus) {
            $virhe = "Vuokrausta ei löytynyt.";
        } else {
            $virhe = "Poistaminen epäonnistui.";
            $autoVapautettu = true;

            if ($sopimus['palautettu'] === null) {
                $autoid = (int)$sopimus['autoid'];
                $paivitys = mysqli_prepare($yhteys, "UPDATE auto SET onkovapaa = 1 WHERE id = ?");
                if ($paivitys) {
                    mysqli_stmt_bind_param($paivitys, "i", $autoid);
                }
                $autoVapautettu = $paivitys && mysqli_stmt_execute($paivitys);
            }

            if ($autoVapautettu) {
                $poisto = mysqli_prepare($yhteys, "DELETE FROM sopimus WHERE id = ?");
                if ($poisto) {
                    mysqli_stmt_bind_param($poisto, "i", $id);
                }

                if ($poisto && mysqli_stmt_execute($poisto) && mysqli_stmt_affected_rows($poisto) === 1
                    && mysqli_commit($yhteys)) {
                    $virhe = '';
                    $viesti = "Vuokraus poistettiin.";
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

$sql = "SELECT s.id, s.aloituspvm, s.lopetuspvm, a.etunimi, a.sukunimi, au.rekno, au.malli
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
    <title>Poista vuokraus</title>
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>
    <h1>Poista vuokraus</h1>
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
                        (<?= date('d.m.Y', strtotime($rivi['aloituspvm'])) ?> - <?= date('d.m.Y', strtotime($rivi['lopetuspvm'])) ?>)
                    </option>
                <?php } ?>
            </select>
        </p>
        <input type="submit" value="Poista vuokraus">
    </form>
</body>
</html>
