<?php get_header(); the_post(); ?>
<?php
$views = get_post_meta(get_the_ID(), '_views', true) ?: 0;
$rating = get_post_meta(get_the_ID(), '_rating', true) ?: 0;
$rating_count = get_post_meta(get_the_ID(), '_rating_count', true) ?: 0;
$chapters = ta_get_chapters(get_the_ID());
$genres = wp_get_post_terms(get_the_ID(), 'the_loai');
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
    <div class="content-layout">
        <div class="main-content">
            <!-- Story Hero Section -->
            <div class="story-hero">
                <div class="story-hero-inner">
                    <div class="story-hero-cover">
                        <?php if (has_post_thumbnail()) the_post_thumbnail('large'); else echo '<div style="width:100%;aspect-ratio:3/4;background:#2a2a4e;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:60px;">📚</div>'; ?>
                    </div>
                    <div class="story-hero-info">
                        <h1><?php the_title(); ?></h1>
                        
                        <div class="story-hero-meta">
                            <?php foreach ($genres as $g) echo '<span class="meta-tag"><a href="' . get_term_link($g) . '">' . $g->name . '</a></span>'; ?>
                            <span class="meta-tag">📖 <?php echo count($chapters); ?> chương</span>
                            <span class="meta-tag">👁 <?php echo ta_views($views); ?> lượt xem</span>
                            <?php if (!empty($statuses)) echo '<span class="meta-tag">' . $statuses[0]->name . '</span>'; ?>
                            <?php if (get_post_meta(get_the_ID(), '_dao_linh_thach', true) === '1'): ?>
                                <span class="meta-tag" style="background:#e74c3c;color:#fff;">🔥 Đào Linh Thạch</span>
                            <?php endif; ?>
                        </div>

                        <div class="story-hero-rating" data-post-id="<?php the_ID(); ?>">
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $user_rating ? 'active' : ''; ?>" data-rating="<?php echo $i; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text"><?php echo $rating; ?>/5 (<?php echo $rating_count; ?> đánh giá)</span>
                        </div>

                        <div class="story-hero-actions">
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
            </div>

            <!-- Story Description -->
            <div class="story-description">
                <h3>📖 Giới thiệu</h3>
                <div class="desc-text"><?php the_content(); ?></div>
            </div>

            <!-- Report Modal -->
            <div id="report-modal" class="modal-overlay">
                <div class="modal-box">
                    <div class="modal-header">
                        <h3>🚩 Báo cáo truyện</h3>
                        <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p style="color:var(--text-muted);font-size:14px;margin-bottom:15px;">Lý do bạn báo cáo truyện <strong><?php the_title(); ?></strong>?</p>
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
        </div>

        <!-- Sidebar (widgets only) -->
        <div class="sidebar">
            <?php get_sidebar(); ?>
        </div>

        <!-- Chapter List (CSS Grid places it in sidebar column at >1024, full width below at 768-1024) -->
        <div class="chapter-list-section sidebar-widget chapter-list-grid">
            <div class="chapter-list-header">
                <h3>Danh sách chương</h3>
                <span class="chapter-count"><?php echo count($chapters); ?> chương</span>
            </div>
            <div class="chapter-list-scroll">
                <?php if (empty($chapters)): ?>
                    <p style="color:var(--text-muted);text-align:center;padding:40px 16px;">Chưa có chương nào.</p>
                <?php else: ?>
                    <?php foreach ($chapters as $ch):
                        $is_vip = get_post_meta($ch->ID, '_is_vip', true);
                        $can_read = ta_can_read_chapter($ch->ID, get_the_ID());
                        $chapter_num = get_post_meta($ch->ID, '_chapter_number', true);
                        $audio_url = get_post_meta($ch->ID, '_audio_url', true);
                    ?>
                    <div class="chapter-item">
                        <a href="<?php echo get_permalink($ch->ID); ?>" class="<?php echo $can_read ? '' : 'locked'; ?>">
                            <span class="chapter-num"><?php echo $chapter_num ?: ''; ?></span>
                            <?php echo $ch->post_title; ?>
                        </a>
                        <div class="chapter-badges">
                            <?php if ($audio_url): ?>
                                <span class="audio-badge">🎧</span>
                            <?php endif; ?>
                            <?php if ($is_vip): ?>
                                <span class="vip-badge">VIP</span>
                            <?php else: ?>
                                <span class="free-badge">FREE</span>
                            <?php endif; ?>
                            <?php if (!$can_read): ?>
                                <span style="color:var(--text-muted);">🔒</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(function($) {
    // Rating
    $('.story-hero-rating .star').on('click', function() {
        var rating = $(this).data('rating');
        var post_id = $('.story-hero-rating').data('post-id');
        var $box = $(this).closest('.story-hero-rating');
        $.post(ta_ajax.ajax_url, {
            action: 'rate_story',
            post_id: post_id,
            rating: rating
        }, function(res) {
            $box.find('.star').each(function() {
                $(this).toggleClass('active', $(this).data('rating') <= res.rating);
            });
            $box.find('.rating-text').text(res.rating + '/5 (' + res.count + ' đánh giá)');
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

<?php get_footer(); ?>