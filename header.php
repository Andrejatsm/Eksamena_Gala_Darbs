<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="lv" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Saprasts - Psihologu pieteikumi'; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class', // Svarīgi: Dark mode aktivizējas ar klasi 'dark'
            theme: {
                extend: {
                    colors: {
                        primary: '#10b981', // Emerald-500 (līdzīgs Bootstrap success)
                        dark: {
                            bg: '#121212',
                            card: '#1e1e1e',
                            text: '#e0e0e0'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Slēdža stils (Toggle) - Tailwind nav iebūvēts form inputs stils šim */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #10b981;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #10b981;
        }
        /* Lai footer vienmēr būtu apakšā */
        body { display: flex; flex-direction: column; min-height: 100vh; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-zinc-900 dark:text-gray-100 transition-colors duration-300">

    <nav class="sticky top-0 z-50 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md border-b border-gray-200 dark:border-zinc-800 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="index.php" class="text-2xl font-bold text-primary tracking-wide">Saprasts</a>
                </div>

                <div class="hidden md:flex space-x-8 items-center">
                    
                    <div class="flex items-center mr-4">
                        <label for="theme-toggle" class="flex items-center cursor-pointer relative">
                            <input type="checkbox" id="theme-toggle" class="sr-only">
                            <div class="w-10 h-5 bg-gray-300 rounded-full shadow-inner dark:bg-gray-600 transition duration-300"></div>
                            <div class="dot absolute w-5 h-5 bg-white rounded-full shadow -left-1 -top-0 transition duration-300 transform peer-checked:translate-x-full"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <i class="fas fa-moon"></i>
                            </span>
                        </label>
                    </div>

                    <?php if ($is_logged_in): ?>
                        <span class="text-gray-700 dark:text-gray-300">Sveiki, <span class="font-bold text-primary"><?php echo htmlspecialchars($_SESSION['vards']); ?></span></span>
                        <a href="dashboard.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">Sistēma</a>
                        <a href="logout.php" class="px-4 py-2 border border-primary text-primary rounded-full hover:bg-primary hover:text-white transition text-sm font-medium">Iziet</a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">Ielogoties</a>
                        <?php endif; ?>
                </div>

                <div class="md:hidden flex items-center">
                     <button id="mobile-menu-btn" class="text-gray-700 dark:text-gray-300 hover:text-primary focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                 <div class="px-3 py-2">
                    <label for="theme-toggle-mobile" class="flex items-center cursor-pointer">
                        <span class="mr-3 text-gray-700 dark:text-gray-300">Dark Mode</span>
                        <input type="checkbox" id="theme-toggle-mobile" class="accent-primary">
                    </label>
                 </div>

                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">Sistēma</a>
                    <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-500 hover:bg-gray-50 dark:hover:bg-zinc-800">Iziet</a>
                <?php else: ?>
                    <a href="login.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">Ielogoties</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>