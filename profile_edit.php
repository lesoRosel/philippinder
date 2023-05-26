<?php
// Connessione al database
$host = "localhost";
$port = "5432";
$dbname = "testdb";
$user = "user";
$password = "password";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connessione al database fallita");
}

// Prendi i valori inviati dal form HTML
$biografia = $_POST['biografia'];
$etnia = $_POST['etnia'];
$foto = $_FILES['foto'];

// Gestione del salvataggio del file
$nomeFile = time() . '_' . uniqid() . '_' . $foto['name'];
$destinazione = 'C:/xampp/htdocs/philippinder/uploads/' . $nomeFile;

if (move_uploaded_file($foto['tmp_name'], $destinazione)) {
    // Salvataggio del file riuscito

    // Esegui l'aggiornamento della riga nella tabella "Profilo" con il percorso del file
    $sql = "UPDATE Profilo SET biografia = '$biografia', etnia = '$etnia', foto = '$destinazione' WHERE id_profilo = 1";
    $result = pg_query($conn, $sql);

    if ($result) {
        echo "Profilo aggiornato con successo!";
    } else {
        echo "Errore durante l'aggiornamento del profilo: " . pg_last_error($conn);
    }
} else {
    // Errore nel salvataggio del file
    echo "Errore durante il caricamento del file.";
}

pg_close($conn);
?>