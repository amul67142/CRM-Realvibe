/**
 * Main JavaScript File
 * Global utilities and initialization
 */

(function ($) {
    'use strict';

    // Auto-hide flash messages after 3 seconds
    setTimeout(function () {
        $('.flash-message').fadeOut('slow');
    }, 3000);

    // Confirm delete actions
    $('.btn-delete').on('click', function (e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });

    // CSRF token handling for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Global AJAX error handler
    $(document).ajaxError(function (event, jqxhr, settings, thrownError) {
        console.error('AJAX Error:', thrownError);
        showNotification('An error occurred. Please try again.', 'error');
    });

    // Show notification (toast)
    window.showNotification = function (message, type = 'info') {
        const alertClass = type === 'error' ? 'alert-error' :
            type === 'success' ? 'alert-success' :
                type === 'warning' ? 'alert-warning' : 'alert-info';

        const notification = $(`
            <div class="alert ${alertClass} fixed top-4 right-4 z-50 max-w-md shadow-lg">
                <span>${message}</span>
            </div>
        `);

        $('body').append(notification);

        setTimeout(function () {
            notification.fadeOut('slow', function () {
                $(this).remove();
            });
        }, 3000);
    };

    // Insert merge tag into textarea
    window.insertMergeTag = function (elementId, tag) {
        const element = document.getElementById(elementId);
        if (element) {
            const cursorPos = element.selectionStart;
            const text = element.value;
            element.value = text.substring(0, cursorPos) + tag + text.substring(cursorPos);
            element.focus();
        }
    };

    // Form validation helper
    window.validateForm = function (formId) {
        const form = document.getElementById(formId);
        if (!form) return false;

        return form.checkValidity();
    };

    // Format phone number as user types
    $('.phone-input').on('input', function () {
        let value = $(this).val().replace(/\D/g, '');

        if (value.length > 10) {
            value = value.substring(0, 10);
        }

        if (value.length > 5) {
            value = value.slice(0, 5) + ' ' + value.slice(5);
        }

        $(this).val(value);
    });

    // File upload preview
    $('.file-input').on('change', function (e) {
        const file = e.target.files[0];
        const previewId = $(this).data('preview');

        if (file && previewId) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $(`#${previewId}`).attr('src', e.target.result).removeClass('hidden');
            };
            reader.readAsDataURL(file);
        }

        // Show filename
        const filename = file ? file.name : 'No file chosen';
        $(this).next('.file-label').text(filename);
    });

    // Debounce function for search
    window.debounce = function (func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // Number formatting
    window.formatNumber = function (num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    // Initialize tooltips (if using a tooltip library)
    $('[data-tooltip]').each(function () {
        $(this).attr('title', $(this).data('tooltip'));
    });

})(jQuery);
