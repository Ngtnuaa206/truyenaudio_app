<?php /* Template Name: Theo dõi */
ta_require_auth();
get_header();
$bookmarks = get_user_meta(get_current_user_id(), '_bookmarks', true) ?: [];
?>

<div class="container" style="padding:40px 15px;">
    <h1 style="color:#fff;margin-bottom:20px;">📚 Truyện theo dõi</h1>

    <?php if (empty($bookmarks)): ?>
        <p style="text-align:center;padding:60px;color:#888;">Bạn chưa theo dõi truyện nào. <a href="<?php echo home_url('/truyen'); ?>">Khám phá ngay</a></p>
    <?php else: ?>
    <div class="story-list">
        <?php foreach ($bookmarks as $bid): $story = get_post($bid); if (!$story) continue;
            $chapters = ta_get_chapters($bid);
        ?>
        <div class="story-row">
            <div class="story-thumb">
                <a href="<?php echo get_permalink($bid); ?>">
                    <?php if (has_post_thumbnail($bid)) echo get_the_post_thumbnail($bid, 'medium'); else echo '<div style="width:80px;height:110px;display:flex;align-items:center;justify-content:center;color:#555;background:#2a2a4e;border-radius:8px;">📚</div>'; ?>
                </a>
            </div>
            <div class="story-info">
                <a href="<?php echo get_permalink($bid); ?>"><h3 class="story-title"><?php echo $story->post_title; ?></h3></a>
                <div class="story-meta">
                    <span>📖 <?php echo count($chapters); ?> chương</span>
                </div>
                <div class="story-excerpt"><?php echo wp_trim_words($story->post_excerpt, 15); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
