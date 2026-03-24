<?php
$pageTitle = "Maksājums";
require 'db.php';
require 'header.php';

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: login.php");
    exit();
}

$user_account_id = (int)$_SESSION['account_id'];
$psychologist_id = intval($_GET['psychologist_id'] ?? 0);
$slot_id = intval($_GET['slot_id'] ?? 0);
$rate = floatval($_GET['rate'] ?? 0);

if (!$psychologist_id || !$slot_id || !$rate) {
    header("Location: dashboard.php");
    exit();
}

// Get psychologist and slot details
$stmt = $conn->prepare("
    SELECT p.full_name, p.specialization, a.starts_at, a.ends_at
    FROM availability_slots a
    JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id
    WHERE a.id = ? AND a.psychologist_account_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $slot_id, $psychologist_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    header("Location: dashboard.php");
    exit();
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $consultation_type = $_POST['consultation_type'] ?? 'online';
    $payment_method = $_POST['payment_method'] ?? 'card';
    
    // Create appointment
    $stmt = $conn->prepare("
        INSERT INTO appointments (user_account_id, psychologist_account_id, scheduled_at, consultation_type, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");
    
    $scheduled_at = $booking['starts_at'];
    $stmt->bind_param("iiss", $user_account_id, $psychologist_id, $scheduled_at, $consultation_type);
    
    if ($stmt->execute()) {
        $appointment_id = $conn->insert_id;
        
        // Optionally: Process payment (simplified for demo)
        // In production, you'd integrate Stripe or another payment gateway
        
        // Redirect to success page
        header("Location: booking_success.php?appointment_id=" . $appointment_id);
        exit();
    }
}
?>

<div class="min-h-screen bg-gray-50 dark:bg-zinc-900">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <a href="javascript:history.back()" class="inline-flex items-center text-primary hover:text-primaryHover mb-8 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i> Atpakaļ
        </a>

        <div class="space-y-8">
            <!-- Order Summary -->
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-zinc-700">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Pasūtījuma kopsavilkums</h2>
                
                <div class="space-y-4 pb-6 border-b border-gray-200 dark:border-zinc-700">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 dark:text-gray-300">Speciālists</span>
                        <span class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($booking['full_name']); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 dark:text-gray-300">Specializācija</span>
                        <span class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($booking['specialization']); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 dark:text-gray-300">Laiks</span>
                        <span class="font-bold text-gray-900 dark:text-white"><?php echo date('d.m.Y H:i', strtotime($booking['starts_at'])); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 dark:text-gray-300">Ilgums</span>
                        <span class="font-bold text-gray-900 dark:text-white">1 stunda</span>
                    </div>
                </div>

                <div class="flex justify-between items-center text-lg font-bold mt-6">
                    <span class="text-gray-900 dark:text-white">KOPĀ:</span>
                    <span class="text-3xl text-primary">€<?php echo number_format($rate, 2); ?></span>
                </div>
            </div>

            <!-- Booking Form -->
            <form method="POST" class="space-y-6">
                <!-- Consultation Type -->
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-zinc-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Konsultācijas veids</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border border-gray-300 dark:border-zinc-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
                            <input type="radio" name="consultation_type" value="online" checked class="w-4 h-4 text-primary">
                            <span class="ml-3">
                                <span class="font-semibold text-gray-900 dark:text-white">Tiešsaistes konsultācija</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Video call vai chat</p>
                            </span>
                        </label>
                        
                        <label class="flex items-center p-4 border border-gray-300 dark:border-zinc-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
                            <input type="radio" name="consultation_type" value="in_person" class="w-4 h-4 text-primary">
                            <span class="ml-3">
                                <span class="font-semibold text-gray-900 dark:text-white">Klātienē</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Personīga vizīte kabinetā</p>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-zinc-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Maksāšanas metode</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border border-primary rounded-lg cursor-pointer bg-primary/5 dark:bg-primary/10">
                            <input type="radio" name="payment_method" value="card" checked class="w-4 h-4 text-primary">
                            <span class="ml-3">
                                <span class="font-semibold text-gray-900 dark:text-white">Kredītkarte</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Visa, Mastercard, AMEX</p>
                            </span>
                        </label>
                        
                        <label class="flex items-center p-4 border border-gray-300 dark:border-zinc-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
                            <input type="radio" name="payment_method" value="bank_transfer" class="w-4 h-4 text-primary">
                            <span class="ml-3">
                                <span class="font-semibold text-gray-900 dark:text-white">Bankas pārskaitījums</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Tiešā norēķina</p>
                            </span>
                        </label>

                        <label class="flex items-center p-4 border border-gray-300 dark:border-zinc-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
                            <input type="radio" name="payment_method" value="paypal" class="w-4 h-4 text-primary">
                            <span class="ml-3">
                                <span class="font-semibold text-gray-900 dark:text-white">PayPal</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Draudzīga tiešsaistes maksāšana</p>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Confirmation & Submit -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                    <div class="flex gap-3">
                        <i class="fas fa-shield-alt text-blue-600 dark:text-blue-400 text-xl flex-shrink-0 mt-1"></i>
                        <div>
                            <p class="font-semibold text-blue-900 dark:text-blue-300">Tavs maksājums ir droši aizsargāts</p>
                            <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">Mēs izmantojam jaunāko šifrēšanas tehnoloģiju. Tavs dati nekad netiks kopīgots.</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 px-8 py-4 bg-primary text-white font-bold rounded-lg hover:bg-primaryHover transition shadow-lg">
                        <i class="fas fa-lock mr-2"></i>Samaksāt €<?php echo number_format($rate, 2); ?>
                    </button>
                </div>

                <p class="text-xs text-gray-600 dark:text-gray-400 text-center">
                    Nospiedot "Samaksāt", jūs piekrītat mūsu <a href="privacy.php" class="text-primary hover:underline">noteikumiem</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
