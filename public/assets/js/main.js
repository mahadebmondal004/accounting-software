document.addEventListener('DOMContentLoaded', function() {
    console.log('AccuBooks UI Initialized');

    // Initialize Bootstrap Tooltips (if using them)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Sidebar Active State Highlighting (Fallback if PHP doesn't catch it)
    const currentLocation = location.href;
    const menuItem = document.querySelectorAll('.sidebar a');
    const menuLength = menuItem.length;
    for (let i = 0; i < menuLength; i++) {
        if (menuItem[i].href === currentLocation) {
            menuItem[i].classList.add("active");
        }
    }
});
