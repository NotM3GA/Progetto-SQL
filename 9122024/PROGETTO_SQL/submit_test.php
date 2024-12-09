<?php
// Avvia la sessione per mantenere lo stato dell'utente.
session_start();

// Include il file di configurazione per connettersi al database.
require 'config.php';

// **Controllo accesso**:
// Verifica che l'utente sia autenticato e che il ruolo sia "student".
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php"); // Reindirizza alla pagina di login se l'utente non è autenticato o il ruolo non è corretto.
    exit(); // Termina l'esecuzione dello script.
}

// Recupera l'ID dello studente dalla sessione.
$student_id = $_SESSION['user_id'];

// **Gestione del form**:
// Verifica che il modulo sia stato inviato con il metodo POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati inviati dal form: ID del test e risposte dell'utente.
    $test_id = $_POST['test_id']; // ID del test.
    $answers = $_POST['answers']; // Array associativo question_id => answer.

    // **Eliminazione dei risultati precedenti**:
    // Rimuove le risposte già registrate per lo studente in questo test, in modo da evitare duplicati.
    $sql_delete_previous_results = "DELETE FROM test_results 
                                    WHERE question_id IN (
                                        SELECT id FROM questions WHERE test_id = ?
                                    ) 
                                    AND student_id = ?";
    $stmt = $conn->prepare($sql_delete_previous_results);
    $stmt->bind_param("ii", $test_id, $student_id);
    $stmt->execute();

    // **Inserimento dei nuovi risultati**:
    foreach ($answers as $question_id => $answer) {
        // **Validazione delle risposte**:
        // Recupera la risposta corretta dalla tabella `options` per la domanda corrente.
        $sql_validate = "SELECT o.option_text AS correct_answer 
                         FROM options o
                         WHERE o.question_id = ? AND o.is_correct = 1";
        $stmt_validate = $conn->prepare($sql_validate);
        $stmt_validate->bind_param("i", $question_id);
        $stmt_validate->execute();
        $result = $stmt_validate->get_result();

        $correct_answer = ""; // Inizializza la variabile della risposta corretta.
        $is_correct = 0; // Inizializza lo stato di correttezza della risposta.

        if ($result->num_rows === 1) {
            // Recupera la risposta corretta e verifica se coincide con quella fornita dall'utente.
            $row = $result->fetch_assoc();
            $correct_answer = $row['correct_answer'];
            $is_correct = (strtolower($answer) === strtolower($correct_answer)) ? 1 : 0; // Confronta in modo insensibile alla maiuscola/minuscola.
        }

        // **Salvataggio del risultato**:
        // Inserisce la risposta dell'utente nella tabella `test_results`.
        $sql_insert_results = "INSERT INTO test_results (student_id, question_id, answer, is_correct) 
                               VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert_results);
        $stmt_insert->bind_param("iisi", $student_id, $question_id, $answer, $is_correct);
        $stmt_insert->execute();
    }

    // **Reindirizzamento**:
    // Dopo aver salvato i risultati, reindirizza l'utente alla pagina dei risultati.
    header("Location: view_results.php");
    exit(); // Termina l'esecuzione dello script.
} else {
    // **Gestione accessi non validi**:
    // Se l'accesso non avviene tramite POST, reindirizza l'utente alla dashboard.
    header("Location: dashboard.php");
    exit(); // Termina l'esecuzione dello script.
}
?>
