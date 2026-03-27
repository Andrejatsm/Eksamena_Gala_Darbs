<?php
session_start();
require 'db.php';

function sanitize_next(string $next): string {
    $next = trim($next);
    if ($next === '' || str_contains($next, '://') || str_starts_with($next, '//') || str_contains($next, "\n") || str_contains($next, "\r")) {
        return '';
    }
    if (!preg_match('/^[a-zA-Z0-9_\-\/\.\?=&%#]+$/', $next)) {
        return '';
    }
    return $next;
}

$next = sanitize_next($_GET['next'] ?? $_POST['next'] ?? '');

// Ja jau ielogojies -> ej uz atbilstošo paneli
if (isset($_SESSION['account_id'], $_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'user' && $next !== '') {
        header("Location: " . $next);
    } elseif ($role === 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($role === 'psychologist') {
        header("Location: specialist_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lietotajvards = trim($_POST['lietotajvards']);
    $parole = $_POST['parole'];

    $stmt = $conn->prepare("SELECT id, password_hash, role, status FROM accounts WHERE username = ?");
    $stmt->bind_param("s", $lietotajvards);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!password_verify($parole, $row['password_hash'])) {
            $error = "Nepareiza parole!";
        } elseif ($row['status'] !== 'active') {
            $error = match($row['status']) {
                'pending' => 'Jūsu profils vēl nav apstiprināts.',
                'rejected' => 'Jūsu pieteikums tika noraidīts.',
                'disabled' => 'Profils ir deaktivizēts.',
                default => 'Nav atļauts ielogoties.',
            };
        } else {
            $accountId = (int)$row['id'];
            $role = $row['role'];

            $_SESSION['account_id'] = $accountId;
            $_SESSION['role'] = $role;

            // Display name
            $displayName = $lietotajvards;
            if ($role === 'psychologist') {
                $s2 = $conn->prepare("SELECT full_name FROM psychologist_profiles WHERE account_id = ?");
                $s2->bind_param("i", $accountId);
                $s2->execute();
                $r2 = $s2->get_result();
                if ($p = $r2->fetch_assoc()) $displayName = $p['full_name'];
                $s2->close();
            } elseif ($role === 'user') {
                $s2 = $conn->prepare("SELECT first_name FROM user_profiles WHERE account_id = ?");
                $s2->bind_param("i", $accountId);
                $s2->execute();
                $r2 = $s2->get_result();
                if ($p = $r2->fetch_assoc()) $displayName = $p['first_name'];
                $s2->close();
            }
            $_SESSION['display_name'] = $displayName;

            if ($role === 'user' && $next !== '') {
                header("Location: " . $next);
            } elseif ($role === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($role === 'psychologist') {
                header("Location: specialist_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        }
    } else {
        $error = "Lietotājs nav atrasts!";
    }
    $stmt->close();
}

require 'header.php';
?>

<div class="auth-shell page-surface transition-colors duration-300">
    <div class="auth-card stack-md">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Ielogoties
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Vai <a href="register.php" class="font-medium text-primary hover:text-primaryHover transition">izveidot jaunu profilu</a>
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

        <form class="mt-8 stack-md" method="POST">
            <?php if ($next !== ''): ?>
            <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>">
            <?php endif; ?>
            <div class="space-y-4">
                <div>
                    <label for="lietotajvards" class="field-label">Lietotājvārds</label>
                    <input id="lietotajvards" name="lietotajvards" type="text" required class="input-control" placeholder="Ievadiet lietotājvārdu">
                </div>
                <div>
                    <label for="parole" class="field-label">Parole</label>
                    <input id="parole" name="parole" type="password" required class="input-control" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="button-primary w-full">
                Ielogoties
            </button>
        </form>
    </div>
</div>

<?php require 'footer.php'; ?>