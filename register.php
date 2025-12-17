<?php
session_start(); // Ja nav jau startēta db.php
require 'db.php';


if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
if (isset($_SESSION['psihologs_id'])) {
    header("Location: specialist_dashboard.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vards = trim($_POST['vards']);
    $uzvards = trim($_POST['uzvards']);
    $epasts = trim($_POST['epasts']);
    $lietotajvards = trim($_POST['lietotajvards']);
    $parole = password_hash($_POST['parole'], PASSWORD_DEFAULT);

    // Pārbaude, vai lietotājvārds vai e-pasts jau eksistē
    $check = $conn->prepare("SELECT id FROM users WHERE lietotajvards = ? OR epasts = ?");
    $check->bind_param("ss", $lietotajvards, $epasts);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Lietotājvārds vai E-pasts jau eksistē!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (vards, uzvards, epasts, lietotajvards, parole) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $vards, $uzvards, $epasts, $lietotajvards, $parole);

        if ($stmt->execute()) {
            // Pārsūtīšana notiek šeit, pirms jebkāda HTML
            header("Location: login.php?success=1");
            exit();
        } else {
            $error = "Kļūda sistēmā: " . $conn->error;
        }
        $stmt->close();
    }
    $check->close();
}

// 2. TAGAD HEADER
require 'header.php';
?>

<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-zinc-900">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-zinc-700">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Izveidot profilu
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Jau ir profils? <a href="login.php" class="font-medium text-primary hover:text-green-500 transition">Ielogoties</a>
            </p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg text-sm text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vārds</label>
                        <input type="text" name="vards" required class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Uzvārds</label>
                        <input type="text" name="uzvards" required class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">E-pasts</label>
                    <input type="email" name="epasts" required class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lietotājvārds</label>
                    <input type="text" name="lietotajvards" required class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parole</label>
                    <input type="password" name="parole" required class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Vismaz 1 lielais burts, 1 simbols.</p>
                </div>
            </div>

            <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition transform hover:scale-[1.02]">
                Reģistrēties
            </button>
        </form>
    </div>
</div>

<?php require 'footer.php'; ?>