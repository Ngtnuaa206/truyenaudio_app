<?php get_header(); the_post();
$story_id = get_post_meta(get_the_ID(), '_story_id', true);
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
                if (!$audioSection.length) {
                    // Create audio section
                    $contentArea.before(
                        '<div class="audio-player-section" id="audio-player-section">' +
                        '<div class="audio-player-title">🎧 Nghe chương này</div>' +
                        '<div class="audio-progress" id="audio-progress"><div class="audio-progress-fill" id="audio-progress-fill"></div></div>' +
                        '<div class="audio-time"><span id="audio-current-time">0:00</span><span id="audio-duration">0:00</span></div>' +
                        '<div class="audio-controls">' +
                        '<button class="audio-btn" id="audio-prev" title="Quay lại 10s">⏪</button>' +
                        '<button class="audio-btn play-btn" id="audio-play" title="Phát/Tạm dừng"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></button>' +
                        '<button class="audio-btn" id="audio-next" title="Tua 10s">⏩</button>' +
                        '</div>' +
                        '<div class="audio-extras">' +
                        '<div class="speed-control">' +
                        '<button class="speed-btn" data-speed="0.75">0.75x</button>' +
                        '<button class="speed-btn active" data-speed="1">1x</button>' +
                        '<button class="speed-btn" data-speed="1.25">1.25x</button>' +
                        '<button class="speed-btn" data-speed="1.5">1.5x</button>' +
                        '<button class="speed-btn" data-speed="2">2x</button>' +
                        '</div>' +
                        '<div class="sleep-timer" style="position:relative;">' +
                        '<button class="sleep-btn" id="sleep-timer-btn">⏰ Hẹn giờ</button>' +
                        '<div class="sleep-popup" id="sleep-popup"><div class="sleep-popup-title">Tắt âm thanh sau</div>' +
                        '<button data-minutes="15">15 phút</button><button data-minutes="30">30 phút</button><button data-minutes="60">60 phút</button><button data-minutes="0">Tắt hẹn giờ</button>' +
                        '</div></div></div></div>'
                    );
                    if (!$audioEl.length) $contentArea.before('<audio id="audio-element"></audio>');
                    $audioEl = $('#audio-element');
                    initAudioPlayer();
                }
                $audioEl[0].pause();
                $audioEl.attr('src', d.audio_url);
                $audioEl[0].load();
                $audioSection.show();
                $('#sticky-title').text('Chương ' + (d.chapter_num || '') + ': ' + d.title);
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

        var $playBtn = $('#audio-play');
        var $progress = $('#audio-progress');
        var $progressFill = $('#audio-progress-fill');
        var $currentTime = $('#audio-current-time');
        var $duration = $('#audio-duration');
        var $stickyBar = $('#sticky-audio-bar');
        var $stickyPlay = $('#sticky-play');
        var $stickyFill = $('#sticky-progress-fill');

        function formatTime(s) { var m = Math.floor(s/60); var sec = Math.floor(s%60); return m+':'+(sec<10?'0':'')+sec; }

        function updateProgress() {
            if (audio.duration) {
                var pct = (audio.currentTime / audio.duration) * 100;
                $progressFill.css('width', pct+'%');
                $stickyFill.css('width', pct+'%');
                $currentTime.text(formatTime(audio.currentTime));
            }
        }

        function togglePlay() {
            if (audio.paused) { audio.play(); $playBtn.html('<svg viewBox="0 0 24 24"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>'); $stickyPlay.text('⏸'); $stickyBar.addClass('show'); }
            else { audio.pause(); $playBtn.html('<svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>'); $stickyPlay.text('▶'); }
        }

        $playBtn.off('click').on('click', togglePlay);
        $stickyPlay.off('click').on('click', togglePlay);
        $progress.off('click').on('click', function(e) { var r = this.getBoundingClientRect(); audio.currentTime = ((e.clientX-r.left)/r.width)*audio.duration; });
        audio.removeEventListener('timeupdate', updateProgress);
        audio.addEventListener('timeupdate', updateProgress);
        audio.removeEventListener('loadedmetadata', function(){ $duration.text(formatTime(audio.duration)); });
        audio.addEventListener('loadedmetadata', function(){ $duration.text(formatTime(audio.duration)); });
        $('#audio-prev').off('click').on('click', function(){ audio.currentTime = Math.max(0, audio.currentTime-10); });
        $('#audio-next').off('click').on('click', function(){ audio.currentTime = Math.min(audio.duration, audio.currentTime+10); });

        // Speed
        $('.speed-btn').off('click').on('click', function(){ var sp = $(this).data('speed'); audio.playbackRate = sp; $('.speed-btn').removeClass('active'); $(this).addClass('active'); localStorage.setItem('ta_audio_speed', sp); });
        var savedSpeed = localStorage.getItem('ta_audio_speed');
        if (savedSpeed) { audio.playbackRate = parseFloat(savedSpeed); $('.speed-btn').removeClass('active'); $('.speed-btn[data-speed="'+savedSpeed+'"]').addClass('active'); }

        // Sleep timer
        $('#sleep-timer-btn').off('click').on('click', function(e){ e.stopPropagation(); $('#sleep-popup').toggleClass('show'); });
        $(document).off('click.sleepPopup').on('click.sleepPopup', function(){ $('#sleep-popup').removeClass('show'); });
        $('.sleep-popup button').off('click').on('click', function(){
            var min = $(this).data('minutes');
            clearInterval(window._sleepTimer); window._sleepTimer = null;
            $('.sleep-btn').removeClass('active');
            if (min > 0) {
                window._sleepEnd = Date.now() + min*60*1000;
                $('.sleep-btn').addClass('active');
                ta_toast('Hẹn giờ '+min+' phút', 'info');
                window._sleepTimer = setInterval(function(){ if (Date.now() >= window._sleepEnd) { audio.pause(); clearInterval(window._sleepTimer); window._sleepTimer = null; $('.sleep-btn').removeClass('active'); ta_toast('Đã tắt âm thanh theo hẹn giờ', 'info'); } }, 1000);
            } else { ta_toast('Đã tắt hẹn giờ', 'info'); }
            $('#sleep-popup').removeClass('show');
        });
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
        $('#theme-sepia').on('click', function(){applyReadingTheme('sepia');});
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
        });
        $(document).on('click', function(){ $('#settings-expanded').removeClass('show'); });
    }
});
</script>

<?php get_footer(); ?>