<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$depth = $scriptDir === '' ? 0 : substr_count($scriptDir, '/') + 1;
$pathPrefix = str_repeat('../', $depth);

// Nosakām, kurš ir ielogojies (vienota autorizācija: account_id + role)
$user_name = '';
$user_role = '';
$dashboard_link = $pathPrefix . 'login.php';
$is_logged_in = false;

if (isset($_SESSION['account_id'], $_SESSION['role'])) {
    $is_logged_in = true;
    $user_name = $_SESSION['display_name'] ?? '';
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        $user_role = 'Administrators';
        $dashboard_link = $pathPrefix . 'admin/admin_dashboard.php';
    } elseif ($role === 'psychologist') {
        $user_role = 'Psihologs';
        $dashboard_link = $pathPrefix . 'psihologi/specialist_dashboard.php';
    } else {
        $user_role = 'Lietotājs';
        $dashboard_link = $pathPrefix . 'dashboard.php';
    }
}
?>
<!DOCTYPE html>
<html lang="lv" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Saprasts - Psihologu pieteikumi'; ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?php echo htmlspecialchars($pathPrefix); ?>assets/js/tailwind_config.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php $cssVersion = '20260327b'; ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($pathPrefix); ?>assets/css/style.css?v=<?php echo $cssVersion; ?>">
    <?php if (!empty($pageStyles) && is_array($pageStyles)): ?>
        <?php foreach ($pageStyles as $pageStyle): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($pathPrefix . 'assets/css/' . basename((string)$pageStyle)); ?>?v=<?php echo $cssVersion; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($pageHead)): ?>
    <?php echo $pageHead; ?>
    <?php endif; ?>
</head>
<body class="bg-surface text-gray-900 dark:bg-zinc-900 dark:text-gray-100 transition-colors duration-300" data-path-prefix="<?php echo htmlspecialchars($pathPrefix, ENT_QUOTES, 'UTF-8'); ?>">

    <nav class="sticky top-0 z-50 bg-[#f1f9ff]/90 dark:bg-zinc-900/90 backdrop-blur-md border-b border-[#ccecee] dark:border-zinc-800 shadow-sm transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?php echo htmlspecialchars($pathPrefix); ?>index.php" class="text-2xl font-bold text-primary tracking-wide hover:opacity-80 transition">Saprasts</a>
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

                            <div class="hidden absolute right-0 z-50 my-4 text-base list-none bg-surface divide-y divide-[#ccecee] rounded-lg shadow-xl dark:bg-zinc-800 dark:divide-zinc-700 w-56 border border-[#ccecee] dark:border-zinc-700" id="user-dropdown">
                                <div class="px-4 py-3">
                                    <span class="block text-sm text-gray-900 dark:text-white font-bold"><?php echo htmlspecialchars($user_name); ?></span>
                                    <span class="block text-xs font-medium text-gray-500 truncate dark:text-gray-400 uppercase tracking-wider mt-1"><?php echo $user_role; ?></span>
                                </div>
                                <ul class="py-2" aria-labelledby="user-menu-button">
                                    <li>
                                        <a href="<?php echo $dashboard_link; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-200 transition">
                                            <i class="fas fa-columns w-5 text-center mr-2"></i> Panelis
                                        </a>
                                    </li>

                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>admin/messages.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-200 transition">
                                            <i class="fas fa-inbox w-5 text-center mr-2"></i> Ziņojumi
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>user_profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-200 transition">
                                            <i class="fas fa-user-circle w-5 text-center mr-2"></i> Mans profils
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                                    <!-- User-only features -->
                                    <li>
                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>tests/tests.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-200 transition">
                                            <i class="fas fa-clipboard-list w-5 text-center mr-2"></i> Pašnovērtējuma testi
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>appointments.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-200 transition">
                                            <i class="fas fa-calendar-check w-5 text-center mr-2"></i> Mani pieraksti
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <li class="border-t border-gray-100 dark:border-zinc-700">
                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>logout.php" class="block px-4 py-2 text-sm text-[#095d7e] hover:bg-[#f1f9ff] dark:hover:bg-zinc-700 dark:text-[#ccecee] transition">
                                            <i class="fas fa-sign-out-alt w-5 text-center mr-2"></i> Iziet
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>login.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition font-medium">Ielogoties</a>
                    <?php endif; ?>
                </div>

                <div class="md:hidden flex items-center">
                     <button id="mobile-menu-btn" class="text-gray-700 dark:text-gray-300 hover:text-primary focus:outline-none p-2">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-surface dark:bg-zinc-900 border-t border-[#ccecee] dark:border-zinc-800 shadow-inner">
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
                    <a href="<?php echo $dashboard_link; ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">Panelis</a>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>admin/messages.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">Ziņojumi</a>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'): ?>
                          <a href="<?php echo htmlspecialchars($pathPrefix); ?>user_profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">Mans profils</a>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                          <a href="<?php echo htmlspecialchars($pathPrefix); ?>tests/tests.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">Pašnovērtējuma testi</a>
                          <a href="<?php echo htmlspecialchars($pathPrefix); ?>appointments.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">Mani pieraksti</a>
                      <?php endif; ?>
                    
                          <a href="<?php echo htmlspecialchars($pathPrefix); ?>logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-[#095d7e] dark:text-[#ccecee] hover:bg-[#f1f9ff] dark:hover:bg-zinc-800">Iziet</a>
                 <?php else: ?>
                          <a href="<?php echo htmlspecialchars($pathPrefix); ?>login.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">Ielogoties</a>
                 <?php endif; ?>

                 <div class="pt-4 border-t border-gray-200 dark:border-zinc-700 mt-2">
                    <div class="flex items-center justify-between px-3 py-2">
                        <span class="text-gray-700 dark:text-gray-300 font-medium">Tumšais režīms</span>
                        <label for="theme-toggle-mobile" class="inline-flex relative items-center cursor-pointer">
                            <input type="checkbox" id="theme-toggle-mobile" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                 </div>
            </div>
        </div>
    </nav>

    <script src="<?php echo htmlspecialchars($pathPrefix); ?>assets/js/header_menu.js"></script>