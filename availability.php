<?php
$pageTitle = "Pieejamība";
require 'db.php';
require 'header.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'psychologist') {
    header("Location: login.php");
    exit();
}

$account_id = (int)$_SESSION['account_id'];
$message = "";
$error = "";

// Handle adding availability
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_availability'])) {
    $starts_at = $_POST['starts_at'] ?? '';
    $ends_at = $_POST['ends_at'] ?? '';
    $note = trim($_POST['note'] ?? '');

    if(empty($starts_at) || empty($ends_at)) {
        $error = "Sākuma un beigu laiki ir obligāti.";
    } else if(strtotime($ends_at) <= strtotime($starts_at)) {
        $error = "Beigu laiks jābūt vēlāks nekā sākuma laiks.";
    } else {
        $stmt = $conn->prepare("INSERT INTO availability_slots (psychologist_account_id, starts_at, ends_at, note) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $account_id, $starts_at, $ends_at, $note);
        if($stmt->execute()) {
            $message = "Pieejamības slots pievienots!";
        } else {
            $error = "Kļūda pievienojot slotu.";
        }
        $stmt->close();
    }
}

// Handle deleting availability
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_slot'])) {
    $slot_id = (int)$_POST['slot_id'];
    $stmt = $conn->prepare("DELETE FROM availability_slots WHERE id = ? AND psychologist_account_id = ?");
    $stmt->bind_param("ii", $slot_id, $account_id);
    $stmt->execute();
    $stmt->close();
    $message = "Slots dzēsts.";
}

// Get availability slots
$sql = "SELECT id, starts_at, ends_at, note FROM availability_slots WHERE psychologist_account_id = ? ORDER BY starts_at DESC";
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

<div class="flex-grow max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mana pieejamība</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Pārvaldiet savus pieejamos laikus konsultācijām.</p>
    </div>

    <?php if(!empty($message)): ?>
        <div class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 px-4 py-3 rounded-lg">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($error)): ?>
        <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add form -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Pievienot laika slotu</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sākuma laiks</label>
                    <input type="datetime-local" name="starts_at" required class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Beigu laiks</label>
                    <input type="datetime-local" name="ends_at" required class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezīme (neobligāta)</label>
                    <input type="text" name="note" placeholder="Piem. Tikai tiešsaiste" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
                </div>

                <button type="submit" name="add_availability" class="w-full bg-primary hover:bg-primaryHover text-white px-4 py-2 rounded-lg transition font-medium">
                    Pievienot slotu
                </button>
            </form>
        </div>

        <!-- Slots list -->
        <div class="lg:col-span-2 space-y-4">
            <?php foreach($slots as $slot): ?>
                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white">
                                <i class="fas fa-calendar-alt text-primary"></i> 
                                <?php echo date('d.m.Y H:i', strtotime($slot['starts_at'])); ?> - 
                                <?php echo date('H:i', strtotime($slot['ends_at'])); ?>
                            </p>
                            <?php if($slot['note']): ?>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo htmlspecialchars($slot['note']); ?></p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" class="inline">
                            <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                            <button type="submit" name="delete_slot" onclick="return confirm('Dzēst slotu?')" class="text-red-600 hover:text-red-700 dark:text-red-400 text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if(empty($slots)): ?>
                <div class="bg-gray-50 dark:bg-zinc-800 border border-dashed border-gray-300 dark:border-zinc-700 rounded-lg p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">Jūs vēl neesat pievienojis nekādus laika slotus.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
