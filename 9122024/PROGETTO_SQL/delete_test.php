<?php
// Avvia una sessione per mantenere lo stato dell'utente.
session_start();

// Include il file di configurazione per la connessione al database.
require 'config.php';

// Controllo accesso:
// Verifica che l'utente sia autenticato e abbia il ruolo di "teacher".
// Se l'utente non soddisfa i criteri, viene reindirizzato alla pagina di login.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php"); // Reindirizza alla pagina di accesso.
    exit(); // Termina l'esecuzione dello script.
}

// Verifica che sia stato fornito l'ID del test nei parametri GET.
if (isset($_GET['id'])) {
    $test_id = $_GET['id']; // Recupera l'ID del test dai parametri GET.

    // **Elimina i record nella tabella test_sessions**:
    // Rimuove tutte le sessioni di test associate al test specificato.
    $sql_delete_sessions = "DELETE FROM test_sessions WHERE test_id = ?";
    $stmt = $conn->prepare($sql_delete_sessions); // Prepara la query per prevenire SQL injection.
    $stmt->bind_param("i", $test_id); // Associa l'ID del test come parametro.
    $stmt->execute(); // Esegue la query.

    // **Elimina i risultati correlati**:
    // Rimuove i risultati del test associati alle domande del test.
    $sql_delete_results = "DELETE FROM test_results WHERE question_id IN (SELECT id FROM questions WHERE test_id = ?)";
    $stmt = $conn->prepare($sql_delete_results); // Prepara la query.
    $stmt->bind_param("i", $test_id); // Associa l'ID del test come parametro.
    $stmt->execute(); // Esegue la query.

    // **Elimina le opzioni correlate**:
    // Rimuove le opzioni di risposta associate alle domande del test.
    $sql_delete_options = "DELETE FROM options WHERE question_id IN (SELECT id FROM questions WHERE test_id = ?)";
    $stmt = $conn->prepare($sql_delete_options); // Prepara la query.
    $stmt->bind_param("i", $test_id); // Associa l'ID del test come parametro.
    $stmt->execute(); // Esegue la query.

    // **Elimina le domande del test**:
    // Rimuove tutte le domande associate al test.
    $sql_delete_questions = "DELETE FROM questions WHERE test_id = ?";
    $stmt = $conn->prepare($sql_delete_questions); // Prepara la query.
    $stmt->bind_param("i", $test_id); // Associa l'ID del test come parametro.
    $stmt->execute(); // Esegue la query.

    // **Elimina il test**:
    // Rimuove il record del test dalla tabella `tests`.
    $sql_delete_test = "DELETE FROM tests WHERE id = ?";
    $stmt = $conn->prepare($sql_delete_test); // Prepara la query.
    $stmt->bind_param("i", $test_id); // Associa l'ID del test come parametro.
    $stmt->execute(); // Esegue la query.

    // **Reindirizza alla dashboard**:
    // Dopo l'eliminazione, l'utente viene reindirizzato alla sua dashboard.
    header("Location: dashboard.php");
    exit(); // Termina lo script.
} else {
    // Se l'ID del test non è fornito, reindirizza direttamente alla dashboard.
    header("Location: dashboard.php");
    exit(); // Termina lo script.
}
?>
