<?php
// Avvia una sessione per gestire lo stato dell'utente.
session_start();

// Include il file di configurazione per la connessione al database.
require 'config.php';

// Controlla che l'utente abbia il ruolo di "student".
// Se il ruolo non è corretto, reindirizza alla dashboard e termina l'esecuzione dello script.
if ($_SESSION['role'] !== 'student') {
    header("Location: dashboard.php");
    exit();
}

// Controlla che l'ID del test sia presente nei parametri GET.
// Se non è presente, reindirizza alla dashboard.
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

// Recupera l'ID del test dai parametri GET.
$test_id = $_GET['id'];

// Query per recuperare i dettagli del test specifico.
$sql = "SELECT * FROM tests WHERE id = ?";
$stmt = $conn->prepare($sql); // Prepara la query per prevenire SQL injection.
$stmt->bind_param("i", $test_id); // Associa l'ID del test come parametro.
$stmt->execute(); // Esegue la query.
$test_result = $stmt->get_result(); // Ottiene i risultati della query.

// Controlla se il test esiste.
// Se non esiste, reindirizza alla dashboard.
if ($test_result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

// Recupera i dettagli del test come array associativo.
$test = $test_result->fetch_assoc();

// Query per recuperare tutte le domande associate al test.
$sql = "SELECT * FROM questions WHERE test_id = ?";
$stmt = $conn->prepare($sql); // Prepara la query.
$stmt->bind_param("i", $test_id); // Associa l'ID del test come parametro.
$stmt->execute(); // Esegue la query.
$questions = $stmt->get_result(); // Ottiene le domande associate al test.

// Gestione dell'invio delle risposte da parte dello studente.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cicla attraverso le risposte inviate nel form.
    foreach ($_POST['answers'] as $question_id => $answer) {
        // Query per salvare la risposta dello studente.
        $sql = "INSERT INTO test_results (student_id, question_id, answer, is_correct) 
                VALUES (?, ?, ?, ?)";

        // Verifica se la risposta è corretta.
        $stmt_check = $conn->prepare("SELECT is_correct FROM options WHERE question_id = ? AND option_text = ?");
        $stmt_check->bind_param("is", $question_id, $answer); // Associa i parametri della query.
        $stmt_check->execute(); // Esegue la query.
        $check_result = $stmt_check->get_result(); // Ottiene il risultato della verifica.

        // Recupera lo stato di correttezza della risposta (true/false).
        $is_correct = $check_result->fetch_assoc()['is_correct'];

        // Esegue l'inserimento della risposta nel database.
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisi", $_SESSION['user_id'], $question_id, $answer, $is_correct);
        $stmt->execute();
    }

    // Reindirizza l'utente alla pagina dei risultati del test dopo aver salvato le risposte.
    header("Location: test_results.php?test_id=" . $test_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tenta il Test</title>
    <link rel="stylesheet" href="css/style-edit.css">
</head>
<body>
    <h1>Tenta il Test: <?= htmlspecialchars($test['title']) ?></h1>

    <form method="POST" action="">
        <?php while ($question = $questions->fetch_assoc()): ?>
            <h3><?= htmlspecialchars($question['question_text']) ?></h3>
            <?php
            if ($question['type'] === 'multiple_choice'):
                $sql = "SELECT * FROM options WHERE question_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $question['id']);
                $stmt->execute();
                $options = $stmt->get_result();
                while ($option = $options->fetch_assoc()):
            ?>
                    <label>
                        <input type="radio" name="answers[<?= $question['id'] ?>]" value="<?= htmlspecialchars($option['option_text']) ?>">
                        <?= htmlspecialchars($option['option_text']) ?>
                    </label><br>
            <?php
                endwhile;
            else:
            ?>
                <textarea name="answers[<?= $question['id'] ?>]"></textarea>
            <?php endif; ?>
        <?php endwhile; ?>

        <button type="submit">Invia Risposte</button>
    </form>
</body>
</html>
