<?php get_header(); ?>

<section class="hero">
    <div class="container">
        <h1>Truyen<span>Audio</span> - Nghe là nghiện, Đọc là Mê</h1>
        <p>Kho truyện audio khổng lồ với hàng ngàn bộ Xuyên Không, Tiên Hiệp, Huyền Huyễn, Ngôn Tình,... hoàn toàn miễn phí. Hỗ trợ cả nghe truyện audio và đọc truyện chữ.</p>
        <div class="hero-buttons">
            <a href="<?php echo home_url('/truyen'); ?>" class="btn btn-primary">Khám Phá Ngay</a>
            <a href="<?php echo home_url('/bang-xep-hang'); ?>" class="btn btn-outline">Bảng Xếp Hạng</a>
        </div>
    </div>
</section>

<div class="container">
    <!-- Filter -->
    <section class="section">
        <form class="filter-bar" method="get" action="<?php echo home_url('/truyen'); ?>">
            <div class="filter-row">
                <div>
                    <label>Thể loại</label>
                    <select name="the_loai">
                        <option value="">Tất cả thể loại</option>
                        <?php $terms = get_terms(['taxonomy' => 'the_loai', 'hide_empty' => false]);
                        foreach ($terms as $t) echo '<option value="' . $t->slug . '">' . $t->name . ' (' . $t->count . ')</option>'; ?>
                    </select>
                </div>
                <div>
                    <label>Trạng thái</label>
                    <select name="trang_thai">
                        <option value="">Tất cả trạng thái</option>
                        <option value="full">Đã hoàn thành (Full)</option>
                        <option value="dang-tien-hanh">Còn tiếp (On-going)</option>
                    </select>
                </div>
                <div>
                    <label>Sắp xếp</label>
                    <select name="orderby">
                        <option value="date">Mới cập nhật</option>
                        <option value="views">Lượt xem nhiều</option>
                        <option value="title">Tên A-Z</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Lọc truyện</button>
                </div>
            </div>
        </form>
    </section>

    <!-- New Stories -->
    <section class="section">
        <div class="section-header">
            <h2>Truyện Mới Đăng</h2>
            <a href="<?php echo home_url('/truyen'); ?>" class="view-all">Xem tất cả →</a>
        </div>
        <div class="story-grid">
            <?php
            $new = new WP_Query(['post_type' => 'truyen', 'posts_per_page' => 12]);
            while ($new->have_posts()): $new->the_post();
                $views = get_post_meta(get_the_ID(), '_views', true) ?: 0;
                $rating = get_post_meta(get_the_ID(), '_rating', true) ?: 0;
                $terms = wp_get_post_terms(get_the_ID(), 'the_loai');
                $chapters = ta_get_chapters(get_the_ID());
            ?>
            <div class="story-card">
                <a href="<?php the_permalink(); ?>">
                    <div class="story-thumb">
                        <?php if (has_post_thumbnail()) the_post_thumbnail('medium'); else echo '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#555;">📚</div>'; ?>
                        <span class="story-badge new">NEW</span>
                    </div>
                </a>
                <div class="story-info">
                    <a href="<?php the_permalink(); ?>"><h3 class="story-title"><?php the_title(); ?></h3></a>
                    <div class="story-meta">
                        <span><?php echo count($chapters); ?> Chương</span>
                        <?php if (!empty($terms)): ?>
                            <span><?php echo $terms[0]->name; ?></span>
                        <?php endif; ?>
                        <br>
                        <span class="views"><?php echo ta_views($views); ?> views</span>
                        <?php echo ta_get_stars($rating); ?>
                    </div>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </section>

    <!-- Trending -->
    <section class="section">
        <div class="section-header">
            <h2>Truyện Trending</h2>
            <a href="<?php echo home_url('/truyen'); ?>" class="view-all">Xem tất cả →</a>
        </div>
        <div class="story-list">
            <?php
            $trending = new WP_Query([
                'post_type' => 'truyen',
                'posts_per_page' => 8,
                'meta_key' => '_views',
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
            ]);
            while ($trending->have_posts()): $trending->the_post();
                $views = get_post_meta(get_the_ID(), '_views', true) ?: 0;
                $rating = get_post_meta(get_the_ID(), '_rating', true) ?: 0;
                $terms = wp_get_post_terms(get_the_ID(), 'the_loai');
                $chapters = ta_get_chapters(get_the_ID());
            ?>
            <div class="story-row">
                <div class="story-thumb">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) the_post_thumbnail('medium'); else echo '<div style="width:80px;height:110px;display:flex;align-items:center;justify-content:center;color:#555;">📚</div>'; ?>
                    </a>
                </div>
                <div class="story-info">
                    <a href="<?php the_permalink(); ?>"><h3 class="story-title"><?php the_title(); ?></h3></a>
                    <div class="story-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></div>
                    <div class="story-meta">
                        <span><?php echo count($chapters); ?> Chương</span>
                        <?php if (!empty($terms)): ?>
                            <span><?php echo $terms[0]->name; ?></span>
                        <?php endif; ?>
                        <span class="views">👁 <?php echo ta_views($views); ?></span>
                        <?php echo ta_get_stars($rating); ?>
                    </div>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </section>

    <!-- Categories -->
    <section class="section">
        <div class="section-header">
            <h2>Khám Phá Thể Loại</h2>
        </div>
        <div class="category-grid">
            <?php
            $cats = get_terms(['taxonomy' => 'the_loai', 'hide_empty' => false]);
            foreach ($cats as $c) {
                echo '<a href="' . get_term_link($c) . '" class="category-item">';
                echo '<span class="cat-name">' . $c->name . '</span>';
                echo '<span class="cat-count">' . $c->count . ' truyện</span>';
                echo '</a>';
            }
            ?>
        </div>
    </section>

    <!-- Popular -->
    <section class="section">
        <div class="section-header">
            <h2>Truyện Phổ Biến</h2>
            <a href="<?php echo home_url('/truyen'); ?>" class="view-all">Xem tất cả →</a>
        </div>
        <div class="story-grid">
            <?php
            $popular = new WP_Query([
                'post_type' => 'truyen',
                'posts_per_page' => 8,
                'meta_key' => '_rating',
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
            ]);
            while ($popular->have_posts()): $popular->the_post();
                $views = get_post_meta(get_the_ID(), '_views', true) ?: 0;
                $rating = get_post_meta(get_the_ID(), '_rating', true) ?: 0;
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
                        <span><?php echo count($chapters); ?> Chương</span>
                        <span class="views"><?php echo ta_views($views); ?> views</span>
                        <?php echo ta_get_stars($rating); ?>
                    </div>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </section>
</div>

<?php get_footer(); ?>
