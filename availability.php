<?php
session_start();
$pageTitle = "Pieejamība";
require 'database/db.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'psychologist') {
    header("Location: login.php");
    exit();
}

$account_id = (int)$_SESSION['account_id'];
$message = (string)($_SESSION['availability_flash_success'] ?? '');
$error = (string)($_SESSION['availability_flash_error'] ?? '');
unset($_SESSION['availability_flash_success'], $_SESSION['availability_flash_error']);

$starts_date = date('Y-m-d');
$starts_time = date('09:00');
$ends_time = date('10:00');
$consultation_type = 'online';
$note = '';

// Psihologs šeit definē savus rezervējamos laikus, kurus vēlāk redz klienta profilā un checkout plūsmā.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_availability'])) {
    $starts_date = trim((string)($_POST['starts_date'] ?? ''));
    $starts_time = trim((string)($_POST['starts_time'] ?? ''));
    $ends_time = trim((string)($_POST['ends_time'] ?? ''));
    $starts_at_legacy = trim((string)($_POST['starts_at'] ?? ''));
    $ends_at_legacy = trim((string)($_POST['ends_at'] ?? ''));
    $consultation_type = $_POST['consultation_type'] ?? 'online';
    $note = trim($_POST['note'] ?? '');
    $allowed_types = ['in_person', 'online'];

    if ($starts_date !== '' && $starts_time !== '' && $ends_time !== '') {
        $starts_at = $starts_date . ' ' . $starts_time . ':00';
        $ends_at = $starts_date . ' ' . $ends_time . ':00';
    } else {
        // Backward compatibility if older form payload is still posted.
        $starts_at = $starts_at_legacy;
        $ends_at = $ends_at_legacy;
    }

    if (!in_array($consultation_type, $allowed_types, true)) {
        $consultation_type = 'online';
    }

    if(empty($starts_at) || empty($ends_at)) {
        $error = "Sākuma un beigu laiki ir obligāti.";
    } else if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $starts_time) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $ends_time)) {
        $error = "Laiku norādiet 24 stundu formātā HH:MM.";
    } else if(strtotime($ends_at) <= strtotime($starts_at)) {
        $error = "Beigu laikam jābūt vēlākiem nekā sākuma laikam.";
    } else if (round((strtotime($ends_at) - strtotime($starts_at)) / 60) > 240) {
        $error = "Slota ilgums nevar pārsniegt 4 stundas.";
    } else if ($starts_date !== '' && $starts_date < date('Y-m-d')) {
        $error = "Nevar pievienot slotu pagātnes datumā.";
    } else {
        // Glabājam arī konsultācijas tipu un piezīmi, lai katrs slots būtu saprotams jau pirms rezervācijas.
        $stmt = $conn->prepare("INSERT INTO availability_slots (psychologist_account_id, starts_at, ends_at, consultation_type, note) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $account_id, $starts_at, $ends_at, $consultation_type, $note);
        if($stmt->execute()) {
            $_SESSION['availability_flash_success'] = "Pieejamības slots pievienots!";
            header('Location: availability.php');
            exit();
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
    $_SESSION['availability_flash_success'] = "Slots dzēsts.";
    header('Location: availability.php');
    exit();
}

// Esošos slotus ielādējam vienā vietā, lai var gan attēlot sarakstu, gan uzreiz dzēst nevajadzīgos laikus.
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 5;
$offset = ($page - 1) * $per_page;

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM availability_slots WHERE psychologist_account_id = ?");
$count_stmt->bind_param("i", $account_id);
$count_stmt->execute();
$total_slots = (int)$count_stmt->get_result()->fetch_row()[0];
$count_stmt->close();
$total_pages = (int)ceil($total_slots / $per_page);

$sql = "SELECT id, starts_at, ends_at, consultation_type, note FROM availability_slots WHERE psychologist_account_id = ? ORDER BY starts_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $account_id, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$slots = [];
while($row = $result->fetch_assoc()) {
    $slots[] = $row;
}
$stmt->close();

require 'header.php';
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
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Pievienot pierakstus pēc laikiem</h2>
            <form method="POST" class="stack-md">
                <div>
                    <label class="field-label">Sākuma datums</label>
                    <input type="date" name="starts_date" id="starts_date" required class="input-control" value="<?php echo htmlspecialchars($starts_date); ?>" min="<?php echo date('Y-m-d'); ?>" lang="lv">
                </div>

                <div>
                    <label class="field-label">Sākuma laiks</label>
                    <input type="text" name="starts_time" id="starts_time" required class="input-control" value="<?php echo htmlspecialchars($starts_time); ?>" placeholder="14:30" inputmode="numeric" pattern="(?:[01]\d|2[0-3]):[0-5]\d">
                </div>

                <div>
                    <label class="field-label">Beigu laiks</label>
                    <input type="text" name="ends_time" id="ends_time" required class="input-control" value="<?php echo htmlspecialchars($ends_time); ?>" placeholder="15:30" inputmode="numeric" pattern="(?:[01]\d|2[0-3]):[0-5]\d">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Beigu datumam automātiski izmanto to pašu datumu, kas ir sākuma laikam.</p>
                </div>

                <div>
                    <label class="field-label">Konsultācijas veids</label>
                    <select name="consultation_type" class="input-control mb-2">
                        <option value="online" <?php echo $consultation_type === 'online' ? 'selected' : ''; ?>>Tiešsaistē</option>
                        <option value="in_person" <?php echo $consultation_type === 'in_person' ? 'selected' : ''; ?>>Klātienē</option>
                    </select>
                    <label class="field-label">Piezīme (neobligāta)</label>
                    <input type="text" name="note" placeholder="Piem. tikai tiešsaistē" class="input-control" value="<?php echo htmlspecialchars($note); ?>">
                </div>

                <button type="submit" name="add_availability" class="button-primary w-full">
                    Pievienot laiku
                </button>
            </form>
        </div>

        <!-- Esošo slotu saraksts -->
        <div class="space-y-4">
            <?php foreach($slots as $slot): ?>
                <div class="list-card p-4">
                    <div class="flex justify-between items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-gray-900 dark:text-white">
                                <i class="fas fa-calendar-alt text-primary"></i> 
                                <?php echo date('d.m.Y H:i', strtotime($slot['starts_at'])); ?> - 
                                <?php echo date('H:i', strtotime($slot['ends_at'])); ?>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <i class="fas fa-video mr-1"></i><?php echo ($slot['consultation_type'] ?? 'online') === 'online' ? 'Tiešsaistē' : 'Klātienē'; ?>
                            </p>
                            <?php if($slot['note']): ?>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 break-words min-w-0"><?php echo htmlspecialchars($slot['note']); ?></p>
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
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center items-center gap-2 pt-4">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-1.5 rounded-lg bg-[#ccecee] text-[#095d7e] hover:bg-[#b8dde0] font-semibold text-sm transition"><i class="fas fa-chevron-left mr-1"></i>Iepriekšējā</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-lg bg-[#ccecee]/40 text-[#095d7e]/40 font-semibold text-sm cursor-not-allowed"><i class="fas fa-chevron-left mr-1"></i>Iepriekšējā</span>
                <?php endif; ?>
                <span class="text-sm text-gray-600 dark:text-gray-400 px-2">Lapa <?php echo $page; ?> no <?php echo $total_pages; ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-1.5 rounded-lg bg-[#ccecee] text-[#095d7e] hover:bg-[#b8dde0] font-semibold text-sm transition">Nākamā<i class="fas fa-chevron-right ml-1"></i></a>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-lg bg-[#ccecee]/40 text-[#095d7e]/40 font-semibold text-sm cursor-not-allowed">Nākamā<i class="fas fa-chevron-right ml-1"></i></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="assets/js/availability.js"></script>

<?php require 'footer.php'; ?>
