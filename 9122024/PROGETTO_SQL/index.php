<?php
// Avvia una sessione per poter memorizzare variabili globali per l'utente.
session_start();

// Include il file di configurazione che probabilmente contiene i parametri di connessione al database.
require 'config.php';

// Controlla se il metodo della richiesta è POST (ossia se il form è stato inviato).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Estrae i dati inseriti dall'utente nel form (login e password).
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Prepara una query SQL per cercare l'utente corrispondente al login fornito.
    $sql = "SELECT * FROM utenti WHERE login = ?";
    $stmt = $conn->prepare($sql); // Usa una query parametrizzata per prevenire SQL injection.
    $stmt->bind_param("s", $login); // Associa il valore della variabile $login al parametro della query.
    $stmt->execute(); // Esegue la query.
    $result = $stmt->get_result(); // Ottiene il risultato della query.

    // Controlla se è stato trovato un utente con il login specificato.
    if ($result->num_rows === 1) {
        // Recupera i dati dell'utente come array associativo.
        $user = $result->fetch_assoc();

        // Verifica se la password inserita corrisponde a quella memorizzata nel database usando `password_verify`.
        if (password_verify($password, $user['password'])) {
            // Memorizza i dati dell'utente nella sessione.
            $_SESSION['user_id'] = $user['id']; // ID dell'utente.
            $_SESSION['role'] = $user['role']; // Ruolo dell'utente (es. admin, user).
            $_SESSION['class'] = $user['class']; // Classe dell'utente (se applicabile).

            // Reindirizza l'utente alla dashboard.
            header("Location: dashboard.php");
            exit(); // Termina l'esecuzione dello script per evitare ulteriori elaborazioni.
        }
    }

    // Imposta un messaggio di errore se il login o la password non sono corretti.
    $error = "Login o password errati.";
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/style-login.css">
</head>

<body>
    <h1>Login</h1>
    <form method="POST" action="">
        <label>Login:</label>
        <input type="text" name="login" required>
        <br>
        <label>Password:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Login</button>
        </br>
        </br>
        </br>

        
        
    </form>

    <form action="register.php">
        <input type="submit" value="Registrati" formnovalidate>
    </form>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>

</html>