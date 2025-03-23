// Wait for the DOM to be loaded
document.addEventListener('DOMContentLoaded', function() {
    // Format currency inputs
    const amountInput = document.getElementById('amount');
    if (amountInput) {
        amountInput.addEventListener('blur', function() {
            if (this.value) {
                const value = parseFloat(this.value).toFixed(2);
                this.value = value;
            }
        });
    }
    
    // Set today's date as default on the date input
    const dateInput = document.getElementById('expense_date');
    if (dateInput && !dateInput.value) {
        const today = new Date();
        const year = today.getFullYear();
        let month = today.getMonth() + 1;
        let day = today.getDate();
        
        month = month < 10 ? '0' + month : month;
        day = day < 10 ? '0' + day : day;
        
        dateInput.value = `${year}-${month}-${day}`;
    }
    
    // Handle the URL parameters to display notifications
    const urlParams = new URLSearchParams(window.location.search);
    const successParam = urlParams.get('success');
    const errorParam = urlParams.get('error');
    
    if (successParam) {
        let message = '';
        switch (successParam) {
            case '1':
                message = 'Expense added successfully!';
                break;
            case '2':
                message = 'Expense updated successfully!';
                break;
            case '3':
                message = 'Expense deleted successfully!';
                break;
            default:
                message = 'Operation completed successfully!';
        }
        
        showNotification(message, 'success');
    }
    
    if (errorParam) {
        showNotification(errorParam, 'error');
    }
    
    // Add row highlighting for table rows on hover
    const tableRows = document.querySelectorAll('table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseover', function() {
            this.classList.add('table-hover');
        });
        
        row.addEventListener('mouseout', function() {
            this.classList.remove('table-hover');
        });
    });
});

// Function to show notifications
function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    
    alertDiv.innerHTML = `
        <i class="fas ${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Insert at the top of the container
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
        alert.close();
    }, 5000);
}

// Function to confirm before deleting
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this expense?')) {
        window.location.href = `delete-expense.php?id=${id}`;
    }
}

// Handle date range filtering
const startDateInput = document.querySelector('input[name="start_date"]');
const endDateInput = document.querySelector('input[name="end_date"]');

if (startDateInput && endDateInput) {
    startDateInput.addEventListener('change', function() {
        if (this.value && !endDateInput.value) {
            endDateInput.value = this.value;
        }
    });
    
    endDateInput.addEventListener('change', function() {
        if (this.value && !startDateInput.value) {
            startDateInput.value = this.value;
        }
    });
}