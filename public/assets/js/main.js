document.addEventListener('DOMContentLoaded', function () {
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

// Delete Functions with Bootstrap Modal Confirmation
let deleteUrl = '';

function deleteInvoice(id, invoiceNumber) {
    deleteUrl = `${window.location.origin}/Accounting/sales/delete/${id}`;
    document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete <strong>Invoice ${invoiceNumber}</strong>?`;
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

function deletePurchase(id, billNumber) {
    deleteUrl = `${window.location.origin}/Accounting/purchases/delete/${id}`;
    document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete <strong>Purchase Bill ${billNumber}</strong>?`;
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

function deleteReturn(id, returnNumber) {
    deleteUrl = `${window.location.origin}/Accounting/returns/delete/${id}`;
    document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete <strong>Return ${returnNumber}</strong>?`;
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

function deleteEstimate(id, estimateNumber) {
    deleteUrl = `${window.location.origin}/Accounting/estimates/delete/${id}`;
    document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete <strong>Estimate ${estimateNumber}</strong>?`;
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

// Handle confirmation button click
document.addEventListener('DOMContentLoaded', function () {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (deleteUrl) {
                window.location.href = deleteUrl;
            }
        });
    }
});
