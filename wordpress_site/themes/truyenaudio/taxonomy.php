<?php
// Taxonomy archive for the_loai and tac_gia
get_header();

$tax = get_queried_object();
?>

<div class="container" style="padding:40px 15px;">
    <h1 style="color:#fff;margin-bottom:5px;font-size:28px;"><?php echo $tax->name; ?></h1>
    <p style="color:#888;margin-bottom:30px;"><?php echo $tax->description ?: $tax->count . ' truyện'; ?></p>

    <?php
    $paged = get_query_var('paged') ?: 1;
    $query = new WP_Query([
        'post_type' => 'truyen',
        'posts_per_page' => 24,
        'paged' => $paged,
        'tax_query' => [[
            'taxonomy' => $tax->taxonomy,
            'field' => 'term_id',
            'terms' => $tax->term_id,
        ]],
    ]);

    if ($query->have_posts()): ?>
    <div class="story-grid">
        <?php while ($query->have_posts()): $query->the_post();
            $views = get_post_meta(get_the_ID(), '_views', true) ?: 0;
            $chapters = ta_get_chapters(get_the_ID());
        ?>
        <div class="story-card">
            <a href="<?php the_permalink(); ?>">
                <div class="story-thumb">
                    <?php if (has_post_thumbnail()) the_post_thumbnail('medium'); else echo '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#555;">📚</div>'; ?>
                </div>
            </a>
            <div class="story-info">
                <a href="<?php the_permalink(); ?>"><h3 class="story-title"><?php the_title(); ?></h3></a>
                <div class="story-meta">
                    <span><?php echo count($chapters); ?> Chương</span><br>
                    <span class="views"><?php echo ta_views($views); ?> views</span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <div style="margin-top:30px;text-align:center;">
        <?php $wp_query = $query; ta_pagination(); ?>
    </div>
    <?php else: ?>
    <p style="text-align:center;padding:40px;color:#888;">Chưa có truyện trong thể loại này.</p>
    <?php endif; wp_reset_postdata(); ?>
</div>

<?php get_footer(); ?>
