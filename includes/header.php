<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$depth = $scriptDir === '' ? 0 : substr_count($scriptDir, '/') + 1;
$pathPrefix = str_repeat('../', $depth);

// Load translation system
require_once __DIR__ . '/lang.php';

// Nosakām, kurš ir ielogojies (vienota autorizācija: account_id + role)
$user_name = '';
$user_role = '';
$dashboard_link = $pathPrefix . 'auth/login.php';
$is_logged_in = false;

if (isset($_SESSION['account_id'], $_SESSION['role'])) {
    $is_logged_in = true;
    $user_name = $_SESSION['display_name'] ?? '';
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        $user_role = t('role_admin');
        $dashboard_link = $pathPrefix . 'admin/admin_dashboard.php';
    } elseif ($role === 'psychologist') {
        $user_role = t('role_psychologist');
        $dashboard_link = $pathPrefix . 'specialist/specialist_dashboard.php';
    } else {
        $user_role = t('role_user');
        $dashboard_link = $pathPrefix . 'pages/dashboard.php';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo currentLang(); ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? t('site_title'); ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#14967f',
                        primaryHover: '#095d7e',
                        secondary: '#095d7e',
                        surface: '#f1f9ff',
                        background: '#f1f9ff',
                        mint: '#e2fcd6',
                        accent: '#ccecee',
                        onSurface: '#0d2d3a',
                        onSurfaceVariant: '#2d6a7f',
                        tertiary: '#ccecee',
                        dark: {
                            bg: '#121212',
                            card: '#1e1e1e',
                            text: '#e0e0e0'
                        }
                    }
                }
            }
        };
    </script>
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
                    
                    <!-- Language switcher -->
                    <?php $otherLang = currentLang() === 'lv' ? 'en' : 'lv'; ?>
                    <a href="?lang=<?php echo $otherLang; ?>" class="text-xs font-bold px-2 py-1 rounded border border-[#ccecee] dark:border-zinc-600 text-gray-600 dark:text-gray-300 hover:text-primary hover:border-primary dark:hover:text-primary dark:hover:border-primary transition uppercase tracking-wider"><?php echo strtoupper($otherLang); ?></a>

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
                        <!-- Notifikāciju zvaniņš -->
                        <div class="relative" id="notification-bell-wrapper">
                            <button type="button" id="notification-bell-btn" class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition rounded-full hover:bg-gray-100 dark:hover:bg-zinc-800" aria-label="<?php echo t('notifications'); ?>">
                                <i class="fas fa-bell text-lg"></i>
                                <span id="notification-bell-badge" class="hidden absolute -top-0.5 -right-0.5 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full"></span>
                            </button>
                            <div id="notification-dropdown" class="hidden absolute right-0 z-50 mt-2 w-80 bg-white dark:bg-zinc-800 rounded-xl shadow-2xl border border-gray-200 dark:border-zinc-700 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-white"><?php echo t('notifications'); ?></h4>
                                    <span id="notification-count-label" class="text-xs text-gray-500 dark:text-gray-400"></span>
                                </div>
                                <div id="notification-list" class="max-h-80 overflow-y-auto">
                                    <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo t('no_new_notifications'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="relative ml-3">
                            <button type="button" class="flex text-sm bg-gray-100 dark:bg-zinc-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-zinc-600 p-2 items-center gap-2 transition hover:bg-gray-200 dark:hover:bg-zinc-700" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown">
                                <span class="sr-only"><?php echo t('open_user_menu'); ?></span>
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
                                            <i class="fas fa-columns w-5 text-center mr-2"></i> <?php echo t('panel'); ?>
                                        </a>
                                    </li>

                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>admin/messages.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-200 transition">
                                            <i class="fas fa-inbox w-5 text-center mr-2"></i> <?php echo t('messages'); ?>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/user_profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-200 transition">
                                            <i class="fas fa-user-circle w-5 text-center mr-2"></i> <?php echo t('my_profile'); ?>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                                    <!-- User-only features -->
                                    <li>
                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>tests/tests.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-200 transition">
                                            <i class="fas fa-clipboard-list w-5 text-center mr-2"></i> <?php echo t('self_tests'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/appointments.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-700 dark:text-gray-200 transition">
                                            <i class="fas fa-calendar-check w-5 text-center mr-2"></i> <?php echo t('my_appointments'); ?>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <li class="border-t border-gray-100 dark:border-zinc-700">
                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>auth/logout.php" class="block px-4 py-2 text-sm text-[#095d7e] hover:bg-[#f1f9ff] dark:hover:bg-zinc-700 dark:text-[#ccecee] transition">
                                            <i class="fas fa-sign-out-alt w-5 text-center mr-2"></i> <?php echo t('logout'); ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>auth/login.php" class="text-gray-700 dark:text-gray-300 hover:text-primary transition font-medium"><?php echo t('login'); ?></a>
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
                    <a href="<?php echo $dashboard_link; ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800"><?php echo t('panel'); ?></a>
                    <button type="button" id="mobile-notification-btn" class="w-full text-left block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800">
                        <i class="fas fa-bell w-5 text-center mr-1"></i> <?php echo t('notifications'); ?> <span id="mobile-notification-badge" class="hidden ml-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full"></span>
                    </button>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>admin/messages.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800"><?php echo t('messages'); ?></a>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'): ?>
                          <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/user_profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800"><?php echo t('my_profile'); ?></a>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                          <a href="<?php echo htmlspecialchars($pathPrefix); ?>tests/tests.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800"><?php echo t('self_tests'); ?></a>
                          <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/appointments.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800"><?php echo t('my_appointments'); ?></a>
                      <?php endif; ?>
                    
                          <a href="<?php echo htmlspecialchars($pathPrefix); ?>auth/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-[#095d7e] dark:text-[#ccecee] hover:bg-[#f1f9ff] dark:hover:bg-zinc-800"><?php echo t('logout'); ?></a>
                 <?php else: ?>
                          <a href="<?php echo htmlspecialchars($pathPrefix); ?>auth/login.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:text-primary hover:bg-gray-50 dark:hover:bg-zinc-800"><?php echo t('login'); ?></a>
                 <?php endif; ?>

                 <div class="pt-4 border-t border-gray-200 dark:border-zinc-700 mt-2">
                    <div class="flex items-center justify-between px-3 py-2">
                        <span class="text-gray-700 dark:text-gray-300 font-medium"><?php echo t('dark_mode'); ?></span>
                        <label for="theme-toggle-mobile" class="inline-flex relative items-center cursor-pointer">
                            <input type="checkbox" id="theme-toggle-mobile" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between px-3 py-2">
                        <span class="text-gray-700 dark:text-gray-300 font-medium">Language</span>
                        <div class="flex gap-1">
                            <a href="?lang=lv" class="px-2 py-1 text-xs font-bold rounded border <?php echo currentLang() === 'lv' ? 'bg-primary text-white border-primary' : 'border-[#ccecee] dark:border-zinc-600 text-gray-600 dark:text-gray-300 hover:text-primary hover:border-primary'; ?> transition">LV</a>
                            <a href="?lang=en" class="px-2 py-1 text-xs font-bold rounded border <?php echo currentLang() === 'en' ? 'bg-primary text-white border-primary' : 'border-[#ccecee] dark:border-zinc-600 text-gray-600 dark:text-gray-300 hover:text-primary hover:border-primary'; ?> transition">EN</a>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </nav>

    <script src="<?php echo htmlspecialchars($pathPrefix); ?>assets/js/header_menu.js"></script>