<?php
session_start();
$pageTitle = "Pieejamība";
require 'database/db.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'psychologist') {
    header("Location: login.php");
    exit();
}

require 'header.php';

$account_id = (int)$_SESSION['account_id'];
$message = "";
$error = "";

// Apstrādājam jauna pieejamības slota pievienošanu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_availability'])) {
    $starts_at = $_POST['starts_at'] ?? '';
    $ends_at = $_POST['ends_at'] ?? '';
    $consultation_type = $_POST['consultation_type'] ?? 'online';
    $note = trim($_POST['note'] ?? '');
    $allowed_types = ['in_person', 'online'];

    if (!in_array($consultation_type, $allowed_types, true)) {
        $consultation_type = 'online';
    }

    if(empty($starts_at) || empty($ends_at)) {
        $error = "Sākuma un beigu laiki ir obligāti.";
    } else if(strtotime($ends_at) <= strtotime($starts_at)) {
        $error = "Beigu laikam jābūt vēlākiem nekā sākuma laikam.";
    } else {
        $stmt = $conn->prepare("INSERT INTO availability_slots (psychologist_account_id, starts_at, ends_at, consultation_type, note) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $account_id, $starts_at, $ends_at, $consultation_type, $note);
        if($stmt->execute()) {
            $message = "Pieejamības slots pievienots!";
        } else {
            $error = "Kļūda pievienojot slotu.";
        }
        $stmt->close();
    }
}

// Apstrādājam pieejamības slota dzēšanu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_slot'])) {
    $slot_id = (int)$_POST['slot_id'];
    $stmt = $conn->prepare("DELETE FROM availability_slots WHERE id = ? AND psychologist_account_id = ?");
    $stmt->bind_param("ii", $slot_id, $account_id);
    $stmt->execute();
    $stmt->close();
    $message = "Slots dzēsts.";
}

// Iegūstam pieejamības slotus
$sql = "SELECT id, starts_at, ends_at, consultation_type, note FROM availability_slots WHERE psychologist_account_id = ? ORDER BY starts_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$slots = [];
while($row = $result->fetch_assoc()) {
    $slots[] = $row;
}
$stmt->close();
?>

<div class="page-shell page-surface">
    <div class="page-heading">
        <h1 class="page-title">Mana pieejamība</h1>
        <p class="page-subtitle">Pārvaldiet savus pieejamos laikus konsultācijām.</p>
    </div>

    <?php if(!empty($message)): ?>
        <div class="alert-success">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($error)): ?>
        <div class="alert-error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="layout-sidebar-3">
        <!-- Slota pievienošanas forma -->
        <div class="form-card">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Pievienot laika slotu</h2>
            <form method="POST" class="stack-md">
                <div>
                    <label class="field-label">Sākuma laiks</label>
                    <input type="datetime-local" name="starts_at" required class="input-control">
                </div>

                <div>
                    <label class="field-label">Beigu laiks</label>
                    <input type="datetime-local" name="ends_at" required class="input-control">
                </div>

                <div>
                    <label class="field-label">Konsultācijas veids</label>
                    <select name="consultation_type" class="input-control mb-2">
                        <option value="online">Tiešsaistē</option>
                        <option value="in_person">Klātienē</option>
                    </select>
                    <label class="field-label">Piezīme (neobligāta)</label>
                    <input type="text" name="note" placeholder="Piem. tikai tiešsaistē" class="input-control">
                </div>

                <button type="submit" name="add_availability" class="button-primary w-full">
                    Pievienot slotu
                </button>
            </form>
        </div>

        <!-- Esošo slotu saraksts -->
        <div class="lg:col-span-2 space-y-4">
            <?php foreach($slots as $slot): ?>
                <div class="list-card p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white">
                                <i class="fas fa-calendar-alt text-primary"></i> 
                                <?php echo date('d.m.Y H:i', strtotime($slot['starts_at'])); ?> - 
                                <?php echo date('H:i', strtotime($slot['ends_at'])); ?>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <i class="fas fa-video mr-1"></i><?php echo ($slot['consultation_type'] ?? 'online') === 'online' ? 'Tiešsaistē' : 'Klātienē'; ?>
                            </p>
                            <?php if($slot['note']): ?>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo htmlspecialchars($slot['note']); ?></p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" class="inline">
                            <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                            <button type="submit" name="delete_slot" onclick="return confirm('Dzēst slotu?')" class="button-danger-icon text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if(empty($slots)): ?>
                <div class="empty-card">
                    <p class="text-gray-500 dark:text-gray-400">Jūs vēl neesat pievienojis nekādus laika slotus.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
