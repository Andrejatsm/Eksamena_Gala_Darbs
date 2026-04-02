(() => {
    const config = window.VIDEO_CONFIG;
    if (!config) return;

    const container = document.getElementById('jitsiContainer');
    const endCallBtn = document.getElementById('endCallBtn');

    async function initCall() {
        try {
            const res = await fetch(`${config.apiUrl}?appointment_id=${config.appointmentId}`);
            if (!res.ok) {
                container.innerHTML = `
                    <div class="flex items-center justify-center h-full text-white">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle text-4xl mb-4 text-yellow-400"></i>
                            <p>Neizdevās izveidot videozvanu. Lūdzu, mēģiniet vēlreiz.</p>
                        </div>
                    </div>`;
                return;
            }

            const data = await res.json();
            if (!data.room_token) return;

            container.innerHTML = '';

            const domain = config.jitsiDomain || 'meet.jit.si';
            const api = new JitsiMeetExternalAPI(domain, {
                roomName: data.room_token,
                parentNode: container,
                width: '100%',
                height: '100%',
                userInfo: {
                    displayName: config.displayName,
                },
                configOverwrite: {
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    prejoinPageEnabled: false,
                    disableDeepLinking: true,
                    enableInsecureRoomNameWarning: false,
                    requireDisplayName: false,
                    toolbarButtons: [
                        'microphone', 'camera', 'desktop', 'chat',
                        'fullscreen', 'hangup', 'settings',
                        'tileview', 'toggle-camera'
                    ],
                },
                interfaceConfigOverwrite: {
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    DEFAULT_BACKGROUND: '#1a1a2e',
                    TOOLBAR_ALWAYS_VISIBLE: true,
                    DISABLE_JOIN_LEAVE_NOTIFICATIONS: true,
                },
            });

            api.addEventListener('readyToClose', () => {
                window.location.href = endCallBtn.getAttribute('href');
            });

            if (endCallBtn) {
                endCallBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    api.executeCommand('hangup');
                    setTimeout(() => {
                        window.location.href = endCallBtn.getAttribute('href');
                    }, 500);
                });
            }
        } catch (e) {
            container.innerHTML = `
                <div class="flex items-center justify-center h-full text-white">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-4xl mb-4 text-yellow-400"></i>
                        <p>Kļūda ielādējot videozvanu.</p>
                    </div>
                </div>`;
        }
    }

    initCall();
})();
