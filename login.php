<?php
session_start();
require 'db.php';

// 1. VISPIRMS LOĢIKA (Pirms jebkāda HTML)

// Ja lietotājs jau ir ielogojies, sūtam uz dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lietotajvards = trim($_POST['lietotajvards']);
    $parole = $_POST['parole'];

    // Izmantojam prepared statements drošībai
    $stmt = $conn->prepare("SELECT id, vards, parole FROM users WHERE lietotajvards = ?");
    $stmt->bind_param("s", $lietotajvards);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($parole, $row['parole'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['vards'] = $row['vards'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Nepareiza parole!";
        }
    } else {
        $error = "Lietotājs nav atrasts!";
    }
    $stmt->close();
}

// 2. TIKAI TAGAD IELĀDĒJAM HEADER (jo tas satur HTML)
require 'header.php';
?>

<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-zinc-900">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-zinc-700">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Ielogoties
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Vai <a href="register.php" class="font-medium text-primary hover:text-green-500">izveidot jaunu profilu</a>
            </p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">Reģistrācija veiksmīga! Lūdzu, ielogojieties.</span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="lietotajvards" class="sr-only">Lietotājvārds</label>
                    <input id="lietotajvards" name="lietotajvards" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-500 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-t-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Lietotājvārds">
                </div>
                <div>
                    <label for="parole" class="sr-only">Parole</label>
                    <input id="parole" name="parole" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-500 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-b-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Parole">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                    Ielogoties
                </button>
            </div>
        </form>
    </div>
</div>

<?php require 'footer.php'; ?>