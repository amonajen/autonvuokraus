<?php
mysqli_report(MYSQLI_REPORT_OFF);

$yhteys = mysqli_connect('localhost', 'root', '', 'autonvuokraus');

if (!$yhteys) {
    die('Yhteys epäonnistui: ' . mysqli_connect_error());
}

mysqli_set_charset($yhteys, 'utf8mb4');
date_default_timezone_set('Europe/Helsinki');
