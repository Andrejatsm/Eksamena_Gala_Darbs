document.addEventListener('DOMContentLoaded', () => {
    const appPathPrefix = typeof window.APP_PATH_PREFIX === 'string' ? window.APP_PATH_PREFIX : '';

    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    const sendContactBtn = document.getElementById('sendContactBtn');
    const openContactModalBtn = document.getElementById('openContactModalBtn');
    const closeContactModalBtn = document.getElementById('closeContactModalBtn');
    const contactModalBackdrop = document.getElementById('contactModalBackdrop');
    const contactModal = document.getElementById('contactModal');

    const closeContactModal = () => {
        if (contactModal) {
            contactModal.classList.add('hidden');
        }
    };

    if (openContactModalBtn && contactModal) {
        openContactModalBtn.addEventListener('click', () => {
            contactModal.classList.remove('hidden');
        });
    }

    if (closeContactModalBtn) {
        closeContactModalBtn.addEventListener('click', closeContactModal);
    }

    if (contactModalBackdrop) {
        contactModalBackdrop.addEventListener('click', closeContactModal);
    }

    if (sendContactBtn) {
        sendContactBtn.addEventListener('click', () => {
            const emailEl = document.getElementById('contactEmail');
            const messageEl = document.getElementById('contactMessage');
            if (!emailEl || !messageEl) return;

            const email = emailEl.value.trim();
            const message = messageEl.value.trim();

            if (!email || !message) {
                alert('Lūdzu, aizpildiet visus laukus.');
                return;
            }

            fetch(`${appPathPrefix}contact_handler.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, message }),
            })
                .then((res) => res.json())
                .then((data) => {
                    alert(data.message);
                    closeContactModal();
                    emailEl.value = '';
                    messageEl.value = '';
                })
                .catch(() => {
                    alert('Kļūda sūtot ziņu.');
                });
        });
    }

    const chatToggleBtn = document.getElementById('chat-toggle-btn');
    const chatContainer = document.getElementById('chat-container');
    const chatCloseBtn = document.getElementById('chat-close-btn');
    const chatInput = document.getElementById('chat-input');
    const chatSendBtn = document.getElementById('chat-send-btn');
    const chatMessages = document.getElementById('chat-messages');

    if (chatToggleBtn && chatContainer && chatCloseBtn && chatInput && chatSendBtn && chatMessages) {
        chatToggleBtn.addEventListener('click', () => {
            chatContainer.classList.remove('hidden');
            chatToggleBtn.classList.add('hidden');
            setTimeout(() => chatInput.focus(), 100);
        });

        chatCloseBtn.addEventListener('click', () => {
            chatContainer.classList.add('hidden');
            chatToggleBtn.classList.remove('hidden');
        });

        const escapeHtml = (text) => {
            return text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const formatChatMessage = (text) => {
            let html = escapeHtml(text);

            html = html.replace(/\btest\.php\b/gi, 'tests/tests.php');
            html = html.replace(/\barticle\.php\b/gi, 'published_articles.php');
            html = html.replace(/\barticles\.php\b/gi, 'published_articles.php');
            html = html.replace(/\bprofile\.php\b/gi, 'user_profile.php');
            html = html.replace(/\btests\.php\b/gi, 'tests/tests.php');

            html = html.replace(/\bReģistrēties\b/g, `<a class="ai-link" href="${appPathPrefix}register.php">Reģistrēties</a>`);
            html = html.replace(/\bIelogoties\b/g, `<a class="ai-link" href="${appPathPrefix}login.php">Ielogoties</a>`);
            html = html.replace(/\bPašnovērtējuma testi\b/g, `<a class="ai-link" href="${appPathPrefix}tests/tests.php">Pašnovērtējuma testi</a>`);

            html = html.replace(/\[([^\]]+)\]\(([^)]+\.php(?:\?[^)\s]+)?)\)/g, `<a class="ai-link" href="${appPathPrefix}$2">$1</a>`);
            html = html.replace(/(^|\s)([a-z0-9_\-/]+\.php(?:\?[a-z0-9_\-=&%]+)?)(?=$|\s|[.,!?:;])/gi, `$1<a class="ai-link" href="${appPathPrefix}$2">$2</a>`);

            return html.replace(/\n/g, '<br>');
        };

        const appendMessage = (text, isUser = false) => {
            const msgDiv = document.createElement('div');
            msgDiv.className = isUser ? 'flex justify-end mt-2' : 'flex justify-start mt-2';

            const innerDiv = document.createElement('div');
            innerDiv.className = isUser
                ? 'chat-message bg-primary text-white rounded-2xl rounded-tr-sm py-3 px-4 max-w-[85%] text-sm shadow-sm'
                : 'chat-message bg-white dark:bg-zinc-700 text-gray-800 dark:text-gray-200 rounded-2xl rounded-tl-sm py-3 px-4 max-w-[85%] text-sm shadow-sm border border-gray-100 dark:border-zinc-600';

            innerDiv.innerHTML = formatChatMessage(text);
            msgDiv.appendChild(innerDiv);
            chatMessages.appendChild(msgDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        };

        const sendChatMessage = async () => {
            const message = chatInput.value.trim();
            if (!message) return;

            appendMessage(message, true);
            chatInput.value = '';
            chatInput.disabled = true;
            chatSendBtn.disabled = true;

            const loadingId = 'loading-' + Date.now();
            const loadingDiv = document.createElement('div');
            loadingDiv.id = loadingId;
            loadingDiv.className = 'flex justify-start mt-2';
            loadingDiv.innerHTML = '<div class="bg-white dark:bg-zinc-700 text-gray-500 rounded-2xl rounded-tl-sm py-3 px-4 max-w-[85%] text-sm shadow-sm border border-gray-100 dark:border-zinc-600"><i class="fas fa-circle-notch fa-spin"></i> Domā...</div>';
            chatMessages.appendChild(loadingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            try {
                const res = await fetch(`${appPathPrefix}ai_handler.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message }),
                });
                const data = await res.json();
                const loadingEl = document.getElementById(loadingId);
                if (loadingEl) {
                    loadingEl.remove();
                }
                appendMessage(data.error ? 'Kļūda: ' + data.error : data.reply);
            } catch (err) {
                const loadingEl = document.getElementById(loadingId);
                if (loadingEl) {
                    loadingEl.remove();
                }
                appendMessage('Pievienojuma kļūda serverim.');
            }

            chatInput.disabled = false;
            chatSendBtn.disabled = false;
            chatInput.focus();
        };

        chatSendBtn.addEventListener('click', sendChatMessage);
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendChatMessage();
            }
        });
    }
});
