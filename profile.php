<?php
session_start();
// Connessione al database PostgreSQL
$host = "localhost";
$port = "5432";
$dbname = "testdb";
$user = "user";
$password = "password";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connessione al database fallita");
}

// Recupera l'ID del profilo dell'utente (sostituisci con la logica appropriata)
$id_profilo = $_SESSION['id_utente'];

// Esegui una query per ottenere i dati del profilo, inclusa la foto, nome e data di nascita
$query = "SELECT utente.nome, utente.data_nascita, profilo.biografia, profilo.foto 
          FROM utente
          JOIN profilo ON utente.id_utente = profilo.id_profilo
          WHERE utente.id_utente = $id_profilo";
$result = pg_query($conn, $query);

if ($result) {
    // Verifica se esiste un record corrispondente all'ID del profilo
    if (pg_num_rows($result) > 0) {
        $profilo = pg_fetch_assoc($result);
        
        // Recupera il nome, la data di nascita, la biografia e la foto dal record del profilo
        $nome = $profilo['nome'];
        $dataNascita = $profilo['data_nascita'];
        $biografia = $profilo['biografia'];
        $foto = $profilo['foto'];
    } else {
        // Nessun record trovato per l'ID del profilo specificato
        echo 'Profilo non trovato';
        exit;
    }
} else {
    // Errore nella query
    echo 'Errore nella query: ' . pg_last_error($conn);
    exit;
}

// Chiudi la connessione al database
pg_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profilo Utente</title>
    <link rel="stylesheet" type="text/css" href="profile.css">
</head>
<body>
    <div class="user-profile">
        <div id="img">
            <?php
                // Verifica se la foto esiste e stampala
                if (!empty($foto)) {
                    echo '<img src="' . $foto . '" alt="Foto del profilo">';
                } else {
                    echo '<img src="placeholder.jpg" alt="Foto del profilo">';
                }
            ?>
        </div>
        
        <h1><?php echo $nome; ?></h1>
        <p>Data di nascita: <?php echo $dataNascita; ?></p>
        <p>Biografia: <?php echo $biografia; ?></p>
    </div>
</body>
</html>
