(() => {
    const config = window.CHAT_CONFIG;
    if (!config) return;

    const messagesContainer = document.getElementById('chatMessages');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    let lastMessageId = 0;
    let pollTimer = null;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatTime(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        return d.toLocaleTimeString('lv-LV', { hour: '2-digit', minute: '2-digit' });
    }

    function renderMessage(msg) {
        const wrapper = document.createElement('div');
        wrapper.className = `flex ${msg.is_mine ? 'justify-end' : 'justify-start'}`;

        const bubble = document.createElement('div');
        bubble.className = msg.is_mine
            ? 'max-w-[75%] bg-primary text-white rounded-2xl rounded-br-md px-4 py-2.5 shadow-sm'
            : 'max-w-[75%] bg-gray-100 dark:bg-zinc-700 text-gray-900 dark:text-white rounded-2xl rounded-bl-md px-4 py-2.5 shadow-sm';

        const text = document.createElement('p');
        text.className = 'text-sm break-words whitespace-pre-wrap';
        text.innerHTML = escapeHtml(msg.message);

        const time = document.createElement('span');
        time.className = msg.is_mine
            ? 'text-[10px] text-white/70 mt-1 block text-right'
            : 'text-[10px] text-gray-400 dark:text-gray-500 mt-1 block';
        time.textContent = formatTime(msg.created_at);

        bubble.appendChild(text);
        bubble.appendChild(time);
        wrapper.appendChild(bubble);
        return wrapper;
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    async function fetchMessages() {
        try {
            const res = await fetch(
                `${config.apiUrl}?action=fetch&appointment_id=${config.appointmentId}&after_id=${lastMessageId}`
            );
            if (!res.ok) return;
            const data = await res.json();

            // Sesija nav aktivizēta — parādām gaidīšanas ziņojumu
            if (data.chat_inactive) {
                if (!messagesContainer.querySelector('.chat-waiting-msg')) {
                    messagesContainer.innerHTML = `
                        <div class="chat-waiting-msg flex flex-col items-center justify-center h-full text-amber-500 dark:text-amber-400">
                            <i class="fas fa-clock text-4xl mb-3"></i>
                            <p class="text-sm text-center">${(window.LANG || {}).session_not_active || 'Psihologs vēl nav aktivizējis sesiju.'}</p>
                        </div>`;
                }
                return;
            }

            // Ja sesija tikko tika aktivizēta — pārlādējam lapu, lai serveris ģenerē ievades formu
            if (messagesContainer.querySelector('.chat-waiting-msg')) {
                window.location.reload();
                return;
            }

            if (data.messages && data.messages.length > 0) {
                // Remove loading spinner on first load
                if (lastMessageId === 0) {
                    messagesContainer.innerHTML = '';
                }

                const wasAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight < 80;

                data.messages.forEach(msg => {
                    messagesContainer.appendChild(renderMessage(msg));
                    lastMessageId = Math.max(lastMessageId, msg.id);
                });

                if (wasAtBottom || lastMessageId === 0) {
                    scrollToBottom();
                }
            } else if (lastMessageId === 0) {
                messagesContainer.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 dark:text-gray-500">
                        <i class="fas fa-comments text-4xl mb-3"></i>
                        <p class="text-sm">${(window.LANG || {}).no_messages || 'Vēl nav ziņojumu. Sāciet sarunu!'}</p>
                    </div>`;
            }
        } catch (e) {
            // Silently retry on next poll
        }
    }

    async function sendMessage(text) {
        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('appointment_id', config.appointmentId);
        formData.append('message', text);

        try {
            const res = await fetch(config.apiUrl, {
                method: 'POST',
                body: formData
            });
            if (!res.ok) return;
            const data = await res.json();

            if (data.success && data.message) {
                // Remove empty state if present
                const emptyState = messagesContainer.querySelector('.fa-comments');
                if (emptyState) {
                    messagesContainer.innerHTML = '';
                }

                messagesContainer.appendChild(renderMessage(data.message));
                lastMessageId = Math.max(lastMessageId, data.message.id);
                scrollToBottom();
            }
        } catch (e) {
            // Will appear on next poll anyway
        }
    }

    if (chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text) return;
            chatInput.value = '';
            chatInput.focus();
            await sendMessage(text);
        });
    }

    // Initial fetch + poll every 3 seconds
    fetchMessages();
    pollTimer = setInterval(fetchMessages, 3000);

    // Stop polling when page is hidden, resume when visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(pollTimer);
        } else {
            fetchMessages();
            pollTimer = setInterval(fetchMessages, 3000);
        }
    });
})();
