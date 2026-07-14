<?php /* Template Name: Lịch sử */
ta_require_auth();
get_header();
$history = get_user_meta(get_current_user_id(), '_reading_history', true) ?: [];
$history = array_reverse($history, true);
?>

<div class="container" style="padding:40px 15px;">
    <h1 style="color:var(--text);margin-bottom:24px;">📖 Lịch sử đọc</h1>

    <?php if (empty($history)): ?>
        <div style="text-align:center;padding:60px 20px;">
            <div style="font-size:48px;margin-bottom:16px;">📚</div>
            <p style="color:var(--text-muted);font-size:15px;">Bạn chưa đọc truyện nào.</p>
            <a href="<?php echo home_url('/'); ?>" class="btn btn-primary" style="margin-top:16px;">Khám phá truyện ngay</a>
        </div>
    <?php else: ?>
    <div class="story-list">
        <?php foreach ($history as $sid => $hdata):
            $story = get_post($sid);
            if (!$story) continue;
            $chapter_id = intval($hdata['chapter_id']);
            $ch = get_post($chapter_id);
            $chapter_num = $ch ? get_post_meta($ch->ID, '_chapter_number', true) : '';
            $is_vip = $ch ? get_post_meta($ch->ID, '_is_vip', true) : '';
            $genres = wp_get_post_terms($sid, 'the_loai', ['fields' => 'names']);
        ?>
        <div class="story-row">
            <div class="story-thumb">
                <a href="<?php echo $ch ? get_permalink($ch->ID) : get_permalink($sid); ?>">
                    <?php if (has_post_thumbnail($sid)) echo get_post_thumbnail($sid, 'medium'); else echo '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-muted);background:var(--border);border-radius:8px;font-size:32px;">📚</div>'; ?>
                </a>
            </div>
            <div class="story-info">
                <a href="<?php echo get_permalink($sid); ?>"><h3 class="story-title"><?php echo esc_html($story->post_title); ?></h3></a>
                <?php if (!empty($genres)): ?>
                    <div style="margin-top:2px;"><span style="font-size:12px;color:var(--accent);"><?php echo esc_html(implode(' · ', $genres)); ?></span></div>
                <?php endif; ?>
                <?php if ($ch): ?>
                    <div style="margin-top:6px;">
                        <a href="<?php echo get_permalink($ch->ID); ?>" style="color:var(--accent);font-size:13px;font-weight:500;text-decoration:none;">
                            <?php echo $chapter_num ? 'Chương ' . $chapter_num . ': ' : ''; ?><?php echo esc_html($ch->post_title); ?> →
                        </a>
                        <?php if ($is_vip): ?><span class="vip-badge" style="margin-left:6px;">VIP</span><?php endif; ?>
                    </div>
                <?php endif; ?>
                <div style="margin-top:6px;font-size:12px;color:var(--text-muted);">
                    Đọc lúc: <?php echo date('d/m/Y H:i', strtotime($hdata['time'])); ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
