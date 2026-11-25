<?php
require_once('db.php');

$virhe = '';
$viesti = '';
$asiakasid = 0;
$autoid = 0;
$aloituspvm = '';
$lopetuspvm = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asiakasid = (int)($_POST['asiakasid'] ?? 0);
    $autoid = (int)($_POST['autoid'] ?? 0);
    $aloituspvm = trim($_POST['aloituspvm'] ?? '');
    $lopetuspvm = trim($_POST['lopetuspvm'] ?? '');

    if ($asiakasid <= 0 || $autoid <= 0 || $aloituspvm == '' || $lopetuspvm == '') {
        $virhe = "Valitse asiakas ja auto sekä anna molemmat päivämäärät.";
    } elseif ($aloituspvm < date('Y-m-d')) {
        $virhe = "Aloituspäivä ei voi olla menneisyydessä.";
    } elseif ($lopetuspvm < $aloituspvm) {
        $virhe = "Lopetuspäivä ei voi olla ennen aloituspäivää.";
    } else {
        $sql = "SELECT COUNT(*) AS lkm FROM auto WHERE id = ? AND onkovapaa = 1";
        $stmt = mysqli_prepare($yhteys, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $autoid);
        }
        if ($stmt && mysqli_stmt_execute($stmt)) {
            $rivi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            if ($rivi['lkm'] == 0) {
                $virhe = "Auto ei ole vapaana.";
            }
        } else {
            $virhe = "Tallennus epäonnistui.";
        }
    }

    if ($virhe == '') {
        $sql = "INSERT INTO sopimus (asiakasid, autoid, aloituspvm, lopetuspvm)
                VALUES ((SELECT id FROM asiakas WHERE id = ? LOCK IN SHARE MODE), ?, ?, ?)";
        $stmt = mysqli_prepare($yhteys, $sql);
        $paivitys = mysqli_prepare($yhteys, "UPDATE auto SET onkovapaa = 0 WHERE id = ? AND onkovapaa = 1");

        // Vuokraus ja auton tila tallennetaan yhdessä.
        if ($stmt && $paivitys && mysqli_begin_transaction($yhteys)) {
            mysqli_stmt_bind_param($stmt, "iiss", $asiakasid, $autoid, $aloituspvm, $lopetuspvm);
            mysqli_stmt_bind_param($paivitys, "i", $autoid);

            $kyselytOnnistuivat = mysqli_stmt_execute($stmt) && mysqli_stmt_execute($paivitys);
            if ($kyselytOnnistuivat && mysqli_stmt_affected_rows($paivitys) === 1 && mysqli_commit($yhteys)) {
                $viesti = "Vuokraus lisättiin.";
                $asiakasid = 0;
                $autoid = 0;
                $aloituspvm = '';
                $lopetuspvm = '';
            } else {
                $virhe = $kyselytOnnistuivat && mysqli_stmt_affected_rows($paivitys) === 0
                    ? "Auto ei ole vapaana."
                    : "Tallennus epäonnistui.";
                mysqli_rollback($yhteys);
            }
        } else {
            $virhe = "Tallennus epäonnistui.";
        }
    }
}

$asiakkaat = mysqli_query($yhteys, "SELECT id, etunimi, sukunimi FROM asiakas");
$autot = mysqli_query($yhteys, "SELECT id, valmistaja, malli FROM auto WHERE onkovapaa = 1");
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Lisää vuokraus</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Lisää vuokraus</h1>

    <?php if ($virhe != '') { ?><p class="viesti virhe"><?= htmlspecialchars($virhe) ?></p><?php } ?>
    <?php if ($viesti != '') { ?><p class="viesti"><?= htmlspecialchars($viesti) ?></p><?php } ?>

    <form method="post">
        <p>
            <label for="asiakasid">Asiakas:</label>
            <select id="asiakasid" name="asiakasid">
                <option value="0">-- Valitse --</option>
                <?php while ($rivi = mysqli_fetch_assoc($asiakkaat)) { ?>
                    <option value="<?= (int)$rivi['id'] ?>"<?= (int)$rivi['id'] === $asiakasid ? ' selected' : '' ?>>
                        <?= htmlspecialchars($rivi['etunimi'] . " " . $rivi['sukunimi']) ?>
                    </option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="autoid">Auto:</label>
            <select id="autoid" name="autoid">
                <option value="0">-- Valitse --</option>
                <?php while ($rivi = mysqli_fetch_assoc($autot)) { ?>
                    <option value="<?= (int)$rivi['id'] ?>"<?= (int)$rivi['id'] === $autoid ? ' selected' : '' ?>>
                        <?= htmlspecialchars($rivi['valmistaja'] . " " . $rivi['malli']) ?>
                    </option>
                <?php } ?>
            </select>
        </p>
        <p><label for="aloituspvm">Alkaa:</label>
           <input type="date" id="aloituspvm" name="aloituspvm" value="<?= htmlspecialchars($aloituspvm) ?>"></p>
        <p><label for="lopetuspvm">Päättyy:</label>
           <input type="date" id="lopetuspvm" name="lopetuspvm" value="<?= htmlspecialchars($lopetuspvm) ?>"></p>
        <input type="submit" value="Lisää vuokraus">
    </form>
</body>
</html>
