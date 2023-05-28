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

// Se viene inviato il comando 'rifiuta', esegui la query DELETE
if (isset($_POST['rifiuta'])) {
    $id_match = $_POST['id_match'];
    $query = 'DELETE FROM match WHERE id = $1';
    $result = pg_query_params($conn, $query, array($id_match));

    if (!$result) {
        echo 'Errore durante l\'eliminazione del match: ' . pg_last_error($conn);
    }
}

// Recupera l'ID del profilo dell'utente
$id_utente = $_SESSION['id_utente'];

// Esegui una query per ottenere i dati del profilo
$query = "SELECT utente.nome, utente.data_nascita, profilo.biografia, profilo.foto 
          FROM utente
          JOIN profilo ON utente.id_utente = profilo.id_profilo
          WHERE utente.id_utente = $1";
$result = pg_query_params($conn, $query, array($id_utente));

if ($result) {
    // Verifica se esiste un record corrispondente all'ID dell'utente
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

// Esegui una query per ottenere i match dell'utente
$query_match = "SELECT utente.nome, utente.data_nascita, profilo.foto
                FROM Match
                JOIN utente ON Match.id_utente_match = utente.id_utente
                JOIN profilo ON Match.id_utente_match = profilo.id_profilo
                WHERE Match.id_utente_loggato = $1";
$result_match = pg_query_params($conn, $query_match, array($id_utente));

if ($result_match) {
    // Recupera i record dei match
    $match = pg_fetch_all($result_match);
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
        <h1>Il tuo profilo</h1>
        <!-- Mostra le informazioni del profilo dell'utente -->
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
    <h1>Richieste</h1>
        <!-- Mostra le informazioni dei match dell'utente -->
        <?php foreach ($match as $m): ?>
            <div class="user-match">
                <div id="img">
                    <img src="<?php echo $m['foto']; ?>" alt="Foto del profilo">
                </div>
                <h2><?php echo $m['nome']; ?></h2>
                <p>Data di nascita: <?php echo $m['data_nascita']; ?></p>

                <form action="" method="post">
                    
                    <input type="date" name="data_incontro">
                    <input type="submit" value="Pianifica un incontro">
                    <input type="submit" value="Rifiuta">
                </form>
                <script>
        // Ricarica la pagina al click del bottone "Passa"
        document.getElementById("Rifiuta").addEventListener("click", function() {
            location.reload();
        });
    </script>  
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
