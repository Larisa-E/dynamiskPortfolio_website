document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('[data-dismiss]');
    alerts.forEach((alert) => {
        const timeout = Number(alert.dataset.dismiss) || 0;
        if (timeout > 0) {
            setTimeout(() => {
                alert.classList.add('is-hidden');
            }, timeout);
        }
    });
});
