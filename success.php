<?php 
$pageTitle = "Maksājums veiksmīgs";
require 'header.php'; 
?>

<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-zinc-900">
    <div class="max-w-md w-full bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-xl border-2 border-green-100 dark:border-green-900/30 text-center">
        
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/50 mb-6">
            <i class="fas fa-check text-4xl text-green-600 dark:text-green-400"></i>
        </div>

        <h1 class="text-3xl font-extrabold text-green-600 dark:text-green-400 mb-2">
            Paldies!
        </h1>
        
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            Maksājums saņemts veiksmīgi.
        </h3>
        
        <p class="text-gray-600 dark:text-gray-300 mb-8">
            Jūsu pieteikums konsultācijai ir reģistrēts sistēmā. Psihologs ar jums sazināsies norādītajā laikā.
        </p>
        
        <a href="dashboard.php" class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition transform hover:scale-[1.02] shadow-lg shadow-green-500/30">
            Atgriezties sistēmā
        </a>
    </div>
</div>

<?php require 'footer.php'; ?>