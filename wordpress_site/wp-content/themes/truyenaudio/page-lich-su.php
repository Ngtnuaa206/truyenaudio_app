<?php /* Template Name: Lịch sử */
ta_require_auth();
get_header();
$history = get_user_meta(get_current_user_id(), '_reading_history', true) ?: [];
$history = array_reverse($history);
?>

<div class="container" style="padding:40px 15px;">
    <h1 style="color:#fff;margin-bottom:20px;">📖 Lịch sử đọc</h1>

    <?php if (empty($history)): ?>
        <p style="text-align:center;padding:60px;color:#888;">Bạn chưa đọc truyện nào.</p>
    <?php else: ?>
    <div class="story-list">
        <?php foreach ($history as $sid => $hdata): $story = get_post($sid); if (!$story) continue;
            $ch = get_post($hdata['chapter_id']);
        ?>
        <div class="story-row">
            <div class="story-thumb">
                <a href="<?php echo get_permalink($sid); ?>">
                    <?php if (has_post_thumbnail($sid)) echo get_the_post_thumbnail($sid, 'medium'); else echo '<div style="width:80px;height:110px;display:flex;align-items:center;justify-content:center;color:#555;background:#2a2a4e;border-radius:8px;">📚</div>'; ?>
                </a>
            </div>
            <div class="story-info">
                <a href="<?php echo get_permalink($sid); ?>"><h3 class="story-title"><?php echo $story->post_title; ?></h3></a>
                <?php if ($ch): ?>
                    <div class="story-meta"><a href="<?php echo get_permalink($ch->ID); ?>" style="color:#f0c040;">Tiếp tục đọc →</a></div>
                <?php endif; ?>
                <div class="story-excerpt" style="font-size:12px;color:#666;">Đọc lúc: <?php echo $hdata['time']; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
