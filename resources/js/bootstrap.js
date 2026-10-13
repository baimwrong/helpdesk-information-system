// Client-side helper scripts
window.addEventListener('DOMContentLoaded', () => {
    // Enable Bootstrap tooltips if any
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (window.bootstrap && window.bootstrap.Tooltip) {
        [...tooltipTriggerList].map(tooltipTriggerEl => new window.bootstrap.Tooltip(tooltipTriggerEl));
    }
});
