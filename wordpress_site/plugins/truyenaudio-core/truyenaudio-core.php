<?php
/**
 * Plugin Name: TruyenAudio Core
 * Description: Custom post types and functionality for web truyện
 * Version: 2.0
 */

// Register Story CPT
add_action('init', 'ta_register_post_types');
function ta_register_post_types() {
    register_post_type('truyen', [
        'labels' => [
            'name' => 'Truyện',
            'singular_name' => 'Truyện',
            'add_new' => 'Thêm truyện',
            'add_new_item' => 'Thêm truyện mới',
            'edit_item' => 'Sửa truyện',
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-book',
        'menu_position' => 5,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author'],
        'rewrite' => ['slug' => 'truyen'],
        'show_in_rest' => true,
        'show_in_menu' => true,
        'capability_type' => 'post',
        'map_meta_cap' => true,
    ]);

    register_post_type('chapter', [
        'labels' => [
            'name' => 'Chương',
            'singular_name' => 'Chương',
            'add_new' => 'Thêm chương',
            'add_new_item' => 'Thêm chương mới',
        ],
        'public' => false,
        'show_ui' => false,
        'has_archive' => false,
        'supports' => ['title', 'editor'],
        'rewrite' => ['slug' => 'chuong'],
        'show_in_rest' => true,
        'show_in_menu' => false,
    ]);

    // Register chapter meta for REST API
    register_post_meta('chapter', '_story_id', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('chapter', '_chapter_number', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('chapter', '_audio_url', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('chapter', '_is_vip', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('chapter', '_vip_price', ['show_in_rest' => true, 'type' => 'string']);

    // Register story meta for REST API
    register_post_meta('truyen', '_views', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('truyen', '_rating', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('truyen', '_rating_count', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('truyen', '_chapter_count', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('truyen', '_status', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('truyen', '_dao_linh_thach', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('truyen', '_free_chapters', ['show_in_rest' => true, 'type' => 'string']);
    register_post_meta('truyen', '_dao_price', ['show_in_rest' => true, 'type' => 'string']);

    register_taxonomy('the_loai', 'truyen', [
        'labels' => [
            'name' => 'Thể loại',
            'singular_name' => 'Thể loại',
            'add_new_item' => 'Thêm thể loại mới',
        ],
        'hierarchical' => true,
        'rewrite' => ['slug' => 'the-loai'],
        'show_in_rest' => true,
    ]);

    register_taxonomy('trang_thai', 'truyen', [
        'labels' => [
            'name' => 'Trạng thái',
            'singular_name' => 'Trạng thái',
        ],
        'hierarchical' => true,
        'rewrite' => ['slug' => 'trang-thai'],
        'show_in_rest' => true,
    ]);

    register_post_type('report', [
        'labels' => [
            'name' => 'Báo cáo',
            'singular_name' => 'Báo cáo',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=truyen',
        'supports' => ['title', 'editor'],
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'rewrite' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'has_archive' => false,
    ]);
}

// ==================== REPORT SYSTEM ====================
add_action('add_meta_boxes', 'ta_add_report_meta');
function ta_add_report_meta() {
    add_meta_box('report_details', 'Chi tiết báo cáo', 'ta_report_meta_html', 'report', 'normal');
}

function ta_report_meta_html($post) {
    $story_id = get_post_meta($post->ID, '_reported_story_id', true);
    $reporter_id = get_post_meta($post->ID, '_reporter_id', true);
    $reason = get_post_meta($post->ID, '_report_reason', true);
    $status = get_post_meta($post->ID, '_report_status', true) ?: 'pending';
    $story = $story_id ? get_post($story_id) : null;
    $reporter = $reporter_id ? get_userdata($reporter_id) : null;
    $reasons = [
        'spam' => 'Spam / Quảng cáo',
        'inappropriate' => 'Nội dung không phù hợp',
        'copyright' => 'Vi phạm bản quyền',
        'wrong_category' => 'Sai thể loại / mô tả',
        'other' => 'Khác',
    ];
    ?>
    <p><strong>Truyện bị báo cáo:</strong>
        <?php if ($story): ?>
            <a href="<?php echo get_permalink($story_id); ?>" target="_blank"><?php echo esc_html($story->post_title); ?></a>
            (ID: <?php echo $story_id; ?>)
        <?php else: ?>
            N/A (ID: <?php echo $story_id; ?>)
        <?php endif; ?>
    </p>
    <p><strong>Người báo cáo:</strong> <?php echo $reporter ? esc_html($reporter->display_name) . ' (' . esc_html($reporter->user_login) . ')' : 'N/A'; ?></p>
    <p><strong>Lý do:</strong> <?php echo isset($reasons[$reason]) ? $reasons[$reason] : esc_html($reason); ?></p>
    <p><strong>Trạng thái:</strong>
        <select name="report_status">
            <option value="pending" <?php selected($status, 'pending'); ?>>⏳ Chờ xử lý</option>
            <option value="resolved" <?php selected($status, 'resolved'); ?>>✅ Đã xử lý</option>
            <option value="dismissed" <?php selected($status, 'dismissed'); ?>>❌ Bỏ qua</option>
        </select>
    </p>
    <?php if ($status === 'pending'): ?>
    <p style="color:#f39c12;">🔔 Báo cáo này đang chờ xem xét.</p>
    <?php endif; ?>
    <?php
}

add_action('save_post', 'ta_save_report_meta');
function ta_save_report_meta($post_id) {
    if (get_post_type($post_id) !== 'report') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['report_status'])) {
        update_post_meta($post_id, '_report_status', sanitize_text_field($_POST['report_status']));
    }
}

add_filter('manage_report_posts_columns', 'ta_report_columns');
function ta_report_columns($columns) {
    $new = [];
    $new['cb'] = $columns['cb'];
    $new['title'] = 'Báo cáo';
    $new['report_story'] = 'Truyện';
    $new['report_reporter'] = 'Người báo cáo';
    $new['report_reason'] = 'Lý do';
    $new['report_status'] = 'Trạng thái';
    $new['date'] = $columns['date'];
    return $new;
}

add_action('manage_report_posts_custom_column', 'ta_report_columns_content', 10, 2);
function ta_report_columns_content($column, $post_id) {
    if ($column === 'report_story') {
        $story_id = get_post_meta($post_id, '_reported_story_id', true);
        $story = $story_id ? get_post($story_id) : null;
        if ($story) {
            echo '<a href="' . get_permalink($story_id) . '" target="_blank">' . esc_html($story->post_title) . '</a>';
            echo '<br><small style="color:#888;">ID: ' . $story_id . '</small>';
        } else {
            echo 'N/A';
        }
    }
    if ($column === 'report_reporter') {
        $reporter_id = get_post_meta($post_id, '_reporter_id', true);
        $reporter = $reporter_id ? get_userdata($reporter_id) : null;
        echo $reporter ? esc_html($reporter->display_name) : 'N/A';
    }
    if ($column === 'report_reason') {
        $reason = get_post_meta($post_id, '_report_reason', true);
        $reasons = [
            'spam' => 'Spam / Quảng cáo',
            'inappropriate' => 'Nội dung không phù hợp',
            'copyright' => 'Vi phạm bản quyền',
            'wrong_category' => 'Sai thể loại / mô tả',
            'other' => 'Khác',
        ];
        echo isset($reasons[$reason]) ? $reasons[$reason] : esc_html($reason);
    }
    if ($column === 'report_status') {
        $status = get_post_meta($post_id, '_report_status', true) ?: 'pending';
        $labels = [
            'pending' => '<span style="color:#f39c12;">⏳ Chờ</span>',
            'resolved' => '<span style="color:#2ecc71;">✅ Xong</span>',
            'dismissed' => '<span style="color:#e74c3c;">❌ Bỏ qua</span>',
        ];
        echo $labels[$status] ?? $status;
    }
}

add_filter('manage_edit-report_sortable_columns', 'ta_report_sortable_columns');
function ta_report_sortable_columns($columns) {
    $columns['report_status'] = 'report_status';
    return $columns;
}

// AJAX: Submit report
add_action('wp_ajax_ta_report_story', 'ta_ajax_report_story');
add_action('wp_ajax_nopriv_ta_report_story', 'ta_ajax_report_story');
function ta_ajax_report_story() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Vui lòng đăng nhập để báo cáo.');
    }

    $story_id = intval($_POST['story_id'] ?? 0);
    $reason = sanitize_text_field($_POST['reason'] ?? '');
    $details = sanitize_textarea_field($_POST['details'] ?? '');

    if (!$story_id || !get_post($story_id)) {
        wp_send_json_error('Không tìm thấy truyện.');
    }
    if (!in_array($reason, ['spam', 'inappropriate', 'copyright', 'wrong_category', 'other'])) {
        wp_send_json_error('Lý do không hợp lệ.');
    }

    $user_id = get_current_user_id();

    // Check if user already reported this story
    $existing = get_posts([
        'post_type' => 'report',
        'meta_query' => [
            ['key' => '_reported_story_id', 'value' => $story_id],
            ['key' => '_reporter_id', 'value' => $user_id],
            ['key' => '_report_status', 'value' => 'pending'],
        ],
        'numberposts' => 1,
    ]);
    if ($existing) {
        wp_send_json_error('Bạn đã báo cáo truyện này rồi. Admin sẽ xem xét sớm!');
    }

    $report_id = wp_insert_post([
        'post_title' => 'Báo cáo: ' . get_the_title($story_id) . ' (#' . $story_id . ')',
        'post_content' => $details,
        'post_type' => 'report',
        'post_status' => 'publish',
        'post_author' => $user_id,
    ]);

    if (is_wp_error($report_id)) {
        wp_send_json_error('Lỗi khi gửi báo cáo.');
    }

    update_post_meta($report_id, '_reported_story_id', $story_id);
    update_post_meta($report_id, '_reporter_id', $user_id);
    update_post_meta($report_id, '_report_reason', $reason);
    update_post_meta($report_id, '_report_status', 'pending');

    wp_send_json_success(['message' => '✅ Báo cáo đã được gửi! Admin sẽ xem xét sớm nhất.']);
}

// ==================== CHAPTER META ====================
add_action('add_meta_boxes', 'ta_add_chapter_meta');
function ta_add_chapter_meta() {
    add_meta_box('chapter_details', 'Chi tiết chương', 'ta_chapter_meta_html', 'chapter', 'side');
}

function ta_chapter_meta_html($post) {
    $story_id = get_post_meta($post->ID, '_story_id', true);
    $chapter_num = get_post_meta($post->ID, '_chapter_number', true);
    $audio_url = get_post_meta($post->ID, '_audio_url', true);
    $is_vip = get_post_meta($post->ID, '_is_vip', true);
    $vip_price = get_post_meta($post->ID, '_vip_price', true) ?: 5;
    ?>
    <p>
        <label>Truyện:</label>
        <select name="story_id" style="width:100%">
            <option value="">-- Chọn truyện --</option>
            <?php
            $stories = get_posts(['post_type' => 'truyen', 'numberposts' => -1]);
            foreach ($stories as $s) {
                echo '<option value="' . $s->ID . '" ' . selected($story_id, $s->ID, false) . '>' . esc_html($s->post_title) . '</option>';
            }
            ?>
        </select>
    </p>
    <p>
        <label>Số chương:</label>
        <input type="number" name="chapter_number" value="<?php echo $chapter_num; ?>" style="width:100%">
    </p>
    <p>
        <label>Audio URL (MP3):</label>
        <input type="url" name="audio_url" value="<?php echo esc_url($audio_url); ?>" style="width:100%" placeholder="https://...mp3">
    </p>
    <p>
        <label>
            <input type="checkbox" name="is_vip" value="1" <?php checked($is_vip, '1'); ?>>
            Chương VIP
        </label>
    </p>
    <p>
        <label>Giá VIP (Linh Thạch):</label>
        <input type="number" name="vip_price" value="<?php echo $vip_price; ?>" style="width:100%" min="1">
    </p>
    <?php
}

add_action('save_post', 'ta_save_chapter_meta');
function ta_save_chapter_meta($post_id) {
    if (get_post_type($post_id) !== 'chapter') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['story_id'])) update_post_meta($post_id, '_story_id', intval($_POST['story_id']));
    if (isset($_POST['chapter_number'])) update_post_meta($post_id, '_chapter_number', intval($_POST['chapter_number']));
    if (isset($_POST['audio_url'])) update_post_meta($post_id, '_audio_url', esc_url_raw($_POST['audio_url']));
    if (isset($_POST['is_vip'])) update_post_meta($post_id, '_is_vip', '1'); else update_post_meta($post_id, '_is_vip', '0');
    if (isset($_POST['vip_price'])) update_post_meta($post_id, '_vip_price', intval($_POST['vip_price']));
}

// ==================== STORY META ====================
add_action('add_meta_boxes', 'ta_add_story_meta');
function ta_add_story_meta() {
    add_meta_box('story_details', 'Chi tiết truyện', 'ta_story_meta_html', 'truyen', 'side');
    add_meta_box('story_dao_box', 'Cài đặt Đào Linh Thạch', 'ta_story_dao_meta_html', 'truyen', 'side');
}

function ta_story_meta_html($post) {
    $views = get_post_meta($post->ID, '_views', true) ?: 0;
    $rating = get_post_meta($post->ID, '_rating', true) ?: 0;
    $rating_count = get_post_meta($post->ID, '_rating_count', true) ?: 0;
    $revenue = get_post_meta($post->ID, '_story_revenue', true) ?: 0;
    ?>
    <p>
        <label>Lượt xem:</label>
        <input type="number" name="story_views" value="<?php echo $views; ?>" style="width:100%" min="0">
    </p>
    <p>
        <label>Đánh giá (/5):</label>
        <input type="number" name="story_rating" value="<?php echo $rating; ?>" style="width:80px" min="0" max="5" step="0.1">
        <input type="number" name="story_rating_count" value="<?php echo $rating_count; ?>" style="width:70px" min="0" placeholder="Lượt">
    </p>
    <p>
        <label>Doanh thu (💎):</label>
        <input type="number" name="story_revenue" value="<?php echo $revenue; ?>" style="width:100%" min="0">
    </p>
    <?php
}

function ta_story_dao_meta_html($post) {
    $dao = get_post_meta($post->ID, '_dao_linh_thach', true);
    $free = get_post_meta($post->ID, '_free_chapters', true) ?: 2;
    $price = get_post_meta($post->ID, '_dao_price', true) ?: 3;
    ?>
    <p>
        <label>
            <input type="checkbox" name="dao_linh_thach" value="1" <?php checked($dao, '1'); ?>>
            🔥 Bật chế độ Đào Linh Thạch
        </label>
        <br><small style="color:#888;">Người dùng phải trả LT để đọc chương sau chương miễn phí</small>
    </p>
    <p>
        <label>Số chương miễn phí đầu:</label>
        <input type="number" name="free_chapters" value="<?php echo $free; ?>" style="width:100%" min="1" max="100">
    </p>
    <p>
        <label>Giá mỗi chương (💎):</label>
        <input type="number" name="dao_price" value="<?php echo $price; ?>" style="width:100%" min="1" max="100">
    </p>
    <?php
}

add_action('save_post', 'ta_save_story_meta');
function ta_save_story_meta($post_id) {
    if (get_post_type($post_id) !== 'truyen') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['story_views'])) update_post_meta($post_id, '_views', intval($_POST['story_views']));
    if (isset($_POST['story_rating'])) update_post_meta($post_id, '_rating', floatval($_POST['story_rating']));
    if (isset($_POST['story_rating_count'])) update_post_meta($post_id, '_rating_count', intval($_POST['story_rating_count']));
    if (isset($_POST['story_revenue'])) update_post_meta($post_id, '_story_revenue', intval($_POST['story_revenue']));

    if (isset($_POST['dao_linh_thach'])) update_post_meta($post_id, '_dao_linh_thach', '1'); else update_post_meta($post_id, '_dao_linh_thach', '0');
    if (isset($_POST['free_chapters'])) update_post_meta($post_id, '_free_chapters', intval($_POST['free_chapters']));
    if (isset($_POST['dao_price'])) update_post_meta($post_id, '_dao_price', intval($_POST['dao_price']));
}

// ==================== CHAPTER MANAGEMENT (in Story Edit) ====================
add_action('add_meta_boxes', 'ta_add_story_chapters_meta');
function ta_add_story_chapters_meta() {
    add_meta_box('story_chapters', 'Quản lý Chương', 'ta_story_chapters_meta_html', 'truyen', 'normal', 'high');
}

function ta_story_chapters_meta_html($post) {
    $story_id = $post->ID;
    $chapters = ta_get_chapters($story_id);
    $default_vip = get_option('ta_default_vip_price', 5);
    wp_nonce_field('ta_story_chapters_meta', 'ta_story_chapters_nonce');
    ?>
    <style>
        #ta-chapters-list { margin: 0; padding: 0; }
        #ta-chapters-list .chapter-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; margin-bottom: 6px;
            background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px;
            transition: background 0.15s;
        }
        #ta-chapters-list .chapter-row:hover { background: #f0f4ff; }
        .ch-num { font-weight: 700; min-width: 30px; color: #3568D4; }
        .ch-title { flex: 1; font-weight: 500; }
        .ch-badge { font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
        .ch-badge.vip { background: #ff6b35; color: #fff; }
        .ch-badge.free { background: #2ecc71; color: #fff; }
        .ch-price { color: #888; font-size: 12px; }
        .ch-actions { display: flex; gap: 6px; }
        .ch-actions button { padding: 4px 10px; font-size: 12px; cursor: pointer; border-radius: 4px; border: 1px solid #ccc; background: #fff; }
        .ch-actions .btn-edit { color: #3568D4; border-color: #3568D4; }
        .ch-actions .btn-delete { color: #e74c3c; border-color: #e74c3c; }
        #ta-add-chapter-form { display: none; margin-top: 16px; padding: 16px; background: #f0f4ff; border: 1px solid #3568D4; border-radius: 8px; }
        #ta-add-chapter-form label { font-weight: 600; margin-bottom: 4px; display: block; font-size: 13px; }
        #ta-add-chapter-form input[type="text"],
        #ta-add-chapter-form input[type="url"],
        #ta-add-chapter-form input[type="number"] { width: 100%; padding: 6px 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .ta-ch-row { display: flex; gap: 12px; margin-bottom: 8px; }
        .ta-ch-row > div { flex: 1; }
        .ta-ch-toggle { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
        .ta-ch-toggle input[type="checkbox"] { width: 18px; height: 18px; }
        #ta-chapter-content-wrap { margin-bottom: 10px; }
    </style>
    <p style="margin-bottom:12px;">
        <button type="button" class="button button-primary" id="ta-toggle-add-chapter">+ Thêm chương mới</button>
        <span style="color:#888;font-size:13px;margin-left:10px;">(<?php echo count($chapters); ?> chương)</span>
    </p>
    <div id="ta-chapters-list">
        <?php if (empty($chapters)): ?>
            <p style="color:#888;text-align:center;padding:20px;">Chưa có chương nào. Nhấn "+ Thêm chương mới" để bắt đầu.</p>
        <?php else: ?>
            <?php foreach ($chapters as $ch):
                $num = get_post_meta($ch->ID, '_chapter_number', true);
                $is_vip = get_post_meta($ch->ID, '_is_vip', true);
                $price = get_post_meta($ch->ID, '_vip_price', true);
                ?>
                <div class="chapter-row" data-id="<?php echo $ch->ID; ?>">
                    <span class="ch-num">#<?php echo $num; ?></span>
                    <span class="ch-title"><?php echo esc_html($ch->post_title); ?></span>
                    <?php if ($is_vip): ?>
                        <span class="ch-badge vip">VIP</span>
                        <span class="ch-price"><?php echo $price; ?>💎</span>
                    <?php else: ?>
                        <span class="ch-badge free">FREE</span>
                    <?php endif; ?>
                    <span class="ch-actions">
                        <button type="button" class="btn-edit" onclick="taEditChapter(<?php echo $ch->ID; ?>)">Sửa</button>
                        <button type="button" class="btn-delete" onclick="taDeleteChapter(<?php echo $ch->ID; ?>)">Xóa</button>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="ta-add-chapter-form">
        <h3 id="ta-form-title">Thêm chương mới</h3>
        <input type="hidden" id="ta-edit-chapter-id" value="">
        <div class="ta-ch-row">
            <div>
                <label>Số chương</label>
                <input type="number" id="ta-ch-number" min="1" value="<?php echo count($chapters) + 1; ?>">
            </div>
            <div>
                <label>Audio URL (MP3)</label>
                <input type="url" id="ta-ch-audio" placeholder="https://example.com/audio.mp3">
            </div>
        </div>
        <div>
            <label>Tiêu đề chương</label>
            <input type="text" id="ta-ch-title" placeholder="Ví dụ: Chương 1 - Khởi đầu">
        </div>
        <div>
            <label>Nội dung chương</label>
            <div id="ta-chapter-content-wrap">
                <?php
                wp_editor('', 'ta_chapter_content', [
                    'textarea_name' => '',
                    'textarea_rows' => 12,
                    'media_buttons' => true,
                    'teeny' => false,
                    'quicktags' => true,
                ]);
                ?>
            </div>
        </div>
        <div class="ta-ch-toggle">
            <input type="checkbox" id="ta-ch-vip">
            <label for="ta-ch-vip" style="margin-bottom:0;">Chương VIP (yêu cầu Linh Thạch để mở khóa)</label>
        </div>
        <div class="ta-ch-row" id="ta-ch-price-row">
            <div>
                <label>Giá VIP (Linh Thạch)</label>
                <input type="number" id="ta-ch-price" min="1" value="<?php echo $default_vip; ?>">
            </div>
        </div>
        <p>
            <button type="button" class="button button-primary" id="ta-save-chapter" onclick="taSaveChapter()">Lưu chương</button>
            <button type="button" class="button" id="ta-cancel-edit" onclick="taCancelEdit()">Hủy</button>
        </p>
    </div>

    <script>
    var taStoryId = <?php echo $story_id; ?>;
    var taAjaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    var taNonce = '<?php echo wp_create_nonce('ta_story_chapters_meta'); ?>';

    jQuery(function($) {
        $('#ta-toggle-add-chapter').on('click', function() {
            $('#ta-add-chapter-form').slideToggle(200);
            $('#ta-form-title').text('Thêm chương mới');
            $('#ta-edit-chapter-id').val('');
        });
        $('#ta-ch-vip').on('change', function() {
            $('#ta-ch-price-row').toggle(this.checked);
        });
        $('#ta-ch-price-row').hide();
    });

    function taSaveChapter() {
        var editId = jQuery('#ta-edit-chapter-id').val();
        var data = {
            action: 'ta_save_chapter_admin',
            nonce: taNonce,
            story_id: taStoryId,
            chapter_id: editId,
            chapter_number: jQuery('#ta-ch-number').val(),
            title: jQuery('#ta-ch-title').val(),
            content: jQuery('#ta_chapter_content').val(),
            audio_url: jQuery('#ta-ch-audio').val(),
            is_vip: jQuery('#ta-ch-vip').is(':checked') ? '1' : '0',
            vip_price: jQuery('#ta-ch-price').val()
        };
        if (!data.title) { alert('Vui lòng nhập tiêu đề chương'); return; }
        jQuery.post(taAjaxUrl, data, function(res) {
            if (res.success) { location.reload(); }
            else { alert(res.data); }
        });
    }

    function taEditChapter(id) {
        jQuery.post(taAjaxUrl, {action: 'ta_get_chapter_admin', nonce: taNonce, chapter_id: id}, function(res) {
            if (!res.success) { alert(res.data); return; }
            var d = res.data;
            jQuery('#ta-edit-chapter-id').val(d.id);
            jQuery('#ta-ch-number').val(d.chapter_number);
            jQuery('#ta-ch-title').val(d.title);
            jQuery('#ta-ch-audio').val(d.audio_url);
            jQuery('#ta-ch-vip').prop('checked', d.is_vip === '1');
            jQuery('#ta-ch-price').val(d.vip_price || 5);
            jQuery('#ta-ch-price-row').toggle(d.is_vip === '1');
            if (typeof tinymce !== 'undefined' && tinymce.get('ta_chapter_content')) {
                tinymce.get('ta_chapter_content').setContent(d.content);
            } else if (typeof QTags !== 'undefined') {
                jQuery('#ta_chapter_content').val(d.content);
            }
            jQuery('#ta-form-title').text('Sửa chương #' + d.chapter_number);
            jQuery('#ta-add-chapter-form').slideDown(200);
            jQuery('html, body').animate({scrollTop: jQuery('#ta-add-chapter-form').offset().top - 50}, 300);
        });
    }

    function taDeleteChapter(id) {
        if (!confirm('Bạn có chắc muốn xóa chương này?')) return;
        jQuery.post(taAjaxUrl, {action: 'ta_delete_chapter_admin', nonce: taNonce, chapter_id: id}, function(res) {
            if (res.success) { location.reload(); }
            else { alert(res.data); }
        });
    }

    function taCancelEdit() {
        jQuery('#ta-add-chapter-form').slideUp(200);
        jQuery('#ta-edit-chapter-id').val('');
        jQuery('#ta-ch-number').val(jQuery('.chapter-row').length + 1);
        jQuery('#ta-ch-title').val('');
        jQuery('#ta-ch-audio').val('');
        jQuery('#ta-ch-vip').prop('checked', false);
        jQuery('#ta-ch-price-row').hide();
        if (typeof tinymce !== 'undefined' && tinymce.get('ta_chapter_content')) {
            tinymce.get('ta_chapter_content').setContent('');
        } else {
            jQuery('#ta_chapter_content').val('');
        }
    }
    </script>
    <?php
}

// AJAX: Save chapter from admin story edit
add_action('wp_ajax_ta_save_chapter_admin', 'ta_ajax_save_chapter_admin');
function ta_ajax_save_chapter_admin() {
    if (!current_user_can('administrator')) wp_send_json_error('Không có quyền');
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ta_story_chapters_meta')) wp_send_json_error('Nonce không hợp lệ');

    $story_id = intval($_POST['story_id']);
    $chapter_id = intval($_POST['chapter_id'] ?? 0);
    $chapter_number = intval($_POST['chapter_number']);
    $title = sanitize_text_field($_POST['title']);
    $content = wp_kses_post($_POST['content']);
    $audio_url = esc_url_raw($_POST['audio_url'] ?? '');
    $is_vip = isset($_POST['is_vip']) && $_POST['is_vip'] === '1' ? '1' : '0';
    $vip_price = intval($_POST['vip_price'] ?? get_option('ta_default_vip_price', 5));

    if (empty($title)) wp_send_json_error('Thiếu tiêu đề');

    if ($chapter_id) {
        wp_update_post([
            'ID' => $chapter_id,
            'post_title' => $title,
            'post_content' => $content,
        ]);
        update_post_meta($chapter_id, '_chapter_number', $chapter_number);
        update_post_meta($chapter_id, '_audio_url', $audio_url);
        update_post_meta($chapter_id, '_is_vip', $is_vip);
        update_post_meta($chapter_id, '_vip_price', $vip_price);
    } else {
        $chapter_id = wp_insert_post([
            'post_title' => $title,
            'post_content' => $content,
            'post_type' => 'chapter',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ]);
        if (is_wp_error($chapter_id)) wp_send_json_error('Lỗi tạo chương');
        update_post_meta($chapter_id, '_story_id', $story_id);
        update_post_meta($chapter_id, '_chapter_number', $chapter_number);
        update_post_meta($chapter_id, '_audio_url', $audio_url);
        update_post_meta($chapter_id, '_is_vip', $is_vip);
        update_post_meta($chapter_id, '_vip_price', $vip_price);
    }

    update_post_meta($story_id, '_chapter_count', count(ta_get_chapters($story_id)));
    wp_send_json_success(['message' => $chapter_id ? 'Đã cập nhật chương' : 'Đã thêm chương mới']);
}

// AJAX: Get chapter data for editing
add_action('wp_ajax_ta_get_chapter_admin', 'ta_ajax_get_chapter_admin');
function ta_ajax_get_chapter_admin() {
    if (!current_user_can('administrator')) wp_send_json_error('Không có quyền');
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ta_story_chapters_meta')) wp_send_json_error('Nonce không hợp lệ');

    $id = intval($_POST['chapter_id']);
    $post = get_post($id);
    if (!$post || $post->post_type !== 'chapter') wp_send_json_error('Không tìm thấy chương');

    wp_send_json_success([
        'id' => $id,
        'title' => $post->post_title,
        'content' => $post->post_content,
        'chapter_number' => get_post_meta($id, '_chapter_number', true),
        'audio_url' => get_post_meta($id, '_audio_url', true),
        'is_vip' => get_post_meta($id, '_is_vip', true),
        'vip_price' => get_post_meta($id, '_vip_price', true),
    ]);
}

// AJAX: Delete chapter from admin
add_action('wp_ajax_ta_delete_chapter_admin', 'ta_ajax_delete_chapter_admin');
function ta_ajax_delete_chapter_admin() {
    if (!current_user_can('administrator')) wp_send_json_error('Không có quyền');
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ta_story_chapters_meta')) wp_send_json_error('Nonce không hợp lệ');

    $id = intval($_POST['chapter_id']);
    $story_id = get_post_meta($id, '_story_id', true);
    wp_delete_post($id, true);
    if ($story_id) update_post_meta($story_id, '_chapter_count', count(ta_get_chapters($story_id)));
    wp_send_json_success(['message' => 'Đã xóa chương']);
}

function ta_can_read_chapter($chapter_id, $story_id = null) {
    if (!$story_id) $story_id = get_post_meta($chapter_id, '_story_id', true);
    $is_vip = get_post_meta($chapter_id, '_is_vip', true);
    if (current_user_can('administrator')) return true;
    if (!is_user_logged_in()) return false;
    if ($is_vip !== '1') return true;
    return ta_has_purchased($chapter_id);
}

// Track views
add_action('wp', 'ta_track_views');
function ta_track_views() {
    if (is_singular('truyen')) {
        $post_id = get_the_ID();
        $views = get_post_meta($post_id, '_views', true) ?: 0;
        update_post_meta($post_id, '_views', $views + 1);
    }
}

// Get chapters of a story
function ta_get_chapters($story_id) {
    return get_posts([
        'post_type' => 'chapter',
        'meta_query' => [
            ['key' => '_story_id', 'value' => $story_id, 'compare' => '='],
        ],
        'meta_key' => '_chapter_number',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'numberposts' => -1,
    ]);
}

// AJAX rating
add_action('wp_ajax_rate_story', 'ta_rate_story');
function ta_rate_story() {
    $post_id = intval($_POST['post_id']);
    $rating = intval($_POST['rating']);
    if ($rating < 1 || $rating > 5) wp_die('Invalid');

    $old_rating = get_post_meta($post_id, '_rating', true) ?: 0;
    $count = get_post_meta($post_id, '_rating_count', true) ?: 0;
    $new_rating = (($old_rating * $count) + $rating) / ($count + 1);
    update_post_meta($post_id, '_rating', round($new_rating, 1));
    update_post_meta($post_id, '_rating_count', $count + 1);

    $user_id = get_current_user_id();
    $ratings = get_post_meta($post_id, '_user_ratings', true) ?: [];
    $ratings[$user_id] = $rating;
    update_post_meta($post_id, '_user_ratings', $ratings);

    wp_send_json(['rating' => round($new_rating, 1), 'count' => $count + 1]);
}

// Bookmarks
add_action('wp_ajax_toggle_bookmark', 'ta_toggle_bookmark');
function ta_toggle_bookmark() {
    $user_id = get_current_user_id();
    $post_id = intval($_POST['post_id']);
    $bookmarks = get_user_meta($user_id, '_bookmarks', true) ?: [];
    if (in_array($post_id, $bookmarks)) {
        $bookmarks = array_diff($bookmarks, [$post_id]);
        $status = 'removed';
    } else {
        $bookmarks[] = $post_id;
        $status = 'added';
    }
    update_user_meta($user_id, '_bookmarks', $bookmarks);
    wp_send_json(['status' => $status]);
}

// Reading history
add_action('wp_ajax_save_history', 'ta_save_history');
function ta_save_history() {
    $user_id = get_current_user_id();
    $chapter_id = intval($_POST['chapter_id']);
    $story_id = intval($_POST['story_id']);
    $history = get_user_meta($user_id, '_reading_history', true) ?: [];
    $history[$story_id] = ['chapter_id' => $chapter_id, 'time' => current_time('mysql')];
    update_user_meta($user_id, '_reading_history', $history);
    wp_die();
}

// ==================== LINH THẠCH SYSTEM ====================
add_action('show_user_profile', 'ta_show_linh_thach_admin');
add_action('edit_user_profile', 'ta_show_linh_thach_admin');
function ta_show_linh_thach_admin($user) {
    if (!current_user_can('administrator')) return;
    $lt = get_user_meta($user->ID, '_linh_thach', true) ?: 0;
    ?>
    <h3>Linh Thạch</h3>
    <table class="form-table">
        <tr><th>Số dư Linh Thạch</th><td><input type="number" step="any" name="linh_thach" value="<?php echo $lt; ?>"></td></tr>
    </table>
    <?php
}

add_action('personal_options_update', 'ta_save_linh_thach_admin');
add_action('edit_user_profile_update', 'ta_save_linh_thach_admin');
function ta_save_linh_thach_admin($user_id) {
    if (!current_user_can('administrator')) return;
    if (isset($_POST['linh_thach'])) update_user_meta($user_id, '_linh_thach', floatval($_POST['linh_thach']));
}

// ==================== VIP CHAPTER PURCHASE ====================
add_action('wp_ajax_purchase_vip_chapter', 'ta_purchase_vip_chapter');
function ta_purchase_vip_chapter() {
    $user_id = get_current_user_id();
    $chapter_id = intval($_POST['chapter_id']);

    if (!$user_id) wp_send_json_error('Vui lòng đăng nhập');
    if (!$chapter_id) wp_send_json_error('Không tìm thấy chương');

    $story_id = get_post_meta($chapter_id, '_story_id', true);
    $is_vip = get_post_meta($chapter_id, '_is_vip', true);
    $vip_price = get_post_meta($chapter_id, '_vip_price', true) ?: 5;

    if (!$is_vip) wp_send_json_error('Chương này không phải VIP');

    // Check if already purchased
    $purchased = get_user_meta($user_id, '_purchased_chapters', true) ?: [];
    if (in_array($chapter_id, $purchased)) {
        wp_send_json_error('Bạn đã mua chương này rồi');
    }

    $lt = get_user_meta($user_id, '_linh_thach', true) ?: 0;
    if ($lt < $vip_price) {
        wp_send_json_error('Không đủ Linh Thạch. Cần ' . $vip_price . ' LT');
    }

    // Deduct linh thạch
    $new_lt = $lt - $vip_price;
    update_user_meta($user_id, '_linh_thach', $new_lt);

    // Add to purchased list
    $purchased[] = $chapter_id;
    update_user_meta($user_id, '_purchased_chapters', $purchased);

    // Track revenue
    $revenue = get_post_meta($story_id, '_story_revenue', true) ?: 0;
    $revenue += $vip_price;
    update_post_meta($story_id, '_story_revenue', $revenue);

    wp_send_json_success([
        'message' => 'Mua thành công! Bạn đã mở khóa chương VIP.',
        'new_balance' => $new_lt,
    ]);
}

// ==================== SOCIAL LOGIN (OAUTH) ====================
add_action('init', 'ta_oauth_rewrite');
function ta_oauth_rewrite() {
    add_rewrite_rule('^ta-oauth/([^/]+)/?$', 'index.php?ta_oauth=$matches[1]', 'top');
}
add_filter('query_vars', function ($vars) { $vars[] = 'ta_oauth'; return $vars; });
add_action('template_redirect', 'ta_oauth_callback');
function ta_oauth_callback() {
    $action = get_query_var('ta_oauth');
    if (!$action) return;

    // Facebook callback
    if ($action === 'facebook' && isset($_GET['code'])) {
        $app_id = get_option('ta_fb_app_id');
        $app_secret = get_option('ta_fb_app_secret');
        $redirect_uri = home_url('ta-oauth/facebook');
        $code = $_GET['code'];

        // Exchange code for token
        $token_url = "https://graph.facebook.com/v18.0/oauth/access_token?client_id=$app_id&redirect_uri=$redirect_uri&client_secret=$app_secret&code=$code";
        $token_resp = wp_remote_get($token_url);
        if (is_wp_error($token_resp)) { wp_redirect(home_url('/dang-nhap?login=social_error')); exit; }
        $token_body = json_decode(wp_remote_retrieve_body($token_resp), true);
        if (empty($token_body['access_token'])) { wp_redirect(home_url('/dang-nhap?login=social_error')); exit; }

        // Get user info
        $graph_url = "https://graph.facebook.com/me?fields=id,name,email&access_token=" . $token_body['access_token'];
        $graph_resp = wp_remote_get($graph_url);
        if (is_wp_error($graph_resp)) { wp_redirect(home_url('/dang-nhap?login=social_error')); exit; }
        $user_data = json_decode(wp_remote_retrieve_body($graph_resp), true);
        if (empty($user_data['id'])) { wp_redirect(home_url('/dang-nhap?login=social_error')); exit; }

        $fb_id = $user_data['id'];
        $email = !empty($user_data['email']) ? $user_data['email'] : 'fb_' . $fb_id . '@ta-social.com';
        $name = !empty($user_data['name']) ? $user_data['name'] : 'User_' . $fb_id;

        $user = get_users(['meta_key' => '_social_fb_id', 'meta_value' => $fb_id]);
        if (!empty($user)) {
            $user_id = $user[0]->ID;
        } elseif (email_exists($email)) {
            $user_id = email_exists($email);
            update_user_meta($user_id, '_social_fb_id', $fb_id);
        } else {
            $username = 'fb_' . $fb_id;
            $suffix = '';
            while (username_exists($username . $suffix)) { $suffix = $suffix ? '_' . ($suffix + 1) : '_1'; }
            $user_id = wp_insert_user([
                'user_login' => $username . $suffix,
                'user_email' => $email,
                'user_pass'  => wp_generate_password(),
                'display_name' => $name,
                'role' => 'subscriber',
            ]);
            if (is_wp_error($user_id)) { wp_redirect(home_url('/dang-nhap?login=social_error')); exit; }
            update_user_meta($user_id, '_email_verified', '1');
            update_user_meta($user_id, '_social_fb_id', $fb_id);
        }

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        ta_set_flash('success', '🎉 Đăng nhập thành công với Facebook!');
        wp_redirect(home_url('/profile'));
        exit;
    }

    // Google callback
    if ($action === 'google' && isset($_GET['code'])) {
        $client_id = get_option('ta_google_client_id');
        $client_secret = get_option('ta_google_client_secret');
        $redirect_uri = home_url('ta-oauth/google');
        $code = $_GET['code'];

        // Exchange code for token
        $token_resp = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'code' => $code,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'grant_type' => 'authorization_code',
            ],
        ]);
        if (is_wp_error($token_resp)) { wp_redirect(home_url('/dang-nhap?login=social_error')); exit; }
        $token_body = json_decode(wp_remote_retrieve_body($token_resp), true);
        if (empty($token_body['access_token'])) { wp_redirect(home_url('/dang-nhap?login=social_error')); exit; }

        // Get user info
        $user_resp = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token_body['access_token']);
        if (is_wp_error($user_resp)) { wp_redirect(home_url('/dang-nhap?login=social_error')); exit; }
        $user_data = json_decode(wp_remote_retrieve_body($user_resp), true);
        if (empty($user_data['id'])) { wp_redirect(home_url('/dang-nhap?login=social_error')); exit; }

        $google_id = $user_data['id'];
        $email = $user_data['email'] ?? 'google_' . $google_id . '@ta-social.com';
        $name = $user_data['name'] ?? 'User_' . $google_id;

        $user = get_users(['meta_key' => '_social_google_id', 'meta_value' => $google_id]);
        if (!empty($user)) {
            $user_id = $user[0]->ID;
        } elseif (email_exists($email)) {
            $user_id = email_exists($email);
            update_user_meta($user_id, '_social_google_id', $google_id);
        } else {
            $username = 'google_' . $google_id;
            $suffix = '';
            while (username_exists($username . $suffix)) { $suffix = $suffix ? '_' . ($suffix + 1) : '_1'; }
            $user_id = wp_insert_user([
                'user_login' => $username . $suffix,
                'user_email' => $email,
                'user_pass'  => wp_generate_password(),
                'display_name' => $name,
                'role' => 'subscriber',
            ]);
            if (is_wp_error($user_id)) { wp_redirect(home_url('/dang-nhap?login=social_error')); exit; }
            update_user_meta($user_id, '_email_verified', '1');
            update_user_meta($user_id, '_social_google_id', $google_id);
        }

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        ta_set_flash('success', '🎉 Đăng nhập thành công với Google!');
        wp_redirect(home_url('/profile'));
        exit;
    }

    wp_redirect(home_url('/dang-nhap?login=social_error'));
    exit;
}

// Social login URL helpers
function ta_fb_login_url() {
    $app_id = get_option('ta_fb_app_id');
    if (!$app_id) return '#';
    $redirect = urlencode(home_url('ta-oauth/facebook'));
    return "https://www.facebook.com/v18.0/dialog/oauth?client_id=$app_id&redirect_uri=$redirect&scope=email,public_profile";
}
function ta_google_login_url() {
    $client_id = get_option('ta_google_client_id');
    if (!$client_id) return '#';
    $redirect = urlencode(home_url('ta-oauth/google'));
    return "https://accounts.google.com/o/oauth2/auth?client_id=$client_id&redirect_uri=$redirect&scope=email+profile&response_type=code";
}

// ==================== ADMIN SETTINGS ====================
add_action('admin_menu', 'ta_admin_menu');
function ta_admin_menu() {
    add_menu_page(
        'TruyenAudio',
        'TruyenAudio',
        'manage_options',
        'truyenaudio-settings',
        'ta_settings_page',
        'dashicons-book',
        30
    );
    add_submenu_page(
        'truyenaudio-settings',
        'Cấu hình',
        'Cấu hình',
        'manage_options',
        'truyenaudio-settings',
        'ta_settings_page'
    );
}

function ta_settings_page() {
    if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['_wpnonce'], 'ta_settings')) {
        update_option('ta_lt_to_vnd', intval($_POST['lt_to_vnd']));
        update_option('ta_default_vip_price', intval($_POST['default_vip_price']));
        update_option('ta_fb_app_id', sanitize_text_field($_POST['fb_app_id']));
        update_option('ta_fb_app_secret', sanitize_text_field($_POST['fb_app_secret']));
        update_option('ta_google_client_id', sanitize_text_field($_POST['google_client_id']));
        update_option('ta_google_client_secret', sanitize_text_field($_POST['google_client_secret']));
        // Save packages
        $packages = [];
        if (isset($_POST['pkg_lt']) && is_array($_POST['pkg_lt'])) {
            foreach ($_POST['pkg_lt'] as $i => $lt) {
                $lt = intval($lt);
                $vnd = intval($_POST['pkg_vnd'][$i] ?? 0);
                $bonus = intval($_POST['pkg_bonus'][$i] ?? 0);
                if ($lt > 0 && $vnd > 0) {
                    $packages[] = [
                        'id' => 'pkg_' . $i,
                        'lt' => $lt,
                        'vnd' => $vnd,
                        'bonus' => $bonus,
                    ];
                }
            }
        }
        update_option('ta_lt_packages', $packages);
        echo '<div class="notice notice-success"><p>Đã lưu cấu hình.</p></div>';
    }
    $lt_vnd = get_option('ta_lt_to_vnd', 1000);
    $default_vip = get_option('ta_default_vip_price', 5);
    $fb_id = get_option('ta_fb_app_id', '');
    $fb_secret = get_option('ta_fb_app_secret', '');
    $gg_id = get_option('ta_google_client_id', '');
    $gg_secret = get_option('ta_google_client_secret', '');
    $packages = get_option('ta_lt_packages', []);
    ?>
    <div class="wrap">
        <h1>TruyenAudio - Cấu hình</h1>
        <form method="post">
            <?php wp_nonce_field('ta_settings'); ?>
            <table class="form-table">
                <tr>
                    <th>Mặc định giá VIP chương mới</th>
                    <td>
                        <input type="number" name="default_vip_price" value="<?php echo $default_vip; ?>" min="1">
                        <p class="description">Giá Linh Thạch mặc định khi tạo chương mới (admin có thể sửa từng chương)</p>
                    </td>
                </tr>
                <tr>
                    <th>1 Linh Thạch = ? VNĐ</th>
                    <td>
                        <input type="number" name="lt_to_vnd" value="<?php echo $lt_vnd; ?>" min="1">
                        <p class="description">Quy đổi 1 Linh Thạch sang VNĐ</p>
                    </td>
                </tr>
                <tr>
                    <th colspan="2"><h3 style="margin:10px 0 0;">🔐 Đăng nhập Facebook</h3></th>
                </tr>
                <tr>
                    <th>Facebook App ID</th>
                    <td><input type="text" name="fb_app_id" value="<?php echo esc_attr($fb_id); ?>" style="width:300px;">
                        <p class="description">Lấy từ <a href="https://developers.facebook.com" target="_blank">Facebook Developers</a></p>
                    </td>
                </tr>
                <tr>
                    <th>Facebook App Secret</th>
                    <td><input type="text" name="fb_app_secret" value="<?php echo esc_attr($fb_secret); ?>" style="width:300px;">
                        <p class="description">Redirect URI: <code><?php echo home_url('ta-oauth/facebook'); ?></code></p>
                    </td>
                </tr>
                <tr>
                    <th colspan="2"><h3 style="margin:10px 0 0;">🔐 Đăng nhập Google</h3></th>
                </tr>
                <tr>
                    <th>Google Client ID</th>
                    <td><input type="text" name="google_client_id" value="<?php echo esc_attr($gg_id); ?>" style="width:400px;">
                        <p class="description">Lấy từ <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a></p>
                    </td>
                </tr>
                <tr>
                    <th>Google Client Secret</th>
                    <td><input type="text" name="google_client_secret" value="<?php echo esc_attr($gg_secret); ?>" style="width:400px;">
                        <p class="description">Redirect URI: <code><?php echo home_url('ta-oauth/google'); ?></code></p>
                    </td>
                </tr>
                <tr>
                    <th>Gói nạp Linh Thạch</th>
                    <td>
                        <p class="description">Cấu hình các gói nạp hiển thị cho người dùng.</p>
                        <table style="border-collapse:collapse;margin-top:10px;">
                            <thead>
                                <tr style="border-bottom:1px solid #ccc;">
                                    <th style="padding:6px 12px;text-align:left;">Linh Thạch</th>
                                    <th style="padding:6px 12px;text-align:left;">Giá (VNĐ)</th>
                                    <th style="padding:6px 12px;text-align:left;">Bonus %</th>
                                    <th style="padding:6px 12px;"></th>
                                </tr>
                            </thead>
                            <tbody id="ta-pkg-rows">
                                <?php foreach ($packages as $i => $p): ?>
                                <tr>
                                    <td><input type="number" name="pkg_lt[]" value="<?php echo $p['lt']; ?>" min="1" style="width:100px;"></td>
                                    <td><input type="number" name="pkg_vnd[]" value="<?php echo $p['vnd']; ?>" min="1" style="width:120px;"></td>
                                    <td><input type="number" name="pkg_bonus[]" value="<?php echo $p['bonus']; ?>" min="0" max="500" style="width:80px;"></td>
                                    <td><button type="button" class="button ta-remove-pkg" style="color:#a00;">Xóa</button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="button" class="button" id="ta-add-pkg">+ Thêm gói</button>
                    </td>
                </tr>
            </table>
            <p><button type="submit" name="save_settings" class="button button-primary">Lưu cấu hình</button></p>
        </form>
        <script>
        jQuery(function($) {
            $('#ta-add-pkg').on('click', function() {
                var row = '<tr><td><input type="number" name="pkg_lt[]" value="1000" min="1" style="width:100px;"></td>' +
                    '<td><input type="number" name="pkg_vnd[]" value="10000" min="1" style="width:120px;"></td>' +
                    '<td><input type="number" name="pkg_bonus[]" value="0" min="0" max="500" style="width:80px;"></td>' +
                    '<td><button type="button" class="button ta-remove-pkg" style="color:#a00;">Xóa</button></td></tr>';
                $('#ta-pkg-rows').append(row);
            });
            $('#ta-pkg-rows').on('click', '.ta-remove-pkg', function() {
                $(this).closest('tr').remove();
            });
        });
        </script>
    </div>
    <?php
}

// ==================== REST API ====================
add_action('rest_api_init', function () {
    register_rest_route('wp/v2', '/users/register', [
        'methods' => 'POST',
        'callback' => 'ta_register_user',
        'permission_callback' => '__return_true',
    ]);

    // Chapters by story ID
    register_rest_route('truyenaudio/v1', '/stories/(?P<id>\d+)/chapters', [
        'methods' => 'GET',
        'callback' => 'ta_rest_get_chapters',
        'permission_callback' => '__return_true',
    ]);

    // Stories with meta
    register_rest_route('truyenaudio/v1', '/stories/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'ta_rest_get_story',
        'permission_callback' => '__return_true',
    ]);

    // Create chapter (author only)
    register_rest_route('truyenaudio/v1', '/chapters', [
        'methods' => 'POST',
        'callback' => 'ta_rest_create_chapter',
        'permission_callback' => 'is_user_logged_in',
    ]);

    // Delete chapter
    register_rest_route('truyenaudio/v1', '/chapters/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'ta_rest_delete_chapter',
        'permission_callback' => 'is_user_logged_in',
    ]);

    // Create story (author only)
    register_rest_route('truyenaudio/v1', '/stories', [
        'methods' => 'POST',
        'callback' => 'ta_rest_create_story',
        'permission_callback' => 'is_user_logged_in',
    ]);

    // Update story
    register_rest_route('truyenaudio/v1', '/stories/(?P<id>\d+)', [
        'methods' => 'PUT',
        'callback' => 'ta_rest_update_story',
        'permission_callback' => 'is_user_logged_in',
    ]);

    // Delete story
    register_rest_route('truyenaudio/v1', '/stories/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'ta_rest_delete_story',
        'permission_callback' => 'is_user_logged_in',
    ]);
});

function ta_rest_get_chapters($request) {
    $story_id = intval($request['id']);
    $chapters = ta_get_chapters($story_id);
    $data = [];
    foreach ($chapters as $ch) {
        $data[] = [
            'id' => $ch->ID,
            'title' => $ch->post_title,
            'content' => ta_can_read_chapter($ch->ID, $story_id) ? apply_filters('the_content', $ch->post_content) : '',
            'can_read' => ta_can_read_chapter($ch->ID, $story_id),
            'meta' => [
                '_story_id' => get_post_meta($ch->ID, '_story_id', true),
                '_chapter_number' => get_post_meta($ch->ID, '_chapter_number', true),
                '_audio_url' => get_post_meta($ch->ID, '_audio_url', true),
                '_is_vip' => get_post_meta($ch->ID, '_is_vip', true),
                '_vip_price' => get_post_meta($ch->ID, '_vip_price', true),
            ],
        ];
    }
    return new WP_REST_Response($data);
}

function ta_rest_get_story($request) {
    $id = intval($request['id']);
    $post = get_post($id);
    if (!$post || $post->post_type !== 'truyen') {
        return new WP_Error('not_found', 'Không tìm thấy truyện', ['status' => 404]);
    }
    $thumbnail = '';
    if (has_post_thumbnail($id)) {
        $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'medium');
        if ($thumb) $thumbnail = $thumb[0];
    }
    $terms = wp_get_post_terms($id, ['the_loai', 'tac_gia', 'trang_thai']);
    $genres = [];
    $authors = [];
    foreach ($terms as $t) {
        if ($t->taxonomy === 'the_loai') $genres[] = $t->name;
        if ($t->taxonomy === 'tac_gia') $authors[] = $t->name;
    }
    $chapters = ta_get_chapters($id);
    return new WP_REST_Response([
        'id' => $id,
        'title' => ['rendered' => $post->post_title],
        'excerpt' => ['rendered' => wp_strip_all_tags($post->post_excerpt)],
        'content' => ['rendered' => apply_filters('the_content', $post->post_content)],
        'thumbnail' => $thumbnail,
        'genres' => $genres,
        'authors' => $authors,
        'chapter_count' => count($chapters),
        'meta' => [
            '_views' => get_post_meta($id, '_views', true) ?: 0,
            '_rating' => floatval(get_post_meta($id, '_rating', true) ?: 0),
            '_rating_count' => intval(get_post_meta($id, '_rating_count', true) ?: 0),
            '_chapter_count' => count($chapters),
        ],
    ]);
}

function ta_rest_create_chapter($request) {
    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    if (!current_user_can('administrator')) {
        return new WP_Error('forbidden', 'Bạn không có quyền', ['status' => 403]);
    }
    $title = sanitize_text_field($request->get_param('title'));
    $content = wp_kses_post($request->get_param('content'));
    $story_id = intval($request->get_param('story_id'));
    $chapter_number = intval($request->get_param('chapter_number'));
    $audio_url = esc_url_raw($request->get_param('audio_url') ?? '');
    $is_vip = $request->get_param('is_vip') ? '1' : '0';

    if (empty($title) || !$story_id) {
        return new WP_Error('missing', 'Thiếu tiêu đề hoặc story_id', ['status' => 400]);
    }

    $post_id = wp_insert_post([
        'post_title' => $title,
        'post_content' => $content,
        'post_type' => 'chapter',
        'post_status' => 'publish',
        'post_author' => $user_id,
    ]);
    if (is_wp_error($post_id)) return $post_id;

    update_post_meta($post_id, '_story_id', $story_id);
    update_post_meta($post_id, '_chapter_number', $chapter_number);
    update_post_meta($post_id, '_audio_url', $audio_url);
    update_post_meta($post_id, '_is_vip', $is_vip);

    return new WP_REST_Response(['id' => $post_id, 'message' => 'Tạo chương thành công'], 201);
}

function ta_rest_delete_chapter($request) {
    $id = intval($request['id']);
    $user_id = get_current_user_id();
    if (!current_user_can('administrator')) {
        return new WP_Error('forbidden', 'Bạn không có quyền', ['status' => 403]);
    }
    wp_delete_post($id, true);
    return new WP_REST_Response(['message' => 'Đã xóa chương']);
}

function ta_rest_create_story($request) {
    $user_id = get_current_user_id();
    if (!current_user_can('administrator')) {
        return new WP_Error('forbidden', 'Bạn không có quyền', ['status' => 403]);
    }
    $title = sanitize_text_field($request->get_param('title'));
    $content = wp_kses_post($request->get_param('content'));
    $excerpt = sanitize_textarea_field($request->get_param('excerpt') ?? '');
    if (empty($title)) {
        return new WP_Error('missing', 'Thiếu tiêu đề', ['status' => 400]);
    }
    $post_id = wp_insert_post([
        'post_title' => $title,
        'post_content' => $content,
        'post_excerpt' => $excerpt,
        'post_type' => 'truyen',
        'post_status' => 'draft',
        'post_author' => $user_id,
    ]);
    if (is_wp_error($post_id)) return $post_id;

    update_post_meta($post_id, '_views', 0);
    update_post_meta($post_id, '_rating', 0);
    update_post_meta($post_id, '_rating_count', 0);
    update_post_meta($post_id, '_chapter_count', 0);

    $genre_ids = $request->get_param('genre_ids');
    if (is_array($genre_ids)) {
        wp_set_post_terms($post_id, array_map('intval', $genre_ids), 'the_loai');
    }

    return new WP_REST_Response(['id' => $post_id, 'message' => 'Tạo truyện thành công'], 201);
}

function ta_rest_update_story($request) {
    $id = intval($request['id']);
    $user_id = get_current_user_id();
    if (!current_user_can('administrator')) {
        return new WP_Error('forbidden', 'Bạn không có quyền', ['status' => 403]);
    }
    $update = ['ID' => $id];
    if ($request->get_param('title')) $update['post_title'] = sanitize_text_field($request->get_param('title'));
    if ($request->get_param('content') !== null) $update['post_content'] = wp_kses_post($request->get_param('content'));
    if ($request->get_param('status')) $update['post_status'] = sanitize_text_field($request->get_param('status'));
    wp_update_post($update);
    return new WP_REST_Response(['message' => 'Cập nhật thành công']);
}

function ta_rest_delete_story($request) {
    $id = intval($request['id']);
    $user_id = get_current_user_id();
    if (!current_user_can('administrator')) {
        return new WP_Error('forbidden', 'Bạn không có quyền', ['status' => 403]);
    }
    wp_delete_post($id, true);
    return new WP_REST_Response(['message' => 'Đã xóa truyện']);
}

function ta_register_user($request) {
    $username = sanitize_user($request->get_param('username'));
    $email = sanitize_email($request->get_param('email'));
    $password = $request->get_param('password');

    if (empty($username) || empty($email) || empty($password)) {
        return new WP_Error('missing_fields', 'Vui lòng điền đầy đủ thông tin', ['status' => 400]);
    }
    if (username_exists($username)) {
        return new WP_Error('username_exists', 'Tên đăng nhập đã tồn tại', ['status' => 400]);
    }
    if (email_exists($email)) {
        return new WP_Error('email_exists', 'Email đã tồn tại', ['status' => 400]);
    }

    $user_id = wp_insert_user([
        'user_login' => $username,
        'user_email' => $email,
        'user_pass'  => $password,
        'role'       => 'subscriber',
    ]);

    if (is_wp_error($user_id)) {
        return new WP_Error('registration_failed', 'Đăng ký thất bại', ['status' => 500]);
    }

    return new WP_REST_Response(['message' => 'Đăng ký thành công', 'user_id' => $user_id], 201);
}

function ta_rest_purchase_vip($request) {
    $user_id = get_current_user_id();
    $chapter_id = intval($request->get_param('chapter_id'));
    $chapter = get_post($chapter_id);

    if (!$chapter || $chapter->post_type !== 'chapter') {
        return new WP_Error('invalid', 'Không tìm thấy chương', ['status' => 404]);
    }

    $story_id = get_post_meta($chapter_id, '_story_id', true);
    $is_vip = get_post_meta($chapter_id, '_is_vip', true);
    $vip_price = get_post_meta($chapter_id, '_vip_price', true) ?: 5;

    if (!$is_vip) {
        return new WP_Error('not_vip', 'Chương này không phải VIP', ['status' => 400]);
    }

    $purchased = get_user_meta($user_id, '_purchased_chapters', true) ?: [];
    if (in_array($chapter_id, $purchased)) {
        return new WP_Error('already_purchased', 'Bạn đã mua chương này rồi', ['status' => 400]);
    }

    $lt = get_user_meta($user_id, '_linh_thach', true) ?: 0;
    if ($lt < $vip_price) {
        return new WP_Error('insufficient', 'Không đủ Linh Thạch', ['status' => 400]);
    }

    $new_lt = $lt - $vip_price;
    update_user_meta($user_id, '_linh_thach', $new_lt);

    $purchased[] = $chapter_id;
    update_user_meta($user_id, '_purchased_chapters', $purchased);

    $revenue = get_post_meta($story_id, '_story_revenue', true) ?: 0;
    $revenue += $vip_price;
    update_post_meta($story_id, '_story_revenue', $revenue);

    return new WP_REST_Response([
        'message' => 'Mua thành công!',
        'new_balance' => $new_lt,
    ]);
}

// Check if user has purchased a chapter
function ta_has_purchased($chapter_id, $user_id = null) {
    if (!$user_id) $user_id = get_current_user_id();
    if (!$user_id) return false;
    if (current_user_can('administrator')) return true;
    $purchased = get_user_meta($user_id, '_purchased_chapters', true) ?: [];
    return in_array($chapter_id, $purchased);
}

// Get user role badge
function ta_user_role_badge($user_id = null) {
    if (!$user_id) $user_id = get_current_user_id();
    if (!$user_id) return '';
    $user = get_userdata($user_id);
    if (in_array('administrator', (array) $user->roles)) return '<span class="role-badge admin">Admin</span>';
    return '<span class="role-badge user">Đọc giả</span>';
}

// ==================== 403 ACCESS DENIED ====================
function ta_deny() {
    status_header(403);
    ?>
    <!DOCTYPE html><html><head><meta charset="UTF-8"><title>403 - Truy cập bị từ chối</title>
    <style>
        body { margin:0; padding:0; font-family:-apple-system,sans-serif; background:#0f0f1a; color:#e0e0e0; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .deny-box { text-align:center; padding:40px; }
        .deny-code { font-size:80px; font-weight:800; color:#e74c3c; line-height:1; }
        .deny-title { font-size:24px; margin:15px 0; color:#fff; }
        .deny-desc { color:#888; margin-bottom:25px; }
        .deny-btn { display:inline-block; padding:10px 30px; background:#f0c040; color:#1a1a2e; border-radius:8px; text-decoration:none; font-weight:600; }
        .deny-btn:hover { background:#ffd700; }
    </style></head><body>
    <div class="deny-box">
        <div class="deny-code">403</div>
        <div class="deny-title">🔒 Truy cập bị từ chối</div>
        <div class="deny-desc">Bạn không có quyền truy cập vào trang này.</div>
        <a class="deny-btn" href="<?php echo esc_url(home_url()); ?>">Về trang chủ</a>
    </div></body></html>
    <?php
    exit;
}

function ta_require_auth() {
    if (!is_user_logged_in()) ta_deny();
}

function ta_require_role($roles) {
    ta_require_auth();
    $user = wp_get_current_user();
    if (!array_intersect((array)$roles, (array)$user->roles)) ta_deny();
}

add_action('wp_logout', 'ta_redirect_after_logout');
function ta_redirect_after_logout() {
    wp_redirect(home_url());
    exit;
}

// ==================== FLASH MESSAGES ====================
function ta_set_flash($type, $message) {
    $messages = get_transient('ta_flash_' . get_current_user_id()) ?: [];
    $messages[] = ['type' => $type, 'message' => $message];
    set_transient('ta_flash_' . get_current_user_id(), $messages, 60);
}

function ta_get_flash() {
    $user_id = get_current_user_id();
    if (!$user_id) return [];
    $messages = get_transient('ta_flash_' . $user_id) ?: [];
    delete_transient('ta_flash_' . $user_id);
    return $messages;
}

// ==================== NOTIFICATION SYSTEM ====================
function ta_add_notification($user_id, $type, $message, $link = '') {
    $notifications = get_user_meta($user_id, '_notifications', true) ?: [];
    array_unshift($notifications, [
        'type' => $type,
        'message' => $message,
        'link' => $link,
        'time' => current_time('mysql'),
        'read' => 0,
    ]);
    // Keep max 50
    if (count($notifications) > 50) $notifications = array_slice($notifications, 0, 50);
    update_user_meta($user_id, '_notifications', $notifications);
}

function ta_get_notifications($user_id = null, $unread_only = false) {
    if (!$user_id) $user_id = get_current_user_id();
    if (!$user_id) return [];
    $notifications = get_user_meta($user_id, '_notifications', true) ?: [];
    if ($unread_only) {
        $notifications = array_filter($notifications, function($n) { return empty($n['read']); });
    }
    return $notifications;
}

function ta_count_unread_notifications($user_id = null) {
    return count(ta_get_notifications($user_id, true));
}

// Login welcome notification
add_action('wp_login', 'ta_login_notification', 10, 2);
function ta_login_notification($user_login, $user) {
    ta_add_notification($user->ID, 'info', '👋 Chào mừng bạn quay trở lại!', home_url());
}

// ==================== OTP VERIFICATION ====================
function ta_validate_password($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
    return true;
}

function ta_generate_otp($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function ta_send_otp_email($user_id, $email, $otp) {
    $subject = 'Mã xác thực đăng ký TruyenAudio';
    $message = "Chào bạn,\n\n";
    $message .= "Mã xác thực của bạn là: $otp\n\n";
    $message .= "Mã có hiệu lực trong 10 phút.\n\n";
    $message .= "Cảm ơn bạn đã đăng ký!\n";
    $message .= get_bloginfo('name');
    wp_mail($email, $subject, $message);
}

add_action('wp_ajax_nopriv_ta_verify_otp', 'ta_ajax_verify_otp');
function ta_ajax_verify_otp() {
    $user_id = intval($_POST['user_id'] ?? 0);
    $otp = preg_replace('/[^0-9]/', '', $_POST['otp'] ?? '');

    if (!$user_id || !$otp) {
        wp_send_json_error('Thiếu thông tin xác thực.');
    }

    $stored = get_user_meta($user_id, '_email_otp', true);
    $expires = intval(get_user_meta($user_id, '_email_otp_expires', true));
    $verified = get_user_meta($user_id, '_email_verified', true);

    if ($verified === '1') {
        wp_send_json_error('Tài khoản này đã được xác thực rồi.');
    }

    if (time() > $expires) {
        wp_send_json_error('Mã xác thực đã hết hạn. Vui lòng đăng ký lại.');
    }

    if ($stored !== $otp) {
        wp_send_json_error('Mã xác thực không đúng.');
    }

    update_user_meta($user_id, '_email_verified', '1');
    delete_user_meta($user_id, '_email_otp');
    delete_user_meta($user_id, '_email_otp_expires');

    // Auto-login
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_send_json_success(['message' => '✅ Xác thực thành công!']);
}

add_action('wp_ajax_nopriv_ta_resend_otp', 'ta_ajax_resend_otp');
function ta_ajax_resend_otp() {
    $user_id = intval($_POST['user_id'] ?? 0);
    if (!$user_id) wp_send_json_error('Thiếu thông tin.');

    $user = get_userdata($user_id);
    if (!$user) wp_send_json_error('Người dùng không tồn tại.');

    if (get_user_meta($user_id, '_email_verified', true) === '1') {
        wp_send_json_error('Tài khoản đã được xác thực.');
    }

    $otp = ta_generate_otp();
    update_user_meta($user_id, '_email_otp', $otp);
    update_user_meta($user_id, '_email_otp_expires', time() + 600);
    ta_send_otp_email($user_id, $user->user_email, $otp);

    wp_send_json_success(['message' => '📧 Mã xác thực mới đã được gửi tới email của bạn.']);
}

// ==================== FORGOT / RESET PASSWORD ====================
add_action('wp_ajax_nopriv_ta_forgot_password', 'ta_ajax_forgot_password');
function ta_ajax_forgot_password() {
    $email = sanitize_email($_POST['email'] ?? '');
    if (!$email) wp_send_json_error('Vui lòng nhập email.');

    $user = get_user_by('email', $email);
    if (!$user) wp_send_json_error('Email này chưa được đăng ký.');

    // Check if user has email verified
    if (get_user_meta($user->ID, '_email_verified', true) !== '1') {
        wp_send_json_error('Email này chưa được xác thực. Vui lòng xác thực email trước.');
    }

    $otp = ta_generate_otp();
    update_user_meta($user->ID, '_reset_otp', $otp);
    update_user_meta($user->ID, '_reset_otp_expires', time() + 600);

    $subject = 'Đặt lại mật khẩu TruyenAudio';
    $message = "Chào " . $user->display_name . ",\n\n";
    $message .= "Mã xác thực đặt lại mật khẩu của bạn là: $otp\n\n";
    $message .= "Mã có hiệu lực trong 10 phút.\n\n";
    $message .= "Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.\n\n";
    $message .= get_bloginfo('name');
    wp_mail($email, $subject, $message);

    wp_send_json_success([
        'user_id' => $user->ID,
        'message' => '📧 Mã xác thực đã được gửi tới email ' . $email . '. Vui lòng kiểm tra!',
    ]);
}

add_action('wp_ajax_nopriv_ta_reset_password', 'ta_ajax_reset_password');
function ta_ajax_reset_password() {
    $user_id = intval($_POST['user_id'] ?? 0);
    $otp = preg_replace('/[^0-9]/', '', $_POST['otp'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$user_id || !$otp || !$password) {
        wp_send_json_error('Thiếu thông tin.');
    }
    if (!ta_validate_password($password)) {
        wp_send_json_error('Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.');
    }

    $stored = get_user_meta($user_id, '_reset_otp', true);
    $expires = intval(get_user_meta($user_id, '_reset_otp_expires', true));

    if (time() > $expires) {
        wp_send_json_error('Mã xác thực đã hết hạn. Vui lòng yêu cầu lại.');
    }
    if ($stored !== $otp) {
        wp_send_json_error('Mã xác thực không đúng.');
    }

    wp_set_password($password, $user_id);
    delete_user_meta($user_id, '_reset_otp');
    delete_user_meta($user_id, '_reset_otp_expires');

    wp_send_json_success(['message' => '✅ Mật khẩu đã được đặt lại thành công! Vui lòng đăng nhập.']);
}

// AJAX: fetch notifications
add_action('wp_ajax_ta_get_notifications', 'ta_ajax_get_notifications');
function ta_ajax_get_notifications() {
    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error('Vui lòng đăng nhập');

    $unread_count = ta_count_unread_notifications($user_id);
    $list = ta_get_notifications($user_id, false);

    // Mark all as read
    $notifications = get_user_meta($user_id, '_notifications', true) ?: [];
    foreach ($notifications as &$n) $n['read'] = 1;
    update_user_meta($user_id, '_notifications', $notifications);

    // Format for display
    $items = [];
    foreach ($list as $n) {
        $items[] = [
            'type' => $n['type'],
            'message' => $n['message'],
            'link' => $n['link'],
            'time' => $n['time'],
            'is_new' => empty($n['read']),
        ];
    }

    wp_send_json_success([
        'unread' => $unread_count,
        'items' => $items,
    ]);
}

// Hook into withdrawal approval/rejection
add_action('ta_withdrawal_processed', 'ta_notify_withdrawal', 10, 3);
function ta_notify_withdrawal($user_id, $status, $amount) {
    if ($status === 'approved') {
        ta_add_notification($user_id, 'success', '✅ Yêu cầu rút ' . number_format($amount) . ' Linh Thạch đã được duyệt!', home_url('/rut-linh-thach'));
    } elseif ($status === 'rejected') {
        ta_add_notification($user_id, 'error', '❌ Yêu cầu rút ' . number_format($amount) . ' Linh Thạch đã bị từ chối.', home_url('/rut-linh-thach'));
    }
}

// Hook into report status change
add_action('ta_report_processed', 'ta_notify_report', 10, 3);
function ta_notify_report($story_id, $reporter_id, $status) {
    $story_title = get_the_title($story_id);
    $story_link = get_permalink($story_id);

    if ($status === 'resolved') {
        ta_add_notification($reporter_id, 'success', '✅ Báo cáo truyện "' . $story_title . '" đã được xử lý. Cảm ơn bạn!', $story_link);
    } elseif ($status === 'dismissed') {
        ta_add_notification($reporter_id, 'info', 'ℹ️ Báo cáo truyện "' . $story_title . '" đã được xem xét và không có vi phạm.', $story_link);
    }

    // Also notify the story author
    $author_id = get_post_field('post_author', $story_id);
    if ($author_id && $author_id != $reporter_id) {
        if ($status === 'resolved') {
            ta_add_notification($author_id, 'warning', '⚠️ Truyện "' . $story_title . '" của bạn đã bị báo cáo và admin đã xác nhận vi phạm. Vui lòng kiểm tra lại nội dung!', $story_link);
        } elseif ($status === 'dismissed') {
            ta_add_notification($author_id, 'success', '✅ Báo cáo về truyện "' . $story_title . '" đã được xem xét và không có vi phạm. Cảm ơn bạn!', $story_link);
        }
    }
}

// Hook report status change to fire notification
add_action('save_post', 'ta_report_status_change', 20, 3);
function ta_report_status_change($post_id, $post, $update) {
    if ($post->post_type !== 'report') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;

    $old_status = get_post_meta($post_id, '_report_status_prev', true) ?: 'pending';
    $new_status = get_post_meta($post_id, '_report_status', true) ?: 'pending';

    update_post_meta($post_id, '_report_status_prev', $new_status);

    if ($old_status === $new_status) return;
    if ($old_status === 'pending' && in_array($new_status, ['resolved', 'dismissed'])) {
        $story_id = get_post_meta($post_id, '_reported_story_id', true);
        $reporter_id = get_post_meta($post_id, '_reporter_id', true);
        if ($story_id && $reporter_id) {
            do_action('ta_report_processed', $story_id, $reporter_id, $new_status);
        }

        // Auto-ban story when report is resolved (confirmed violation)
        if ($new_status === 'resolved' && $story_id) {
            $story = get_post($story_id);
            if ($story && $story->post_type === 'truyen' && $story->post_status !== 'draft') {
                $author_id = $story->post_author;
                wp_update_post(['ID' => $story_id, 'post_status' => 'draft']);
                update_post_meta($story_id, '_banned', '1');
                update_post_meta($story_id, '_ban_reason', 'Vi phạm: ' . get_post_meta($post_id, '_report_reason', true));
                update_post_meta($story_id, '_ban_time', current_time('mysql'));

                // Notify story author
                ta_add_notification($author_id, 'error', '🚫 Truyện "' . $story->post_title . '" đã bị khóa do vi phạm. Vui lòng kiểm tra và liên hệ admin.', get_permalink($story_id));
            }
        }
    }
}

// Add ban meta box to story edit screen
add_action('add_meta_boxes', 'ta_add_ban_meta_box');
function ta_add_ban_meta_box() {
    add_meta_box('story_ban_box', '🚫 Khóa truyện', 'ta_ban_meta_html', 'truyen', 'side');
}

function ta_ban_meta_html($post) {
    $banned = get_post_meta($post->ID, '_banned', true);
    $reason = get_post_meta($post->ID, '_ban_reason', true);
    $ban_time = get_post_meta($post->ID, '_ban_time', true);
    $pending_reports = count(get_posts([
        'post_type' => 'report',
        'meta_query' => [
            ['key' => '_reported_story_id', 'value' => $post->ID],
            ['key' => '_report_status', 'value' => 'pending'],
        ],
        'numberposts' => -1,
        'fields' => 'ids',
    ]));
    ?>
    <?php if ($banned): ?>
        <p style="color:#e74c3c;font-weight:600;">🚫 Truyện này đang bị khóa</p>
        <p style="font-size:13px;color:#888;">Lý do: <?php echo esc_html($reason ?: 'N/A'); ?></p>
        <p style="font-size:13px;color:#888;">Thời gian: <?php echo $ban_time ?: 'N/A'; ?></p>
        <p><label><input type="checkbox" name="unban_story" value="1"> Mở khóa truyện</label></p>
    <?php else: ?>
        <p style="color:#2ecc71;">✅ Truyện đang hoạt động</p>
        <p style="font-size:13px;color:#888;">Số báo cáo đang chờ: <strong><?php echo $pending_reports; ?></strong></p>
        <p><label><input type="checkbox" name="ban_story" value="1"> Khóa truyện</label></p>
        <p style="font-size:13px;color:#888;"><input type="text" name="ban_reason" placeholder="Lý do khóa..." style="width:100%;"></p>
    <?php endif; ?>
    <?php
}

add_action('save_post', 'ta_save_ban_meta', 20);
function ta_save_ban_meta($post_id) {
    if (get_post_type($post_id) !== 'truyen') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $banned = get_post_meta($post_id, '_banned', true);

    if (isset($_POST['unban_story']) && $banned) {
        delete_post_meta($post_id, '_banned');
        delete_post_meta($post_id, '_ban_reason');
        delete_post_meta($post_id, '_ban_time');
        wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
    }

    if (isset($_POST['ban_story']) && !$banned) {
        $reason = sanitize_text_field($_POST['ban_reason'] ?? 'Vi phạm quy định');
        wp_update_post(['ID' => $post_id, 'post_status' => 'draft']);
        update_post_meta($post_id, '_banned', '1');
        update_post_meta($post_id, '_ban_reason', $reason);
        update_post_meta($post_id, '_ban_time', current_time('mysql'));

        $author_id = get_post_field('post_author', $post_id);
        $title = get_the_title($post_id);
        ta_add_notification($author_id, 'error', '🚫 Truyện "' . $title . '" đã bị khóa bởi admin. Lý do: ' . $reason, get_permalink($post_id));
    }
}

// ==================== LT ORDER / PAYMENT SYSTEM ====================
add_action('init', 'ta_register_lt_order');
function ta_register_lt_order() {
    register_post_type('lt_order', [
    'labels' => ['name' => 'Đơn nạp LT', 'singular_name' => 'Đơn nạp'],
    'public' => false,
    'show_ui' => true,
    'show_in_menu' => 'edit.php?post_type=truyen',
    'supports' => ['title'],
    'capability_type' => 'post',
    'map_meta_cap' => true,
    'rewrite' => false,
    'exclude_from_search' => true,
    'publicly_queryable' => false,
    'has_archive' => false,
    'query_var' => false,
    ]);
}

add_action('add_meta_boxes', 'ta_add_lt_order_meta');
function ta_add_lt_order_meta() {
    add_meta_box('lt_order_details', 'Chi tiết đơn nạp', 'ta_lt_order_meta_html', 'lt_order', 'normal');
}

function ta_lt_order_meta_html($post) {
    $user_id = get_post_meta($post->ID, '_order_user_id', true);
    $lt_amount = get_post_meta($post->ID, '_order_lt', true);
    $vnd_amount = get_post_meta($post->ID, '_order_vnd', true);
    $status = get_post_meta($post->ID, '_order_status', true) ?: 'pending';
    $user = $user_id ? get_userdata($user_id) : null;
    ?>
    <p><strong>Người dùng:</strong> <?php echo $user ? $user->display_name . ' (' . $user->user_login . ')' : 'N/A'; ?></p>
    <p><strong>Linh Thạch:</strong> 💎<?php echo number_format($lt_amount); ?></p>
    <p><strong>Số tiền:</strong> <?php echo number_format($vnd_amount); ?>₫</p>
    <p><strong>Trạng thái:</strong>
        <select name="order_status">
            <option value="pending" <?php selected($status, 'pending'); ?>>⏳ Chờ thanh toán</option>
            <option value="completed" <?php selected($status, 'completed'); ?>>✅ Hoàn thành</option>
            <option value="cancelled" <?php selected($status, 'cancelled'); ?>>❌ Đã hủy</option>
        </select>
    </p>
    <?php if ($status === 'pending'): ?>
    <p style="color:#f39c12;">🔔 Đang chờ xử lý. Chọn "Hoàn thành" và lưu để cộng LT cho người dùng.</p>
    <?php endif; ?>
    <?php
}

add_action('save_post', 'ta_save_lt_order_meta');
function ta_save_lt_order_meta($post_id) {
    if (get_post_type($post_id) !== 'lt_order') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['order_status'])) {
        $old_status = get_post_meta($post_id, '_order_status', true) ?: 'pending';
        $new_status = sanitize_text_field($_POST['order_status']);
        update_post_meta($post_id, '_order_status', $new_status);
        if ($old_status !== 'completed' && $new_status === 'completed') {
            $user_id = get_post_meta($post_id, '_order_user_id', true);
            $lt = floatval(get_post_meta($post_id, '_order_lt', true));
            if ($user_id && $lt > 0) {
                $current = get_user_meta($user_id, '_linh_thach', true) ?: 0;
                update_user_meta($user_id, '_linh_thach', $current + $lt);
                $order_code = get_the_title($post_id);
                $note = 'Nạp LT: ' . $order_code . ' (+' . $lt . ' LT)';
                // Add to revenue for author commission if needed
                // Log to user history
                $history = get_user_meta($user_id, '_lt_history', true) ?: [];
                $history[] = ['type' => 'deposit', 'amount' => $lt, 'note' => $note, 'time' => current_time('mysql')];
                update_user_meta($user_id, '_lt_history', $history);
            }
        }
    }
}

add_action('manage_lt_order_posts_custom_column', 'ta_lt_order_columns_content', 10, 2);
add_filter('manage_lt_order_posts_columns', 'ta_lt_order_columns');
function ta_lt_order_columns($columns) {
    $columns['lt_user'] = 'Người dùng';
    $columns['lt_lt'] = 'Linh Thạch';
    $columns['lt_vnd'] = 'Số tiền';
    $columns['lt_status'] = 'Trạng thái';
    unset($columns['date']);
    return $columns;
}
function ta_lt_order_columns_content($column, $post_id) {
    if ($column === 'lt_user') {
        $uid = get_post_meta($post_id, '_order_user_id', true);
        $u = $uid ? get_userdata($uid) : null;
        echo $u ? esc_html($u->display_name) : 'N/A';
    }
    if ($column === 'lt_lt') {
        echo '💎' . number_format(intval(get_post_meta($post_id, '_order_lt', true)));
    }
    if ($column === 'lt_vnd') {
        echo number_format(intval(get_post_meta($post_id, '_order_vnd', true))) . '₫';
    }
    if ($column === 'lt_status') {
        $s = get_post_meta($post_id, '_order_status', true) ?: 'pending';
        $labels = ['pending' => '⏳ Chờ', 'completed' => '✅ Xong', 'cancelled' => '❌ Hủy'];
        echo $labels[$s] ?? $s;
    }
}

// Filter orders by user
add_action('restrict_manage_posts', 'ta_lt_order_user_filter');
function ta_lt_order_user_filter() {
    global $typenow;
    if ($typenow !== 'lt_order') return;
    $users = get_users(['number' => 50]);
    $selected = isset($_GET['user_id']) ? intval($_GET['user_id']) : '';
    echo '<select name="user_id"><option value="">Tất cả người dùng</option>';
    foreach ($users as $u) {
        printf('<option value="%d" %s>%s</option>', $u->ID, selected($selected, $u->ID, false), esc_html($u->display_name));
    }
    echo '</select>';
}
add_filter('parse_query', 'ta_lt_order_user_filter_query');
function ta_lt_order_user_filter_query($query) {
    global $pagenow, $typenow;
    if ($pagenow !== 'edit.php' || $typenow !== 'lt_order') return;
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $query->query_vars['meta_key'] = '_order_user_id';
        $query->query_vars['meta_value'] = intval($_GET['user_id']);
    }
}

// AJAX: Create LT order
add_action('wp_ajax_create_lt_order', 'ta_ajax_create_lt_order');
function ta_ajax_create_lt_order() {
    if (!is_user_logged_in()) wp_send_json_error('Vui lòng đăng nhập.');

    $package_id = sanitize_text_field($_POST['package_id'] ?? '');
    $packages = get_option('ta_lt_packages', []);
    $pkg = null;
    foreach ($packages as $p) {
        if ($p['id'] === $package_id) { $pkg = $p; break; }
    }
    if (!$pkg) wp_send_json_error('Gói nạp không hợp lệ.');

    $user_id = get_current_user_id();
    $lt = intval($pkg['lt']);
    $vnd = intval($pkg['vnd']);

    $order_code = 'LT' . date('ymd') . '-' . strtoupper(wp_generate_password(6, false));
    $order_id = wp_insert_post([
        'post_title' => $order_code,
        'post_type' => 'lt_order',
        'post_status' => 'publish',
        'post_author' => $user_id,
    ]);
    if (is_wp_error($order_id)) wp_send_json_error('Lỗi tạo đơn.');

    update_post_meta($order_id, '_order_user_id', $user_id);
    update_post_meta($order_id, '_order_lt', $lt);
    update_post_meta($order_id, '_order_vnd', $vnd);
    update_post_meta($order_id, '_order_status', 'pending');
    update_post_meta($order_id, '_order_package_id', $package_id);

    wp_send_json_success([
        'order_id' => $order_id,
        'order_code' => $order_code,
        'lt' => $lt,
        'vnd' => $vnd,
        'message' => 'Đã tạo đơn nạp ' . number_format($lt) . ' LT thành công!',
    ]);
}

// AJAX: Confirm payment (test mode — auto credit)
add_action('wp_ajax_confirm_lt_payment', 'ta_ajax_confirm_lt_payment');
function ta_ajax_confirm_lt_payment() {
    if (!is_user_logged_in()) wp_send_json_error('Vui lòng đăng nhập.');

    $order_id = intval($_POST['order_id'] ?? 0);
    if (!$order_id) wp_send_json_error('Mã đơn không hợp lệ.');

    $order = get_post($order_id);
    if (!$order || $order->post_type !== 'lt_order') wp_send_json_error('Đơn không tồn tại.');
    if (intval(get_post_meta($order_id, '_order_user_id', true)) !== get_current_user_id())
        wp_send_json_error('Không phải đơn của bạn.');

    $status = get_post_meta($order_id, '_order_status', true);
    if ($status !== 'pending') wp_send_json_error('Đơn này đã xử lý rồi.');

    // Test mode: auto-approve
    $lt = floatval(get_post_meta($order_id, '_order_lt', true));
    $user_id = get_current_user_id();
    $current = get_user_meta($user_id, '_linh_thach', true) ?: 0;
    update_user_meta($user_id, '_linh_thach', $current + $lt);
    update_post_meta($order_id, '_order_status', 'completed');

    $history = get_user_meta($user_id, '_lt_history', true) ?: [];
    $history[] = ['type' => 'deposit', 'amount' => $lt, 'note' => 'Nạp LT: ' . get_the_title($order_id), 'time' => current_time('mysql')];
    update_user_meta($user_id, '_lt_history', $history);

    wp_send_json_success([
        'message' => '💎 Nạp thành công! Bạn nhận được ' . number_format($lt) . ' Linh Thạch.',
        'new_balance' => $current + $lt,
    ]);
}

register_activation_hook(__FILE__, function() {
    ta_register_post_types();
    ta_register_lt_order();
    ta_add_roles();
    // Default packages
    if (!get_option('ta_lt_packages')) {
        update_option('ta_lt_packages', [
            ['id' => 'pkg_0', 'lt' => 1000, 'vnd' => 10000, 'bonus' => 0],
            ['id' => 'pkg_1', 'lt' => 5000, 'vnd' => 50000, 'bonus' => 10],
            ['id' => 'pkg_2', 'lt' => 10000, 'vnd' => 100000, 'bonus' => 20],
            ['id' => 'pkg_3', 'lt' => 50000, 'vnd' => 500000, 'bonus' => 50],
        ]);
    }
    flush_rewrite_rules();
});

// Expose roles to authenticated user via REST API
add_filter('wp_rest_prepare_user', function($response, $user, $request) {
    if (get_current_user_id() === $user->ID || current_user_can('list_users')) {
        $response->data['roles'] = $user->roles;
    }
    return $response;
}, 10, 3);
