<?php
    session_start(); // Avvia la sessione

    // Dati di input da un form
    $nome = $_POST['nome'];
    $data_nascita = $_POST['data_nascita'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Connessione al database
    $host = "localhost";
    $port = "5432";
    $dbname = "testdb";
    $user = "user";
    $db_password = "password";
    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$db_password")
        or die('Could not connect: ' . pg_last_error());

    // Controlla se l'email è già registrata
    $query = "SELECT * FROM utente WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));

    if (pg_num_rows($result) > 0) {
        echo "Email già registrata!";
    } else {
        // Inizia una transazione
        pg_query($conn, 'BEGIN') or die('Could not start transaction');

        // Registra il nuovo utente
        
        $query = "INSERT INTO utente (nome, data_nascita, email, password) VALUES ($1, $2, $3, $4) RETURNING id_utente";
        $result = pg_query_params($conn, $query, array($nome, $data_nascita, $email, $password));
        
        if (!$result) {
            echo "Errore durante la registrazione. Per favore riprova.";
            pg_query($conn, 'ROLLBACK');
            pg_close($conn);
            exit;
        }

        // Ottieni l'ID dell'utente appena registrato
        $row = pg_fetch_row($result);
        $new_user_id = $row[0];
        
        // Crea una nuova riga nella tabella profilo
        $query2 = "INSERT INTO \"profilo\" (\"id_profilo\") VALUES ($1)";
        $result = pg_query_params($conn, $query2, array($new_user_id));

        if (!$result) {
            echo "Errore durante la creazione del profilo. Per favore riprova.";
            pg_query($conn, 'ROLLBACK');
            pg_close($conn);
            exit;
        }

        // Crea una nuova riga nella tabella interessi
        $query = "INSERT INTO interessi (id_utente) VALUES ($1)";
        $result = pg_query_params($conn, $query, array($new_user_id));

        if (!$result) {
            echo "Errore durante la creazione degli interessi. Per favore riprova.";
            pg_query($conn, 'ROLLBACK');
            pg_close($conn);
            exit;
        }

        // Se tutto va bene, commit della transazione
        pg_query($conn, 'COMMIT');

        // Imposta i dati dell'utente nella sessione
        $_SESSION['id_utente'] = $new_user_id;
        $_SESSION['username'] = $email;

        // Reindirizza l'utente alla pagina dopo la registrazione
        header("Location: index.php");
    }

    pg_close($conn);
?>
