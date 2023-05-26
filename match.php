<?php
session_start();

// Verifica se l'utente è loggato
if (!isset($_SESSION['id_utente'])) {
  die('Devi essere loggato per vedere i tuoi match');
}

$id_utente = $_SESSION['id_utente'];

$dbconn = pg_connect("host=localhost dbname=testdb user=user password=password");

if (!$dbconn) {
  die('Could not connect: ' . pg_last_error());
}

// Esegue il codice di matching qui (vedi il codice del post precedente)
// Assumendo che $id_utente contenga l'ID dell'utente attualmente loggato
$query = "SELECT * FROM interessi WHERE id_utente = $1";
$result = pg_query_params($dbconn, $query, array($id_utente));

if (!$result) {
  die('Errore nella query: ' . pg_last_error($dbconn));
}

$user_interests = pg_fetch_assoc($result);

// Costruisci la query di matching
$matches_query = "SELECT id_utente FROM interessi WHERE id_utente != $1 AND (";
$interest_counter = 0;

foreach ($user_interests as $interest => $value) {
  if ($value && $interest != 'id_utente') {
    if ($interest_counter >= 1) {
      $matches_query .= ' + ';
    }
    $matches_query .= "(CASE WHEN $interest = true THEN 1 ELSE 0 END)";
    $interest_counter++;
  }
}

$matches_query .= ") >= 2";  // Almeno due interessi in comune

$matches_result = pg_query_params($dbconn, $matches_query, array($id_utente));

if (!$matches_result) {
  die('Errore nella query: ' . pg_last_error($dbconn));
}

$matches = pg_fetch_all_columns($matches_result);

echo "Gli utenti con almeno due interessi in comune sono: " . implode(', ', $matches);


pg_close($dbconn);
?>