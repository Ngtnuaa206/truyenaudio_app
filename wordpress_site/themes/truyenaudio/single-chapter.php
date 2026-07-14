<?php get_header(); the_post();
$story_id = get_post_meta(get_the_ID(), '_story_id', true) ?: get_post()->post_parent;
$chapter_num = get_post_meta(get_the_ID(), '_chapter_number', true);
$audio_url = get_post_meta(get_the_ID(), '_audio_url', true);
$is_vip = get_post_meta(get_the_ID(), '_is_vip', true);
$vip_price = get_post_meta(get_the_ID(), '_vip_price', true) ?: 5;
$story = $story_id ? get_post($story_id) : null;

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

if ($can_read && is_user_logged_in() && $story_id) {
    $history = get_user_meta(get_current_user_id(), '_reading_history', true) ?: [];
    $history[$story_id] = ['chapter_id' => get_the_ID(), 'time' => current_time('mysql')];
    update_user_meta(get_current_user_id(), '_reading_history', $history);
}
?>

<div class="container">
    <div class="reader-layout">
        <div class="reader-main" id="reader-main">
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
            <?php if ($audio_url):
                $cover_url = '';
                if ($story && has_post_thumbnail($story->ID)) {
                    $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($story->ID), 'medium');
                    if ($thumb) $cover_url = $thumb[0];
                }
            ?>
            <div class="audio-player-section" id="audio-player-section">
                <audio id="audio-element" src="<?php echo esc_url($audio_url); ?>" preload="auto"></audio>
                <div class="ap-inner">
                    <div class="ap-cover" id="ap-cover">
                        <?php if ($cover_url): ?>
                            <img src="<?php echo esc_url($cover_url); ?>" alt="cover" />
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:40px;">📚</div>
                        <?php endif; ?>
                        <div class="ap-cover-dot"></div>
                    </div>
                    <div class="ap-body">
                        <div class="ap-info">
                            <div style="min-width:0;">
                                <h3 class="ap-title">Chương <?php echo $chapter_num ?: ''; ?>:</h3>
                                <div class="ap-chapter-sub"><?php the_title(); ?></div>
                            </div>
                            <span class="ap-speed-badge" id="ap-speed-badge">1.3x</span>
                        </div>
                        <div class="ap-progress-wrap">
                            <input type="range" class="ap-range" id="ap-range" min="0" max="100" value="0" step="0.1" />
                            <div class="ap-times">
                                <span id="ap-current">0:00</span>
                                <span id="ap-duration">0:00</span>
                            </div>
                        </div>
                        <div class="ap-controls">
                            <button class="ap-ctrl-btn" id="ap-shuffle" title="Phát ngẫu nhiên">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M10.59 9.17L5.41 4 4 5.41l5.17 5.17 1.42-1.41zM14.5 4l2.04 2.04L4 18.59 5.41 20 17.96 7.46 20 9.5V4h-5.5zm.33 9.41l-1.41 1.41 3.13 3.13L14.5 20H20v-5.5l-2.04 2.04-3.13-3.13z"/></svg>
                            </button>
                            <button class="ap-ctrl-btn" id="ap-repeat" title="Lặp lại">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M7 7h10v3l4-4-4-4v3H5v6h2V7zm10 10H7v-3l-4 4 4 4v-3h12v-6h-2v4z"/></svg>
                            </button>
                            <?php if ($prev_chapter): ?>
                            <button class="ap-ctrl-btn" id="ap-prev-chapter" title="Chương trước" data-chapter-id="<?php echo $prev_chapter->ID; ?>">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg>
                            </button>
                            <?php endif; ?>
                            <button class="ap-ctrl-btn" id="ap-backward-step" title="Lùi 10s">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.5 8.5l-3.5 3.5 3.5 3.5"/><path d="M19 12a7 7 0 1 1-2.1-5"/><text x="12" y="15" font-size="7" fill="currentColor" stroke="none" text-anchor="middle" font-weight="700">10</text></svg>
                            </button>
                            <button class="ap-ctrl-btn ap-play-btn" id="ap-play" title="Phát/Tạm dừng">
                                <svg class="ap-icon-play" viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                <svg class="ap-icon-pause" viewBox="0 0 24 24" width="28" height="28" fill="currentColor" style="display:none;"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>
                            </button>
                            <button class="ap-ctrl-btn" id="ap-forward-step" title="Tua 10s">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.5 8.5l3.5 3.5-3.5 3.5"/><path d="M5 12a7 7 0 1 0 2.1-5"/><text x="12" y="15" font-size="7" fill="currentColor" stroke="none" text-anchor="middle" font-weight="700">10</text></svg>
                            </button>
                            <?php if ($next_chapter): ?>
                            <button class="ap-ctrl-btn" id="ap-next-chapter" title="Chương tiếp" data-chapter-id="<?php echo $next_chapter->ID; ?>">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="ap-bottom-row">
                            <div class="ap-volume-wrap">
                                <button class="ap-icon-btn" id="ap-volume-btn" title="Âm lượng">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3A4.5 4.5 0 0 0 14 8.5v7a4.49 4.49 0 0 0 2.5-3.5zM14 3.23v2.06a7 7 0 0 1 0 13.42v2.06A9 9 0 0 0 14 3.23z"/></svg>
                                </button>
                                <input type="range" class="ap-vol-range" id="ap-volume" min="0" max="1" step="0.05" value="1" />
                            </div>
                            <div class="ap-settings-wrap">
                                <button class="ap-icon-btn" id="ap-settings-btn" title="Cài đặt">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19.14 12.94a7.07 7.07 0 0 0 .06-.94c0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.49.49 0 0 0-.59-.22l-2.39.96a6.94 6.94 0 0 0-1.63-.94l-.36-2.54a.48.48 0 0 0-.48-.41h-3.84a.48.48 0 0 0-.48.41l-.36 2.54c-.59.24-1.13.57-1.63.94l-2.39-.96a.49.49 0 0 0-.59.22L2.74 8.87a.48.48 0 0 0 .12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 0 0-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.37 1.04.7 1.63.94l.36 2.54c.05.24.26.41.48.41h3.84c.24 0 .44-.17.48-.41l.36-2.54c.59-.24 1.13-.57 1.63-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32a.49.49 0 0 0-.12-.61l-2.03-1.58zM12 15.6A3.6 3.6 0 1 1 12 8.4a3.6 3.6 0 0 1 0 7.2z"/></svg>
                                </button>
                                <div class="ap-settings-popup" id="ap-settings">
                                    <div class="ap-settings-section">
                                        <div class="ap-settings-label">Tốc độ</div>
                                        <div class="ap-speed-grid" id="ap-speed-grid">
                                            <button data-speed="0.75">0.75</button>
                                            <button data-speed="1">1</button>
                                            <button data-speed="1.25">1.25</button>
                                            <button data-speed="1.5">1.5</button>
                                            <button data-speed="2">2</button>
                                        </div>
                                    </div>
                                    <div class="ap-settings-section">
                                        <div class="ap-settings-label">Hẹn giờ tắt</div>
                                        <div class="ap-sleep-grid" id="ap-sleep-grid">
                                            <button data-minutes="15">15p</button>
                                            <button data-minutes="30">30p</button>
                                            <button data-minutes="60">60p</button>
                                        </div>
                                        <div class="ap-sleep-custom">
                                            <input type="number" placeholder="Phút..." id="ap-sleep-input" min="1" max="480" />
                                            <button id="ap-sleep-set">Đặt</button>
                                        </div>
                                        <button id="ap-sleep-off" class="ap-sleep-off-btn">Tắt hẹn giờ</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                    <button class="setting-btn" id="tts-btn" title="Đọc truyện">🔊</button>
                </div>
                <div class="setting-group">
                    <button class="setting-btn" id="auto-scroll-btn" title="Tự động cuộn">↕️</button>
                </div>
                <button class="setting-btn fullscreen-btn" id="fullscreen-btn" title="Toàn màn hình">⛶</button>
                <div class="settings-more-wrap">
                    <button class="settings-more-btn" id="settings-more-btn" title="Thêm tùy chọn">⋯</button>
                    <div class="settings-expanded" id="settings-expanded">
                        <div class="setting-group">
                            <select class="line-spacing-select" id="line-spacing-exp">
                                <option value="1.5">1.5</option>
                                <option value="1.8">1.8</option>
                                <option value="2" selected>2.0</option>
                                <option value="2.5">2.5</option>
                                <option value="3">3.0</option>
                            </select>
                        </div>
                        <button class="setting-btn" id="tts-btn-exp" title="Đọc truyện">🔊</button>
                        <button class="setting-btn" id="auto-scroll-btn-exp" title="Tự động cuộn">↕️</button>
                        <button class="setting-btn fullscreen-btn" id="fullscreen-btn-exp" title="Toàn màn hình">⛶</button>
                    </div>
                </div>
            </div>

            <!-- Chapter Content -->
            <div id="chapter-content-area">
            <?php if ($is_vip): ?>
                <?php $can_view_vip = is_user_logged_in() && (ta_has_purchased(get_the_ID()) || current_user_can('administrator')); ?>
                <?php if ($can_view_vip): ?>
                    <div class="reader-content-section">
                        <h1 id="chapter-title-display">Chương <?php echo $chapter_num ?: ''; ?>: <?php the_title(); ?></h1>
                        <div class="reader-content" id="reader-content"><?php the_content(); ?></div>
                    </div>
                <?php else: ?>
                    <div class="reader-content-section" id="chapter-locked-section" style="text-align:center;padding:60px;">
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
                    <h1 id="chapter-title-display">Chương <?php echo $chapter_num ?: ''; ?>: <?php the_title(); ?></h1>
                    <div class="reader-content" id="reader-content"><?php the_content(); ?></div>
                </div>
            <?php else: ?>
                <div class="reader-content-section" id="chapter-locked-section" style="text-align:center;padding:60px;">
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
            </div>

            <!-- Chapter Navigation -->
            <div class="reader-nav" id="reader-nav">
                <?php if ($prev_chapter): ?>
                    <button class="btn btn-outline nav-chapter-btn" data-chapter-id="<?php echo $prev_chapter->ID; ?>">← Chương trước</button>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
                <?php if ($next_chapter): ?>
                    <button class="btn btn-primary nav-chapter-btn" data-chapter-id="<?php echo $next_chapter->ID; ?>">Chương tiếp →</button>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="reader-sidebar">
            <!-- Chapter List -->
            <div class="chapter-list-section sidebar-widget">
                <div class="chapter-list-header">
                    <h3>Danh sách chương</h3>
                    <span class="chapter-count"><?php echo count($chapters); ?> chương</span>
                </div>
                <div class="chapter-list-scroll" id="chapter-list-scroll">
                    <?php if (empty($chapters)): ?>
                        <p style="color:var(--text-muted);text-align:center;padding:40px 16px;">Chưa có chương nào.</p>
                    <?php else: ?>
                        <?php foreach ($chapters as $ch):
                            $ch_is_vip = get_post_meta($ch->ID, '_is_vip', true);
                            $ch_can_read = ta_can_read_chapter($ch->ID, $story_id);
                            $ch_num = get_post_meta($ch->ID, '_chapter_number', true);
                            $ch_audio = get_post_meta($ch->ID, '_audio_url', true);
                            $is_current = ($ch->ID == get_the_ID());
                        ?>
                        <div class="chapter-item <?php echo $is_current ? 'active' : ''; ?>" data-chapter-id="<?php echo $ch->ID; ?>">
                            <a href="<?php echo get_permalink($ch->ID); ?>" class="<?php echo $ch_can_read ? '' : 'locked'; ?>">
                                <span class="chapter-num"><?php echo $ch_num ?: ''; ?></span>
                                <?php echo $ch->post_title; ?>
                            </a>
                            <div class="chapter-badges">
                                <?php if ($ch_audio): ?>
                                    <span class="audio-badge">🎧</span>
                                <?php endif; ?>
                                <?php if ($ch_is_vip): ?>
                                    <span class="vip-badge">VIP</span>
                                <?php else: ?>
                                    <span class="free-badge">FREE</span>
                                <?php endif; ?>
                                <?php if (!$ch_can_read): ?>
                                    <span style="color:var(--text-muted);">🔒</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<!-- Sticky Audio Bar (Mobile) -->
<div class="sticky-audio-bar" id="sticky-audio-bar">
    <div class="sticky-info">
        <div class="sticky-title" id="sticky-title">Chương <?php echo $chapter_num ?: ''; ?>: <?php the_title(); ?></div>
        <div class="sticky-progress">
            <div class="sticky-progress-fill" id="sticky-progress-fill"></div>
        </div>
    </div>
    <button class="sticky-play" id="sticky-play">▶</button>
</div>

<script>
var TA_ChapterData = {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    story_id: <?php echo $story_id; ?>,
    current_chapter_id: <?php echo get_the_ID(); ?>
};
</script>
<script>
jQuery(function($) {
    // AJAX chapter switching
    $(document).on('click', '.chapter-item a, .nav-chapter-btn', function(e) {
        e.preventDefault();
        var $el = $(this);
        var chapterId = $el.closest('.chapter-item').data('chapter-id') || $el.data('chapter-id');
        if (!chapterId || chapterId == TA_ChapterData.current_chapter_id) return;

        // Track if audio was playing before switch
        var wasPlaying = false;
        var oldAudio = document.getElementById('audio-element');
        if (oldAudio && !oldAudio.paused && oldAudio.src) wasPlaying = true;

        var $contentArea = $('#chapter-content-area');
        $contentArea.css('opacity', '0.5');

        $.post(TA_ChapterData.ajax_url, {
            action: 'ta_load_chapter',
            chapter_id: chapterId
        }, function(res) {
            if (!res.success) {
                // Fallback: navigate normally
                window.location.href = $el.attr('href');
                return;
            }
            var d = res.data;

            // Update URL without reload
            history.pushState({chapter_id: chapterId}, '', d.permalink);

            // Update current chapter id
            TA_ChapterData.current_chapter_id = chapterId;

            // Update title
            document.title = 'Chương ' + (d.chapter_num || '') + ': ' + d.title + ' - ' + ($('site-title').text() || 'TruyenAudio');

            // Update chapter list active state
            $('.chapter-item').removeClass('active');
            $('.chapter-item[data-chapter-id="' + chapterId + '"]').addClass('active');

            // Scroll active chapter into view in sidebar
            var $active = $('.chapter-item.active');
            if ($active.length) {
                var scroll = $('#chapter-list-scroll');
                scroll.scrollTop($active.position().top - scroll.position().top + scroll.scrollTop() - 50);
            }

            // Update content area
            if (d.can_read) {
                $contentArea.html(
                    '<div class="reader-content-section">' +
                    '<h1 id="chapter-title-display">Chương ' + (d.chapter_num || '') + ': ' + d.title + '</h1>' +
                    '<div class="reader-content" id="reader-content">' + d.content + '</div>' +
                    '</div>'
                );
            } else {
                var lockHtml = '';
                if (d.login_required) {
                    lockHtml = '<div class="reader-content-section" id="chapter-locked-section" style="text-align:center;padding:60px;">' +
                        '<p style="font-size:24px;color:var(--accent);margin-bottom:15px;">🔒 Chương này bị khóa</p>' +
                        '<p style="color:var(--text-muted);margin-bottom:20px;">Bạn cần đăng nhập để đọc chương này.</p>' +
                        '<a href="<?php echo home_url("/dang-nhap"); ?>?redirect_to=' + encodeURIComponent(d.permalink) + '" class="btn btn-primary">Đăng nhập</a>' +
                        '</div>';
                } else if (d.purchase_required) {
                    lockHtml = '<div class="reader-content-section" id="chapter-locked-section" style="text-align:center;padding:60px;">' +
                        '<p style="font-size:24px;color:var(--accent);margin-bottom:15px;">🔒 Chương VIP</p>' +
                        '<p style="color:var(--text-muted);margin-bottom:20px;">Chương này yêu cầu mở khóa bằng Linh Thạch</p>' +
                        '<div class="vip-lock" style="display:inline-flex;"><span class="lock-icon">💎</span><span class="vip-price">' + d.vip_price + ' Linh Thạch</span></div>' +
                        '<div style="margin-top:20px;"><p style="color:var(--text-muted);font-size:13px;margin-bottom:10px;">Số dư: 💎' + number_format(d.user_lt) + '</p>' +
                        '<button id="purchase-vip" class="btn btn-primary" data-chapter="' + d.id + '">Mở khóa ngay</button>' +
                        '<a href="<?php echo home_url("/linh-thach"); ?>" class="btn btn-outline" style="margin-left:10px;">Nạp thêm</a></div></div>';
                } else {
                    lockHtml = '<div class="reader-content-section" id="chapter-locked-section" style="text-align:center;padding:60px;">' +
                        '<p style="font-size:24px;color:var(--accent);margin-bottom:15px;">🔒 Chương này bị khóa</p>' +
                        '<p style="color:var(--text-muted);">Bạn không có quyền xem chương này.</p>' +
                        '<a href="<?php echo get_permalink($story_id); ?>" class="btn btn-outline">← Về trang truyện</a></div>';
                }
                $contentArea.html(lockHtml);
            }

            // Update audio player
            var $audioSection = $('#audio-player-section');
            var $audioEl = $('#audio-element');
            if (d.audio_url) {
                var coverHtml = d.cover_url ? '<img src="' + d.cover_url + '" alt="cover" />' : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:40px;">📚</div>';
                if (!$audioSection.length) {
                    $contentArea.before(
                        '<div class="audio-player-section" id="audio-player-section">' +
                        '<audio id="audio-element" preload="auto"></audio>' +
                        '<div class="ap-inner">' +
                        '<div class="ap-cover" id="ap-cover">' + coverHtml + '<div class="ap-cover-dot"></div></div>' +
                        '<div class="ap-body">' +
                        '<div class="ap-info"><div style="min-width:0;"><h3 class="ap-title" id="ap-title">Chương ' + (d.chapter_num||'') + ':</h3><div class="ap-chapter-sub" id="ap-chapter-sub">' + d.title + '</div></div><span class="ap-speed-badge" id="ap-speed-badge">1.3x</span></div>' +
                        '<div class="ap-progress-wrap"><input type="range" class="ap-range" id="ap-range" min="0" max="100" value="0" step="0.1" /><div class="ap-times"><span id="ap-current">0:00</span><span id="ap-duration">0:00</span></div></div>' +
                        '<div class="ap-controls">' +
                        '<button class="ap-ctrl-btn" id="ap-shuffle" title="Phát ngẫu nhiên"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M10.59 9.17L5.41 4 4 5.41l5.17 5.17 1.42-1.41zM14.5 4l2.04 2.04L4 18.59 5.41 20 17.96 7.46 20 9.5V4h-5.5zm.33 9.41l-1.41 1.41 3.13 3.13L14.5 20H20v-5.5l-2.04 2.04-3.13-3.13z"/></svg></button>' +
                        '<button class="ap-ctrl-btn" id="ap-repeat" title="Lặp lại"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M7 7h10v3l4-4-4-4v3H5v6h2V7zm10 10H7v-3l-4 4 4 4v-3h12v-6h-2v4z"/></svg></button>' +
                        (d.prev_id ? '<button class="ap-ctrl-btn" id="ap-prev-chapter" title="Chương trước" data-chapter-id="'+d.prev_id+'"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg></button>' : '') +
                        '<button class="ap-ctrl-btn" id="ap-backward-step" title="Lùi 10s"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.5 8.5l-3.5 3.5 3.5 3.5"/><path d="M19 12a7 7 0 1 1-2.1-5"/><text x="12" y="15" font-size="7" fill="currentColor" stroke="none" text-anchor="middle" font-weight="700">10</text></svg></button>' +
                        '<button class="ap-ctrl-btn ap-play-btn" id="ap-play" title="Phát/Tạm dừng"><svg class="ap-icon-play" viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M8 5v14l11-7z"/></svg><svg class="ap-icon-pause" viewBox="0 0 24 24" width="28" height="28" fill="currentColor" style="display:none;"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg></button>' +
                        '<button class="ap-ctrl-btn" id="ap-forward-step" title="Tua 10s"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.5 8.5l3.5 3.5-3.5 3.5"/><path d="M5 12a7 7 0 1 0 2.1-5"/><text x="12" y="15" font-size="7" fill="currentColor" stroke="none" text-anchor="middle" font-weight="700">10</text></svg></button>' +
                        (d.next_id ? '<button class="ap-ctrl-btn" id="ap-next-chapter" title="Chương tiếp" data-chapter-id="'+d.next_id+'"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg></button>' : '') +
                        '</div>' +
                        '<div class="ap-bottom-row"><div class="ap-volume-wrap"><button class="ap-icon-btn" id="ap-volume-btn" title="Âm lượng"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3A4.5 4.5 0 0 0 14 8.5v7a4.49 4.49 0 0 0 2.5-3.5zM14 3.23v2.06a7 7 0 0 1 0 13.42v2.06A9 9 0 0 0 14 3.23z"/></svg></button><input type="range" class="ap-vol-range" id="ap-volume" min="0" max="1" step="0.05" value="1" /></div>' +
                        '<div class="ap-settings-wrap"><button class="ap-icon-btn" id="ap-settings-btn" title="Cài đặt"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19.14 12.94a7.07 7.07 0 0 0 .06-.94c0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.49.49 0 0 0-.59-.22l-2.39.96a6.94 6.94 0 0 0-1.63-.94l-.36-2.54a.48.48 0 0 0-.48-.41h-3.84a.48.48 0 0 0-.48.41l-.36 2.54c-.59.24-1.13.57-1.63.94l-2.39-.96a.49.49 0 0 0-.59.22L2.74 8.87a.48.48 0 0 0 .12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 0 0-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.37 1.04.7 1.63.94l.36 2.54c.05.24.26.41.48.41h3.84c.24 0 .44-.17.48-.41l.36-2.54c.59-.24 1.13-.57 1.63-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32a.49.49 0 0 0-.12-.61l-2.03-1.58zM12 15.6A3.6 3.6 0 1 1 12 8.4a3.6 3.6 0 0 1 0 7.2z"/></svg></button>' +
                        '<div class="ap-settings-popup" id="ap-settings"><div class="ap-settings-section"><div class="ap-settings-label">Tốc độ</div><div class="ap-speed-grid" id="ap-speed-grid"><button data-speed="0.75">0.75</button><button data-speed="1">1</button><button data-speed="1.25">1.25</button><button data-speed="1.5">1.5</button><button data-speed="2">2</button></div></div>' +
                        '<div class="ap-settings-section"><div class="ap-settings-label">Hẹn giờ tắt</div><div class="ap-sleep-grid" id="ap-sleep-grid"><button data-minutes="15">15p</button><button data-minutes="30">30p</button><button data-minutes="60">60p</button></div><div class="ap-sleep-custom"><input type="number" placeholder="Phút..." id="ap-sleep-input" min="1" max="480" /><button id="ap-sleep-set">Đặt</button></div><button id="ap-sleep-off" class="ap-sleep-off-btn">Tắt hẹn giờ</button></div></div>' +
                        '</div></div></div></div></div>'
                    );
                    $audioEl = $('#audio-element');
                    initAudioPlayer();
                }
                $audioEl[0].pause();
                $audioEl.attr('src', d.audio_url);
                $audioEl[0].load();
                $audioSection.show();
                $('#ap-title').text('Chương ' + (d.chapter_num || '') + ': ' + d.title);
                $('#sticky-title').text('Chương ' + (d.chapter_num || '') + ': ' + d.title);
                // Apply saved speed
                var savedSpeed = localStorage.getItem('ta_audio_speed') || '1.3';
                $audioEl[0].playbackRate = parseFloat(savedSpeed);
                $('#ap-speed-badge').text(savedSpeed + 'x');
                $('#ap-speed-grid button').removeClass('active').filter('[data-speed="'+savedSpeed+'"]').addClass('active');
                // Auto-play if was playing before
                if (wasPlaying) {
                    $audioEl[0].play().catch(function(){});
                }
            } else {
                $audioSection.hide();
                if ($audioEl.length) $audioEl[0].pause();
            }

            // Update nav buttons
            var $nav = $('#reader-nav');
            var prevBtn = d.prev_id ? '<button class="btn btn-outline nav-chapter-btn" data-chapter-id="' + d.prev_id + '">← Chương trước</button>' : '<span></span>';
            var nextBtn = d.next_id ? '<button class="btn btn-primary nav-chapter-btn" data-chapter-id="' + d.next_id + '">Chương tiếp →</button>' : '<span></span>';
            $nav.html(prevBtn + nextBtn);

            $contentArea.css('opacity', '1');

            // Scroll to top of content
            $('html, body').animate({scrollTop: $contentArea.offset().top - 80}, 300);
        }).fail(function() {
            window.location.href = $el.attr('href');
        });
    });

    // Handle browser back/forward
    $(window).on('popstate', function(e) {
        if (e.originalEvent.state && e.originalEvent.state.chapter_id) {
            var $item = $('.chapter-item[data-chapter-id="' + e.originalEvent.state.chapter_id + '"] a');
            if ($item.length) $item.trigger('click');
        }
    });

    // Purchase VIP
    $(document).on('click', '#purchase-vip', function() {
        var $btn = $(this).prop('disabled', true).text('Đang xử lý...');
        $.post(TA_ChapterData.ajax_url, {
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

    function initAudioPlayer() {
        var audio = document.getElementById('audio-element');
        if (!audio) return;

        var $playBtn = $('#ap-play');
        var $iconPlay = $playBtn.find('.ap-icon-play');
        var $iconPause = $playBtn.find('.ap-icon-pause');
        var $range = $('#ap-range');
        var $current = $('#ap-current');
        var $duration = $('#ap-duration');
        var $cover = $('#ap-cover');
        var $stickyBar = $('#sticky-audio-bar');
        var $stickyPlay = $('#sticky-play');
        var $stickyFill = $('#sticky-progress-fill');

        var defaultSpeed = '1.3';

        function formatTime(s) {
            if (!s || isNaN(s)) return '0:00';
            var h = Math.floor(s / 3600);
            var m = Math.floor((s % 3600) / 60);
            var sec = Math.floor(s % 60);
            if (h > 0) return h + ':' + (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
            return m + ':' + (sec < 10 ? '0' : '') + sec;
        }

        function updateSliderBackground() {
            var pct = audio.duration ? (audio.currentTime / audio.duration) * 100 : 0;
            var accent = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#6366f1';
            var border = getComputedStyle(document.documentElement).getPropertyValue('--border').trim() || '#333';
            $range[0].style.background = 'linear-gradient(to right, ' + accent + ' ' + pct + '%, ' + border + ' ' + pct + '%)';
        }

        function updateProgress() {
            if (audio.duration) {
                $range.val((audio.currentTime / audio.duration) * 100);
                $current.text(formatTime(audio.currentTime));
                $stickyFill.css('width', (audio.currentTime / audio.duration) * 100 + '%');
                updateSliderBackground();
            }
        }

        function setPlayState() {
            $iconPlay.hide();
            $iconPause.show();
            $cover.addClass('spinning');
            $stickyPlay.text('⏸');
            $stickyBar.addClass('show');
        }

        function setPauseState() {
            $iconPlay.show();
            $iconPause.hide();
            $cover.removeClass('spinning');
            $stickyPlay.text('▶');
        }

        function togglePlay() {
            if (audio.paused) { audio.play(); setPlayState(); }
            else { audio.pause(); setPauseState(); }
        }

        // Apply saved speed
        var savedSpeed = localStorage.getItem('ta_audio_speed') || defaultSpeed;
        audio.playbackRate = parseFloat(savedSpeed);
        $('#ap-speed-badge').text(savedSpeed + 'x');
        $('#ap-speed-grid button').removeClass('active').filter('[data-speed="' + savedSpeed + '"]').addClass('active');

        // Events
        $playBtn.off('click').on('click', togglePlay);
        $stickyPlay.off('click').on('click', togglePlay);

        audio.removeEventListener('timeupdate', updateProgress);
        audio.addEventListener('timeupdate', updateProgress);
        audio.removeEventListener('loadedmetadata', function(){});
        audio.addEventListener('loadedmetadata', function(){
            $duration.text(formatTime(audio.duration));
            updateSliderBackground();
        });

        audio.addEventListener('play', setPlayState);
        audio.addEventListener('pause', setPauseState);

        // Range seek
        $range.off('input').on('input', function(){
            if (audio.duration) {
                audio.currentTime = ($range.val() / 100) * audio.duration;
                updateSliderBackground();
            }
        });

        // Backward 10s / Forward 10s
        $('#ap-backward-step').off('click').on('click', function(){ audio.currentTime = Math.max(0, audio.currentTime - 10); });
        $('#ap-forward-step').off('click').on('click', function(){ audio.currentTime = Math.min(audio.duration || 0, audio.currentTime + 10); });

        // Prev/Next chapter
        $('#ap-prev-chapter, #ap-next-chapter').off('click').on('click', function(){
            var cid = $(this).data('chapter-id');
            if (cid) {
                var $item = $('.chapter-item[data-chapter-id="' + cid + '"] a');
                if ($item.length) $item.trigger('click');
            }
        });

        // Shuffle/Repeat toggle
        $('#ap-shuffle').off('click').on('click', function(){ $(this).toggleClass('active'); ta_toast($(this).hasClass('active') ? 'Bật phát ngẫu nhiên' : 'Tắt phát ngẫu nhiên', 'info'); });
        $('#ap-repeat').off('click').on('click', function(){ $(this).toggleClass('active'); ta_toast($(this).hasClass('active') ? 'Bật lặp lại' : 'Tắt lặp lại', 'info'); });

        // Speed
        $('#ap-speed-grid').off('click', 'button').on('click', 'button', function(){
            var sp = $(this).data('speed');
            audio.playbackRate = sp;
            localStorage.setItem('ta_audio_speed', sp);
            $('#ap-speed-grid button').removeClass('active');
            $(this).addClass('active');
            $('#ap-speed-badge').text(sp + 'x');
        });

        // Volume
        $('#ap-volume').off('input').on('input', function(){
            audio.volume = parseFloat($(this).val());
        });

        // Settings popup toggle
        $('#ap-settings-btn').off('click').on('click', function(e){
            e.stopPropagation();
            $('#ap-settings').toggleClass('show');
        });
        $(document).off('click.apSettings').on('click.apSettings', function(){ $('#ap-settings').removeClass('show'); });

        // Sleep timer
        $('#ap-sleep-grid button').off('click').on('click', function(){
            var min = $(this).data('minutes');
            startSleepTimer(min);
            $('#ap-sleep-grid button').removeClass('active');
            $(this).addClass('active');
        });
        $('#ap-sleep-set').off('click').on('click', function(){
            var min = parseInt($('#ap-sleep-input').val());
            if (min > 0) {
                startSleepTimer(min);
                $('#ap-sleep-grid button').removeClass('active');
            }
        });
        $('#ap-sleep-off').off('click').on('click', function(){
            clearInterval(window._sleepTimer); window._sleepTimer = null;
            $('#ap-sleep-grid button').removeClass('active');
            ta_toast('Đã tắt hẹn giờ', 'info');
        });

        function startSleepTimer(min) {
            clearInterval(window._sleepTimer); window._sleepTimer = null;
            window._sleepEnd = Date.now() + min * 60 * 1000;
            ta_toast('Hẹn giờ ' + min + ' phút', 'info');
            window._sleepTimer = setInterval(function(){
                if (Date.now() >= window._sleepEnd) {
                    audio.pause(); clearInterval(window._sleepTimer); window._sleepTimer = null;
                    $('#ap-sleep-grid button').removeClass('active');
                    ta_toast('Đã tắt âm thanh theo hẹn giờ', 'info');
                }
            }, 1000);
        }
    }

    // Init audio on page load
    if ($('#audio-element').length) initAudioPlayer();

    // Reading settings (same as before)
    var $readerContent = $('#reader-content');
    if ($readerContent.length) {
        var fontSize = parseInt(localStorage.getItem('ta_font_size')) || 16;
        $readerContent.css('font-size', fontSize+'px');
        $('#font-size-display').text(fontSize);
        $('#font-increase').on('click', function(){ if(fontSize<24){fontSize+=2;$readerContent.css('font-size',fontSize+'px');$('#font-size-display').text(fontSize);localStorage.setItem('ta_font_size',fontSize);} });
        $('#font-decrease').on('click', function(){ if(fontSize>12){fontSize-=2;$readerContent.css('font-size',fontSize+'px');$('#font-size-display').text(fontSize);localStorage.setItem('ta_font_size',fontSize);} });
        var lineSpacing = localStorage.getItem('ta_line_spacing') || '2';
        $readerContent.css('line-height', lineSpacing);
        $('#line-spacing').val(lineSpacing);
        $('#line-spacing').on('change', function(){ lineSpacing=$(this).val();$readerContent.css('line-height',lineSpacing);localStorage.setItem('ta_line_spacing',lineSpacing); });
        var readingTheme = localStorage.getItem('ta_reading_theme') || 'dark';
        applyReadingTheme(readingTheme);
        $('#theme-light').on('click', function(){applyReadingTheme('light');});
        $('#theme-dark').on('click', function(){applyReadingTheme('dark');});
        function applyReadingTheme(theme){document.documentElement.setAttribute('data-theme',theme);localStorage.setItem('ta_reading_theme',theme);$('.setting-btn[id^="theme-"]').removeClass('active');$('#theme-'+theme).addClass('active');}
        var autoScrollInterval = null;
        $('#auto-scroll-btn, #auto-scroll-btn-exp').on('click', function(){
            var $all = $('#auto-scroll-btn, #auto-scroll-btn-exp');
            if(autoScrollInterval){clearInterval(autoScrollInterval);autoScrollInterval=null;$all.removeClass('active');}
            else{$all.addClass('active');autoScrollInterval=setInterval(function(){window.scrollBy(0,1);},50);}
        });
        $('#fullscreen-btn, #fullscreen-btn-exp').on('click', function(){if(!document.fullscreenElement){document.documentElement.requestFullscreen();}else{document.exitFullscreen();}});
        $('#line-spacing-exp').val(lineSpacing);
        $('#line-spacing, #line-spacing-exp').on('change', function(){
            lineSpacing=$(this).val();
            $readerContent.css('line-height',lineSpacing);
            localStorage.setItem('ta_line_spacing',lineSpacing);
            $('#line-spacing').val(lineSpacing);
            $('#line-spacing-exp').val(lineSpacing);
        });

        // Settings more button
        $('#settings-more-btn').on('click', function(e){
            e.stopPropagation();
            $('#settings-expanded').toggleClass('show');
            if (ttsPlaying) {
                if (ttsPaused) {
                    $('#tts-btn-exp').addClass('active').text('▶️');
                } else {
                    $('#tts-btn-exp').addClass('active').text('⏹');
                }
            }
        });
        $(document).on('click', function(){ $('#settings-expanded').removeClass('show'); });

        // ========== Vietnamese TTS (Google TTS via AJAX) ==========
        var ttsChunkSize = 150;
        var ttsChunks = [];
        var ttsIndex = 0;
        var ttsPlaying = false;
        var ttsPaused = false;
        var ttsAudio = null;
        var ttsAjaxPending = null;

        function chunkText(text) {
            var chunks = [];
            var sentences = text.replace(/\n+/g, ' ').split(/(?<=[.!?])\s+/);
            var current = '';
            for (var i = 0; i < sentences.length; i++) {
                var test = current ? current + ' ' + sentences[i] : sentences[i];
                if (test.length > ttsChunkSize && current) {
                    chunks.push(current.trim());
                    current = sentences[i];
                } else {
                    current = test;
                }
            }
            if (current.trim()) chunks.push(current.trim());
            return chunks;
        }

        function ttsSpeakChunk() {
            if (!ttsPlaying || ttsIndex >= ttsChunks.length) {
                ttsStop();
                return;
            }
            var chunk = ttsChunks[ttsIndex];
            var $allBtns = $('#tts-btn, #tts-btn-exp');
            $allBtns.text('⏳');

            ttsAjaxPending = $.ajax({
                url: TA_ChapterData.ajax_url,
                type: 'POST',
                data: { action: 'ta_tts', text: chunk },
                xhrFields: { responseType: 'blob' },
                success: function(blob) {
                    if (!ttsPlaying) return;
                    if (ttsAudio) { ttsAudio.pause(); }
                    var url = URL.createObjectURL(blob);
                    ttsAudio = new Audio(url);
                    $allBtns.text('⏹');

                    ttsAudio.onended = function() {
                        URL.revokeObjectURL(url);
                        ttsIndex++;
                        ttsSpeakChunk();
                    };
                    ttsAudio.onerror = function() {
                        URL.revokeObjectURL(url);
                        ttsIndex++;
                        ttsSpeakChunk();
                    };
                    ttsAudio.play().catch(function() {
                        ttsIndex++;
                        ttsSpeakChunk();
                    });
                },
                error: function() {
                    if (!ttsPlaying) return;
                    ttsIndex++;
                    ttsSpeakChunk();
                }
            });
        }

        function ttsStop() {
            if (ttsAjaxPending) { ttsAjaxPending.abort(); ttsAjaxPending = null; }
            if (ttsAudio) { ttsAudio.pause(); ttsAudio = null; }
            ttsPlaying = false;
            ttsPaused = false;
            ttsIndex = 0;
            ttsChunks = [];
            $('#tts-btn, #tts-btn-exp').removeClass('active').text('🔊');
        }

        $('#tts-btn, #tts-btn-exp').on('click', function() {
            var $allBtns = $('#tts-btn, #tts-btn-exp');
            if (!ttsPlaying) {
                var content = document.getElementById('reader-content');
                if (!content) { ta_toast('Không tìm thấy nội dung chương!', 'error'); return; }
                var text = content.innerText || content.textContent;
                if (!text || text.trim().length < 10) { ta_toast('Nội dung quá ngắn để đọc.', 'info'); return; }
                ttsChunks = chunkText(text);
                ttsIndex = 0;
                ttsPlaying = true;
                ttsPaused = false;
                $allBtns.addClass('active');
                ta_toast('Đang tải giọng đọc...', 'info');
                ttsSpeakChunk();
            } else if (ttsPaused) {
                if (ttsAudio) ttsAudio.play();
                ttsPaused = false;
                $allBtns.text('⏹');
            } else if (ttsAudio && !ttsAudio.paused) {
                ttsAudio.pause();
                ttsPaused = true;
                $allBtns.text('▶️');
            } else {
                ttsStop();
            }
        });

        $(window).on('beforeunload', function() { ttsStop(); });
    }
});
</script>

<?php get_footer(); ?>