// Register Service Worker for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const swUrl = '/service-worker.js';

        navigator.serviceWorker
            .register(swUrl)
            .then((registration) => {
                console.log('Service Worker registered successfully:', registration.scope);

                // Check for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    console.log('Service Worker update found!');

                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New service worker available, prompt user to refresh
                            if (confirm('Versi baru aplikasi tersedia! Muat ulang halaman untuk update?')) {
                                newWorker.postMessage({ type: 'SKIP_WAITING' });
                                window.location.reload();
                            }
                        }
                    });
                });
            })
            .catch((error) => {
                console.error('Service Worker registration failed:', error);
            });

        // Reload page when new service worker takes control
        let refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (!refreshing) {
                refreshing = true;
                window.location.reload();
            }
        });
    });
}

// Listen for online/offline events
window.addEventListener('online', () => {
    console.log('Back online!');
    // You can add custom logic here, like showing a notification
});

window.addEventListener('offline', () => {
    console.log('Gone offline!');
    // You can add custom logic here, like showing a notification
});

// Prompt to install PWA
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();
    // Stash the event so it can be triggered later
    deferredPrompt = e;

    // Show install button or notification
    console.log('PWA install prompt available');

    // You can show a custom install button here
    // Example: showInstallButton();
});

window.addEventListener('appinstalled', () => {
    console.log('PWA installed successfully!');
    deferredPrompt = null;
});
