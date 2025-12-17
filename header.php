<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Nosakām, kurš ir ielogojies (Lietotājs vai Psihologs)
$user_name = '';
$user_role = '';
$dashboard_link = 'login.php';
$is_logged_in = false;

if (isset($_SESSION['user_id'])) {
    $is_logged_in = true;
    $user_name = $_SESSION['vards'];
    $user_role = 'Lietotājs'; // Klienta loma
    $dashboard_link = 'dashboard.php';
} elseif (isset($_SESSION['psihologs_id'])) {
    $is_logged_in = true;
    $user_name = $_SESSION['psihologs_vards'];
    $user_role = 'Psihologs'; // Speciālista loma
    $dashboard_link = 'specialist_dashboard.php';
}
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
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#10b981', 
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
        /* Pielāgots slēdža stils */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #10b981;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #10b981;
        }
        body { display: flex; flex-direction: column; min-height: 100vh; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-zinc-900 dark:text-gray-100 transition-colors duration-300">

    <nav class="sticky top-0 z-50 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md border-b border-gray-200 dark:border-zinc-800 shadow-sm transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="index.php" class="text-2xl font-bold text-primary tracking-wide hover:opacity-80 transition">Saprasts</a>
                </div>

                <div class="hidden md:flex space-x-6 items-center">
                    
                    <div class="flex items-center mr-2">
                        <label for="theme-toggle" class="flex items-center cursor-pointer relative">
                            <input type="checkbox" id="theme-toggle" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <i class="fas fa-moon"></i>
                            </span>
                        </label>
                    </div>

                    <?php if ($is_logged_in): ?>
                        <div class="relative ml-3">
                            <button type="button" class="flex text-sm bg-gray-100 dark:bg-zinc-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-zinc-600 p-2 items-center gap-2 transition hover:bg-gray-200 dark:hover:bg-zinc-700" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown">
                                <span class="sr-only">Atvērt lietotāja izvēlni</span>
                                <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="hidden lg:block font-medium text-gray-700 dark:text-gray-200 pr-2">
                                    <?php echo htmlspecialchars($user_name); ?>
                                </span>
                                <i class="fas fa-chevron-down text-xs text-gray-500 dark:text-gray-400"></i>
                            </button>

                            <div class="hidden absolute right-0 z-50 my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-xl dark:bg-zinc-800 dark:divide-zinc-700 w-56 border border-gray-100 dark:border-zinc-700" id="user-dropdown">
                                <div class="px-4 py-3">
                                    <span class="block text-sm text-gray-900 dark:text-white font-bold"><?php echo htmlspecialchars($user_name); ?></span>
                                    <span class="block text-xs font-medium text-gray-500 truncate dark:text-gray-400 uppercase tracking-wider mt-1"><?php echo $user_role; ?></span>
                                </div>
                                <ul class="py-2" aria-labelledby="user-menu-button">
                                    <li>
                                        <a href="<?php echo $dashboard_link; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-200 transition">
                                            <i class="fas fa-columns w-5 text-center mr-2"></i> Sistēma
                                        </a>
                                    </li>
                                    <li>
                                        <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-red-400 transition">
                                            <i class="fas fa-sign-out-alt w-5 text-center mr-2"></i> Iziet
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition font-medium">Ielogoties</a>
                    <?php endif; ?>
                </div>

                <div class="md:hidden flex items-center">
                     <button id="mobile-menu-btn" class="text-gray-700 dark:text-gray-300 hover:text-primary focus:outline-none p-2">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800 shadow-inner">
            <div class="px-4 pt-2 pb-4 space-y-1">
                 
                 <?php if ($is_logged_in): ?>
                    <div class="border-b border-gray-200 dark:border-zinc-700 pb-3 mb-3">
                        <div class="flex items-center px-2">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary text-lg">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-base font-medium leading-none text-gray-800 dark:text-white"><?php echo htmlspecialchars($user_name); ?></div>
                                <div class="text-sm font-medium leading-none text-gray-500 dark:text-gray-400 mt-1"><?php echo $user_role; ?></div>
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo $dashboard_link; ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">Sistēma</a>
                    <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10">Iziet</a>
                 <?php else: ?>
                    <a href="login.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">Ielogoties</a>
                 <?php endif; ?>

                 <div class="pt-4 border-t border-gray-200 dark:border-zinc-700 mt-2">
                    <div class="flex items-center justify-between px-3 py-2">
                        <span class="text-gray-700 dark:text-gray-300 font-medium">Dark Mode</span>
                        <label for="theme-toggle-mobile" class="inline-flex relative items-center cursor-pointer">
                            <input type="checkbox" id="theme-toggle-mobile" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                 </div>
            </div>
        </div>
    </nav>

    <script>
        // Dropdown Logic
        const userMenuBtn = document.getElementById('user-menu-button');
        const userDropdown = document.getElementById('user-dropdown');

        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation(); // Novērš klikšķa nodošanu tālāk
                userDropdown.classList.toggle('hidden');
            });

            // Aizvērt, ja klikšķina citur
            document.addEventListener('click', (e) => {
                if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
        }
    </script>