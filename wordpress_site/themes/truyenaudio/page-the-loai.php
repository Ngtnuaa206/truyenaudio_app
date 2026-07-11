<?php /* Template Name: Thể loại */ get_header(); ?>

<div class="container" style="padding:40px 15px;">
    <h1 style="color:#fff;margin-bottom:10px;">📂 Thể Loại</h1>
    <p style="color:#888;margin-bottom:30px;">Khám phá truyện theo thể loại yêu thích.</p>
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
</div>

<?php get_footer(); ?>
