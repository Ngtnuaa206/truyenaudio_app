// Number format with commas
function number_format(x) { return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

// Toast notification
function ta_toast(message, type) {
    type = type || 'success';
    var icons = { success: '✅', error: '❌', info: 'ℹ️' };
    var container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = '<span>' + (icons[type] || '') + '</span><span>' + message + '</span>';
    container.appendChild(toast);
    setTimeout(function() { if (toast.parentNode) toast.remove(); }, 4000);
}

// ========== Theme Toggle ==========
document.addEventListener('DOMContentLoaded', function() {
    var html = document.documentElement;
    var toggle = document.getElementById('theme-toggle');
    var overlayLeft = document.getElementById('theme-overlay-left');
    var overlayRight = document.getElementById('theme-overlay-right');

    if (!toggle || !overlayLeft || !overlayRight) return;

    // Load saved theme
    var saved = localStorage.getItem('ta_theme') || 'light';
    html.setAttribute('data-theme', saved);
    toggle.textContent = saved === 'dark' ? '🌙' : '☀️';

    function syncIcons(theme) {
        var label = document.getElementById('ta-theme-label');
        if (label) label.textContent = theme === 'dark' ? '🌙 Tối' : '☀️ Sáng';
        if (toggle) toggle.textContent = theme === 'dark' ? '🌙' : '☀️';
    }

    toggle.addEventListener('click', function() {
        var current = html.getAttribute('data-theme') || 'light';
        var next = current === 'light' ? 'dark' : 'light';

        overlayLeft.style.transform = 'translateX(0)';
        overlayRight.style.transform = 'translateX(0)';

        setTimeout(function() {
            html.setAttribute('data-theme', next);
            localStorage.setItem('ta_theme', next);
            syncIcons(next);
            overlayLeft.style.transform = 'translateX(-100%)';
            overlayRight.style.transform = 'translateX(100%)';
        }, 300);
    });
});

jQuery(function($) {
    // Password toggle (show/hide)
    $(document).on('click', '.pw-toggle', function() {
        var input = $(this).closest('.pw-field').find('input');
        if (!input.length) return;
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).text('🙈');
        } else {
            input.attr('type', 'password');
            $(this).text('👁');
        }
    });

    // Rating stars hover
    $('.rating-box .star').on('mouseenter', function() {
        var val = $(this).data('rating');
        $(this).closest('.rating-box').find('.star').each(function() {
            $(this).toggleClass('hover', $(this).data('rating') <= val);
        });
    }).on('mouseleave', function() {
        $(this).closest('.rating-box').find('.star').removeClass('hover');
    });
});
