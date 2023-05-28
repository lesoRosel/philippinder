<!DOCTYPE html>
<html>
<head>
    <title>Philippinder</title>
    <link rel="stylesheet" type="text/css" href="index.css">
</head>
<body>
    <header>
        <h1>Benvenuto al nostro sito di incontri!</h1>
    </header>

    <main>
        <div id="profilo">
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

            // Seleziona un profilo casuale dalla tabella utente
            $sqlRandomProfilo = "SELECT utente.id_utente, utente.nome, utente.data_nascita, profilo.foto FROM utente INNER JOIN profilo ON utente.id_profilo = profilo.id_profilo ORDER BY RANDOM() LIMIT 1";
            $resultRandomProfilo = pg_query($conn, $sqlRandomProfilo);

            if ($rowProfilo = pg_fetch_assoc($resultRandomProfilo)) {
                echo "<h2>Informazioni del profilo:</h2>";
                echo "<p>Nome: " . $rowProfilo['nome'] . "</p>";
                echo "<p>Data di nascita: " . $rowProfilo['data_nascita'] . "</p>";
                echo "<img src='" . $rowProfilo['foto'] . "' alt='Foto profilo'>";

                // ID dell'utente loggato
                $idUtenteLoggato = $_SESSION['id_utente'];
                // ID dell'utente randomizzato
                $idUtenteRandomizzato = $rowProfilo['id_utente'];

                // Aggiungi il match nel database al click del pulsante "Mi Piace"
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['miPiace'])) {
                    // Inserisci il match nella tabella "Match"
                    $sqlInsertMatch = "INSERT INTO Match (id_utente_loggato, id_utente_match) VALUES ($idUtenteLoggato, $idUtenteRandomizzato)";
                    $resultInsertMatch = pg_query($conn, $sqlInsertMatch);

                    if ($resultInsertMatch) {
                        echo "<p>Match aggiunto con successo!</p>";
                    } else {
                        echo "<p>Errore nell'aggiunta del match.</p>";
                    }
                }
            } else {
                echo "Nessun profilo trovato.";
            }

            pg_close($conn);
            ?>
        </div>

        <form method="post" action="">
            <button id="passa" onclick="window.location.reload();">Passa</button>
            <button id="miPiace" name="miPiace">Mi Piace</button>
        </form>
    </main>

    <footer>
        <p>© 2023 Sito stile Tinder</p>
    </footer
