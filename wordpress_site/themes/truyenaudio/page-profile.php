<?php /* Template Name: Profile */ 
ta_require_auth();
get_header();
$user = wp_get_current_user();
$lt = get_user_meta($user->ID, '_linh_thach', true) ?: 0;
$bookmarks = get_user_meta($user->ID, '_bookmarks', true) ?: [];
$history = get_user_meta($user->ID, '_reading_history', true) ?: [];
$is_admin = in_array('administrator', (array) $user->roles);
?>

<div class="container profile-section">
    <div class="profile-card">
        <h3>👤 Xin chào, <?php echo $user->display_name; ?> <?php echo ta_user_role_badge($user->ID); ?></h3>
        <div class="linh-thach-box">
            <span class="lt-icon">💎</span>
            <span><strong><?php echo number_format($lt); ?></strong> Linh Thạch</span>
        </div>
        <p style="margin-top:15px;color:#888;font-size:14px;">Email: <?php echo $user->user_email; ?></p>
        <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?php echo home_url('/theo-doi'); ?>" class="btn btn-outline">📚 Truyện theo dõi (<?php echo count($bookmarks); ?>)</a>
            <a href="<?php echo home_url('/lich-su'); ?>" class="btn btn-outline">📖 Lịch sử đọc</a>
            <a href="<?php echo home_url('/linh-thach'); ?>" class="btn btn-outline">💎 Nạp Linh Thạch</a>
        </div>
    </div>

    <?php if (!empty($bookmarks)): ?>
    <div class="profile-card">
        <h3>📚 Truyện đang theo dõi</h3>
        <div class="story-list">
            <?php foreach ($bookmarks as $bid): $story = get_post($bid); if (!$story) continue; ?>
            <div class="story-row">
                <div class="story-thumb">
                    <a href="<?php echo get_permalink($bid); ?>">
                        <?php if (has_post_thumbnail($bid)) echo get_the_post_thumbnail($bid, 'medium'); else echo '<div style="width:80px;height:110px;display:flex;align-items:center;justify-content:center;color:#555;background:#2a2a4e;border-radius:8px;">📚</div>'; ?>
                    </a>
                </div>
                <div class="story-info">
                    <a href="<?php echo get_permalink($bid); ?>"><h3 class="story-title"><?php echo $story->post_title; ?></h3></a>
                    <div class="story-excerpt"><?php echo wp_trim_words($story->post_excerpt, 15); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($history)): $history = array_reverse($history); ?>
    <div class="profile-card">
        <h3>📖 Đọc gần đây</h3>
        <div class="story-list">
            <?php $count = 0; foreach ($history as $sid => $hdata): if ($count++ >= 5) break; $story = get_post($sid); if (!$story) continue; ?>
            <div class="story-row">
                <div class="story-thumb">
                    <a href="<?php echo get_permalink($hdata['chapter_id']); ?>">
                        <?php if (has_post_thumbnail($sid)) echo get_the_post_thumbnail($sid, 'medium'); else echo '<div style="width:80px;height:110px;display:flex;align-items:center;justify-content:center;color:#555;background:#2a2a4e;border-radius:8px;">📚</div>'; ?>
                    </a>
                </div>
                <div class="story-info">
                    <a href="<?php echo get_permalink($sid); ?>"><h3 class="story-title"><?php echo $story->post_title; ?></h3></a>
                    <div class="story-meta">
                        <span>Đọc lúc: <?php echo $hdata['time']; ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
