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

// Elaborazione del salvataggio dei valori della biografia, etnia e foto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ottenere l'ID del profilo dell'utente (sostituisci con la logica appropriata)
    $id_profilo = $_SESSION['id_utente'];

    // Salvataggio della biografia
    $biografia = isset($_POST['biografia']) ? $_POST['biografia'] : '';
    $sqlBiografia = "UPDATE profilo SET biografia = '$biografia' WHERE id_profilo = $id_profilo";
    $resultBiografia = pg_query($conn, $sqlBiografia);

    if (!$resultBiografia) {
        echo "Errore nell'aggiornamento della biografia: " . pg_last_error($conn);
    }

    // Salvataggio dell'etnia
    $etnia = isset($_POST['etnia']) ? $_POST['etnia'] : '';
    $sqlEtnia = "UPDATE profilo SET etnia = '$etnia' WHERE id_profilo = $id_profilo";
    $resultEtnia = pg_query($conn, $sqlEtnia);

    if (!$resultEtnia) {
        echo "Errore nell'aggiornamento dell'etnia: " . pg_last_error($conn);
    }

    // Salvataggio della foto
    $foto = $_FILES['foto']['tmp_name'];
    $fotoNome = $_FILES['foto']['name'];

    if (!empty($foto)) {
        // Rimuovi il valore precedente della foto
        $sqlRimuoviFoto = "UPDATE profilo SET foto = NULL WHERE id_profilo = $id_profilo";
        $resultRimuoviFoto = pg_query($conn, $sqlRimuoviFoto);

        if (!$resultRimuoviFoto) {
            echo "Errore nella rimozione del valore precedente della foto: " . pg_last_error($conn);
        }

        // Genera un nome univoco per la foto
        $nomeFotoUnivoco = uniqid() . '_' . $fotoNome;

        // Sposta il file nella cartella "uploads"
        $destinazioneFoto = "uploads/" . $nomeFotoUnivoco;
        if (move_uploaded_file($foto, $destinazioneFoto)) {
            // Salva il percorso della foto nella colonna "foto" della tabella "profilo"
            $sqlFoto = "UPDATE profilo SET foto = '$destinazioneFoto' WHERE id_profilo = $id_profilo";
            $resultFoto = pg_query($conn, $sqlFoto);

            if (!$resultFoto) {
                echo "Errore nell'aggiornamento della foto: " . pg_last_error($conn);
            }
        } else {
            echo "Errore durante il caricamento della foto.";
        }
    }

    // Elaborazione del salvataggio dei valori degli interessi
    $interessi = ['cinema', 'musica', 'sport', 'lettura', 'viaggi'];

    foreach ($interessi as $interesse) {
        $valore = isset($_POST[$interesse]) && $_POST[$interesse] === 'si' ? 'si' : 'no';

        $sqlInteressi = "UPDATE interessi SET $interesse = '$valore' WHERE id_utente = $id_profilo";
        $resultInteressi = pg_query($conn, $sqlInteressi);

        if (!$resultInteressi) {
            echo "Errore nell'aggiornamento dell'interesse $interesse: " . pg_last_error($conn);
        }
    }

    header("Location: profile_edit.html");
}

pg_close($conn);
?>
