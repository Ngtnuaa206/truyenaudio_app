<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
    <div class="header-inner">
        <a href="<?php echo home_url(); ?>" class="site-title">Truyen<span>Audio</span></a>
        <nav>
            <?php wp_nav_menu(['theme_location' => 'primary', 'menu_class' => 'nav-menu', 'container' => false, 'fallback_cb' => 'ta_menu_fallback']); ?>
        </nav>
        <div class="header-actions">
            <button id="theme-toggle" class="theme-toggle" title="Chuyển giao diện">☀️</button>
            <?php if (is_user_logged_in()):
                $user = wp_get_current_user();
                $lt = get_user_meta($user->ID, '_linh_thach', true) ?: 0;
            ?>
                <a href="<?php echo home_url('/linh-thach'); ?>" class="btn btn-sm btn-outline">💎 <?php echo number_format($lt); ?></a>
                <div class="notif-dropdown" id="notif-wrapper">
                    <button class="notif-btn" id="notif-toggle" title="Thông báo">🔔<span class="notif-badge" id="notif-badge" style="display:none;">0</span></button>
                    <div class="notif-menu" id="notif-menu">
                        <div class="notif-header">
                            <span>Thông báo</span>
                            <button class="notif-close" onclick="document.getElementById('notif-menu').classList.remove('open')">&times;</button>
                        </div>
                        <div class="notif-list" id="notif-list">
                            <div class="notif-empty">Đang tải...</div>
                        </div>
                    </div>
                </div>
                <div class="user-dropdown">
                    <span class="btn btn-sm btn-primary"><?php echo esc_html($user->display_name); ?></span>
                    <div class="dropdown-menu">
                        <a href="<?php echo home_url('/profile'); ?>">Thông tin cá nhân</a>
                        <a href="<?php echo home_url('/theo-doi'); ?>">Truyện theo dõi</a>
                        <a href="<?php echo home_url('/lich-su'); ?>">Lịch sử đọc</a>
                        <?php if (current_user_can('edit_posts')): ?>
                            <a href="<?php echo admin_url(); ?>">Quản trị</a>
                        <?php endif; ?>
                        <a href="<?php echo wp_logout_url(home_url()); ?>">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo home_url('/dang-nhap'); ?>" class="btn btn-sm btn-outline">Đăng nhập</a>
                <a href="<?php echo home_url('/dang-ky'); ?>" class="btn btn-sm btn-primary">Đăng ký</a>
            <?php endif; ?>
        </div>
        <button class="mobile-nav-toggle" onclick="toggleMobileMenu()">☰</button>
    </div>
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobile-menu">
        <div class="mobile-menu-head">
            <button id="mobile-theme-toggle" class="mobile-theme-toggle" title="Chuyển giao diện">☀️</button>
            <button class="mobile-menu-close" onclick="toggleMobileMenu()">&times;</button>
        </div>
        <?php if (is_user_logged_in()):
            $mobile_user = wp_get_current_user();
            $mobile_lt = get_user_meta($mobile_user->ID, '_linh_thach', true) ?: 0;
        ?>
            <div class="mobile-user-section">
                <button class="mobile-user-toggle" onclick="toggleMobileUser()">
                    <span>👤 <?php echo esc_html($mobile_user->display_name); ?></span>
                    <span class="mobile-user-arrow">▼</span>
                </button>
                <div class="mobile-user-items" id="mobile-user-items">
                    <a href="<?php echo home_url('/linh-thach'); ?>">💎 Linh Thạch: <?php echo number_format($mobile_lt); ?></a>
                    <a href="<?php echo home_url('/profile'); ?>">Thông tin cá nhân</a>
                    <a href="<?php echo home_url('/theo-doi'); ?>">Truyện theo dõi</a>
                    <a href="<?php echo home_url('/lich-su'); ?>">Lịch sử đọc</a>
                    <?php if (current_user_can('edit_posts')): ?>
                        <a href="<?php echo admin_url(); ?>">Quản trị</a>
                    <?php endif; ?>
                    <a href="<?php echo wp_logout_url(home_url()); ?>">Đăng xuất</a>
                </div>
            </div>
            <div class="mobile-notif-section">
                <button class="mobile-notif-toggle" onclick="toggleMobileNotif()">
                    <span>🔔 Thông báo</span>
                    <span class="mobile-notif-badge" id="mobile-notif-badge" style="display:none;">0</span>
                </button>
                <div class="mobile-notif-items" id="mobile-notif-items">
                    <div class="notif-empty">Đang tải...</div>
                </div>
            </div>
        <?php else: ?>
            <div class="mobile-auth-section">
                <a href="<?php echo home_url('/dang-nhap'); ?>" class="btn btn-outline">Đăng nhập</a>
                <a href="<?php echo home_url('/dang-ky'); ?>" class="btn btn-primary">Đăng ký</a>
            </div>
        <?php endif; ?>
        <div class="mobile-nav-section">
            <?php wp_nav_menu(['theme_location' => 'primary', 'menu_class' => 'mobile-nav-list', 'container' => false, 'fallback_cb' => 'ta_menu_fallback']); ?>
        </div>
    </div>
</header>

<div id="toast-container"></div>

<script>
// Notification system
jQuery(function($) {
    var notifOpen = false;

    $('#notif-toggle').on('click', function(e) {
        e.stopPropagation();
        notifOpen = !notifOpen;
        $('#notif-menu').toggleClass('open', notifOpen);
        if (notifOpen) loadNotifications();
    });

    $(document).on('click', function(e) {
        if (notifOpen && !$(e.target).closest('#notif-wrapper').length) {
            notifOpen = false;
            $('#notif-menu').removeClass('open');
        }
    });

    function loadNotifications() {
        var $list = $('#notif-list');
        $.ajax({
            type: 'POST',
            url: ta_ajax.ajax_url || ajaxurl,
            data: { action: 'ta_get_notifications' },
            success: function(res) {
                if (!res.success) { $list.html('<div class="notif-empty">' + res.data + '</div>'); return; }
                $('#notif-badge').hide();
                if (!res.data.items.length) {
                    $list.html('<div class="notif-empty">Không có thông báo</div>');
                    return;
                }
                var html = '';
                $.each(res.data.items, function(i, n) {
                    var icon = n.type === 'success' ? '✅' : n.type === 'error' ? '❌' : n.type === 'warning' ? '⚠️' : 'ℹ️';
                    var link = n.link ? ' onclick="window.location.href=\'' + n.link + '\'" style="cursor:pointer;"' : '';
                    html += '<div class="notif-item' + (n.is_new ? ' notif-new' : '') + '"' + link + '>';
                    html += '<div class="notif-icon">' + icon + '</div>';
                    html += '<div class="notif-body">';
                    html += '<div class="notif-text">' + n.message + '</div>';
                    html += '<div class="notif-time">' + n.time + '</div>';
                    html += '</div></div>';
                });
                $list.html(html);
            },
            error: function() {
                $list.html('<div class="notif-empty">Lỗi tải thông báo</div>');
            }
        });
    }

    // Load unread count on page load
    $.ajax({
        type: 'POST',
        url: ta_ajax.ajax_url || ajaxurl,
        data: { action: 'ta_get_notifications' },
        success: function(res) {
            if (res.success && res.data.unread > 0) {
                $('#notif-badge').show().text(res.data.unread);
            }
        }
    });
});
</script>
<script>
function toggleMobileMenu() {
    document.getElementById('mobile-menu').classList.toggle('active');
    document.body.style.overflow = document.getElementById('mobile-menu').classList.contains('active') ? 'hidden' : '';
}
function toggleMobileUser() {
    var items = document.getElementById('mobile-user-items');
    var arrow = document.querySelector('.mobile-user-arrow');
    items.classList.toggle('active');
    arrow.classList.toggle('open');
}
function toggleMobileNotif() {
    var items = document.getElementById('mobile-notif-items');
    items.classList.toggle('active');
    if (items.classList.contains('active')) {
        jQuery.ajax({
            type: 'POST',
            url: ta_ajax.ajax_url || ajaxurl,
            data: { action: 'ta_get_notifications' },
            success: function(res) {
                if (!res.success) { items.innerHTML = '<div class="notif-empty">' + res.data + '</div>'; return; }
                document.getElementById('mobile-notif-badge').style.display = 'none';
                if (!res.data.items.length) {
                    items.innerHTML = '<div class="notif-empty">Không có thông báo</div>';
                    return;
                }
                var html = '';
                jQuery.each(res.data.items, function(i, n) {
                    var icon = n.type === 'success' ? '✅' : n.type === 'error' ? '❌' : n.type === 'warning' ? '⚠️' : 'ℹ️';
                    html += '<div class="notif-item' + (n.is_new ? ' notif-new' : '') + '">';
                    html += '<div class="notif-icon">' + icon + '</div>';
                    html += '<div class="notif-body"><div class="notif-text">' + n.message + '</div>';
                    html += '<div class="notif-time">' + n.time + '</div></div></div>';
                });
                items.innerHTML = html;
            },
            error: function() { items.innerHTML = '<div class="notif-empty">Lỗi tải thông báo</div>'; }
        });
    }
}
var mobileThemeToggle = document.getElementById('mobile-theme-toggle');
if (mobileThemeToggle) {
    mobileThemeToggle.addEventListener('click', function() {
        var html = document.documentElement;
        var current = html.getAttribute('data-theme') || 'light';
        var next = current === 'light' ? 'dark' : 'light';
        html.setAttribute('data-theme', next);
        localStorage.setItem('ta_theme', next);
        var icon = next === 'dark' ? '🌙' : '☀️';
        mobileThemeToggle.textContent = icon;
        var desktopToggle = document.getElementById('theme-toggle');
        if (desktopToggle) desktopToggle.textContent = icon;
        var syncLabel = document.getElementById('ta-theme-label');
        if (syncLabel) syncLabel.textContent = next === 'dark' ? '🌙 Tối' : '☀️ Sáng';
    });
}
</script>

<style>
.notif-dropdown { position:relative; }
.notif-btn { background:transparent;border:none;color:var(--text);font-size:20px;cursor:pointer;padding:6px;position:relative;line-height:1;border-radius:6px; }
.notif-btn:hover { background:var(--border); }
.notif-badge { position:absolute;top:-2px;right:-4px;background:#e74c3c;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;padding:0 4px; }
.notif-menu { display:none;position:absolute;top:100%;right:0;width:360px;max-height:420px;background:var(--bg-card);border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.3);z-index:1000;overflow:hidden; }
.notif-menu.open { display:flex;flex-direction:column; }
.notif-header { display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid var(--border);font-size:15px;font-weight:600;color:var(--text); }
.notif-close { background:none;border:none;color:#888;font-size:22px;cursor:pointer;line-height:1; }
.notif-list { overflow-y:auto;flex:1; }
.notif-empty { padding:30px;text-align:center;color:#888;font-size:14px; }
.notif-item { display:flex;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border);transition:background 0.2s; }
.notif-item:hover { background:var(--border); }
.notif-item.notif-new { background:rgba(240,192,64,0.08); }
.notif-icon { font-size:18px;flex-shrink:0;margin-top:2px; }
.notif-body { flex:1;min-width:0; }
.notif-text { font-size:14px;color:var(--text);line-height:1.4; }
.notif-time { font-size:11px;color:#888;margin-top:4px; }
</style>

<div id="theme-overlay-left" class="theme-overlay"></div>
<div id="theme-overlay-right" class="theme-overlay"></div>

<?php
// Flash messages
$flash_messages = ta_get_flash();
if (!empty($flash_messages)): ?>
<div class="flash-container">
    <?php foreach ($flash_messages as $msg): ?>
        <div class="flash flash-<?php echo $msg['type']; ?>">
            <span><?php echo $msg['message']; ?></span>
            <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
function ta_menu_fallback($args = []) {
    $class = !empty($args['menu_class']) ? $args['menu_class'] : 'nav-menu';
    echo '<ul class="' . esc_attr($class) . '">';
    echo '<li><a href="' . home_url() . '">Trang Chủ</a></li>';
    echo '<li><a href="' . home_url('/the-loai') . '">Thể Loại</a></li>';
    echo '<li><a href="' . home_url('/truyen') . '">Truyện</a></li>';
    echo '<li><a href="' . home_url('/bang-xep-hang') . '">Bảng Xếp Hạng</a></li>';
    echo '</ul>';
}
