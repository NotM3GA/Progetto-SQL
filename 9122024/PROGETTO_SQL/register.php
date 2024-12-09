<?php
// Avvia la sessione per gestire lo stato dell'utente.
session_start();

// Include il file di configurazione per stabilire la connessione al database.
require 'config.php';

// Verifica se il metodo della richiesta è POST, che indica che il form è stato inviato.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati inviati dal modulo.
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $class = $_POST['class'] ?? null;

    // Crittografia della password.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Preparazione della query.
    $sql = "INSERT INTO utenti (name, surname, login, password, role, class) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $name, $surname, $login, $hashed_password, $role, $class);

    // Esecuzione della query.
    if ($stmt->execute()) {
        // Se l'inserimento è avvenuto con successo, reindirizza l'utente alla pagina di login.
        header("Location: index.php");
        exit();
    } else {
        // In caso di errore durante l'inserimento.
        $error = "Errore nella registrazione dell'utente.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Registrazione Utente</title>
    <link rel="stylesheet" href="css/style-register.css">
    <style>
        /* Nasconde il campo classe inizialmente */
        #class-field {
            display: none;
        }
    </style>
</head>

<body>
    <h1>Registrazione Utente</h1>
    <form method="POST" action="">
        <label>Nome:</label>
        <input type="text" name="name" required>
        <br>
        <label>Cognome:</label>
        <input type="text" name="surname" required>
        <br>
        <label>Login:</label>
        <input type="text" name="login" required>
        <br>
        <label>Password:</label>
        <input type="password" name="password" required>
        <br>
        <br>
        <label>Ruolo:</label>
        <select name="role" id="role" required>
            <option value="student">Studente</option>
            <option value="teacher">Insegnante</option>
        </select>
        <br>
        <br>
        <!-- Campo Classe -->
        <div id="class-field">
            <label>Classe:</label>
            <input type="text" name="class">
        </div>
        <br>
        <button type="submit" >Registrati</button>
    </form>



    <!-- JAVASCRIPT -->
    <script>
        // Seleziona l'elemento del ruolo e il campo classe
        const ruolo_selezionato = document.getElementById('role');
        const contenuto = document.getElementById('class-field');

        // Aggiunge un event listener per monitorare i cambiamenti nel selettore
        ruolo_selezionato.addEventListener('change', function() {
            if (ruolo_selezionato.value === 'student') {
                // Mostra il campo classe
                contenuto.style.display = 'block';
            } else {
                // Nasconde il campo classe
                contenuto.style.display = 'none';
            }
        });

        // Avvia lo stato iniziale (per mantenere il comportamento coerente al refresh)
        if (ruolo_selezionato.value === 'student') {
            contenuto.style.display = 'block';
        }
    </script>

    <form action="index.php">
        <input type="submit" value="Torna pagina Login" formnovalidate>
    </form>

</body>

</html>