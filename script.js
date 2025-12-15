document.addEventListener('DOMContentLoaded', () => {
    // --- DARK MODE LOGIC (TAILWIND) ---
    const themeToggle = document.getElementById('theme-toggle');
    const themeToggleMobile = document.getElementById('theme-toggle-mobile');
    const htmlElement = document.documentElement;

    function applyTheme(isDark) {
        if (isDark) {
            htmlElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
            if(themeToggle) themeToggle.checked = true;
            if(themeToggleMobile) themeToggleMobile.checked = true;
        } else {
            htmlElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
            if(themeToggle) themeToggle.checked = false;
            if(themeToggleMobile) themeToggleMobile.checked = false;
        }
    }

    // Pārbaudam saglabāto
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        applyTheme(true);
    } else {
        applyTheme(false);
    }

    // Klausītāji
    if(themeToggle) themeToggle.addEventListener('change', (e) => applyTheme(e.target.checked));
    if(themeToggleMobile) themeToggleMobile.addEventListener('change', (e) => applyTheme(e.target.checked));


    // --- AI CHAT LOGIC ---
    const chatToggleBtn = document.getElementById('chat-toggle-btn');
    const chatContainer = document.getElementById('chat-container');
    const chatCloseBtn = document.getElementById('chat-close-btn');
    const chatInput = document.getElementById('chat-input');
    const chatSendBtn = document.getElementById('chat-send-btn');
    const chatMessages = document.getElementById('chat-messages');

    if (chatToggleBtn) {
        chatToggleBtn.addEventListener('click', () => {
            chatContainer.classList.remove('hidden');
            chatToggleBtn.classList.add('hidden');
        });

        chatCloseBtn.addEventListener('click', () => {
            chatContainer.classList.add('hidden');
            chatToggleBtn.classList.remove('hidden');
        });

        function sendMessage() {
            const text = chatInput.value.trim();
            if (!text) return;

            addMessage(text, 'user');
            chatInput.value = '';

            const loadingMsg = addMessage('AI domā...', 'bot');

            fetch('ai_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            })
            .then(res => res.json())
            .then(data => {
                loadingMsg.remove();
                addMessage(data.reply, 'bot');
            })
            .catch(err => {
                loadingMsg.remove();
                addMessage('Kļūda savienojumā.', 'bot');
            });
        }

        chatSendBtn.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        function addMessage(text, type) {
            const div = document.createElement('div');
            div.className = 'flex w-full ' + (type === 'user' ? 'justify-end' : 'justify-start');
            
            const bubble = document.createElement('div');
            // Tailwind klases burbuļiem
            if(type === 'user') {
                bubble.className = 'bg-green-100 dark:bg-green-900 text-green-900 dark:text-green-100 py-2 px-3 rounded-lg rounded-br-none max-w-[85%] text-sm';
            } else {
                bubble.className = 'bg-white dark:bg-zinc-700 text-gray-800 dark:text-gray-200 py-2 px-3 rounded-lg rounded-tl-none max-w-[85%] text-sm shadow-sm';
            }
            
            bubble.innerHTML = text; // innerHTML lai strādātu saites
            div.appendChild(bubble);
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return div;
        }
    }


    // --- DASHBOARD AJAX & MODAL ---
    const searchInput = document.getElementById('searchInput');
    const container = document.getElementById('psychologistsContainer');
    const paginationControls = document.getElementById('paginationControls');
    let currentPage = 1;

    // Ja esam dashboard lapā
    if (container) {
        function loadPsychologists(page = 1, query = '') {
            const url = `fetch_psychologists.php?page=${page}&search=${encodeURIComponent(query)}`;
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                    const metaData = document.getElementById('pagination-data');
                    if (metaData) {
                        const totalPages = parseInt(metaData.getAttribute('data-total-pages'));
                        renderPagination(totalPages, page);
                    } else {
                        paginationControls.innerHTML = '';
                    }
                });
        }

        function renderPagination(totalPages, currentPage) {
            let buttons = '';
            const baseClass = "px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-zinc-800 dark:border-zinc-700 dark:text-gray-400 dark:hover:bg-zinc-700 dark:hover:text-white transition";
            const activeClass = "px-3 py-2 leading-tight text-white bg-primary border border-primary hover:bg-green-600 hover:text-white dark:border-zinc-700 dark:bg-zinc-700 dark:text-white transition";

            // Prev
            buttons += `<li><a href="#" onclick="changePage(${currentPage - 1}); return false;" class="${baseClass} rounded-l-lg ${currentPage === 1 ? 'pointer-events-none opacity-50' : ''}">Atpakaļ</a></li>`;
            
            // Numbers
            for (let i = 1; i <= totalPages; i++) {
                buttons += `<li><a href="#" onclick="changePage(${i}); return false;" class="${i === currentPage ? activeClass : baseClass}">${i}</a></li>`;
            }
            
            // Next
            buttons += `<li><a href="#" onclick="changePage(${currentPage + 1}); return false;" class="${baseClass} rounded-r-lg ${currentPage === totalPages ? 'pointer-events-none opacity-50' : ''}">Tālāk</a></li>`;

            paginationControls.innerHTML = buttons;
        }

        window.changePage = function(page) {
            if (page < 1) return;
            currentPage = page;
            loadPsychologists(currentPage, searchInput.value);
        };

        searchInput.addEventListener('input', (e) => {
            currentPage = 1;
            loadPsychologists(1, e.target.value);
        });

        loadPsychologists(1);

        // Event delegation for Details Button
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('details-btn')) {
                const btn = e.target;
                const modal = document.getElementById('psychologistModal');
                const modalContent = document.getElementById('modalBodyContent');

                modal.classList.remove('hidden');

                const vards = btn.getAttribute('data-vards');
                const spec = btn.getAttribute('data-spec');
                const pieredze = btn.getAttribute('data-pieredze');
                const apraksts = btn.getAttribute('data-apraksts');
                const cena = btn.getAttribute('data-cena');
                const attels = btn.getAttribute('data-attels');
                
                modalContent.innerHTML = `
                    <div class="text-center">
                        <img src="${attels}" class="mx-auto h-32 w-32 rounded-full object-cover border-4 border-white dark:border-zinc-700 shadow-lg mb-4">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">${vards}</h3>
                        <p class="text-primary font-medium mb-4">${spec}</p>
                        
                        <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-lg p-4 mb-4 text-left">
                            <p class="text-gray-600 dark:text-gray-300 mb-2"><i class="fas fa-history w-6 text-center"></i> <strong>Pieredze:</strong> ${pieredze} gadi</p>
                            <p class="text-gray-600 dark:text-gray-300"><i class="fas fa-tag w-6 text-center"></i> <strong>Cena:</strong> ${cena} EUR/h</p>
                        </div>

                        <p class="text-gray-600 dark:text-gray-400 text-left mb-6 text-sm leading-relaxed">${apraksts}</p>

                        <form action="checkout.php" method="POST">
                            <input type="hidden" name="psihologs_vards" value="${vards}">
                            <input type="hidden" name="cena" value="${parseFloat(cena.replace(',', '.'))}">
                            <button type="submit" class="w-full bg-primary hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg shadow-md transition transform hover:scale-105 flex justify-center items-center gap-2">
                                <i class="fas fa-credit-card"></i> Pieteikties un Maksāt
                            </button>
                        </form>
                    </div>
                `;
            }
        });
    }
});

// --- AI ČATA LOĢIKA ---
    const chatToggleBtn = document.getElementById('chat-toggle-btn');
    const chatContainer = document.getElementById('chat-container');
    const chatCloseBtn = document.getElementById('chat-close-btn');
    const chatInput = document.getElementById('chat-input');
    const chatSendBtn = document.getElementById('chat-send-btn');
    const chatMessages = document.getElementById('chat-messages');

    if (chatToggleBtn) {
        // Atvērt/Aizvērt čatu
        chatToggleBtn.addEventListener('click', () => {
            chatContainer.classList.remove('d-none');
            chatToggleBtn.classList.add('d-none');
        });

        chatCloseBtn.addEventListener('click', () => {
            chatContainer.classList.add('d-none');
            chatToggleBtn.classList.remove('d-none');
        });

        // Sūtīt ziņu
        function sendMessage() {
            const text = chatInput.value.trim();
            if (!text) return;


            addMessage(text, 'user-message');
            chatInput.value = '';


            const loadingMsg = addMessage('AI domā...', 'bot-message');


            fetch('ai_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            })
            .then(res => res.json())
            .then(data => {
                loadingMsg.remove(); 
                addMessage(data.reply, 'bot-message');
            })
            .catch(err => {
                loadingMsg.remove();
                addMessage('Kļūda savienojumā.', 'bot-message');
                console.error(err);
            });
        }

        chatSendBtn.addEventListener('click', sendMessage);
        

        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

function addMessage(text, className) {
            const div = document.createElement('div');
            div.classList.add('message', className);
            div.innerHTML = text; 
            
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight; 
            return div;
        }
    }