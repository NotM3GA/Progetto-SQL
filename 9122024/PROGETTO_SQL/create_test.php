
<?php
// Avvia una sessione per mantenere lo stato dell'utente.
session_start();

// Include il file di configurazione per la connessione al database.
require 'config.php';

// Controlla che l'utente abbia il ruolo di "teacher".
// Se l'utente non è un insegnante, viene reindirizzato alla dashboard.
if ($_SESSION['role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit(); // Termina l'esecuzione dello script.
}

// Controlla se il modulo è stato inviato con il metodo POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati inviati dal modulo.
    $title = $_POST['title']; // Titolo del test.
    $description = $_POST['description']; // Descrizione del test.
    $class = $_POST['class']; // Classe a cui è destinato il test.
    $teacher_id = $_SESSION['user_id']; // ID dell'insegnante preso dalla sessione.

    // Query per inserire un nuovo test nella tabella `tests`.
    $sql = "INSERT INTO tests (title, description, teacher_id, class) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql); // Prepara la query per prevenire SQL injection.
    $stmt->bind_param("ssis", $title, $description, $teacher_id, $class); // Associa i valori come parametri.

    // Esegue la query e controlla se ha successo.
    if ($stmt->execute()) {
        // Recupera l'ID del test appena creato.
        $test_id = $stmt->insert_id;

        // Query per inserire una sessione di test nella tabella `test_sessions`.
        $sql_test_session = "INSERT INTO test_sessions (test_id, class) VALUES (?, ?)";
        $stmt_session = $conn->prepare($sql_test_session); // Prepara la query.
        $stmt_session->bind_param("is", $test_id, $class); // Associa l'ID del test e la classe come parametri.
        $stmt_session->execute(); // Esegue la query.

        // Reindirizza l'insegnante alla sua dashboard dopo la creazione del test.
        header("Location: dashboard.php");
        exit(); // Termina lo script per evitare ulteriori elaborazioni.
    } else {
        // Imposta un messaggio di errore in caso di fallimento dell'inserimento.
        $error = "Errore nella creazione del test.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crea Test</title>
    <link rel="stylesheet" href="css/style-create.css">
</head>
<body>
    <h1>Crea un nuovo test</h1>
    <form method="POST" action="">
        <label>Titolo:</label>
        <input type="text" name="title" required>
        <br>
        <label>Descrizione:</label>
        <textarea name="description" required></textarea>
        <br>
        <label>Classe:</label>
        <input type="text" name="class" required>
        <br>
        <button type="submit">Crea</button>
    </form>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
