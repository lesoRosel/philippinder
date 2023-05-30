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
    $id_utente_loggato = $_SESSION['id_utente'];
    $id_utente_match = $_POST['id_utente_match'];
    
    $query = 'DELETE FROM "match" WHERE id_utente_loggato = $1 AND id_utente_match = $2';
    $result = pg_query_params($conn, $query, array($id_utente_loggato, $id_utente_match));

    if (!$result) {
        echo 'Errore durante l\'eliminazione del match: ' . pg_last_error($conn);
    }
}



// Se viene inviata la data proposta, esegui l'aggiornamento della colonna "data_proposta" nella tabella "match"
if (isset($_POST['invia_data_incontro'])) {
    $id_utente_loggato = $_SESSION['id_utente'];
    $id_utente_match = $_POST['id_utente_match'];
    $data_proposta = $_POST['data_proposta'];
    
    // Esegui la query per aggiornare la colonna "data_proposta" nella tabella "match"
    $query_update_data = 'UPDATE "match" SET data_proposta = $1 WHERE id_utente_loggato = $2 AND id_utente_match = $3';
    $result_update_data = pg_query_params($conn, $query_update_data, array($data_proposta, $id_utente_loggato, $id_utente_match));
    
    if (!$result_update_data) {
        echo 'Errore durante l\'aggiornamento della data proposta: ' . pg_last_error($conn);
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

// Esegui una query per ottenere i match dell'utente, considerando solo quelli con data_proposta null
$query_match = "SELECT utente.id_utente, utente.nome, utente.data_nascita, profilo.foto
                FROM \"match\"
                JOIN utente ON \"match\".id_utente_match = utente.id_utente
                JOIN profilo ON \"match\".id_utente_match = profilo.id_profilo
                WHERE \"match\".id_utente_loggato = $1 AND \"match\".data_proposta IS NULL";
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
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="profile.php">Profilo</a></li>
                <li><a href="profile_edit.html">Modifica Profilo</a></li>
            </ul>
        </nav>
    </header>
</head>
<body>
    <div class="user-profile-rand">
        <h1>Il tuo profilo</h1>
        <!-- Mostra le informazioni del profilo dell'utente -->
        <div class="user-profile_user">
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
                    <input type="hidden" name="id_utente_match" value="<?php echo $m['id_utente']; ?>">
                    <input type="date" name="data_proposta">
                    <input type="submit" name="invia_data_incontro" value="Pianifica un incontro">
                    <input type="submit" name="rifiuta" value="Rifiuta">
                </form>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        // Ricarica la pagina al click del bottone "Rifiuta"
        document.getElementById("Rifiuta").addEventListener("click", function() {
            location.reload();
        });
    </script> 
</body>
</html>
