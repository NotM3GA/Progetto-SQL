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

// Controlla che gli ID della domanda e del test siano presenti nei parametri GET.
if (isset($_GET['id']) && isset($_GET['test_id'])) {
    // Recupera gli ID della domanda e del test dai parametri GET.
    $question_id = $_GET['id'];
    $test_id = $_GET['test_id'];

    // Query per eliminare tutte le opzioni associate alla domanda.
    $sql = "DELETE FROM options WHERE question_id = ?";
    $stmt = $conn->prepare($sql); // Prepara la query per prevenire SQL injection.
    $stmt->bind_param("i", $question_id); // Associa l'ID della domanda come parametro.
    $stmt->execute(); // Esegue la query.

    // Query per eliminare la domanda dalla tabella `questions`.
    $sql = "DELETE FROM questions WHERE id = ?";
    $stmt = $conn->prepare($sql); // Prepara la query.
    $stmt->bind_param("i", $question_id); // Associa l'ID della domanda come parametro.
    
    // Esegue la query e controlla se ha successo.
    if ($stmt->execute()) {
        // Se l'eliminazione è riuscita, reindirizza alla pagina di modifica del test.
        header("Location: edit_test.php?id=$test_id");
        exit(); // Termina lo script per evitare ulteriori elaborazioni.
    } else {
        // Se c'è un errore durante l'eliminazione, mostra un messaggio di errore.
        echo "Errore nell'eliminazione della domanda.";
    }
}
?>
