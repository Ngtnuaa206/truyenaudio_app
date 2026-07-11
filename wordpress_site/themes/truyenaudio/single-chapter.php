<?php get_header(); the_post();
$story_id = get_post_meta(get_the_ID(), '_story_id', true);
$chapter_num = get_post_meta(get_the_ID(), '_chapter_number', true);
$audio_url = get_post_meta(get_the_ID(), '_audio_url', true);
$is_vip = get_post_meta(get_the_ID(), '_is_vip', true);
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

// Save reading history
if ($can_read && is_user_logged_in() && $story_id) {
    $history = get_user_meta(get_current_user_id(), '_reading_history', true) ?: [];
    $history[$story_id] = ['chapter_id' => get_the_ID(), 'time' => current_time('mysql')];
    update_user_meta(get_current_user_id(), '_reading_history', $history);
}
?>

<div class="reader-container">
    <div class="reader-header">
        <?php if ($story): ?>
            <a href="<?php echo get_permalink($story->ID); ?>" class="story-link">← <?php echo $story->post_title; ?></a>
        <?php endif; ?>
        <h1>Chương <?php echo $chapter_num ?: ''; ?>: <?php the_title(); ?></h1>
    </div>

    <?php if ($audio_url): ?>
    <div class="audio-player">
        <h4 style="margin-bottom:10px;color:#f0c040;">🎧 Nghe chương này</h4>
        <audio controls>
            <source src="<?php echo esc_url($audio_url); ?>" type="audio/mpeg">
            Trình duyệt không hỗ trợ audio.
        </audio>
    </div>
    <?php endif; ?>

    <?php if ($is_vip): ?>
        <?php // VIP chapter logic (existing) ?>
        <?php
        $can_view_vip = is_user_logged_in() && (ta_has_purchased(get_the_ID()) || current_user_can('administrator'));
        $vip_price = get_post_meta(get_the_ID(), '_vip_price', true) ?: 5;
        ?>
        <?php if ($can_view_vip): ?>
            <div class="reader-content"><?php the_content(); ?></div>
        <?php else: ?>
            <div class="reader-content" style="text-align:center;padding:60px;">
                <p style="font-size:24px;color:#f0c040;margin-bottom:15px;">🔒 Chương VIP</p>
                <p style="color:#888;margin-bottom:20px;">Chương này yêu cầu mở khóa bằng Linh Thạch</p>
                <div class="vip-lock" style="display:inline-flex;">
                    <span class="lock-icon">💎</span>
                    <span class="vip-price"><?php echo $vip_price; ?> Linh Thạch</span>
                </div>
                <?php if (is_user_logged_in()):
                    $user_lt = get_user_meta(get_current_user_id(), '_linh_thach', true) ?: 0;
                ?>
                    <div style="margin-top:20px;">
                        <p style="color:#888;font-size:13px;margin-bottom:10px;">Số dư: 💎<?php echo number_format($user_lt); ?></p>
                        <button id="purchase-vip" class="btn btn-primary" data-chapter="<?php the_ID(); ?>">Mở khóa ngay</button>
                        <a href="<?php echo home_url('/linh-thach'); ?>" class="btn btn-outline" style="margin-left:10px;">Nạp thêm</a>
                    </div>
                <?php else: ?>
                    <div style="margin-top:20px;">
                        <p style="color:#888;">Vui lòng <a href="<?php echo home_url('/dang-nhap'); ?>">đăng nhập</a> để mở khóa.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php elseif ($can_read): ?>
        <div class="reader-content"><?php the_content(); ?></div>
    <?php else: ?>
        <div class="reader-content" style="text-align:center;padding:60px;">
            <p style="font-size:24px;color:#f0c040;margin-bottom:15px;">🔒 Chương này bị khóa</p>
            <?php if (!is_user_logged_in()): ?>
                <p style="color:#888;margin-bottom:20px;">
                    <?php if ($total_chapters <= 2): ?>
                        Bạn cần đăng nhập để đọc chương này.
                    <?php else: ?>
                        Bạn đã đọc hết <?php echo $free_chapters; ?> chương miễn phí. Đăng nhập để đọc tiếp!
                    <?php endif; ?>
                </p>
                <a href="<?php echo home_url('/dang-nhap?redirect_to=' . urlencode(get_permalink())); ?>" class="btn btn-primary">Đăng nhập</a>
                <a href="<?php echo home_url('/dang-ky'); ?>" class="btn btn-outline" style="margin-left:10px;">Đăng ký</a>
            <?php elseif ($is_dao): ?>
                <p style="color:#888;margin-bottom:20px;">Truyện đang bật chế độ Đào Linh Thạch. Mở khóa để đọc tiếp!</p>
                <div class="vip-lock" style="display:inline-flex;">
                    <span class="lock-icon">💎</span>
                    <span class="vip-price"><?php echo $dao_price; ?> Linh Thạch / chương</span>
                </div>
                <div style="margin-top:20px;">
                    <?php $user_lt = get_user_meta(get_current_user_id(), '_linh_thach', true) ?: 0; ?>
                    <p style="color:#888;font-size:13px;margin-bottom:10px;">Số dư: 💎<?php echo number_format($user_lt); ?></p>
                    <button id="purchase-vip" class="btn btn-primary" data-chapter="<?php the_ID(); ?>">Mở khóa ngay</button>
                    <a href="<?php echo home_url('/linh-thach'); ?>" class="btn btn-outline" style="margin-left:10px;">Nạp thêm</a>
                </div>
            <?php else: ?>
                <p style="color:#888;">Bạn không có quyền xem chương này.</p>
                <a href="<?php echo get_permalink($story_id); ?>" class="btn btn-outline">← Về trang truyện</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

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

<script>
jQuery(function($) {
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
