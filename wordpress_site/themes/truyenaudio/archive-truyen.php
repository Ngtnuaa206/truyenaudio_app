<?php get_header(); ?>
<div class="container" style="padding:40px 15px;">
    <h1 style="color:#fff;margin-bottom:20px;font-size:28px;">
        <?php
        if (is_tax()) echo single_term_title();
        else echo 'Tất cả truyện';
        ?>
    </h1>

    <!-- Filter -->
    <form class="filter-bar" method="get">
        <div class="filter-row">
            <div>
                <label>Thể loại</label>
                <select name="the_loai">
                    <option value="">Tất cả</option>
                    <?php
                    $terms = get_terms(['taxonomy' => 'the_loai', 'hide_empty' => false]);
                    $selected_cat = isset($_GET['the_loai']) ? $_GET['the_loai'] : '';
                    foreach ($terms as $t) {
                        echo '<option value="' . $t->slug . '" ' . selected($selected_cat, $t->slug, false) . '>' . $t->name . ' (' . $t->count . ')</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <label>Tác giả</label>
                <select name="tac_gia">
                    <option value="">Tất cả</option>
                    <?php
                    $authors = get_terms(['taxonomy' => 'tac_gia', 'hide_empty' => false]);
                    $selected_author = isset($_GET['tac_gia']) ? $_GET['tac_gia'] : '';
                    foreach ($authors as $a) {
                        echo '<option value="' . $a->slug . '" ' . selected($selected_author, $a->slug, false) . '>' . $a->name . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <label>Trạng thái</label>
                <select name="trang_thai">
                    <option value="">Tất cả</option>
                    <option value="full" <?php selected(isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'full'); ?>>Đã hoàn thành</option>
                    <option value="dang-tien-hanh" <?php selected(isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'dang-tien-hanh'); ?>>Còn tiếp</option>
                </select>
            </div>
            <div>
                <label>Sắp xếp</label>
                <select name="orderby">
                    <option value="date" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] == 'date'); ?>>Mới cập nhật</option>
                    <option value="views" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] == 'views'); ?>>Lượt xem nhiều</option>
                    <option value="title" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] == 'title'); ?>>Tên A-Z</option>
                    <option value="chapters" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] == 'chapters'); ?>>Số chương</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Lọc</button>
            </div>
        </div>
    </form>

    <?php
    // Build query
    $paged = get_query_var('paged') ?: 1;
    $args = ['post_type' => 'truyen', 'posts_per_page' => 24, 'paged' => $paged];

    if (!empty($_GET['the_loai'])) {
        $args['tax_query'][] = ['taxonomy' => 'the_loai', 'field' => 'slug', 'terms' => sanitize_title($_GET['the_loai'])];
    }
    if (!empty($_GET['tac_gia'])) {
        $args['tax_query'][] = ['taxonomy' => 'tac_gia', 'field' => 'slug', 'terms' => sanitize_title($_GET['tac_gia'])];
    }
    if (!empty($_GET['trang_thai'])) {
        $args['tax_query'][] = ['taxonomy' => 'trang_thai', 'field' => 'slug', 'terms' => sanitize_title($_GET['trang_thai'])];
    }

    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'date';
    if ($orderby == 'views') { $args['meta_key'] = '_views'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
    elseif ($orderby == 'title') { $args['orderby'] = 'title'; $args['order'] = 'ASC'; }
    elseif ($orderby == 'date') { $args['orderby'] = 'date'; $args['order'] = 'DESC'; }

    $query = new WP_Query($args);
    ?>

    <?php if ($query->have_posts()): ?>
    <div class="story-grid">
        <?php while ($query->have_posts()): $query->the_post();
            $views = get_post_meta(get_the_ID(), '_views', true) ?: 0;
            $rating = get_post_meta(get_the_ID(), '_rating', true) ?: 0;
            $chapters = ta_get_chapters(get_the_ID());
            $statuses = wp_get_post_terms(get_the_ID(), 'trang_thai');
            $has_badge = !empty($statuses) && $statuses[0]->slug == 'full';
        ?>
        <div class="story-card">
            <a href="<?php the_permalink(); ?>">
                <div class="story-thumb">
                    <?php if (has_post_thumbnail()) the_post_thumbnail('medium'); else echo '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#555;">📚</div>'; ?>
                    <?php if ($has_badge): ?>
                        <span class="story-badge full">FULL</span>
                    <?php endif; ?>
                </div>
            </a>
            <div class="story-info">
                <a href="<?php the_permalink(); ?>"><h3 class="story-title"><?php the_title(); ?></h3></a>
                <div class="story-meta">
                    <span><?php echo count($chapters); ?> Chương</span><br>
                    <span class="views">👁 <?php echo ta_views($views); ?></span>
                    <?php echo ta_get_stars($rating); ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <div style="margin-top:30px;text-align:center;">
        <?php
        $wp_query = $query;
        ta_pagination();
        ?>
    </div>
    <?php else: ?>
    <p style="text-align:center;padding:40px;color:#888;">Chưa có truyện nào.</p>
    <?php endif; wp_reset_postdata(); ?>
</div>
<?php get_footer(); ?>
