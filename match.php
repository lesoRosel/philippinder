<?php
session_start();

// Connetti al database PostgreSQL
$dbconn = pg_connect("host=localhost dbname=testdb user=user password=password");

// Funzione per ottenere un utente casuale escluso l'utente corrente
function getRandomUser($currentUser, $dbconn) {
    // Ottieni l'ID dell'utente corrente e cerca un utente casuale che non sia l'utente corrente
    $sql = "SELECT id_utente FROM utente WHERE id_utente != $currentUser ORDER BY RANDOM() LIMIT 1";
    $result = pg_query($dbconn, $sql);

    // Restituisci l'ID dell'utente casuale
    $randomUser = pg_fetch_assoc($result);
    return $randomUser['id_utente'];
}

// Funzione per confrontare gli interessi
function compareInterests($currentUser, $randomUser, $dbconn) {
    // Ottieni gli interessi dell'utente corrente
    $sql1 = "SELECT cinema, musica, sport, lettura, viaggi FROM interessi WHERE id_utente = $currentUser";
    $result1 = pg_query($dbconn, $sql1);
    $user1Interests = pg_fetch_assoc($result1);

    // Ottieni gli interessi dell'utente casuale
    $sql2 = "SELECT cinema, musica, sport, lettura, viaggi FROM interessi WHERE id_utente = $randomUser";
    $result2 = pg_query($dbconn, $sql2);
    $user2Interests = pg_fetch_assoc($result2);

    // Conta gli interessi in comune
    $commonInterests = 0;
    foreach ($user1Interests as $interest => $value) {
        if ($value == 'si' && $user2Interests[$interest] == 'si') {
            $commonInterests++;
        }
    }

    // Restituisci il numero di interessi in comune
    return $commonInterests;
}

// Ottieni l'ID dell'utente della sessione corrente
$currentUser = $_SESSION['id_utente'];

// Ottieni un utente casuale che non sia l'utente corrente
$randomUser = getRandomUser($currentUser, $dbconn);

// Confronta gli interessi dell'utente corrente con quelli dell'utente casuale
$commonInterests = compareInterests($currentUser, $randomUser, $dbconn);

// Se ci sono almeno 2 interessi in comune, è un match
if ($commonInterests >= 2) {
    echo "It's a match!";
} else {
    echo "No match.";
}

// Chiudi la connessione
pg_close($dbconn);

?>