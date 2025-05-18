<?php
// --- SERVER-SIDE PHP LOGIC ---

// Database file path
$db_file = 'notes.db'; // SQLite database file will be created in the same directory
$db_error = ''; // To store any database connection error messages
$message = ''; // For success or error messages related to form submission

try {
    // Create (connect to) SQLite database in file
    // The PDO object represents a connection between PHP and a database server.
    $db = new PDO('sqlite:' . $db_file);

    // Set errormode to exceptions to handle errors gracefully
    // This tells PDO to throw exceptions when an error occurs, which can be caught in a try-catch block.
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create notes table if it doesn't exist
    // This SQL command creates a table named 'notes' if it's not already present.
    // - id: An integer that is the primary key and auto-increments.
    // - content: Text field for the note, cannot be null.
    // - created_at: Timestamp for when the note was created, defaults to the current time.
    $db->exec("CREATE TABLE IF NOT EXISTS notes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        content TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    // If connection or table creation fails, store the error message.
    $db_error = "Erreur de connexion à la base de données : " . $e->getMessage();
    // In a production app, you'd typically log this error and show a more user-friendly message.
    // For this demo, we'll display it directly.
}

// Handle form submission for adding a new note
// Checks if the request method is POST and if the 'add_note' button was submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    if (isset($db)) { // Proceed only if $db connection was successful
        // Check if 'note_content' is set and not just whitespace.
        if (!empty(trim($_POST['note_content']))) {
            $content = trim($_POST['note_content']); // Get note content from POST and trim whitespace.

            try {
                // Prepare an SQL INSERT statement. Using prepared statements is crucial for preventing SQL injection.
                $stmt = $db->prepare("INSERT INTO notes (content) VALUES (:content)");
                
                // Bind the $content variable to the :content placeholder in the prepared statement.
                $stmt->bindParam(':content', $content);

                // Execute the prepared statement.
                if ($stmt->execute()) {
                    // If successful, redirect to the same page with a success status.
                    // This is the Post/Redirect/Get (PRG) pattern, which prevents form resubmission on page refresh.
                    header("Location: notes_app.php?status=success_add");
                    exit; // Important to call exit after a header redirect.
                } else {
                    $message = "<p class='text-red-500 px-3 py-2 rounded-md bg-red-100 border border-red-300'>Erreur lors de l'ajout de la note.</p>";
                }
            } catch (PDOException $e) {
                $message = "<p class='text-red-500 px-3 py-2 rounded-md bg-red-100 border border-red-300'>Erreur de base de données lors de l'insertion : " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            $message = "<p class='text-yellow-600 px-3 py-2 rounded-md bg-yellow-100 border border-yellow-400'>Le contenu de la note ne peut pas être vide.</p>";
        }
    } else {
         $message = "<p class='text-red-500 px-3 py-2 rounded-md bg-red-100 border border-red-300'>Erreur de base de données. Impossible d'ajouter la note.</p>";
    }
}

// Check for success status from redirect (after adding a note)
if (isset($_GET['status']) && $_GET['status'] === 'success_add') {
    $message = "<p class='text-green-500 px-3 py-2 rounded-md bg-green-100 border border-green-300'>Note ajoutée avec succès !</p>";
}

// Fetch all notes to display
$notes = []; // Initialize an empty array for notes.
if (!$db_error && isset($db)) { // Proceed only if $db connection was successful and $db is set
    try {
        // Prepare and execute a SELECT statement to get all notes, ordered by creation date (newest first).
        $stmt = $db->query("SELECT id, content, created_at FROM notes ORDER BY created_at DESC");
        
        // Fetch all results as an associative array.
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If fetching notes fails, store the error message.
        $db_error = "Erreur lors de la récupération des notes : " . htmlspecialchars($e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application de Notes Simple</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom font (optional, Tailwind uses a system font stack by default) */
        body { font-family: 'Inter', sans-serif; }
        /* Additional custom styles can go here */
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900 py-8">

    <div class="container mx-auto max-w-2xl px-4">
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-blue-600">Mon Bloc-notes PHP</h1>
            <p class="text-gray-600">Une application simple pour gérer vos notes.</p>
        </header>

        <?php if (!empty($db_error) && !isset($db)): // Only show initial connection error if $db is not set ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                <p class="font-bold">Erreur de Base de Données</p>
                <p><?php echo htmlspecialchars($db_error); ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($db)): // Only show form if DB connection was successful ?>
        <div class="bg-white p-6 shadow-lg rounded-xl mb-8 border border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Ajouter une nouvelle note</h2>
            <form action="notes_app.php" method="POST">
                <div class="mb-4">
                    <label for="note_content" class="block text-sm font-medium text-gray-700 mb-1">Contenu de la note :</label>
                    <textarea name="note_content" id="note_content" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" rows="4" placeholder="Écrivez votre note ici..." required></textarea>
                </div>
                <button type="submit" name="add_note" class="w-full px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-150">
                    Ajouter la note
                </button>
            </form>
        </div>
        <?php endif; ?>


        <?php if (!empty($message)): ?>
            <div class="mb-6 text-center">
                <?php echo $message; // Message is already wrapped in <p> with Tailwind classes ?>
            </div>
        <?php endif; ?>
        
        <section class="bg-white p-6 shadow-lg rounded-xl border border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 pb-3 border-b border-gray-200">Notes existantes :</h2>
            <?php if (!empty($db_error) && isset($db)): // Show error if fetching notes failed but connection was initially OK ?>
                 <p class="text-red-600 bg-red-100 p-3 rounded-md border border-red-300"><?php echo htmlspecialchars($db_error); ?></p>
            <?php elseif (isset($db) && empty($notes) && !(isset($_GET['status']) && $_GET['status'] === 'success_add')): ?>
                <p class="text-gray-500 italic">Aucune note pour le moment. Ajoutez-en une ci-dessus !</p>
            <?php elseif (isset($db)): ?>
                <div class="space-y-4">
                    <?php foreach ($notes as $note): ?>
                        <article class="bg-gray-50 p-4 shadow-md rounded-lg border border-gray-200 hover:shadow-lg transition-shadow duration-150">
                            <p class="text-gray-800 text-base whitespace-pre-wrap mb-2">
                                <?php echo htmlspecialchars($note['content']); // Sanitize output to prevent XSS ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                Ajouté le : <?php echo htmlspecialchars(date('d/m/Y à H:i:s', strtotime($note['created_at']))); ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
             <?php if (!isset($db) && empty($db_error)): // Fallback if $db is somehow not set and no specific error yet ?>
                <p class="text-gray-500 italic">La base de données n'est pas accessible pour afficher les notes.</p>
            <?php endif; ?>
        </section>

        <footer class="text-center mt-12 py-4 text-sm text-gray-500">
            <p>TP Démonstration - BUT Informatique - &copy; <?php echo date('Y'); ?></p>
        </footer>

    </div> 

</body>
</html>
