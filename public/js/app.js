$(document).ready(function() {
    // CSRF Token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Sidebar Toggle
    $('#sidebarToggle').on('click', function() {
        const sidebar = $('.sidebar');
        const mainContent = $('.main-content');
        const navbar = $('.navbar');

        sidebar.toggleClass('collapsed');
        mainContent.toggleClass('collapsed');
        navbar.toggleClass('collapsed');

        // Store state in localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.hasClass('collapsed'));
    });

    // Check localStorage on page load
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        $('.sidebar').addClass('collapsed');
        $('.main-content').addClass('collapsed');
        $('.navbar').addClass('collapsed');
    }

    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Update tooltips based on sidebar state
    function updateTooltips() {
        const isCollapsed = $('.sidebar').hasClass('collapsed');
        tooltipList.forEach(tooltip => {
            if (isCollapsed) {
                tooltip.enable();
            } else {
                tooltip.disable();
            }
        });
    }

    // Update tooltips when sidebar toggles
    $('#sidebarToggle').on('click', function() {
        setTimeout(updateTooltips, 300);
    });

    // Initial tooltip state
    updateTooltips();

    // Sidebar Toggle (Mobile)
    $('#sidebarToggle').on('click', function() {
        if ($(window).width() <= 768) {
            $('.sidebar').toggleClass('show');
        }
    });

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});

// Standard AJAX Error Handler
function handleAjaxError(xhr) {
    if (xhr.responseJSON && xhr.responseJSON.message) {
        Swal.fire('Error', xhr.responseJSON.message, 'error');
    } else {
        Swal.fire('Error', 'Something went wrong', 'error');
    }
}

// Standard Success Handler
function showSuccess(message) {
    Swal.fire('Success', message, 'success');
}
