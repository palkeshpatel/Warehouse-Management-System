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

// Loader Functions
function showLoader() {
    if ($('.loader-overlay').length === 0) {
        $('body').append(`
            <div class="loader-overlay">
                <div class="loader-spinner"></div>
            </div>
        `);
    }
}

function hideLoader() {
    $('.loader-overlay').remove();
}

// Setup global AJAX loader
$(document).ajaxStart(function() {
    showLoader();
}).ajaxStop(function() {
    hideLoader();
}).ajaxError(function(event, xhr, settings) {
    hideLoader();
});

// Standard AJAX Error Handler
function handleAjaxError(xhr) {
    hideLoader();
    if (xhr.responseJSON && xhr.responseJSON.message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: xhr.responseJSON.message,
            confirmButtonColor: '#FF9900'
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong',
            confirmButtonColor: '#FF9900'
        });
    }
}

// Standard Success Handler
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        confirmButtonColor: '#FF9900'
    });
}
