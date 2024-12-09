<?php
// Avvia la sessione per gestire lo stato dell'utente.
session_start();

// Include il file di configurazione per la connessione al database.
require 'config.php';

// **Controllo ruolo insegnante**:
// Verifica che l'utente abbia il ruolo di "teacher".
// Se l'utente non è un insegnante, reindirizza alla dashboard.
if ($_SESSION['role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit(); // Termina l'esecuzione dello script.
}

// **Verifica test ID**:
// Controlla che l'ID del test sia specificato nei parametri GET.
if (!isset($_GET['id'])) {
    die("Test ID non specificato."); // Interrompe lo script con un messaggio di errore.
}
$test_id = $_GET['id']; // Recupera l'ID del test dai parametri GET.

// **Recupera i dettagli del test**:
// Prepara una query per ottenere i dettagli del test dal database.
$sql_test = "SELECT * FROM tests WHERE id = ?";
$stmt = $conn->prepare($sql_test); // Prepara la query per evitare SQL injection.
$stmt->bind_param("i", $test_id); // Associa l'ID del test come parametro.
$stmt->execute(); // Esegue la query.
$test = $stmt->get_result()->fetch_assoc(); // Ottiene il risultato come array associativo.

// Controlla se il test esiste. Se non esiste, interrompe lo script con un messaggio di errore.
if (!$test) {
    die("Test non trovato.");
}

// **Recupera le domande associate**:
// Prepara una query per ottenere tutte le domande associate al test.
$sql_questions = "SELECT * FROM questions WHERE test_id = ?";
$stmt = $conn->prepare($sql_questions); // Prepara la query.
$stmt->bind_param("i", $test_id); // Associa l'ID del test come parametro.
$stmt->execute(); // Esegue la query.
$questions = $stmt->get_result(); // Ottiene il risultato.

// **Aggiungi una nuova domanda con risposte**:
// Verifica se la richiesta è di tipo POST e se è stato inviato il testo della domanda.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_text'])) {
    $question_text = $_POST['question_text']; // Recupera il testo della domanda dal form.
    $answers = $_POST['answers']; // Recupera le opzioni di risposta dal form.
    $correct_answers = isset($_POST['correct_answer']) ? $_POST['correct_answer'] : []; // Recupera le risposte corrette.

    // **Inserisci la domanda**:
    // Prepara una query per inserire la domanda nella tabella `questions`.
    $sql_add_question = "INSERT INTO questions (test_id, question_text) VALUES (?, ?)";
    $stmt = $conn->prepare($sql_add_question); // Prepara la query.
    $stmt->bind_param("is", $test_id, $question_text); // Associa i parametri del test ID e del testo della domanda.
    $stmt->execute(); // Esegue la query.
    $question_id = $stmt->insert_id; // Recupera l'ID della domanda appena inserita.

    // **Inserisci le risposte**:
    // Prepara una query per inserire le opzioni di risposta nella tabella `options`.
    $sql_add_option = "INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_add_option); // Prepara la query.

    // Itera attraverso le risposte inviate dal form.
    foreach ($answers as $index => $answer_text) {
        // Verifica se l'opzione è una risposta corretta.
        $is_correct = in_array($index, $correct_answers) ? 1 : 0;
        $stmt->bind_param("isi", $question_id, $answer_text, $is_correct); // Associa i parametri.
        $stmt->execute(); // Esegue la query per ogni opzione.
    }

    // Dopo aver aggiunto la domanda e le risposte, reindirizza alla pagina di modifica del test.
    header("Location: edit_test.php?id=$test_id");
    exit(); // Termina lo script.
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Modifica Test</title>
    <link rel="stylesheet" href="css/style-edit.css">
</head>

<body>
    <h1>Modifica Test: <?= htmlspecialchars($test['title']) ?></h1>

    <!-- Modulo per aggiungere una nuova domanda -->
    <form method="POST" action="">
        <h3>Aggiungi una Domanda</h3>
        <label>Domanda:</label>
        <textarea name="question_text" required></textarea><br>

        <label>Risposte:</label>
        <div id="answers-container">
            <div>
                <input type="text" name="answers[0]" placeholder="Risposta 1" required>
                <input type="checkbox" name="correct_answer[]" value="0"> Corretta
            </div>
            <div>
                <input type="text" name="answers[1]" placeholder="Risposta 2" required>
                <input type="checkbox" name="correct_answer[]" value="1"> Corretta
            </div>
        </div>
        <button type="button" onclick="addAnswer()">Aggiungi Risposta</button><br><br>
        <button type="submit">Salva Domanda</button>
    </form>

    <!-- Domande esistenti -->
    <h3>Domande esistenti:</h3>
    <ul>
        <?php while ($question = $questions->fetch_assoc()): ?>
            <li>
                <?= htmlspecialchars($question['question_text']) ?>
                <a href="delete_question.php?id=<?= $question['id'] ?>&test_id=<?= $test_id ?>">Elimina</a>
            </li>
        <?php endwhile; ?>
    </ul>

    <form action="dashboard.php">
        <button type="submit">Torna alla Dashboard</button>
    </form>

    <script>
        let answerCount = 2;

        function addAnswer() {
            const container = document.getElementById("answers-container");
            const newAnswer = document.createElement("div");
            newAnswer.innerHTML = `
                <input type="text" name="answers[${answerCount}]" placeholder="Risposta ${answerCount + 1}" required>
                <input type="checkbox" name="correct_answer[]" value="${answerCount}"> Corretta
            `;
            container.appendChild(newAnswer);
            answerCount++;
        }
    </script>
</body>

</html>