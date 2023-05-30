<!DOCTYPE html>
<html>
<head>
    <title>Philippinder</title>
    <link rel="stylesheet" type="text/css" href="index.css">
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="profile.php">Profilo</a></li>
                <li><a href="profile_edit.html">Modifica Profilo</a></li>
                <li><a href="login.html">Logout</a></li> 
            </ul>
        </nav>
    </header>
</head>
<body>
    <header>
        <h1>Sito di incontri per Ching Chong People</h1>
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

            // ID dell'utente loggato
            session_start();
            $idUtenteLoggato = $_SESSION['id_utente'];

            if (!isset($_SESSION['id_utente'])) {
                // L'utente non ha effettuato l'accesso, reindirizzalo alla pagina di login o mostra un messaggio di errore
                header("Location: login.php"); // Modifica il percorso con la pagina di login
                exit;
            }

            // Seleziona un profilo casuale dalla tabella utente
            $sqlRandomProfilo = "SELECT utente.nome, utente.data_nascita, profilo.foto, profilo.biografia, utente.id_utente FROM utente INNER JOIN profilo ON utente.id_utente = profilo.id_profilo ORDER BY RANDOM() LIMIT 1";
            $resultRandomProfilo = pg_query($conn, $sqlRandomProfilo);

            if ($rowProfilo = pg_fetch_assoc($resultRandomProfilo)) {
                echo "<h2>Informazioni del profilo:</h2>";
                echo "<p>Nome: " . $rowProfilo['nome'] . "</p>";
                echo "<p>Data di nascita: " . $rowProfilo['data_nascita'] . "</p>";
                echo "<p>Biografia: " . $rowProfilo['biografia'] . "</p>";
                echo "<div><img src='" . $rowProfilo['foto'] . "' alt='Foto profilo'></div>";

                // ID dell'utente randomizzato
                $idUtenteRandomizzato = $rowProfilo['id_utente'];

                // Controlla se ci sono interessi corrispondenti con valore "si"
                $sqlInteressiUtenteLoggato = "SELECT cinema, musica, sport, lettura, viaggi FROM interessi WHERE id_utente = $idUtenteLoggato";
                $resultInteressiUtenteLoggato = pg_query($conn, $sqlInteressiUtenteLoggato);
                $interessiUtenteLoggato = pg_fetch_assoc($resultInteressiUtenteLoggato);

                $sqlInteressiUtenteRandomizzato = "SELECT cinema, musica, sport, lettura, viaggi FROM interessi WHERE id_utente = $idUtenteRandomizzato";
                $resultInteressiUtenteRandomizzato = pg_query($conn, $sqlInteressiUtenteRandomizzato);
                $interessiUtenteRandomizzato = pg_fetch_assoc($resultInteressiUtenteRandomizzato);

                $match = false;

                $interessiColonne = ['cinema', 'musica', 'sport', 'lettura', 'viaggi'];

                foreach ($interessiColonne as $colonna) {
                    if ($interessiUtenteLoggato[$colonna] == "si" && $interessiUtenteRandomizzato[$colonna] == "si") {
                        $match = true;
                        break;
                    }
                }

                // Aggiungi il match nel database solo se c'è un match
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['miPiace']) && $match) {
                    // Inserisci il match nella tabella "match"
                    $sqlInsertMatch = "INSERT INTO \"match\" (id_utente_loggato, id_utente_match) VALUES ($idUtenteLoggato, $idUtenteRandomizzato)";
                    $resultInsertMatch = pg_query($conn, $sqlInsertMatch);

                    if ($resultInsertMatch) {
                        echo "<p>Match aggiunto con successo!</p>";
                    } else {
                        echo "<p>Errore nell'aggiunta del match.</p>";
                    }
                } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['miPiace'])) {
                    echo "<p>Nessun match trovato.</p>";
                }
            } else {
                echo "Nessun profilo trovato.";
            }

            pg_close($conn);
            ?>
        </div>

        <form method="post" action="">
            <button id="passa">Passa</button>
            <button id="miPiace" name="miPiace">Mi Piace</button>
        </form>
    </main>
    <script>
        // Ricarica la pagina al click del bottone "Passa"
        document.getElementById("passa").addEventListener("click", function() {
            location.reload();
        });
    </script>        
</body>
</html>
