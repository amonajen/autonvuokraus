<!doctype html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autonvuokraus</title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">
</head>
<body>
    <main class="content-container">
        <h1>Autonvuokraus</h1>
        <nav aria-label="Päävalikko">
            <h2>Autot</h2>
            <ul>
                <li><a href="php/katsele_autoja.php">Katsele autoja</a></li>
                <li><a href="php/lisaa_auto.php">Lisää auto</a></li>
                <li><a href="php/poista_auto.php">Poista auto</a></li>
            </ul>

            <h2>Asiakkaat</h2>
            <ul>
                <li><a href="php/katsele_asiakkaita.php">Katsele asiakkaita</a></li>
                <li><a href="php/lisaa_asiakas.php">Lisää asiakas</a></li>
                <li><a href="php/poista_asiakas.php">Poista asiakas</a></li>
            </ul>

            <h2>Vuokraukset</h2>
            <ul>
                <li><a href="php/katsele_vuokrauksia.php">Katsele vuokrauksia</a></li>
                <li><a href="php/lisaa_vuokraus.php">Lisää vuokraus</a></li>
                <li><a href="php/palauta_auto.php">Palauta auto</a></li>
                <li><a href="php/poista_vuokraus.php">Poista vuokraus</a></li>
            </ul>
        </nav>
    </main>
</body>
</html>
