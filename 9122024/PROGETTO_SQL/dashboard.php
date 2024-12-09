<?php
// Avvia una sessione per mantenere le variabili globali dell'utente.
session_start();

// Include il file di configurazione per la connessione al database.
require 'config.php';

// Controlla se l'utente è autenticato verificando la presenza di 'user_id' nella sessione.
if (!isset($_SESSION['user_id'])) {
    // Se non è autenticato, reindirizza alla pagina di login.
    header("Location: index.php");
    exit(); // Termina l'esecuzione dello script.
}

// Recupera l'ID e il ruolo dell'utente dalla sessione.
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Sezione dedicata agli studenti.
if ($role === 'student') { 
    // Query per ottenere la classe dello studente utilizzando il suo ID.
    $sql_student_class = "SELECT class FROM utenti WHERE id = ?";
    $stmt = $conn->prepare($sql_student_class); // Prepara la query per prevenire SQL injection.
    $stmt->bind_param("i", $user_id); // Associa l'ID dello studente come parametro.
    $stmt->execute(); // Esegue la query.
    $result = $stmt->get_result(); // Ottiene il risultato della query.

    // Controlla se la query ha restituito un risultato.
    if ($result->num_rows === 1) {
        // Recupera la classe dello studente.
        $student = $result->fetch_assoc();
        $student_class = $student['class'];

        // Query per ottenere i test disponibili per la classe dello studente.
        $sql_class_tests = "
        SELECT t.* 
        FROM tests t
        JOIN test_sessions ts ON t.id = ts.test_id
        WHERE ts.class = ?
        ";
        $stmt = $conn->prepare($sql_class_tests); // Prepara la query.
        $stmt->bind_param("s", $student_class); // Associa la classe come parametro.
        $stmt->execute(); // Esegue la query.
        $tests = $stmt->get_result(); // Ottiene i test disponibili per la classe.
    } else {
        // Se non è possibile recuperare la classe, termina lo script con un messaggio di errore.
        die("Errore nel recupero della classe dello studente.");
    }
}

// Sezione dedicata agli insegnanti.
if ($role === 'teacher') {
    // Query per ottenere i test creati dall'insegnante basandosi sul suo ID.
    $sql_tests = "SELECT * FROM tests WHERE teacher_id = ?";
    $stmt = $conn->prepare($sql_tests); // Prepara la query.
    $stmt->bind_param("i", $user_id); // Associa l'ID dell'insegnante come parametro.
    $stmt->execute(); // Esegue la query.
    $tests = $stmt->get_result(); // Ottiene i test creati dall'insegnante.
}
?>


<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style-Dashboard.css">
</head>

<body>
   

    <?php if ($role === 'student'): ?>
        <h1>Benvenuto nella Dashboard STUDENTI</h1>
        <h2>Test Disponibili per la Tua Classe</h2>
        <?php if ($tests->num_rows > 0): ?>
            <ul>
                <?php while ($test = $tests->fetch_assoc()): ?>
                    <li>
                        <?= htmlspecialchars($test['title']) ?>
                        <a href="take_test.php?id=<?= $test['id'] ?>">Inizia</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Nessun test disponibile per la tua classe.</p>
        <?php endif; ?>

        
        

    <?php elseif ($role === 'teacher'): ?>
        <h1>Benvenuto nella Dashboard INSEGNANTE</h1>
        <h2>Gestione Test</h2>
        <a href="create_test.php">Crea un nuovo test</a>
        <ul>
            <?php while ($test = $tests->fetch_assoc()): ?>
                <li>
                    <?= htmlspecialchars($test['title']) ?>
                    <a href="edit_test.php?id=<?= $test['id'] ?>">Modifica</a>
                    <a href="delete_test.php?id=<?= $test['id'] ?>">Elimina</a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>

    <form action="logout.php">
<button type="submit">Logout</button>
</form>

   
</body>

</html>