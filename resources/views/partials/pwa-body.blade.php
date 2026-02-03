<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('{{ asset('service-worker.js') }}')
                .catch(() => {
                    // Silent fail for browsers that block SW on some contexts
                });
        });
    }
</script>

@include('partials.pwa-install-banner')

<script>
    (() => {
        let installPromptEvent = null;
        const banner = document.getElementById('pwa-install-banner');
        const installButton = document.getElementById('pwa-install-action');
        const closeButton = document.getElementById('pwa-install-close');
        const installedKey = 'pwa-installed';

        const isStandalone = () =>
            window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true;

        const showBanner = () => {
            if (!banner || isStandalone() || localStorage.getItem(installedKey) === '1') {
                return;
            }
            banner.style.display = 'block';
        };

        const hideBanner = () => {
            if (banner) {
                banner.style.display = 'none';
            }
        };

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            installPromptEvent = event;
            showBanner();
        });

        window.addEventListener('appinstalled', () => {
            hideBanner();
            installPromptEvent = null;
            localStorage.setItem(installedKey, '1');
        });

        if (installButton) {
            installButton.addEventListener('click', async () => {
                if (!installPromptEvent) {
                    showBanner();
                    return;
                }
                installPromptEvent.prompt();
                const choice = await installPromptEvent.userChoice;
                if (choice?.outcome === 'accepted') {
                    localStorage.setItem(installedKey, '1');
                    hideBanner();
                }
                installPromptEvent = null;
            });
        }

        if (closeButton) {
            closeButton.addEventListener('click', () => {
                hideBanner();
            });
        }

        window.addEventListener('load', async () => {
            if (localStorage.getItem(installedKey) === '1') {
                return;
            }

            if ('getInstalledRelatedApps' in navigator) {
                try {
                    const apps = await navigator.getInstalledRelatedApps();
                    if (apps?.length) {
                        localStorage.setItem(installedKey, '1');
                        return;
                    }
                } catch (error) {
                    // Ignore detection errors
                }
            }

            if (!isStandalone()) {
                showBanner();
            }
        });
    })();
</script>
