<?php
    $nome = $_POST['nome'];
    $data_nascita = $_POST['data_nascita'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Connetti al database
    $host = "localhost";
    $port = "5432";
    $dbname = "testdb";
    $user = "user";
    $password = "password";
    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password")
        or die('Could not connect: ' . pg_last_error());

    // Controlla se l'email è già registrata
    $query = "SELECT * FROM utente WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));

    if (pg_num_rows($result) > 0) {
        echo "Email già registrata!";
    } else {
        // Registra il nuovo utente
        $query = "INSERT INTO utente (nome, data_nascita, email, password) VALUES ($1, $2, $3, $4)";
        $result = pg_query_params($conn, $query, array($nome, $data_nascita, $email, $password));

        if ($result) {
            header("Location: index.html");
        } else {
            echo "Errore durante la registrazione. Per favore riprova.";
        }
    }

    pg_close($conn);
?>
