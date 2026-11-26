<?php
$viestit = [
    'asiakas_lisatty' => 'Asiakas lisättiin.',
    'vuokraus_lisatty' => 'Vuokraus lisättiin.',
    'auto_palautettu' => 'Auto palautettiin.',
    'auto_poistettu' => 'Auto poistettiin.',
    'asiakas_poistettu' => 'Asiakas poistettiin.',
    'vuokraus_poistettu' => 'Vuokraus poistettiin.',
];

$viestiavain = $_GET['viesti'] ?? '';
$viesti = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && is_string($viestiavain)) {
    $viesti = $viestit[$viestiavain] ?? '';
}
