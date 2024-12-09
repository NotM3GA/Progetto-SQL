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

// Recupera l'ID dello studente dalla sessione
$student_id = $_SESSION['user_id']; // Memorizza l'ID dello studente loggato.

// **Recupero dei risultati dello studente**:
// Esegue una query SQL per recuperare le risposte dello studente e le relative risposte corrette.
// La query unisce le tabelle `test_results`, `questions`, e `options` per ottenere:
// - Il testo della domanda (`q.question_text`),
// - La risposta dello studente (`r.answer`),
// - L'indicazione se la risposta è corretta (`r.is_correct`),
// - La risposta corretta (dalla tabella `options` dove `is_correct` è 1, quindi solo la risposta giusta).
$sql_results = "SELECT 
                    q.question_text,  
                    r.answer,         
                    r.is_correct,     
                    o.option_text AS correct_answer  
                FROM test_results r
                JOIN questions q ON r.question_id = q.id  
                JOIN options o ON q.id = o.question_id AND o.is_correct = 1
                WHERE r.student_id = ?";  
                /*
                riga 27  Testo della domanda
                riga 28  Risposta dello studente
                riga 29  Indica se la risposta dello studente è corretta
                riga 30  Risposta corretta              
                riga 32  Unisce con la tabella delle domande
                riga 33  Unisce con la tabella delle opzioni, ottenendo solo la risposta corretta
                riga 34  Filtra i risultati per lo studente con l'ID corrispondente
                */
            
// Prepara la query SQL per l'esecuzione
$stmt = $conn->prepare($sql_results);

// Collega l'ID dello studente come parametro della query (è un intero)
$stmt->bind_param("i", $student_id);

// Esegue la query SQL per ottenere i risultati
$stmt->execute();

// Ottiene il risultato della query e lo memorizza nella variabile `$results`
// Il risultato sarà un oggetto che contiene tutte le righe corrispondenti ai risultati dello studente.
$results = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Risultati</title>
    <link rel="stylesheet" href="css/style-result.css">
</head>
<body>
    <h1>I tuoi risultati</h1>
    <table border="1">
        <tr>
            <th>Domanda</th>
            <th>La tua Risposta</th>
            <th>Risposta Corretta</th>
            <th>Corretta</th>
        </tr>
        <?php while ($row = $results->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['question_text']) ?></td>
                <td><?= htmlspecialchars($row['answer']) ?></td>
                <td><?= htmlspecialchars($row['correct_answer']) ?></td>
                <td style="color: <?= $row['is_correct'] ? 'green' : 'red' ?>;">
                    <?= $row['is_correct'] ? 'Sì' : 'No' ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    
    
    <input type="button" value=" Torna indietro" onClick="history.go(-2);return true;" name="button">
</body>
</html>
