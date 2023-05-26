<?php
// Connessione al database
$dbconn = pg_connect("host=localhost dbname=testdb user=user password=password")
    or die('Could not connect: ' . pg_last_error());

// Recupera l'ID dell'utente loggato. Questo dovrebbe essere memorizzato in qualche modo, ad esempio in una variabile di sessione.
$id_utente = $_SESSION['id_utente'];

// Esegui la query SQL
$query = "SELECT utente.nome, profilo.biografia, utente.data_nascita, profilo.foto 
          FROM utente 
          INNER JOIN profilo ON utente.id_utente = profilo.id_profilo 
          WHERE utente.id_utente = $1";
$result = pg_query_params($dbconn, $query, array($id_utente));
// Recupera le informazioni dell'utente
$user = pg_fetch_assoc($result);


?>
