<?php
session_start(); // Ja nav jau startēta db.php
require 'db.php';


if (isset($_SESSION['account_id'])) {
    header("Location: dashboard.php");
    exit();
}
if (isset($_SESSION['account_id'])) {
    header("Location: specialist_dashboard.php");
    exit();
}

$error = "";
$role = 'user'; // default
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'] ?? 'user';
    $vards = trim($_POST['vards']);
    $uzvards = trim($_POST['uzvards']);
    $telefons = trim($_POST['telefons']);
    $epasts = trim($_POST['epasts']);
    $lietotajvards = trim($_POST['lietotajvards']);
    $rawPassword = $_POST['parole'];

    // Additional for psychologist
    $specialization = trim($_POST['specialization'] ?? '');
    $experience_years = (int)($_POST['experience_years'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $hourly_rate = (float)($_POST['hourly_rate'] ?? 0.00);

    // Paroles validācija: vismaz 1 lielais, 1 mazais, 1 simbols
    $hasUpper = preg_match('/[A-ZĀČĒĢĪĶĻŅŠŪŽ]/u', $rawPassword);
    $hasLower = preg_match('/[a-zāčēģīķļņšūž]/u', $rawPassword);
    $hasSymbol = preg_match('/[^A-Za-z0-9ĀČĒĢĪĶĻŅŠŪŽāčēģīķļņšūž]/u', $rawPassword);
    if (!$hasUpper || !$hasLower || !$hasSymbol) {
        $error = "Parolei jāietver vismaz 1 lielais burts, 1 mazais burts un 1 simbols.";
    } else {
        $parole = password_hash($rawPassword, PASSWORD_DEFAULT);
    }

    if (empty($error)) {
        // Pārbaude, vai lietotājvārds vai e-pasts jau eksistē
        $check = $conn->prepare("SELECT id FROM accounts WHERE username = ? OR email = ?");
        $check->bind_param("ss", $lietotajvards, $epasts);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Lietotājvārds vai E-pasts jau eksistē!";
        } else {
            $conn->begin_transaction();
            try {
                $status = ($role === 'psychologist') ? 'pending' : 'active';
                $stmt = $conn->prepare("INSERT INTO accounts (username, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $lietotajvards, $epasts, $telefons, $parole, $role, $status);

                if (!$stmt->execute()) {
                    throw new Exception($conn->error);
                }
                $accountId = (int)$conn->insert_id;
                $stmt->close();

                if ($role === 'user') {
                    $stmt2 = $conn->prepare("INSERT INTO user_profiles (account_id, first_name, last_name) VALUES (?, ?, ?)");
                    $stmt2->bind_param("iss", $accountId, $vards, $uzvards);
                } else {
                    $full_name = $vards . ' ' . $uzvards;
                    $stmt2 = $conn->prepare("INSERT INTO psychologist_profiles (account_id, full_name, specialization, experience_years, description, hourly_rate) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt2->bind_param("issisd", $accountId, $full_name, $specialization, $experience_years, $description, $hourly_rate);
                }
                if (!$stmt2->execute()) {
                    throw new Exception($conn->error);
                }
                $stmt2->close();

                $conn->commit();
                if ($role === 'psychologist') {
                    header("Location: login.php?message=Pieteikums iesniegts. Administrators pārbaudīs jūsu kvalifikāciju.");
                } else {
                    header("Location: login.php?success=1");
                }
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Kļūda sistēmā: " . $e->getMessage();
            }
        }
        $check->close();
    }
}

// 2. TAGAD HEADER
require 'header.php';
?>

<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-surface dark:bg-zinc-900">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-zinc-700">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Izveidot profilu
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Jau ir profils? <a href="login.php" class="font-medium text-primary hover:text-primaryHover transition">Ielogoties</a>
            </p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg text-sm text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="space-y-4">
                <!-- Role Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reģistrēties kā</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="role" value="user" checked class="text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Lietotājs (meklēt speciālistus)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="role" value="psychologist" class="text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Psihologs (piedāvāt pakalpojumus)</span>
                        </label>
                    </div>
                </div>

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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefons</label>
                    <input type="text" name="telefons" required class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition" placeholder="+371...">
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

                <!-- Psychologist specific fields -->
                <div id="psychologist-fields" class="hidden space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Specializācija</label>
                        <input type="text" name="specialization" class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition" placeholder="piem. ģimenes terapija">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pieredze (gadi)</label>
                        <input type="number" name="experience_years" min="0" class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apraksts</label>
                        <textarea name="description" rows="3" class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition" placeholder="Īss apraksts par sevi un pakalpojumiem"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stundas tarifa (EUR)</label>
                        <input type="number" name="hourly_rate" min="0" step="0.01" class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-zinc-600 placeholder-gray-400 text-gray-900 dark:text-white dark:bg-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition">
                    </div>
                </div>
            </div>

            <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-primary hover:bg-primaryHover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition transform hover:scale-[1.02] shadow-lg shadow-primary/20">
                Reģistrēties
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const roleRadios = document.querySelectorAll('input[name="role"]');
    const psychologistFields = document.getElementById('psychologist-fields');

    function toggleFields() {
        const selectedRole = document.querySelector('input[name="role"]:checked').value;
        if (selectedRole === 'psychologist') {
            psychologistFields.classList.remove('hidden');
        } else {
            psychologistFields.classList.add('hidden');
        }
    }

    roleRadios.forEach(radio => radio.addEventListener('change', toggleFields));
    toggleFields(); // Initial check
});
</script>

<?php require 'footer.php'; ?>