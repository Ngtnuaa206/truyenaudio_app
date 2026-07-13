<?php
// Theme setup
add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    register_nav_menus(['primary' => 'Primary Menu']);
    set_post_thumbnail_size(300, 400, true);
});

// Enqueue assets
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('ta-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('ta-main', get_template_directory_uri() . '/assets/css/main.css', [], filemtime(get_template_directory() . '/assets/css/main.css'));
    wp_enqueue_script('ta-main', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], filemtime(get_template_directory() . '/assets/js/main.js'), true);
    wp_localize_script('ta-main', 'ta_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);
    wp_localize_script('ta-main', 'ta_config', ['is_admin' => current_user_can('edit_posts')]);
});

// Admin styles
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('ta-admin', get_template_directory_uri() . '/assets/css/admin.css', [], filemtime(get_template_directory() . '/assets/css/admin.css'));
    wp_add_inline_script('jquery', '
        jQuery(function($) {
            function syncIcons(t) {
                var label = $("#ta-theme-label");
                if (label.length) label.text(t === "dark" ? "🌙 Tối" : "☀️ Sáng");
                var ft = $("#theme-toggle");
                if (ft.length) ft.text(t === "dark" ? "🌙" : "☀️");
            }
            var theme = localStorage.getItem("ta_theme") || "light";
            $("html").attr("data-theme", theme);
            syncIcons(theme);
            $(document).on("click", "#wp-admin-bar-ta-theme-toggle .ab-item, #theme-toggle", function(e) {
                if ($(this).is("#wp-admin-bar-ta-theme-toggle .ab-item")) e.preventDefault();
                var current = $("html").attr("data-theme") || "light";
                var next = current === "light" ? "dark" : "light";
                var $l = $("#ta-theme-overlay-left"), $r = $("#ta-theme-overlay-right");
                var $fl = $("#theme-overlay-left"), $fr = $("#theme-overlay-right");
                if ($l.length && $r.length) {
                    $l.css("transform","translateX(0)"); $r.css("transform","translateX(0)");
                }
                if ($fl.length && $fr.length) {
                    $fl.css("transform","translateX(0)"); $fr.css("transform","translateX(0)");
                }
                setTimeout(function() {
                    $("html").attr("data-theme", next);
                    localStorage.setItem("ta_theme", next);
                    syncIcons(next);
                    if ($l.length && $r.length) {
                        $l.css("transform","translateX(-100%)"); $r.css("transform","translateX(100%)");
                    }
                    if ($fl.length && $fr.length) {
                        $fl.css("transform","translateX(-100%)"); $fr.css("transform","translateX(100%)");
                    }
                }, 300);
            });
        });
    ');
});

// Admin curtain overlay (both admin + frontend when logged in)
add_action('admin_footer', function () {
    echo '<div id="ta-theme-overlay-left" style="position:fixed;top:0;bottom:0;left:0;width:50%;z-index:99999;background:#0f0f1a;transition:transform .5s ease;pointer-events:none;transform:translateX(-100%);"></div>';
    echo '<div id="ta-theme-overlay-right" style="position:fixed;top:0;bottom:0;right:0;width:50%;z-index:99999;background:#0f0f1a;transition:transform .5s ease;pointer-events:none;transform:translateX(100%);"></div>';
});
add_action('wp_footer', function () {
    if (!is_user_logged_in()) return;
    echo '<div id="ta-theme-overlay-left" style="position:fixed;top:0;bottom:0;left:0;width:50%;z-index:99999;background:#0f0f1a;transition:transform .5s ease;pointer-events:none;transform:translateX(-100%);"></div>';
    echo '<div id="ta-theme-overlay-right" style="position:fixed;top:0;bottom:0;right:0;width:50%;z-index:99999;background:#0f0f1a;transition:transform .5s ease;pointer-events:none;transform:translateX(100%);"></div>';
});

// Admin bar theme toggle
add_action('admin_bar_menu', function ($wp_admin_bar) {
    if (current_user_can('edit_posts')) return;
    $wp_admin_bar->add_node([
        'id'    => 'ta-theme-toggle',
        'title' => '<span id="ta-theme-label">☀️ Sáng</span>',
        'href'  => '#',
    ]);
}, 999);

// Login page styles
add_action('login_enqueue_scripts', function () {
    wp_enqueue_style('ta-admin', get_template_directory_uri() . '/assets/css/admin.css', [], filemtime(get_template_directory() . '/assets/css/admin.css'));
});

// Custom login logo URL
add_filter('login_headerurl', function () { return home_url(); });
add_filter('login_headertext', function () { return 'TruyenAudio'; });

// Force dark admin color scheme for all users
add_filter('get_user_option_admin_color', function () { return 'fresh'; });

// Add editor styles for dark background
add_action('after_setup_theme', function () {
    add_editor_style(get_template_directory_uri() . '/assets/css/admin.css');
});

// Custom admin footer text
add_filter('admin_footer_text', function () { return 'TruyenAudio - Web truyện audio'; });
add_filter('update_footer', function () { return 'v1.0'; });

// Bootstrap pagination
function ta_pagination() {
    global $wp_query;
    $big = 999999999;
    echo paginate_links([
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages,
        'prev_text' => '«',
        'next_text' => '»',
    ]);
}

// Get story rating stars
function ta_get_stars($rating) {
    $html = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) $html .= '<span class="star filled">★</span>';
        elseif ($i - 0.5 <= $rating) $html .= '<span class="star half">★</span>';
        else $html .= '<span class="star">★</span>';
    }
    $html .= ' <small>' . number_format($rating, 1) . '</small></span>';
    return $html;
}

// Format views
function ta_views($count) {
    if ($count >= 1000000) return round($count / 1000000, 1) . 'M';
    if ($count >= 1000) return round($count / 1000, 1) . 'K';
    return $count;
}

// ============================================================
// THỂ LOẠI: Duyệt bởi Admin
// ============================================================

// Lấy danh sách thể loại chờ duyệt
function ta_get_pending_genres() {
    return get_option('_ta_pending_genres', []);
}

// Lưu danh sách thể loại chờ duyệt
function ta_save_pending_genres($genres) {
    update_option('_ta_pending_genres', $genres);
}

// Xử lý khi gửi thể loại mới
function ta_submit_new_genre($genre_name, $user_id) {
    $genre_name = sanitize_text_field(trim($genre_name));
    if (empty($genre_name)) return false;

    // Kiểm tra trùng tên thể loại đã tồn tại
    $existing = term_exists($genre_name, 'the_loai');
    if ($existing) return 'exists';

    // Kiểm tra trùng trong danh sách chờ duyệt
    $pending = ta_get_pending_genres();
    foreach ($pending as $p) {
        if (strtolower($p['name']) === strtolower($genre_name)) return 'pending';
    }

    $pending[] = [
        'name'    => $genre_name,
        'slug'    => sanitize_title($genre_name),
        'user_id' => $user_id,
        'time'    => current_time('mysql'),
        'status'  => 'pending',
    ];
    ta_save_pending_genres($pending);

    // Gửi email thông báo cho admin
    $admin_email = get_option('admin_email');
    $user_info = get_userdata($user_id);
    $user_name = $user_info ? $user_info->display_name : '未知';
    $site_name = get_bloginfo('name');
    $admin_url = admin_url('admin.php?page=ta-genre-approval');

    wp_mail($admin_email, "[{$site_name}] Thể loại mới cần duyệt", "
Tác giả: {$user_name}
Thể loại mới: {$genre_name}

Duyệt tại: {$admin_url}
    ");

    return true;
}

// Duyệt thể loại
function ta_approve_genre($index) {
    $pending = ta_get_pending_genres();
    if (!isset($pending[$index])) return false;

    $genre = $pending[$index];

    // Kiểm tra lại không trùng
    $existing = term_exists($genre['name'], 'the_loai');
    if ($existing) {
        unset($pending[$index]);
        ta_save_pending_genres(array_values($pending));
        return 'exists';
    }

    // Thêm vào taxonomy
    $result = wp_insert_term($genre['name'], 'the_loai', ['slug' => $genre['slug']]);
    if (is_wp_error($result)) return false;

    // Gửi email + notification cho tác giả
    $user = get_userdata($genre['user_id']);
    if ($user) {
        $genre_name = $genre['name'];
        $site_name = get_bloginfo('name');
        wp_mail($user->user_email, "[{$site_name}] Thể loại đã được duyệt!", "Chào {$user->display_name},\n\nThể loại \"{$genre_name}\" đã được admin duyệt.\n\nTrân trọng,\n{$site_name}");
        if (function_exists('ta_add_notification')) {
            ta_add_notification($genre['user_id'], 'success', '✅ Thể loại "' . $genre_name . '" đã được admin duyệt!', home_url('/the-loai'));
        }
    }

    // Xóa khỏi danh sách chờ
    unset($pending[$index]);
    ta_save_pending_genres(array_values($pending));

    return true;
}

// Từ chối thể loại
function ta_reject_genre($index, $reason = '') {
    $pending = ta_get_pending_genres();
    if (!isset($pending[$index])) return false;

    $genre = $pending[$index];

    // Gửi email + notification cho tác giả
    $user = get_userdata($genre['user_id']);
    if ($user) {
        $genre_name = $genre['name'];
        $site_name = get_bloginfo('name');
        $reason_text = $reason ? "\nLý do: {$reason}" : '';
        wp_mail($user->user_email, "[{$site_name}] Thể loại bị từ chối", "Chào {$user->display_name},\n\nThể loại \"{$genre_name}\" đã bị admin từ chối.{$reason_text}\n\nBạn có thể thử lại với tên thể loại khác.\n\nTrân trọng,\n{$site_name}");
        if (function_exists('ta_add_notification')) {
            ta_add_notification($genre['user_id'], 'error', '❌ Thể loại "' . $genre_name . '" đã bị từ chối.' . ($reason ? ' Lý do: ' . $reason : ''), home_url('/profile'));
        }
    }

    // Xóa khỏi danh sách chờ
    unset($pending[$index]);
    ta_save_pending_genres(array_values($pending));

    return true;
}

// ============================================================
// Admin Page: Duyệt thể loại
// ============================================================
add_action('admin_menu', function () {
    add_menu_page(
        'Duyệt Thể Loại',
        'Duyệt Thể Loại',
        'manage_options',
        'ta-genre-approval',
        'ta_render_genre_approval_page',
        'dashicons-yes-alt',
        30
    );
});

function ta_render_genre_approval_page() {
    if (isset($_GET['action']) && $_GET['action'] === 'approve' && isset($_GET['index']) && check_admin_referer('ta_genre_action')) {
        $index = intval($_GET['index']);
        $result = ta_approve_genre($index);
        if ($result === true) {
            echo '<div class="notice notice-success"><p>Đã duyệt thể loại thành công!</p></div>';
        } elseif ($result === 'exists') {
            echo '<div class="notice notice-warning"><p>Thể loại đã tồn tại, đã bỏ qua.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Lỗi khi duyệt.</p></div>';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ta_reject_genre']) && check_admin_referer('ta_genre_action_reject')) {
        $index = intval($_POST['index']);
        $reason = sanitize_text_field($_POST['reject_reason'] ?? '');
        $result = ta_reject_genre($index, $reason);
        if ($result) {
            echo '<div class="notice notice-success"><p>Đã từ chối thể loại.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Lỗi khi từ chối.</p></div>';
        }
    }

    $pending = ta_get_pending_genres();
    ?>
    <div class="wrap">
        <h1>Duyệt Thể Loại Mới (<?php echo count($pending); ?> chờ duyệt)</h1>

        <?php if (empty($pending)): ?>
            <p>Không có thể loại nào chờ duyệt.</p>
        <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Tên thể loại</th>
                    <th>Slug</th>
                    <th>Tác giả</th>
                    <th>Thời gian gửi</th>
                    <th style="width:250px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $i => $g):
                    $author = get_userdata($g['user_id']);
                ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo esc_html($g['name']); ?></strong></td>
                    <td><?php echo esc_html($g['slug']); ?></td>
                    <td><?php echo $author ? esc_html($author->display_name) : 'ID: ' . $g['user_id']; ?></td>
                    <td><?php echo esc_html($g['time']); ?></td>
                    <td>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=ta-genre-approval&action=approve&index=' . $i), 'ta_genre_action'); ?>"
                           class="button button-primary" style="margin-right:5px;"
                           onclick="return confirm('Duyệt thể loại này?')">✅ Duyệt</a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Từ chối thể loại này?')">
                            <?php wp_nonce_field('ta_genre_action'); ?>
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="index" value="<?php echo $i; ?>">
                            <input type="text" name="reject_reason" placeholder="Lý do từ chối (tùy chọn)" style="width:120px;">
                            <button type="submit" class="button">❌ Từ chối</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php
}
