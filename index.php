<?php 
$pageTitle = "Saprasts - Sākums";
require 'header.php'; 

$btn_link = isset($_SESSION['user_id']) ? 'dashboard.php' : 'login.php';
$btn_text = isset($_SESSION['user_id']) ? 'Doties uz sistēmu' : 'Sākt lietot bez maksas';
?>

<section class="relative bg-gradient-to-br from-green-50 to-blue-50 dark:from-zinc-900 dark:to-zinc-800 pt-20 pb-24 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="text-center lg:text-left">
                <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                    <span class="block">Tava drošā vieta</span>
                    <span class="block text-primary">emocionālajam atbalstam</span>
                </h1>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                    "Saprasts" savieno tevi ar sertificētiem psihologiem ātrai un ērtai palīdzībai. Anonīmi, droši un tev ērtā laikā.
                </p>
                <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start gap-4">
                    <div class="rounded-md shadow">
                        <a href="<?php echo $btn_link; ?>" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-primary hover:bg-green-600 md:py-4 md:text-lg transition transform hover:scale-105">
                            <?php echo $btn_text; ?>
                        </a>
                    </div>
                    <div class="mt-3 sm:mt-0 sm:ml-3">
                        <a href="#how-it-works" class="w-full flex items-center justify-center px-8 py-3 border border-gray-300 dark:border-zinc-600 text-base font-medium rounded-full text-gray-700 dark:text-gray-200 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 md:py-4 md:text-lg transition">
                            Uzzināt vairāk
                        </a>
                    </div>
                </div>
            </div>
            <div class="relative">
                <div class="w-full h-96 bg-gray-200 dark:bg-zinc-700 rounded-2xl shadow-2xl animate-pulse flex items-center justify-center">
                    <span class="text-gray-400 dark:text-gray-500 font-bold text-xl"><img src="Images/psih8.png"></span>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="how-it-works" class="py-16 bg-white dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-base text-primary font-semibold tracking-wide uppercase">Iespējas</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                Viss nepieciešamais tavai labsajūtai
            </p>
        </div>

        <div class="mt-16">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="pt-6">
                    <div class="flow-root bg-gray-50 dark:bg-zinc-800 rounded-2xl px-6 pb-8 h-full hover:shadow-lg transition border border-transparent hover:border-primary/30">
                        <div class="-mt-6">
                            <div class="inline-flex items-center justify-center p-3 bg-primary rounded-xl shadow-lg">
                                <i class="fas fa-lock text-white text-xl"></i>
                            </div>
                            <h3 class="mt-8 text-lg font-medium text-gray-900 dark:text-white tracking-tight">Droša Autorizācija</h3>
                            <p class="mt-5 text-base text-gray-500 dark:text-gray-400">
                                Datu šifrēšana un pilnīga konfidencialitāte. Tavi dati ir drošībā.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <div class="flow-root bg-gray-50 dark:bg-zinc-800 rounded-2xl px-6 pb-8 h-full hover:shadow-lg transition border border-transparent hover:border-primary/30">
                        <div class="-mt-6">
                            <div class="inline-flex items-center justify-center p-3 bg-primary rounded-xl shadow-lg">
                                <i class="fas fa-search text-white text-xl"></i>
                            </div>
                            <h3 class="mt-8 text-lg font-medium text-gray-900 dark:text-white tracking-tight">Gudra Meklēšana</h3>
                            <p class="mt-5 text-base text-gray-500 dark:text-gray-400">
                                Atrodi speciālistu pēc pieredzes, tēmas un cenas.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <div class="flow-root bg-gray-50 dark:bg-zinc-800 rounded-2xl px-6 pb-8 h-full hover:shadow-lg transition border border-transparent hover:border-primary/30">
                        <div class="-mt-6">
                            <div class="inline-flex items-center justify-center p-3 bg-primary rounded-xl shadow-lg">
                                <i class="fas fa-robot text-white text-xl"></i>
                            </div>
                            <h3 class="mt-8 text-lg font-medium text-gray-900 dark:text-white tracking-tight">AI Aģents</h3>
                            <p class="mt-5 text-base text-gray-500 dark:text-gray-400">
                                Mākslīgais intelekts palīdzēs noteikt piemērotāko speciālistu 24/7.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>