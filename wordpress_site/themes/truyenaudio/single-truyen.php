<?php get_header(); the_post(); ?>
<?php
$views = get_post_meta(get_the_ID(), '_views', true) ?: 0;
$rating = get_post_meta(get_the_ID(), '_rating', true) ?: 0;
$rating_count = get_post_meta(get_the_ID(), '_rating_count', true) ?: 0;
$chapters = ta_get_chapters(get_the_ID());
$genres = wp_get_post_terms(get_the_ID(), 'the_loai');
$authors = wp_get_post_terms(get_the_ID(), 'tac_gia');
$statuses = wp_get_post_terms(get_the_ID(), 'trang_thai');
$user_rating = 0;
$is_bookmarked = false;
if (is_user_logged_in()) {
    $ratings = get_post_meta(get_the_ID(), '_user_ratings', true) ?: [];
    $user_rating = isset($ratings[get_current_user_id()]) ? $ratings[get_current_user_id()] : 0;
    $bookmarks = get_user_meta(get_current_user_id(), '_bookmarks', true) ?: [];
    $is_bookmarked = in_array(get_the_ID(), $bookmarks);
}
?>

<div class="container">
    <div class="story-detail-header">
        <div class="story-cover">
            <?php if (has_post_thumbnail()) the_post_thumbnail('large'); else echo '<div style="width:100%;aspect-ratio:3/4;background:#2a2a4e;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:60px;">📚</div>'; ?>
        </div>
        <div class="story-detail-info">
            <h1><?php the_title(); ?></h1>
            <div class="story-metas">
                <?php foreach ($genres as $g) echo '<span><a href="' . get_term_link($g) . '">' . $g->name . '</a></span>'; ?>
                <?php foreach ($authors as $a) echo '<span><a href="' . get_term_link($a) . '">✍ ' . $a->name . '</a></span>'; ?>
                <span>📖 <?php echo count($chapters); ?> chương</span>
                <span>👁 <?php echo ta_views($views); ?> lượt xem</span>
                <?php if (!empty($statuses)) echo '<span>' . $statuses[0]->name . '</span>'; ?>
                <?php if (get_post_meta(get_the_ID(), '_dao_linh_thach', true) === '1'): ?>
                    <span style="background:#e74c3c;">🔥 Đào Linh Thạch</span>
                <?php endif; ?>
            </div>

            <div class="rating-box" data-post-id="<?php the_ID(); ?>">
                Đánh giá:
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?php echo $i <= $user_rating ? 'active' : ''; ?>" data-rating="<?php echo $i; ?>">★</span>
                <?php endfor; ?>
                <span style="font-size:14px;color:#888;"><?php echo $rating; ?>/5 (<?php echo $rating_count; ?> đánh giá)</span>
            </div>

            <div class="story-description"><?php the_content(); ?></div>

            <div class="story-actions">
                <?php if (!empty($chapters)): ?>
                    <a href="<?php echo get_permalink($chapters[0]->ID); ?>" class="btn btn-primary">📖 Đọc từ đầu</a>
                <?php endif; ?>
                <button class="btn btn-outline bookmark-btn" data-post-id="<?php the_ID(); ?>">
                    <?php echo $is_bookmarked ? '★ Đã theo dõi' : '☆ Theo dõi'; ?>
                </button>
                <button class="btn btn-outline report-btn" onclick="document.getElementById('report-modal').style.display='flex'">
                    🚩 Báo cáo
                </button>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div id="report-modal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>🚩 Báo cáo truyện</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
            </div>
            <div class="modal-body">
                <p style="color:#888;font-size:14px;margin-bottom:15px;">Lý do bạn báo cáo truyện <strong><?php the_title(); ?></strong>?</p>
                <div class="report-reasons">
                    <label class="report-option">
                        <input type="radio" name="report_reason" value="spam" checked>
                        <span>Spam / Quảng cáo</span>
                    </label>
                    <label class="report-option">
                        <input type="radio" name="report_reason" value="inappropriate">
                        <span>Nội dung không phù hợp</span>
                    </label>
                    <label class="report-option">
                        <input type="radio" name="report_reason" value="copyright">
                        <span>Vi phạm bản quyền</span>
                    </label>
                    <label class="report-option">
                        <input type="radio" name="report_reason" value="wrong_category">
                        <span>Sai thể loại / mô tả</span>
                    </label>
                    <label class="report-option">
                        <input type="radio" name="report_reason" value="other">
                        <span>Khác</span>
                    </label>
                </div>
                <textarea id="report-details" rows="4" placeholder="Chi tiết thêm (không bắt buộc)..." style="width:100%;background:var(--input-bg);color:var(--text);border:1px solid var(--border);padding:10px 14px;border-radius:6px;font-size:14px;font-family:inherit;margin-top:15px;resize:vertical;"></textarea>
                <div id="report-msg" style="margin-top:10px;font-size:14px;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="document.getElementById('report-modal').style.display='none'">Hủy</button>
                <button class="btn btn-primary" id="report-submit" data-story-id="<?php the_ID(); ?>">Gửi báo cáo</button>
            </div>
        </div>
    </div>

    <!-- Chapter List -->
    <section class="section">
        <div class="chapter-list">
            <h3>Danh sách chương (<?php echo count($chapters); ?>)</h3>
            <?php if (empty($chapters)): ?>
                <p style="color:#888;text-align:center;padding:20px;">Chưa có chương nào.</p>
            <?php else: ?>
                <?php foreach ($chapters as $ch):
                    $is_vip = get_post_meta($ch->ID, '_is_vip', true);
                    $can_read = ta_can_read_chapter($ch->ID, get_the_ID());
                ?>
                <div class="chapter-item">
                    <a href="<?php echo get_permalink($ch->ID); ?>" class="<?php echo $can_read ? '' : 'locked'; ?>">
                        <?php if (!$can_read): ?>🔒 <?php endif; ?>
                        Chương <?php echo get_post_meta($ch->ID, '_chapter_number', true) ?: ''; ?>: <?php echo $ch->post_title; ?>
                        <?php if ($is_vip): ?><span class="vip-badge">VIP</span><?php endif; ?>
                    </a>
                    <span class="chapter-meta"><?php echo get_the_date('d/m/Y', $ch->ID); ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
jQuery(function($) {
    // Rating
    $('.rating-box .star').on('click', function() {
        var rating = $(this).data('rating');
        var post_id = $('.rating-box').data('post-id');
        var $box = $(this).closest('.rating-box');
        $.post(ta_ajax.ajax_url, {
            action: 'rate_story',
            post_id: post_id,
            rating: rating
        }, function(res) {
            $box.find('.star').each(function() {
                $(this).toggleClass('active', $(this).data('rating') <= res.rating);
            });
            $box.find('span:last').text(res.rating + '/5 (' + res.count + ' đánh giá)');
            ta_toast('Cảm ơn bạn đã đánh giá!', 'success');
        }).fail(function() {
            ta_toast('Đánh giá thất bại, vui lòng thử lại.', 'error');
        });
    });

    // Bookmark
    $('.bookmark-btn').on('click', function() {
        var $btn = $(this);
        $.post(ta_ajax.ajax_url, {
            action: 'toggle_bookmark',
            post_id: $btn.data('post-id')
        }, function(res) {
            $btn.text(res.status === 'added' ? '★ Đã theo dõi' : '☆ Theo dõi');
            ta_toast(res.status === 'added' ? 'Đã thêm vào danh sách theo dõi!' : 'Đã bỏ theo dõi!', 'success');
        }).fail(function() {
            ta_toast('Có lỗi xảy ra, vui lòng thử lại.', 'error');
        });
    });

    // Report
    $('#report-submit').on('click', function() {
        var $btn = $(this);
        var $msg = $('#report-msg');
        var story_id = $btn.data('story-id');
        var reason = $('input[name="report_reason"]:checked').val();
        var details = $('#report-details').val();

        $btn.prop('disabled', true).text('Đang gửi...');
        $msg.html('');

        $.ajax({
            type: 'POST',
            url: ta_ajax.ajax_url || ajaxurl,
            data: {
                action: 'ta_report_story',
                story_id: story_id,
                reason: reason,
                details: details
            },
            success: function(res) {
                if (res.success) {
                    $msg.html('<span style="color:#2ecc71;">✅ ' + res.data.message + '</span>');
                    setTimeout(function() {
                        document.getElementById('report-modal').style.display = 'none';
                    }, 2000);
                } else {
                    $msg.html('<span style="color:#e74c3c;">❌ ' + res.data + '</span>');
                }
            },
            error: function() {
                $msg.html('<span style="color:#e74c3c;">❌ Lỗi kết nối, vui lòng thử lại.</span>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Gửi báo cáo');
            }
        });
    });
});
</script>

<style>
.modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center; }
.modal-box { background:var(--bg-card);border-radius:12px;width:90%;max-width:500px;max-height:90vh;overflow-y:auto; }
.modal-header { display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid var(--border); }
.modal-header h3 { margin:0;font-size:18px;color:#f0c040; }
.modal-close { background:none;border:none;color:#888;font-size:28px;cursor:pointer;line-height:1;padding:0; }
.modal-close:hover { color:#fff; }
.modal-body { padding:20px 24px; }
.modal-footer { display:flex;gap:10px;justify-content:flex-end;padding:15px 24px;border-top:1px solid var(--border); }
.report-reasons { display:grid;gap:10px; }
.report-option { display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--input-bg);border:1px solid var(--border);border-radius:6px;cursor:pointer;font-size:14px;transition:border-color 0.2s; }
.report-option:hover { border-color:var(--accent); }
.report-option input[type="radio"] { accent-color:#f0c040; }
</style>

<?php get_footer(); ?>
