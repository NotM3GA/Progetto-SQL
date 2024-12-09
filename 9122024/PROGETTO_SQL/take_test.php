<?php
// Avvia la sessione per mantenere lo stato dell'utente.
session_start();

// Include il file di configurazione per la connessione al database.
require 'config.php';

// **Controllo di accesso**:
// Verifica che l'utente sia loggato e che il suo ruolo sia 'student'.
// Se non è loggato o non ha il ruolo di studente, reindirizza l'utente alla pagina di login (index.php).
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php"); // Reindirizza alla pagina di login.
    exit(); // Termina l'esecuzione dello script.
}

// Recupera l'ID del test dal parametro `id` passato tramite URL.
$test_id = $_GET['id']; // `$_GET['id']` contiene l'ID del test che si vuole visualizzare.

// **Recupero delle domande**:
// Esegue una query SQL per recuperare tutte le domande associate al test con l'ID specificato.
// La query seleziona tutte le righe dalla tabella `questions` dove il campo `test_id` corrisponde all'ID del test fornito.
$sql = "SELECT * FROM questions WHERE test_id = ?";

// Prepara la query SQL per l'esecuzione.
$stmt = $conn->prepare($sql);

// Collega il parametro `test_id` alla query (è un intero).
$stmt->bind_param("i", $test_id);

// Esegue la query SQL per ottenere le domande per il test.
$stmt->execute();

// Ottiene il risultato della query e lo memorizza nella variabile `$questions`
// Il risultato sarà un oggetto che può essere utilizzato per iterare sulle domande.
$questions = $stmt->get_result();
?>



<!DOCTYPE html>
<html>
<head>
    <title>Compila il Test</title>
    <link rel="stylesheet" href="css/style-edit.css">
</head>
<body>
    <h1>Compila il Test</h1>
    <form method="POST" action="submit_test.php">
        <input type="hidden" name="test_id" value="<?= $test_id ?>">
        <?php while ($question = $questions->fetch_assoc()): ?>
            <h3><?= htmlspecialchars($question['question_text']) ?></h3>
            <?php
            // Recupera le opzioni di risposta per ogni domanda
            $sql_options = "SELECT * FROM options WHERE question_id = ?";
            $stmt_options = $conn->prepare($sql_options);
            $stmt_options->bind_param("i", $question['id']);
            $stmt_options->execute();
            $options = $stmt_options->get_result();
            ?>
            <?php while ($option = $options->fetch_assoc()): ?>
                <label>
                    <input type="radio" name="answers[<?= $question['id'] ?>]" value="<?= htmlspecialchars($option['option_text']) ?>" required>
                    <?= htmlspecialchars($option['option_text']) ?>
                </label>
                <br>
            <?php endwhile; ?>
        <?php endwhile; ?>
        <button type="submit">Invia</button>
    </form>
</body>
</html>
