<?php /* Template Name: Bảng xếp hạng */ get_header(); ?>

<div class="container" style="padding:40px 15px;">
    <div style="text-align:center;margin-bottom:40px;">
        <h1 style="color:#fff;font-size:32px;">🏆 Bảng Xếp Hạng</h1>
        <p style="color:#888;">Nơi tôn vinh những đại năng có cống hiến to lớn cho cộng đồng.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;">
        <!-- Most Viewed -->
        <div class="profile-card">
            <h3>👁 Truyện xem nhiều nhất</h3>
            <div class="ranking-list">
                <?php $top_views = new WP_Query([
                    'post_type' => 'truyen', 'posts_per_page' => 10,
                    'meta_key' => '_views', 'orderby' => 'meta_value_num', 'order' => 'DESC',
                ]); $i = 1;
                while ($top_views->have_posts()): $top_views->the_post(); $views = get_post_meta(get_the_ID(), '_views', true) ?: 0; ?>
                <div class="ranking-item">
                    <span class="ranking-number <?php echo $i == 1 ? 'gold' : ($i == 2 ? 'silver' : ($i == 3 ? 'bronze' : '')); ?>">#<?php echo $i++; ?></span>
                    <div style="flex:1;">
                        <a href="<?php the_permalink(); ?>" style="color:#fff;"><?php the_title(); ?></a>
                        <div style="font-size:12px;color:#888;">👁 <?php echo ta_views($views); ?></div>
                    </div>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>

        <!-- Top Rated -->
        <div class="profile-card">
            <h3>⭐ Truyện đánh giá cao nhất</h3>
            <div class="ranking-list">
                <?php $top_rated = new WP_Query([
                    'post_type' => 'truyen', 'posts_per_page' => 10,
                    'meta_key' => '_rating', 'orderby' => 'meta_value_num', 'order' => 'DESC',
                    'meta_query' => [['key' => '_rating_count', 'value' => 1, 'compare' => '>=']],
                ]); $i = 1;
                while ($top_rated->have_posts()): $top_rated->the_post(); $rating = get_post_meta(get_the_ID(), '_rating', true) ?: 0; ?>
                <div class="ranking-item">
                    <span class="ranking-number <?php echo $i == 1 ? 'gold' : ($i == 2 ? 'silver' : ($i == 3 ? 'bronze' : '')); ?>">#<?php echo $i++; ?></span>
                    <div style="flex:1;">
                        <a href="<?php the_permalink(); ?>" style="color:#fff;"><?php the_title(); ?></a>
                        <div style="font-size:12px;color:#888;"><?php echo ta_get_stars($rating); ?></div>
                    </div>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </div>

    <!-- Top Contributors -->
    <div class="profile-card" style="margin-top:30px;">
        <h3>💎 Đại Gia Linh Thạch</h3>
        <p style="color:#888;font-size:13px;margin-bottom:15px;">Top người dùng có nhiều Linh Thạch nhất.</p>
        <div class="ranking-list">
            <?php
            $top_users = get_users([
                'meta_key' => '_linh_thach',
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
                'number' => 10,
            ]);
            $i = 1;
            foreach ($top_users as $u):
                $lt = get_user_meta($u->ID, '_linh_thach', true) ?: 0;
                if ($lt <= 0) continue;
            ?>
            <div class="ranking-item">
                <span class="ranking-number <?php echo $i == 1 ? 'gold' : ($i == 2 ? 'silver' : ($i == 3 ? 'bronze' : '')); ?>">#<?php echo $i++; ?></span>
                <div style="flex:1;">
                    <span style="color:#fff;"><?php echo $u->display_name; ?></span>
                    <div style="font-size:12px;color:#f0c040;">💎 <?php echo number_format($lt); ?> Linh Thạch</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
