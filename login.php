<?php
session_start();
require 'db.php';

// 1. VISPIRMS LOĢIKA (Pirms Header)
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lietotajvards = trim($_POST['lietotajvards']);
    $parole = $_POST['parole'];

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

// 2. TAGAD HEADER
require 'header.php';
?>

<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-zinc-900">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-zinc-700">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Ielogoties
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Vai <a href="register.php" class="font-medium text-primary hover:text-green-500 transition">izveidot jaunu profilu</a>
            </p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg text-sm text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['success'])): ?>
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 px-4 py-3 rounded-lg text-sm text-center">
                Reģistrācija veiksmīga! Lūdzu, ielogojieties.
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="lietotajvards" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lietotājvārds</label>
                    <input id="lietotajvards" name="lietotajvards" type="text" required class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition" placeholder="Ievadiet lietotājvārdu">
                </div>
                <div>
                    <label for="parole" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parole</label>
                    <input id="parole" name="parole" type="password" required class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition transform hover:scale-[1.02]">
                Ielogoties
            </button>
        </form>
    </div>
</div>

<?php require 'footer.php'; ?>