<footer class="mt-auto bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800 py-8 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                
                <div class="text-center md:text-left">
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                        &copy; <?php echo date("Y"); ?> Saprasts. Visas tiesības aizsargātas.
                    </p>
                </div>

                <div class="flex items-center space-x-6 text-sm font-medium">
                    <a href="privacy.php" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors">Privātuma politika</a>
                    <button onclick="document.getElementById('contactModal').classList.remove('hidden')" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors focus:outline-none">Sazināties</button>
                </div>
                
            </div>
        </div>
    </footer>

    <button id="chat-toggle-btn" class="fixed bottom-6 right-6 z-50 bg-primary hover:bg-green-600 text-white p-4 rounded-full shadow-lg transition-all transform hover:scale-110 flex items-center justify-center w-14 h-14 animate-bounce">
        <i class="fas fa-comment-dots text-2xl"></i>
    </button>

    <div id="chat-container" class="hidden fixed bottom-24 right-6 z-50 w-80 md:w-96 h-[500px] bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 flex flex-col overflow-hidden transition-all duration-300">
        <div class="bg-primary p-4 flex justify-between items-center text-white shadow-sm">
            <div class="font-bold flex items-center gap-2">
                <i class="fas fa-robot"></i> AI Aģents
            </div>
            <button id="chat-close-btn" class="hover:text-gray-200 focus:outline-none transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="chat-messages" class="flex-1 p-4 overflow-y-auto space-y-3 bg-gray-50 dark:bg-zinc-900/50 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-zinc-600">
            <div class="flex justify-start">
                <div class="bg-white dark:bg-zinc-700 text-gray-800 dark:text-gray-200 rounded-2xl rounded-tl-none py-2 px-4 max-w-[85%] text-sm shadow-sm border border-gray-100 dark:border-zinc-600">
                    Sveiki! Esmu AI aģents. Kā jūtaties? Es varu ieteikt speciālistu.
                </div>
            </div>
        </div>
        <div class="p-3 border-t border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex gap-2">
            <input type="text" id="chat-input" placeholder="Rakstiet šeit..." class="flex-1 bg-gray-100 dark:bg-zinc-700 text-gray-900 dark:text-white rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary transition placeholder-gray-400">
            <button id="chat-send-btn" class="bg-primary hover:bg-green-600 text-white rounded-full w-10 h-10 flex items-center justify-center transition shadow-sm transform hover:rotate-12">
                <i class="fas fa-paper-plane text-sm"></i>
            </button>
        </div>
    </div>

    <div id="contactModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="document.getElementById('contactModal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-zinc-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full border border-gray-200 dark:border-zinc-700">
                <div class="bg-white dark:bg-zinc-800 px-6 pt-5 pb-4">
                    <h3 class="text-xl leading-6 font-bold text-gray-900 dark:text-white mb-4" id="modal-title">Sazināties ar mums</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jūsu E-pasts</label>
                            <input type="email" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition" placeholder="vards@piemers.lv">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ziņojums</label>
                            <textarea rows="3" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition"></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-zinc-700/30 px-6 py-3 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-green-600 focus:outline-none sm:w-auto sm:text-sm transition" onclick="alert('Ziņa nosūtīta!'); document.getElementById('contactModal').classList.add('hidden')">Nosūtīt</button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-zinc-500 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition" onclick="document.getElementById('contactModal').classList.add('hidden')">Atcelt</button>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Mobile menu toggle logic
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        
        if(btn){
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>