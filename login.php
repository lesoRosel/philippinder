<?php
// Avvia la sessione
session_start();

// Parametri di connessione al database
$host = "localhost";
$port = "5432";
$dbname = "testdb";
$user = "user";
$password = "password";

// Connessione al database
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connessione al database fallita");
}

// Prendi i valori inviati dal form HTML
$email = $_POST['email'];
$password = $_POST['password'];

// Esegui la query per controllare l'email e la password nella tabella "Utente"
$sql = "SELECT * FROM Utente WHERE email = $1 AND password = $2";
$result = pg_query_params($conn, $sql, array($email, $password));

if ($result && pg_num_rows($result) > 0) {
    // L'utente esiste nel database e la password è corretta
    $row = pg_fetch_row($result);
    $new_user_id = $row[4];
    // Registrare i dati dell'utente nella sessione
    $_SESSION['id_utente'] = $new_user_id;
    echo $_SESSION['id_utente'];
    header("Location: index.php");
    
} else {
    // L'utente non esiste o la password è errata
    echo "Accesso fallito. Verifica le credenziali.";
}

// Chiudi la connessione al database
pg_close($conn);
?>
