<?php get_header(); the_post();
$story_id = get_post_meta(get_the_ID(), '_story_id', true);
$chapter_num = get_post_meta(get_the_ID(), '_chapter_number', true);
$audio_url = get_post_meta(get_the_ID(), '_audio_url', true);
$is_vip = get_post_meta(get_the_ID(), '_is_vip', true);
$vip_price = get_post_meta(get_the_ID(), '_vip_price', true) ?: 5;
$story = $story_id ? get_post($story_id) : null;

// Get prev/next chapters
$chapters = $story_id ? ta_get_chapters($story_id) : [];
$prev_chapter = null;
$next_chapter = null;
foreach ($chapters as $i => $ch) {
    if ($ch->ID == get_the_ID()) {
        if (isset($chapters[$i - 1])) $prev_chapter = $chapters[$i - 1];
        if (isset($chapters[$i + 1])) $next_chapter = $chapters[$i + 1];
        break;
    }
}

$can_read = ta_can_read_chapter(get_the_ID(), $story_id);
$is_dao = get_post_meta($story_id, '_dao_linh_thach', true) === '1';
$dao_price = get_post_meta($story_id, '_dao_price', true) ?: 3;
$total_chapters = count($chapters);
$free_chapters = get_post_meta($story_id, '_free_chapters', true) ?: 2;

// Get story genres for related stories
$genres = $story_id ? wp_get_post_terms($story_id, 'the_loai', ['fields' => 'ids']) : [];

// Save reading history
if ($can_read && is_user_logged_in() && $story_id) {
    $history = get_user_meta(get_current_user_id(), '_reading_history', true) ?: [];
    $history[$story_id] = ['chapter_id' => get_the_ID(), 'time' => current_time('mysql')];
    update_user_meta(get_current_user_id(), '_reading_history', $history);
}
?>

<div class="container">
    <div class="reader-layout">
        <div class="reader-main">
            <!-- Story Header -->
            <?php if ($story): ?>
            <div class="reader-story-header">
                <div class="reader-story-cover">
                    <?php if (has_post_thumbnail($story->ID)) echo get_the_post_thumbnail($story->ID, 'thumbnail'); else echo '<div style="width:100%;height:100%;background:var(--border);display:flex;align-items:center;justify-content:center;">📚</div>'; ?>
                </div>
                <div class="reader-story-info">
                    <h2><?php echo $story->post_title; ?></h2>
                    <a href="<?php echo get_permalink($story->ID); ?>">← Về trang truyện</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Audio Player -->
            <?php if ($audio_url): ?>
            <div class="audio-player-section" id="audio-player-section">
                <div class="audio-player-title">🎧 Nghe chương này</div>
                <div class="audio-progress" id="audio-progress">
                    <div class="audio-progress-fill" id="audio-progress-fill"></div>
                </div>
                <div class="audio-time">
                    <span id="audio-current-time">0:00</span>
                    <span id="audio-duration">0:00</span>
                </div>
                <div class="audio-controls">
                    <button class="audio-btn" id="audio-prev" title="Quay lại 10s">⏪</button>
                    <button class="audio-btn play-btn" id="audio-play" title="Phát/Tạm dừng">
                        <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                    <button class="audio-btn" id="audio-next" title="Tua 10s">⏩</button>
                </div>
                <div class="audio-extras">
                    <div class="speed-control">
                        <button class="speed-btn" data-speed="0.75">0.75x</button>
                        <button class="speed-btn active" data-speed="1">1x</button>
                        <button class="speed-btn" data-speed="1.25">1.25x</button>
                        <button class="speed-btn" data-speed="1.5">1.5x</button>
                        <button class="speed-btn" data-speed="2">2x</button>
                    </div>
                    <div class="sleep-timer" style="position:relative;">
                        <button class="sleep-btn" id="sleep-timer-btn">⏰ Hẹn giờ</button>
                        <div class="sleep-popup" id="sleep-popup">
                            <div class="sleep-popup-title">Tắt âm thanh sau</div>
                            <button data-minutes="15">15 phút</button>
                            <button data-minutes="30">30 phút</button>
                            <button data-minutes="60">60 phút</button>
                            <button data-minutes="0">Tắt hẹn giờ</button>
                        </div>
                    </div>
                </div>
            </div>
            <audio id="audio-element" src="<?php echo esc_url($audio_url); ?>"></audio>
            <?php endif; ?>

            <!-- Reading Settings -->
            <div class="reading-settings-bar" id="reading-settings">
                <div class="setting-group">
                    <span class="setting-label">Cỡ chữ</span>
                    <button class="setting-btn" id="font-decrease">A-</button>
                    <span class="font-size-display" id="font-size-display">16</span>
                    <button class="setting-btn" id="font-increase">A+</button>
                </div>
                <div class="setting-divider"></div>
                <div class="setting-group">
                    <span class="setting-label">Nền</span>
                    <button class="setting-btn theme-light" id="theme-light" title="Sáng">☀️</button>
                    <button class="setting-btn theme-dark" id="theme-dark" title="Tối">🌙</button>
                    <button class="setting-btn theme-sepia" id="theme-sepia" title="Nâu">📜</button>
                </div>
                <div class="setting-divider"></div>
                <div class="setting-group">
                    <span class="setting-label">Dãn dòng</span>
                    <select class="line-spacing-select" id="line-spacing">
                        <option value="1.5">1.5</option>
                        <option value="1.8">1.8</option>
                        <option value="2" selected>2.0</option>
                        <option value="2.5">2.5</option>
                        <option value="3">3.0</option>
                    </select>
                </div>
                <div class="setting-divider"></div>
                <div class="setting-group">
                    <button class="setting-btn" id="auto-scroll-btn" title="Tự động cuộn">↕️</button>
                </div>
                <button class="setting-btn fullscreen-btn" id="fullscreen-btn" title="Toàn màn hình">⛶</button>
            </div>

            <!-- Chapter Content -->
            <?php if ($is_vip): ?>
                <?php
                $can_view_vip = is_user_logged_in() && (ta_has_purchased(get_the_ID()) || current_user_can('administrator'));
                ?>
                <?php if ($can_view_vip): ?>
                    <div class="reader-content-section">
                        <h1>Chương <?php echo $chapter_num ?: ''; ?>: <?php the_title(); ?></h1>
                        <div class="reader-content" id="reader-content"><?php the_content(); ?></div>
                    </div>
                <?php else: ?>
                    <div class="reader-content-section" style="text-align:center;padding:60px;">
                        <p style="font-size:24px;color:var(--accent);margin-bottom:15px;">🔒 Chương VIP</p>
                        <p style="color:var(--text-muted);margin-bottom:20px;">Chương này yêu cầu mở khóa bằng Linh Thạch</p>
                        <div class="vip-lock" style="display:inline-flex;">
                            <span class="lock-icon">💎</span>
                            <span class="vip-price"><?php echo $vip_price; ?> Linh Thạch</span>
                        </div>
                        <?php if (is_user_logged_in()):
                            $user_lt = get_user_meta(get_current_user_id(), '_linh_thach', true) ?: 0;
                        ?>
                            <div style="margin-top:20px;">
                                <p style="color:var(--text-muted);font-size:13px;margin-bottom:10px;">Số dư: 💎<?php echo number_format($user_lt); ?></p>
                                <button id="purchase-vip" class="btn btn-primary" data-chapter="<?php the_ID(); ?>">Mở khóa ngay</button>
                                <a href="<?php echo home_url('/linh-thach'); ?>" class="btn btn-outline" style="margin-left:10px;">Nạp thêm</a>
                            </div>
                        <?php else: ?>
                            <div style="margin-top:20px;">
                                <p style="color:var(--text-muted);">Vui lòng <a href="<?php echo home_url('/dang-nhap'); ?>">đăng nhập</a> để mở khóa.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php elseif ($can_read): ?>
                <div class="reader-content-section">
                    <h1>Chương <?php echo $chapter_num ?: ''; ?>: <?php the_title(); ?></h1>
                    <div class="reader-content" id="reader-content"><?php the_content(); ?></div>
                </div>
            <?php else: ?>
                <div class="reader-content-section" style="text-align:center;padding:60px;">
                    <p style="font-size:24px;color:var(--accent);margin-bottom:15px;">🔒 Chương này bị khóa</p>
                    <?php if (!is_user_logged_in()): ?>
                        <p style="color:var(--text-muted);margin-bottom:20px;">
                            <?php if ($total_chapters <= 2): ?>
                                Bạn cần đăng nhập để đọc chương này.
                            <?php else: ?>
                                Bạn đã đọc hết <?php echo $free_chapters; ?> chương miễn phí. Đăng nhập để đọc tiếp!
                            <?php endif; ?>
                        </p>
                        <a href="<?php echo home_url('/dang-nhap?redirect_to=' . urlencode(get_permalink())); ?>" class="btn btn-primary">Đăng nhập</a>
                        <a href="<?php echo home_url('/dang-ky'); ?>" class="btn btn-outline" style="margin-left:10px;">Đăng ký</a>
                    <?php elseif ($is_dao): ?>
                        <p style="color:var(--text-muted);margin-bottom:20px;">Truyện đang bật chế độ Đào Linh Thạch. Mở khóa để đọc tiếp!</p>
                        <div class="vip-lock" style="display:inline-flex;">
                            <span class="lock-icon">💎</span>
                            <span class="vip-price"><?php echo $dao_price; ?> Linh Thạch / chương</span>
                        </div>
                        <div style="margin-top:20px;">
                            <?php $user_lt = get_user_meta(get_current_user_id(), '_linh_thach', true) ?: 0; ?>
                            <p style="color:var(--text-muted);font-size:13px;margin-bottom:10px;">Số dư: 💎<?php echo number_format($user_lt); ?></p>
                            <button id="purchase-vip" class="btn btn-primary" data-chapter="<?php the_ID(); ?>">Mở khóa ngay</button>
                            <a href="<?php echo home_url('/linh-thach'); ?>" class="btn btn-outline" style="margin-left:10px;">Nạp thêm</a>
                        </div>
                    <?php else: ?>
                        <p style="color:var(--text-muted);">Bạn không có quyền xem chương này.</p>
                        <a href="<?php echo get_permalink($story_id); ?>" class="btn btn-outline">← Về trang truyện</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Chapter Navigation -->
            <div class="reader-nav">
                <?php if ($prev_chapter): ?>
                    <a href="<?php echo get_permalink($prev_chapter->ID); ?>" class="btn btn-outline">← Chương trước</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
                <?php if ($next_chapter): ?>
                    <a href="<?php echo get_permalink($next_chapter->ID); ?>" class="btn btn-primary">Chương tiếp →</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="reader-sidebar">
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<!-- Sticky Audio Bar (Mobile) -->
<div class="sticky-audio-bar" id="sticky-audio-bar">
    <div class="sticky-info">
        <div class="sticky-title">Chương <?php echo $chapter_num ?: ''; ?>: <?php the_title(); ?></div>
        <div class="sticky-progress">
            <div class="sticky-progress-fill" id="sticky-progress-fill"></div>
        </div>
    </div>
    <button class="sticky-play" id="sticky-play">▶</button>
</div>

<script>
jQuery(function($) {
    // Purchase VIP chapter
    $('#purchase-vip').on('click', function() {
        var $btn = $(this).prop('disabled', true).text('Đang xử lý...');
        $.post(ta_ajax.ajax_url, {
            action: 'purchase_vip_chapter',
            chapter_id: $btn.data('chapter')
        }, function(res) {
            if (res.success) {
                ta_toast('🎉 ' + res.data.message, 'success');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                ta_toast(res.data, 'error');
                $btn.prop('disabled', false).text('Mở khóa ngay');
            }
        });
    });
});
</script>

<?php get_footer(); ?>