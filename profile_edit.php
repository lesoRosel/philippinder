<?php
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
    $id_profilo = 1;

    // Salvataggio della biografia
    $biografia = isset($_POST['biografia']) ? $_POST['biografia'] : '';
    $sqlBiografia = "UPDATE Profilo SET biografia = '$biografia' WHERE id_profilo = $id_profilo";
    $resultBiografia = pg_query($conn, $sqlBiografia);

    if (!$resultBiografia) {
        echo "Errore nell'aggiornamento della biografia: " . pg_last_error($conn);
    }

    // Salvataggio dell'etnia
    $etnia = isset($_POST['etnia']) ? $_POST['etnia'] : '';
    $sqlEtnia = "UPDATE Profilo SET etnia = '$etnia' WHERE id_profilo = $id_profilo";
    $resultEtnia = pg_query($conn, $sqlEtnia);

    if (!$resultEtnia) {
        echo "Errore nell'aggiornamento dell'etnia: " . pg_last_error($conn);
    }

    // Salvataggio della foto
    $foto = $_FILES['foto']['tmp_name'];
    $fotoNome = $_FILES['foto']['name'];

    if (!empty($foto)) {
        // Rimuovi il valore precedente della foto
        $sqlRimuoviFoto = "UPDATE Profilo SET foto = NULL WHERE id_profilo = $id_profilo";
        $resultRimuoviFoto = pg_query($conn, $sqlRimuoviFoto);

        if (!$resultRimuoviFoto) {
            echo "Errore nella rimozione del valore precedente della foto: " . pg_last_error($conn);
        }

        // Salva la nuova foto
        $destinazione = "./uploads/" . $fotoNome;

        if (move_uploaded_file($foto, $destinazione)) {
            $sqlFoto = "UPDATE Profilo SET foto = '$destinazione' WHERE id_profilo = $id_profilo";
            $resultFoto = pg_query($conn, $sqlFoto);

            if (!$resultFoto) {
                echo "Errore nell'aggiornamento della foto: " . pg_last_error($conn);
            }
        } else {
            echo "Errore durante il caricamento del file.";
        }
    }

    // Elaborazione del salvataggio dei valori degli interessi
    $interessiSelezionati = isset($_POST['interessi']) ? $_POST['interessi'] : [];

    foreach ($interessiSelezionati as $idInteresse) {
        $valore = isset($_POST['interesse_' . $idInteresse]) ? true : false;

        $sqlInteressi = "UPDATE Interessi SET valore = $valore WHERE id_interesse = $idInteresse";
        $resultInteressi = pg_query($conn, $sqlInteressi);

        if (!$resultInteressi) {
            echo "Errore nell'aggiornamento dell'interesse con ID $idInteresse: " . pg_last_error($conn);
        }
    }

    echo "Modifica del profilo salvata!";
}

pg_close($conn);
?>
