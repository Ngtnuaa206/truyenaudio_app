<?php /* Template Name: Tác giả Dashboard */
ta_require_role(['tac_gia_role', 'administrator']);
get_header();

// Handle form submission
$edit_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ta_save_story'])) {
    $post_id = intval($_POST['story_id'] ?? 0);
    $title = sanitize_text_field($_POST['story_title'] ?? '');
    $content = wp_kses_post($_POST['story_content'] ?? '');
    $excerpt = sanitize_textarea_field($_POST['story_excerpt'] ?? '');
    $pen_name = sanitize_text_field($_POST['pen_name'] ?? '');
    $thumb_url = esc_url_raw($_POST['thumb_url'] ?? '');

    if (empty($title)) {
        $edit_msg = ['type' => 'error', 'text' => 'Vui lòng nhập tiêu đề truyện.'];
    } else {
        $post_data = [
            'post_title' => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_type' => 'truyen',
            'post_status' => 'publish',
        ];

        if ($post_id > 0) {
            $post_data['ID'] = $post_id;
            $updated = wp_update_post($post_data);
            if (is_wp_error($updated)) {
                $edit_msg = ['type' => 'error', 'text' => 'Lỗi cập nhật: ' . $updated->get_error_message()];
            } else {
                $post_id = $updated;
                $edit_msg = ['type' => 'success', 'text' => 'Đã cập nhật truyện thành công!'];
            }
        } else {
            $post_data['post_author'] = get_current_user_id();
            $inserted = wp_insert_post($post_data);
            if (is_wp_error($inserted)) {
                $edit_msg = ['type' => 'error', 'text' => 'Lỗi đăng: ' . $inserted->get_error_message()];
            } else {
                $post_id = $inserted;
                $edit_msg = ['type' => 'success', 'text' => 'Đã đăng truyện thành công!'];
            }
        }

        // Save meta
        if ($post_id > 0) {
            // Thumbnail
            if (!empty($thumb_url)) {
                $attach_id = attachment_url_to_postid($thumb_url);
                if ($attach_id) set_post_thumbnail($post_id, $attach_id);
            }
            // Pen name
            if (!empty($pen_name)) {
                update_post_meta($post_id, '_pen_name', $pen_name);
                $term = term_exists($pen_name, 'tac_gia');
                if (!$term) $term = wp_insert_term($pen_name, 'tac_gia');
                if (!is_wp_error($term)) {
                    wp_set_post_terms($post_id, [intval($term['term_id'])], 'tac_gia', true);
                }
            }
            // Genres (existing checkboxes)
            $assigned_genre_ids = isset($_POST['the_loai']) ? array_map('intval', $_POST['the_loai']) : [];

            // New genre request
            if (!empty($_POST['new_genres'])) {
                $lines = array_filter(array_map('trim', explode("\n", sanitize_textarea_field($_POST['new_genres']))));
                $submitted = 0;
                $auto_added = 0;
                foreach ($lines as $line) {
                    if (empty($line)) continue;
                    $result = ta_submit_new_genre($line, get_current_user_id());
                    if ($result === true) {
                        $submitted++;
                    } elseif ($result === 'exists') {
                        // Thể loại đã tồn tại → tự gán vào truyện
                        $existing_term = term_exists($line, 'the_loai');
                        if ($existing_term && !is_wp_error($existing_term)) {
                            $assigned_genre_ids[] = intval($existing_term['term_id']);
                            $auto_added++;
                        }
                    }
                }
                if ($submitted > 0 && $auto_added > 0) {
                    $edit_msg = ['type' => 'success', 'text' => "Đã gửi {$submitted} thể loại mới duyệt. Đã tự thêm {$auto_added} thể loại có sẵn vào truyện."];
                } elseif ($submitted > 0) {
                    $edit_msg = ['type' => 'success', 'text' => "Đã gửi {$submitted} thể loại mới để admin duyệt!"];
                } elseif ($auto_added > 0) {
                    $edit_msg = ['type' => 'success', 'text' => "Đã tự thêm {$auto_added} thể loại có sẵn vào truyện."];
                }
            }

            // Gán tất cả thể loại vào truyện
            if (!empty($assigned_genre_ids)) {
                wp_set_post_terms($post_id, array_unique($assigned_genre_ids), 'the_loai');
            }
            // Status
            if (isset($_POST['trang_thai'])) {
                wp_set_post_terms($post_id, [intval($_POST['trang_thai'])], 'trang_thai');
            }
            // Đào Linh Thạch
            $dao = isset($_POST['dao_linh_thach']) ? '1' : '0';
            update_post_meta($post_id, '_dao_linh_thach', $dao);
            $free = intval($_POST['free_chapters'] ?? 2);
            update_post_meta($post_id, '_free_chapters', max(1, min(100, $free)));
            $price = intval($_POST['dao_price'] ?? 3);
            update_post_meta($post_id, '_dao_price', max(1, min(100, $price)));
        }
    }
}

// Handle chapter save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ta_save_chapter'])) {
    $chapter_id = intval($_POST['chapter_id'] ?? 0);
    $story_id = intval($_POST['story_id'] ?? 0);
    $chapter_title = sanitize_text_field($_POST['chapter_title'] ?? '');
    $chapter_content = wp_kses_post($_POST['chapter_content'] ?? '');
    $chapter_number = intval($_POST['chapter_number'] ?? 0);

    if ($story_id && $chapter_title) {
        $post_data = [
            'post_title' => $chapter_title,
            'post_content' => $chapter_content,
            'post_type' => 'chapter',
            'post_status' => 'publish',
            'menu_order' => $chapter_number,
        ];
        if ($chapter_id > 0) {
            $post_data['ID'] = $chapter_id;
            wp_update_post($post_data);
            $edit_msg = ['type' => 'success', 'text' => 'Đã cập nhật chương!'];
        } else {
            $post_data['post_author'] = get_current_user_id();
            $post_data['post_parent'] = $story_id;
            $new_id = wp_insert_post($post_data);
            if ($new_id && !is_wp_error($new_id)) {
                update_post_meta($new_id, '_story_id', $story_id);
                $edit_msg = ['type' => 'success', 'text' => 'Đã thêm chương mới!'];
            }
        }
    } else {
        $edit_msg = ['type' => 'error', 'text' => 'Vui lòng nhập tiêu đề chương.'];
    }
}

// Check editing mode
$editing = false;
$edit_story = null;
$edit_id = isset($_GET['story_id']) ? intval($_GET['story_id']) : 0;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && $edit_id > 0) {
    $edit_story = get_post($edit_id);
    if ($edit_story && ($edit_story->post_author == get_current_user_id() || current_user_can('administrator'))) {
        $editing = true;
    } else {
        $edit_msg = ['type' => 'error', 'text' => 'Bạn không có quyền sửa truyện này.'];
    }
}

$user = wp_get_current_user();
$stories = get_posts([
    'post_type' => 'truyen',
    'author' => $user->ID,
    'numberposts' => -1,
]);

$total_views = 0;
$total_chapters = 0;
foreach ($stories as $s) {
    $total_views += get_post_meta($s->ID, '_views', true) ?: 0;
    $total_chapters += count(ta_get_chapters($s->ID));
}

$earnings = get_user_meta($user->ID, '_author_earnings', true) ?: 0;
$withdrawn = get_user_meta($user->ID, '_author_withdrawn', true) ?: 0;
$available = $earnings - $withdrawn;
$rate = get_option('ta_revenue_rate', 15);
$lt_vnd = get_option('ta_lt_to_vnd', 1000);

// Get all genres & statuses for the form
$all_genres = get_terms(['taxonomy' => 'the_loai', 'hide_empty' => false]);
$all_statuses = get_terms(['taxonomy' => 'trang_thai', 'hide_empty' => false]);
?>

<div class="container" style="padding:40px 15px;">
    <?php if ($edit_msg): ?>
    <div class="flash flash-<?php echo $edit_msg['type']; ?>">
        <?php echo $edit_msg['text']; ?>
        <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php endif; ?>

    <?php if ($editing || isset($_GET['action']) && $_GET['action'] === 'new'): ?>
        <?php
        $is_new = !$editing;
        $s = $editing ? $edit_story : null;
        $s_id = $s ? $s->ID : 0;
        $s_title = $s ? $s->post_title : '';
        $s_content = $s ? preg_replace('/<!--\s*\/?wp:[^>]*-->/', '', $s->post_content) : '';
        $s_excerpt = $s ? $s->post_excerpt : '';
        $s_pen = $s ? get_post_meta($s->ID, '_pen_name', true) : '';
        $s_thumb = $s && has_post_thumbnail($s->ID) ? get_the_post_thumbnail_url($s->ID) : '';
        $s_genres = $s ? wp_get_post_terms($s->ID, 'the_loai', ['fields' => 'ids']) : [];
        $s_status = $s ? wp_get_post_terms($s->ID, 'trang_thai', ['fields' => 'ids']) : [];
        $s_dao = $s ? get_post_meta($s->ID, '_dao_linh_thach', true) : '';
        $s_free = $s ? (get_post_meta($s->ID, '_free_chapters', true) ?: 2) : 2;
        $s_price = $s ? (get_post_meta($s->ID, '_dao_price', true) ?: 3) : 3;
        ?>

    <div class="profile-card">
        <h3><?php echo $is_new ? '📝 Đăng truyện mới' : '✏️ Sửa truyện'; ?></h3>
        <form method="post" style="margin-top:20px;">
            <input type="hidden" name="story_id" value="<?php echo $s_id; ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Tiêu đề truyện *</label>
                    <input type="text" name="story_title" value="<?php echo esc_attr($s_title); ?>" required style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;font-size:14px;">
                </div>

                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Mô tả truyện</label>
                    <textarea name="story_content" rows="8" style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;font-size:14px;font-family:inherit;"><?php echo esc_textarea($s_content); ?></textarea>
                </div>

                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Trích dẫn ngắn (excerpt)</label>
                    <textarea name="story_excerpt" rows="3" style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;font-size:14px;font-family:inherit;"><?php echo esc_textarea($s_excerpt); ?></textarea>
                </div>

                <div>
                    <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Ảnh bìa (URL)</label>
                    <input type="url" name="thumb_url" value="<?php echo esc_attr($s_thumb); ?>" placeholder="https://..." style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;font-size:14px;">
                </div>

                <div>
                    <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Bút danh</label>
                    <input type="text" name="pen_name" value="<?php echo esc_attr($s_pen); ?>" placeholder="Tên tác giả hiển thị" style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;font-size:14px;">
                </div>

                <div>
                    <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Thể loại</label>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;padding:8px 0;">
                        <?php foreach ($all_genres as $g): ?>
                        <label style="font-size:13px;color:#ccc;display:flex;align-items:center;gap:4px;">
                            <input type="checkbox" name="the_loai[]" value="<?php echo $g->term_id; ?>" <?php echo in_array($g->term_id, $s_genres) ? 'checked' : ''; ?>>
                            <?php echo $g->name; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top:8px;">
                        <textarea name="new_genres" rows="2" placeholder="Thêm thể loại mới (mỗi dòng 1 thể loại)&#10;VD: Tiên hiệp&#10;Huyền huyễn" style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:8px 12px;border-radius:6px;font-size:13px;resize:vertical;"></textarea>
                        <span style="color:#888;font-size:11px;">Gửi admin duyệt (phân tách bằng dòng mới)</span>
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Trạng thái</label>
                    <select name="trang_thai" style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;font-size:14px;">
                        <option value="">-- Chọn --</option>
                        <?php foreach ($all_statuses as $st): ?>
                        <option value="<?php echo $st->term_id; ?>" <?php echo in_array($st->term_id, $s_status) ? 'selected' : ''; ?>><?php echo $st->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="grid-column:1/-1;border-top:1px solid #2a2a4e;padding-top:20px;margin-top:10px;">
                    <h4 style="color:#f0c040;margin-bottom:15px;">🔥 Cài đặt Đào Linh Thạch</h4>

                    <label style="display:flex;align-items:center;gap:8px;font-size:14px;color:#ccc;margin-bottom:15px;">
                        <input type="checkbox" name="dao_linh_thach" value="1" <?php checked($s_dao, '1'); ?>>
                        Bật chế độ Đào Linh Thạch — người dùng trả 💎 để đọc chương
                    </label>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div>
                            <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">🔓 Số chương miễn phí đầu</label>
                            <input type="number" name="free_chapters" value="<?php echo $s_free; ?>" min="1" max="100" style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;font-size:14px;">
                        </div>
                        <div>
                            <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">💎 Giá mỗi chương</label>
                            <input type="number" name="dao_price" value="<?php echo $s_price; ?>" min="1" max="100" style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;font-size:14px;">
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top:25px;display:flex;gap:10px;">
                <button type="submit" name="ta_save_story" class="btn btn-primary"><?php echo $is_new ? 'Đăng truyện' : 'Lưu thay đổi'; ?></button>
                <a href="<?php echo home_url('/tac-gia-dashboard'); ?>" class="btn btn-outline">Hủy</a>
            </div>
        </form>
    </div>

    <?php if (!$is_new):
        $chapters = ta_get_chapters($s_id);
        $edit_chapter_id = isset($_GET['chapter_id']) ? $_GET['chapter_id'] : 0;
        $edit_chapter = null;
        if ($edit_chapter_id > 0 && $edit_chapter_id !== 'new') {
            foreach ($chapters as $c) {
                if ($c->ID == $edit_chapter_id) { $edit_chapter = $c; break; }
            }
        }
        $max_order = $chapters ? max(array_map(function($c) { return $c->menu_order; }, $chapters)) : 0;
    ?>
    <div class="profile-card" style="margin-top:20px;">
        <h3>📖 Chương truyện</h3>
        <div style="overflow-x:auto;margin-top:15px;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border);">
                        <th style="padding:10px;text-align:left;color:#888;font-size:12px;">Số</th>
                        <th style="padding:10px;text-align:left;color:#888;font-size:12px;">Tiêu đề</th>
                        <th style="padding:10px;text-align:center;color:#888;font-size:12px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chapters as $c): ?>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px;color:#888;font-size:13px;"><?php echo $c->menu_order ?: '-'; ?></td>
                        <td style="padding:10px;"><a href="<?php echo get_permalink($c->ID); ?>" style="color:var(--text);"><?php echo $c->post_title; ?></a></td>
                        <td style="padding:10px;text-align:center;">
                            <a href="<?php echo home_url('/tac-gia-dashboard?action=edit&story_id=' . $s_id . '&chapter_id=' . $c->ID); ?>" class="btn btn-sm btn-outline">Sửa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top:15px;">
            <?php if ($edit_chapter || $edit_chapter_id === 'new'):
                $is_new_chapter = $edit_chapter_id === 'new';
                $ec = $is_new_chapter ? null : $edit_chapter;
            ?>
            <form method="post" style="border-top:1px solid var(--border);padding-top:20px;margin-top:15px;">
                <h4 style="color:#f0c040;margin-bottom:15px;"><?php echo $is_new_chapter ? '📝 Thêm chương mới' : '✏️ Sửa chương: ' . $ec->post_title; ?></h4>
                <input type="hidden" name="chapter_id" value="<?php echo $ec ? $ec->ID : 0; ?>">
                <input type="hidden" name="story_id" value="<?php echo $s_id; ?>">
                <div style="display:grid;gap:15px;">
                    <div>
                        <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Số chương</label>
                        <input type="number" name="chapter_number" value="<?php echo $ec ? $ec->menu_order : ($max_order + 1); ?>" min="1" style="width:100px;background:var(--input-bg);color:var(--text);border:1px solid var(--border);padding:10px 14px;border-radius:6px;font-size:14px;">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Tiêu đề chương</label>
                        <input type="text" name="chapter_title" value="<?php echo $ec ? esc_attr($ec->post_title) : ''; ?>" required style="width:100%;background:var(--input-bg);color:var(--text);border:1px solid var(--border);padding:10px 14px;border-radius:6px;font-size:14px;">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Nội dung chương</label>
                        <div class="editor-toolbar">
                            <button type="button" class="editor-btn" data-cmd="bold" title="In đậm"><b>B</b></button>
                            <button type="button" class="editor-btn" data-cmd="italic" title="In nghiêng"><i>I</i></button>
                            <button type="button" class="editor-btn" data-cmd="underline" title="Gạch chân"><u>U</u></button>
                            <span class="editor-sep"></span>
                            <button type="button" class="editor-btn" data-cmd="formatBlock" data-val="h3" title="Tiêu đề"><b>H</b></button>
                            <button type="button" class="editor-btn" data-cmd="formatBlock" data-val="blockquote" title="Trích dẫn">❝</button>
                            <span class="editor-sep"></span>
                            <button type="button" class="editor-btn" data-cmd="removeFormat" title="Xóa định dạng">↺</button>
                            <span class="editor-sep"></span>
                            <button type="button" class="editor-btn" data-cmd="insertUnorderedList" title="Danh sách">•</button>
                            <button type="button" class="editor-btn" data-cmd="insertOrderedList" title="Danh sách số">1.</button>
                        </div>
                        <div class="editor-content" id="chapter-content" contenteditable="true"><?php
$existing_content = $ec ? $ec->post_content : '';
echo $existing_content ? wp_kses_post($existing_content) : '<p>&emsp;</p>';
?></div>
                        <input type="hidden" name="chapter_content" id="chapter-content-hidden" value="">
                        <p style="color:#888;font-size:12px;margin-top:4px;">⏎ Xuống dòng = tự động xuống đoạn mới. Dùng toolbar để bôi đậm, nghiêng...</p>
                    </div>
                    <div>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#888;">
                            <input type="checkbox" id="auto-format" checked>
                            Tự động căn chỉnh (viết hoa đầu đoạn, sau dấu chấm, thụt đầu dòng)
                        </label>
                    </div>
                </div>
                <div style="margin-top:20px;display:flex;gap:10px;">
                    <button type="submit" name="ta_save_chapter" class="btn btn-primary"><?php echo $is_new_chapter ? 'Thêm chương' : 'Lưu chương'; ?></button>
                    <a href="<?php echo home_url('/tac-gia-dashboard?action=edit&story_id=' . $s_id); ?>" class="btn btn-outline">← Về danh sách</a>
                </div>
            </form>
            <?php else: ?>
            <a href="<?php echo home_url('/tac-gia-dashboard?action=edit&story_id=' . $s_id . '&chapter_id=new'); ?>" class="btn btn-primary">+ Thêm chương mới</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;margin-bottom:30px;">
        <h1 style="color:#fff;">✍️ Dashboard Tác giả</h1>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?php echo home_url('/tac-gia-dashboard?action=new'); ?>" class="btn btn-primary">+ Đăng truyện mới</a>
            <a href="<?php echo home_url('/rut-linh-thach'); ?>" class="btn btn-outline">💎 Rút Linh Thạch</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;">
        <div class="profile-card" style="text-align:center;">
            <div style="font-size:36px;margin-bottom:10px;">📚</div>
            <div style="font-size:28px;font-weight:700;color:#f0c040;"><?php echo count($stories); ?></div>
            <div style="color:#888;font-size:13px;">Truyện đã đăng</div>
        </div>
        <div class="profile-card" style="text-align:center;">
            <div style="font-size:36px;margin-bottom:10px;">📖</div>
            <div style="font-size:28px;font-weight:700;color:#f0c040;"><?php echo $total_chapters; ?></div>
            <div style="color:#888;font-size:13px;">Tổng số chương</div>
        </div>
        <div class="profile-card" style="text-align:center;">
            <div style="font-size:36px;margin-bottom:10px;">👁</div>
            <div style="font-size:28px;font-weight:700;color:#f0c040;"><?php echo number_format($total_views); ?></div>
            <div style="color:#888;font-size:13px;">Lượt xem</div>
        </div>
    </div>

    <!-- Earnings -->
    <div class="profile-card">
        <h3>💎 Thu nhập của bạn</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:20px;margin-top:15px;">
            <div>
                <div style="color:#888;font-size:12px;">Tổng thu nhập</div>
                <div style="font-size:24px;font-weight:700;color:#2ecc71;">💎<?php echo number_format($earnings); ?></div>
            </div>
            <div>
                <div style="color:#888;font-size:12px;">Đã rút</div>
                <div style="font-size:24px;font-weight:700;color:#e74c3c;">💎<?php echo number_format($withdrawn); ?></div>
            </div>
            <div>
                <div style="color:#888;font-size:12px;">Có thể rút</div>
                <div style="font-size:24px;font-weight:700;color:#f0c040;">💎<?php echo number_format($available); ?></div>
            </div>
            <div>
                <div style="color:#888;font-size:12px;">Tương đương (VNĐ)</div>
                <div style="font-size:24px;font-weight:700;color:#fff;"><?php echo number_format($available * $lt_vnd); ?>₫</div>
            </div>
        </div>
        <div style="margin-top:15px;padding:15px;background:#0f0f1a;border-radius:8px;">
            <p style="color:#888;font-size:13px;">📊 Bạn nhận <strong style="color:#f0c040;"><?php echo $rate; ?>%</strong> hoa hồng từ doanh thu VIP chapter của truyện</p>
        </div>
    </div>

    <!-- Story List -->
    <div class="profile-card">
        <h3>📚 Truyện của bạn</h3>
        <?php if (empty($stories)): ?>
            <p style="text-align:center;padding:40px;color:#888;">
                Bạn chưa đăng truyện nào.
                <a href="<?php echo home_url('/tac-gia-dashboard?action=new'); ?>" style="display:block;margin-top:10px;">Đăng truyện đầu tiên →</a>
            </p>
        <?php else: ?>
        <div style="overflow-x:auto;margin-top:15px;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #2a2a4e;">
                        <th style="padding:10px;text-align:left;color:#888;font-size:12px;">Truyện</th>
                        <th style="padding:10px;text-align:center;color:#888;font-size:12px;">Chương</th>
                        <th style="padding:10px;text-align:center;color:#888;font-size:12px;">Lượt xem</th>
                        <th style="padding:10px;text-align:center;color:#888;font-size:12px;">Hoa hồng</th>
                        <th style="padding:10px;text-align:center;color:#888;font-size:12px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stories as $s):
                        $views = get_post_meta($s->ID, '_views', true) ?: 0;
                        $revenue = get_post_meta($s->ID, '_story_revenue', true) ?: 0;
                        $chs = ta_get_chapters($s->ID);
                        $commission = round(($revenue * $rate) / 100);
                        $is_dao = get_post_meta($s->ID, '_dao_linh_thach', true) === '1';
                    ?>
                    <tr style="border-bottom:1px solid #2a2a4e;">
                        <td style="padding:10px;">
                            <a href="<?php echo get_permalink($s->ID); ?>" style="color:#fff;"><?php echo $s->post_title; ?></a>
                            <?php if ($is_dao): ?><span style="font-size:11px;color:#e74c3c;"> 🔥</span><?php endif; ?>
                        </td>
                        <td style="padding:10px;text-align:center;color:#888;"><?php echo count($chs); ?></td>
                        <td style="padding:10px;text-align:center;color:#f0c040;"><?php echo number_format($views); ?></td>
                        <td style="padding:10px;text-align:center;color:#f0c040;">💎<?php echo number_format($commission); ?></td>
                        <td style="padding:10px;text-align:center;">
                            <a href="<?php echo home_url('/tac-gia-dashboard?action=edit&story_id=' . $s->ID); ?>" class="btn btn-sm btn-outline">Sửa</a>
                            <a href="<?php echo admin_url('post-new.php?post_type=chapter'); ?>" class="btn btn-sm btn-primary">+ Chương</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Withdrawal History -->
    <?php
    $requests = get_user_meta($user->ID, '_withdrawal_requests', true) ?: [];
    if (!empty($requests)):
    ?>
    <div class="profile-card">
        <h3>📋 Lịch sử rút tiền</h3>
        <div style="overflow-x:auto;margin-top:15px;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #2a2a4e;">
                        <th style="padding:10px;text-align:left;color:#888;font-size:12px;">Ngày</th>
                        <th style="padding:10px;text-align:center;color:#888;font-size:12px;">Số lượng</th>
                        <th style="padding:10px;text-align:center;color:#888;font-size:12px;">Phương thức</th>
                        <th style="padding:10px;text-align:center;color:#888;font-size:12px;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($requests) as $r): ?>
                    <tr style="border-bottom:1px solid #2a2a4e;">
                        <td style="padding:10px;color:#888;font-size:13px;"><?php echo $r['time']; ?></td>
                        <td style="padding:10px;text-align:center;color:#f0c040;">💎<?php echo number_format($r['amount']); ?></td>
                        <td style="padding:10px;text-align:center;color:#888;font-size:13px;"><?php echo $r['method']; ?></td>
                        <td style="padding:10px;text-align:center;">
                            <?php if ($r['status'] == 'pending'): ?>
                                <span style="color:#f39c12;">⏳ Chờ duyệt</span>
                            <?php elseif ($r['status'] == 'approved'): ?>
                                <span style="color:#2ecc71;">✅ Đã duyệt</span>
                            <?php else: ?>
                                <span style="color:#e74c3c;">❌ Từ chối</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<style>
.editor-toolbar { display:flex;gap:4px;align-items:center;padding:6px 10px;background:var(--bg-card);border:1px solid var(--border);border-bottom:none;border-radius:6px 6px 0 0;flex-wrap:wrap; }
.editor-btn { background:transparent;border:none;color:var(--text);padding:4px 10px;border-radius:4px;cursor:pointer;font-size:14px;line-height:1; }
.editor-btn:hover { background:var(--border); }
.editor-btn.active { background:var(--accent);color:#fff; }
.editor-sep { width:1px;height:20px;background:var(--border);margin:0 4px; }
.editor-content { width:100%;min-height:300px;background:var(--input-bg);color:var(--text);border:1px solid var(--border);padding:10px 14px;border-radius:0 0 6px 6px;font-size:15px;line-height:1.8;outline:none;overflow-y:auto; }
.editor-content p { margin:0 0 8px 0; }
.editor-content:focus { border-color:var(--accent); }
</style>
<script>
jQuery(function($) {
    var editor = document.getElementById('chapter-content');
    if (!editor) return;

    // Enter key creates <p> consistently
    document.execCommand('defaultParagraphSeparator', false, 'p');

    // Toolbar buttons - execCommand for WYSIWYG
    $('.editor-btn').on('click', function() {
        var btn = $(this);
        var cmd = btn.data('cmd');
        var val = btn.data('val') || null;
        editor.focus();
        document.execCommand(cmd, false, val);
        btn.blur();
    });

    // Track bold/italic/underline state
    function updateToolbar() {
        $('.editor-btn[data-cmd]').each(function() {
            var cmd = $(this).data('cmd');
            if (['bold','italic','underline'].indexOf(cmd) >= 0) {
                $(this).toggleClass('active', document.queryCommandState(cmd));
            }
        });
    }
    editor.addEventListener('mouseup', updateToolbar);
    editor.addEventListener('keyup', updateToolbar);

    // Paste: strip unwanted formatting
    editor.addEventListener('paste', function(e) {
        e.preventDefault();
        var text = (e.clipboardData || window.clipboardData).getData('text/plain');
        document.execCommand('insertText', false, text);
    });

    // Auto-format on submit
    $('form').has('#chapter-content').on('submit', function() {
        if (document.getElementById('auto-format').checked) {
            // Walk block children and format text, preserving inline tags
            var blocks = editor.querySelectorAll('p, div, h1, h2, h3, h4, h5, h6, blockquote');
            if (!blocks.length) {
                // Wrap entire content in a div for processing
                var wrapper = document.createElement('div');
                wrapper.innerHTML = editor.innerHTML;
                editor.innerHTML = '<p>' + wrapper.innerHTML.replace(/<br\s*\/?>/gi, '</p><p>') + '</p>';
                blocks = editor.querySelectorAll('p');
            }
            for (var b = 0; b < blocks.length; b++) {
                var block = blocks[b];
                // Walk text nodes only
                var walker = document.createTreeWalker(block, 4, null, false);
                var nodes = [];
                while (walker.nextNode()) nodes.push(walker.currentNode);
                for (var n = 0; n < nodes.length; n++) {
                    var node = nodes[n];
                    var txt = node.textContent;
                    if (!txt.trim()) continue;

                    // Capitalize first letter of paragraph
                    if (n === 0) {
                        txt = txt.charAt(0).toUpperCase() + txt.slice(1);
                    }

                    // Capitalize after . ? !
                    txt = txt.replace(/([.?!])\s*(\w)/g, function(m, p1, p2) {
                        return p1 + ' ' + p2.toUpperCase();
                    });

                    // Fix spacing
                    txt = txt.replace(/\s+,/g, ',');
                    txt = txt.replace(/\s*;\s*/g, '; ');
                    txt = txt.replace(/\s*:\s*/g, ': ');
                    txt = txt.replace(/\s{2,}/g, ' ');

                    node.textContent = txt;
                }
                // Add indent to first text node of block
                var indentWalker = document.createTreeWalker(block, 4, null, false);
                var firstTextNode = indentWalker.nextNode();
                if (firstTextNode) {
                    var trimmed = firstTextNode.textContent.replace(/^[\s\u00a0]+/, '');
                    firstTextNode.textContent = '\u00a0\u00a0\u00a0\u00a0' + trimmed;
                }
            }
        }

        // Copy innerHTML into hidden input
        document.getElementById('chapter-content-hidden').value = editor.innerHTML;
    });
});
</script>
<?php get_footer(); ?>
