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
    var isAdmin = typeof ta_config !== 'undefined' && ta_config.is_admin;
    var saved = isAdmin ? 'light' : (localStorage.getItem('ta_theme') || 'light');
    html.setAttribute('data-theme', saved);
    toggle.textContent = saved === 'dark' ? '🌙' : '☀️';

    function syncIcons(theme) {
        var label = document.getElementById('ta-theme-label');
        if (label) label.textContent = theme === 'dark' ? '🌙 Tối' : '☀️ Sáng';
        if (toggle) toggle.textContent = theme === 'dark' ? '🌙' : '☀️';
        var mobileToggle = document.getElementById('mobile-theme-toggle');
        if (mobileToggle) mobileToggle.textContent = theme === 'dark' ? '🌙' : '☀️';
    }

    toggle.addEventListener('click', function() {
        if (isAdmin) return;
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

    var adminBarToggle = document.getElementById('wp-admin-bar-ta-theme-toggle');
    if (adminBarToggle) {
        adminBarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggle.click();
        });
    }
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
    $('.story-hero-rating .star').on('mouseenter', function() {
        var val = $(this).data('rating');
        $(this).closest('.story-hero-rating').find('.star').each(function() {
            $(this).toggleClass('hover', $(this).data('rating') <= val);
        });
    }).on('mouseleave', function() {
        $(this).closest('.story-hero-rating').find('.star').removeClass('hover');
    });

    // ========== Audio Player ==========
    var audio = document.getElementById('audio-element');
    if (audio) {
        var $playBtn = $('#audio-play');
        var $progress = $('#audio-progress');
        var $progressFill = $('#audio-progress-fill');
        var $currentTime = $('#audio-current-time');
        var $duration = $('#audio-duration');
        var $stickyBar = $('#sticky-audio-bar');
        var $stickyPlay = $('#sticky-play');
        var $stickyFill = $('#sticky-progress-fill');
        var sleepTimer = null;
        var sleepEndTime = null;

        function formatTime(seconds) {
            var mins = Math.floor(seconds / 60);
            var secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }

        function updateProgress() {
            if (audio.duration) {
                var percent = (audio.currentTime / audio.duration) * 100;
                $progressFill.css('width', percent + '%');
                $stickyFill.css('width', percent + '%');
                $currentTime.text(formatTime(audio.currentTime));
            }
        }

        // Play/Pause
        function togglePlay() {
            if (audio.paused) {
                audio.play();
                $playBtn.html('<svg viewBox="0 0 24 24"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>');
                $stickyPlay.text('⏸');
                $stickyBar.addClass('show');
            } else {
                audio.pause();
                $playBtn.html('<svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>');
                $stickyPlay.text('▶');
            }
        }

        $playBtn.on('click', togglePlay);
        $stickyPlay.on('click', togglePlay);

        // Progress bar click
        $progress.on('click', function(e) {
            var rect = this.getBoundingClientRect();
            var percent = (e.clientX - rect.left) / rect.width;
            audio.currentTime = percent * audio.duration;
        });

        // Time update
        audio.addEventListener('timeupdate', updateProgress);
        audio.addEventListener('loadedmetadata', function() {
            $duration.text(formatTime(audio.duration));
        });

        // Skip forward/backward
        $('#audio-prev').on('click', function() { audio.currentTime = Math.max(0, audio.currentTime - 10); });
        $('#audio-next').on('click', function() { audio.currentTime = Math.min(audio.duration, audio.currentTime + 10); });

        // Speed control
        $('.speed-btn').on('click', function() {
            var speed = $(this).data('speed');
            audio.playbackRate = speed;
            $('.speed-btn').removeClass('active');
            $(this).addClass('active');
            localStorage.setItem('ta_audio_speed', speed);
        });

        // Load saved speed
        var savedSpeed = localStorage.getItem('ta_audio_speed');
        if (savedSpeed) {
            audio.playbackRate = parseFloat(savedSpeed);
            $('.speed-btn').removeClass('active');
            $('.speed-btn[data-speed="' + savedSpeed + '"]').addClass('active');
        }

        // Sleep timer
        $('#sleep-timer-btn').on('click', function(e) {
            e.stopPropagation();
            $('#sleep-popup').toggleClass('show');
        });

        $(document).on('click', function() {
            $('#sleep-popup').removeClass('show');
        });

        $('.sleep-popup button').on('click', function() {
            var minutes = $(this).data('minutes');
            clearInterval(sleepTimer);
            sleepTimer = null;
            sleepEndTime = null;
            $('.sleep-btn').removeClass('active');

            if (minutes > 0) {
                sleepEndTime = Date.now() + minutes * 60 * 1000;
                $('.sleep-btn').addClass('active');
                ta_toast('Hẹn giờ ' + minutes + ' phút', 'info');
                
                sleepTimer = setInterval(function() {
                    if (Date.now() >= sleepEndTime) {
                        audio.pause();
                        clearInterval(sleepTimer);
                        sleepTimer = null;
                        sleepEndTime = null;
                        $('.sleep-btn').removeClass('active');
                        ta_toast('Đã tắt âm thanh theo hẹn giờ', 'info');
                    }
                }, 1000);
            } else {
                ta_toast('Đã tắt hẹn giờ', 'info');
            }
            $('#sleep-popup').removeClass('show');
        });
    }

    // ========== Reading Settings ==========
    var $readerContent = $('#reader-content');
    if ($readerContent.length) {
        // Font size
        var fontSize = parseInt(localStorage.getItem('ta_font_size')) || 16;
        $readerContent.css('font-size', fontSize + 'px');
        $('#font-size-display').text(fontSize);

        $('#font-increase').on('click', function() {
            if (fontSize < 24) {
                fontSize += 2;
                $readerContent.css('font-size', fontSize + 'px');
                $('#font-size-display').text(fontSize);
                localStorage.setItem('ta_font_size', fontSize);
            }
        });

        $('#font-decrease').on('click', function() {
            if (fontSize > 12) {
                fontSize -= 2;
                $readerContent.css('font-size', fontSize + 'px');
                $('#font-size-display').text(fontSize);
                localStorage.setItem('ta_font_size', fontSize);
            }
        });

        // Line spacing
        var lineSpacing = localStorage.getItem('ta_line_spacing') || '2';
        $readerContent.css('line-height', lineSpacing);
        $('#line-spacing').val(lineSpacing);

        $('#line-spacing').on('change', function() {
            lineSpacing = $(this).val();
            $readerContent.css('line-height', lineSpacing);
            localStorage.setItem('ta_line_spacing', lineSpacing);
        });

        // Reading theme
        var readingTheme = localStorage.getItem('ta_reading_theme') || 'dark';
        applyReadingTheme(readingTheme);

        $('#theme-light').on('click', function() { applyReadingTheme('light'); });
        $('#theme-dark').on('click', function() { applyReadingTheme('dark'); });

        function applyReadingTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('ta_reading_theme', theme);
            localStorage.setItem('ta_theme', theme);
            $('.setting-btn[id^="theme-"]').removeClass('active');
            $('#theme-' + theme).addClass('active');
            var icon = theme === 'dark' ? '🌙' : '☀️';
            var toggle = document.getElementById('theme-toggle');
            if (toggle) toggle.textContent = icon;
            var mobileToggle = document.getElementById('mobile-theme-toggle');
            if (mobileToggle) mobileToggle.textContent = icon;
        }

        // Auto-scroll
        var autoScrollInterval = null;
        var autoScrollSpeed = 1;

        $('#auto-scroll-btn').on('click', function() {
            if (autoScrollInterval) {
                clearInterval(autoScrollInterval);
                autoScrollInterval = null;
                $(this).removeClass('active');
            } else {
                $(this).addClass('active');
                autoScrollInterval = setInterval(function() {
                    window.scrollBy(0, autoScrollSpeed);
                }, 50);
            }
        });

        // Fullscreen
        $('#fullscreen-btn').on('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        });
    }
});