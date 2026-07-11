<?php
// Sidebar widgets
$current_post_id = is_singular() ? get_the_ID() : 0;
$story_id = 0;
if (is_singular('chapter')) {
    $story_id = get_post_meta(get_the_ID(), '_story_id', true);
} elseif (is_singular('truyen')) {
    $story_id = get_the_ID();
}

// Helper: format views
function ta_format_views($views) {
    if ($views >= 1000000) return round($views / 1000000, 1) . 'M';
    if ($views >= 1000) return round($views / 1000, 1) . 'K';
    return $views;
}
?>

<!-- Stories You May Like -->
<?php if (is_user_logged_in()): ?>
<?php
$user_id = get_current_user_id();
$history = get_user_meta($user_id, '_reading_history', true) ?: [];
$bookmarks = get_user_meta($user_id, '_bookmarks', true) ?: [];

// Get genres from reading history
$history_genres = [];
if (!empty($history)) {
    foreach (array_slice(array_keys($history), 0, 10) as $sid) {
        $terms = wp_get_post_terms($sid, 'the_loai', ['fields' => 'ids']);
        $history_genres = array_merge($history_genres, $terms);
    }
    $history_genres = array_unique($history_genres);
}

if (!empty($history_genres)) {
    $related_args = [
        'post_type' => 'truyen',
        'posts_per_page' => 5,
        'post__not_in' => array_merge([$story_id], $bookmarks),
        'meta_key' => '_views',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'tax_query' => [
            [
                'taxonomy' => 'the_loai',
                'field' => 'term_id',
                'terms' => $history_genres,
            ]
        ]
    ];
    $related_query = new WP_Query($related_args);
    if ($related_query->have_posts()):
?>
<div class="sidebar-widget">
    <div class="sidebar-widget-title">💡 Có thể bạn thích</div>
    <?php while ($related_query->have_posts()): $related_query->the_post();
        $rviews = get_post_meta(get_the_ID(), '_views', true) ?: 0;
        $rgenres = wp_get_post_terms(get_the_ID(), 'the_loai', ['fields' => 'names']);
    ?>
    <a href="<?php the_permalink(); ?>" class="sidebar-story-item">
        <div class="sidebar-story-thumb">
            <?php if (has_post_thumbnail()) the_post_thumbnail('thumbnail'); else echo '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">📚</div>'; ?>
        </div>
        <div class="sidebar-story-info">
            <div class="sidebar-story-title"><?php the_title(); ?></div>
            <div class="sidebar-story-meta"><?php echo !empty($rgenres) ? $rgenres[0] : ''; ?> · 👁 <?php echo ta_format_views($rviews); ?></div>
        </div>
    </a>
    <?php endwhile; wp_reset_postdata(); ?>
</div>
<?php endif; } endif; ?>

<!-- Truyện Mới Cập Nhật -->
<?php
$new_args = [
    'post_type' => 'truyen',
    'posts_per_page' => 5,
    'orderby' => 'modified',
    'order' => 'DESC',
];
$new_query = new WP_Query($new_args);
if ($new_query->have_posts()):
?>
<div class="sidebar-widget">
    <div class="sidebar-widget-title">🆕 Truyện mới cập nhật</div>
    <?php while ($new_query->have_posts()): $new_query->the_post();
        $nviews = get_post_meta(get_the_ID(), '_views', true) ?: 0;
        $ngenres = wp_get_post_terms(get_the_ID(), 'the_loai', ['fields' => 'names']);
        $nchapters = count(ta_get_chapters(get_the_ID()));
    ?>
    <a href="<?php the_permalink(); ?>" class="sidebar-story-item">
        <div class="sidebar-story-thumb">
            <?php if (has_post_thumbnail()) the_post_thumbnail('thumbnail'); else echo '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">📚</div>'; ?>
        </div>
        <div class="sidebar-story-info">
            <div class="sidebar-story-title"><?php the_title(); ?></div>
            <div class="sidebar-story-meta"><?php echo !empty($ngenres) ? $ngenres[0] : ''; ?> · 📖 <?php echo $nchapters; ?> chương</div>
        </div>
    </a>
    <?php endwhile; wp_reset_postdata(); ?>
</div>
<?php endif; ?>

<!-- Truyện Nổi Bật -->
<?php
$featured_args = [
    'post_type' => 'truyen',
    'posts_per_page' => 5,
    'meta_key' => '_views',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
];
$featured_query = new WP_Query($featured_args);
if ($featured_query->have_posts()):
?>
<div class="sidebar-widget">
    <div class="sidebar-widget-title">🔥 Truyện nổi bật</div>
    <?php while ($featured_query->have_posts()): $featured_query->the_post();
        $fviews = get_post_meta(get_the_ID(), '_views', true) ?: 0;
        $frating = get_post_meta(get_the_ID(), '_rating', true) ?: 0;
    ?>
    <a href="<?php the_permalink(); ?>" class="sidebar-story-item">
        <div class="sidebar-story-thumb">
            <?php if (has_post_thumbnail()) the_post_thumbnail('thumbnail'); else echo '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">📚</div>'; ?>
        </div>
        <div class="sidebar-story-info">
            <div class="sidebar-story-title"><?php the_title(); ?></div>
            <div class="sidebar-story-meta">👁 <?php echo ta_format_views($fviews); ?> · ⭐ <?php echo number_format($frating, 1); ?></div>
        </div>
    </a>
    <?php endwhile; wp_reset_postdata(); ?>
</div>
<?php endif; ?>

<!-- Bảng Xếp Hạng -->
<?php
$ranking_args = [
    'post_type' => 'truyen',
    'posts_per_page' => 10,
    'meta_key' => '_rating_count',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
];
$ranking_query = new WP_Query($ranking_args);
if ($ranking_query->have_posts()):
?>
<div class="sidebar-widget">
    <div class="sidebar-widget-title">🏆 Bảng xếp hạng</div>
    <?php $rank = 1; while ($ranking_query->have_posts()): $ranking_query->the_post();
        $rcount = get_post_meta(get_the_ID(), '_rating_count', true) ?: 0;
        $rrating = get_post_meta(get_the_ID(), '_rating', true) ?: 0;
    ?>
    <div class="sidebar-ranking-item">
        <span class="sidebar-rank-num <?php echo $rank <= 3 ? 'r' . $rank : ''; ?>"><?php echo $rank; ?></span>
        <a href="<?php the_permalink(); ?>" class="sidebar-story-info">
            <div class="sidebar-rank-title"><?php the_title(); ?></div>
            <div class="sidebar-rank-meta">⭐ <?php echo number_format($rrating, 1); ?> (<?php echo $rcount; ?> đánh giá)</div>
        </a>
    </div>
    <?php $rank++; endwhile; wp_reset_postdata(); ?>
</div>
<?php endif; ?>

<!-- Thể Loại -->
<?php
$genres_list = get_terms([
    'taxonomy' => 'the_loai',
    'hide_empty' => true,
    'number' => 20,
]);
if (!empty($genres_list) && !is_wp_error($genres_list)):
?>
<div class="sidebar-widget">
    <div class="sidebar-widget-title">📂 Thể loại</div>
    <div class="sidebar-genre-list">
        <?php foreach ($genres_list as $genre): ?>
        <a href="<?php echo get_term_link($genre); ?>" class="sidebar-genre-tag"><?php echo $genre->name; ?></a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Truyện Có Audio -->
<?php
$audio_args = [
    'post_type' => 'chapter',
    'posts_per_page' => 5,
    'meta_key' => '_audio_url',
    'meta_compare' => '!=',
    'meta_value' => '',
    'orderby' => 'date',
    'order' => 'DESC',
];
$audio_query = new WP_Query($audio_args);
if ($audio_query->have_posts()):
?>
<div class="sidebar-widget">
    <div class="sidebar-widget-title">🎧 Mới nghe</div>
    <?php while ($audio_query->have_posts()): $audio_query->the_post();
        $chapter_story_id = get_post_meta(get_the_ID(), '_story_id', true);
        $chapter_story = $chapter_story_id ? get_post($chapter_story_id) : null;
        if (!$chapter_story) continue;
        $ch_num = get_post_meta(get_the_ID(), '_chapter_number', true);
    ?>
    <a href="<?php the_permalink(); ?>" class="sidebar-story-item">
        <div class="sidebar-story-thumb">
            <?php if (has_post_thumbnail($chapter_story->ID)) echo get_the_post_thumbnail($chapter_story->ID, 'thumbnail'); else echo '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">🎧</div>'; ?>
        </div>
        <div class="sidebar-story-info">
            <div class="sidebar-story-title"><?php echo $chapter_story->post_title; ?></div>
            <div class="sidebar-story-meta">Chương <?php echo $ch_num; ?> · 🎧 Audio</div>
        </div>
    </a>
    <?php endwhile; wp_reset_postdata(); ?>
</div>
<?php endif; ?>