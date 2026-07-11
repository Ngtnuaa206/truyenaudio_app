<?php /* Template Name: Truyện của tôi */
ta_require_role(['tac_gia_role', 'administrator']);
get_header();
$user = wp_get_current_user();

$paged = get_query_var('paged') ?: 1;
$stories = new WP_Query([
    'post_type' => 'truyen',
    'author' => $user->ID,
    'posts_per_page' => 20,
    'paged' => $paged,
]);
?>

<div class="container" style="padding:40px 15px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;margin-bottom:30px;">
        <h1 style="color:#fff;">📚 Truyện của tôi</h1>
        <a href="<?php echo admin_url('post-new.php?post_type=truyen'); ?>" class="btn btn-primary">+ Đăng truyện mới</a>
    </div>

    <?php if ($stories->have_posts()): ?>
    <div class="story-list">
        <?php while ($stories->have_posts()): $stories->the_post();
            $views = get_post_meta(get_the_ID(), '_views', true) ?: 0;
            $chapters = ta_get_chapters(get_the_ID());
            $revenue = get_post_meta(get_the_ID(), '_story_revenue', true) ?: 0;
        ?>
        <div class="story-row">
            <div class="story-thumb">
                <a href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()) the_post_thumbnail('medium'); else echo '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#555;">📚</div>'; ?>
                </a>
            </div>
            <div class="story-info">
                <a href="<?php the_permalink(); ?>"><h3 class="story-title"><?php the_title(); ?></h3></a>
                <div class="story-meta" style="margin-top:8px;display:flex;gap:15px;flex-wrap:wrap;">
                    <span>📖 <?php echo count($chapters); ?> chương</span>
                    <span class="views">👁 <?php echo number_format($views); ?></span>
                    <span style="color:#2ecc71;">💎 <?php echo number_format($revenue); ?> doanh thu</span>
                </div>
                <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="<?php echo get_permalink(); ?>" class="btn btn-sm btn-outline">Xem</a>
                    <a href="<?php echo home_url('/tac-gia-dashboard?action=edit&story_id=' . get_the_ID()); ?>" class="btn btn-sm btn-outline">Sửa</a>
                    <a href="<?php echo admin_url('post-new.php?post_type=chapter'); ?>" class="btn btn-sm btn-primary">+ Thêm chương</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <div style="margin-top:30px;text-align:center;">
        <?php $wp_query = $stories; ta_pagination(); ?>
    </div>
    <?php else: ?>
        <div class="profile-card" style="text-align:center;padding:60px;">
            <div style="font-size:48px;margin-bottom:15px;">📚</div>
            <p style="color:#888;font-size:16px;">Bạn chưa đăng truyện nào.</p>
            <a href="<?php echo admin_url('post-new.php?post_type=truyen'); ?>" class="btn btn-primary" style="margin-top:15px;">Đăng truyện đầu tiên</a>
        </div>
    <?php endif; wp_reset_postdata(); ?>
</div>

<?php get_footer(); ?>
