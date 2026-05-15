<?php get_header(); ?>

<main id="primary" class="site-main">
    <?php if (is_singular('page') || is_front_page()) : ?>
        <?php sls_render_page_sections(); ?>

        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <?php if (trim(wp_strip_all_tags(get_the_content()))) : ?>
                    <div class="sls-container sls-page-content">
                        <article id="post-<?php the_ID(); ?>" <?php post_class('sls-entry'); ?>>
                            <?php the_content(); ?>
                        </article>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php endif; ?>
    <?php elseif (have_posts()) : ?>
        <div class="sls-container sls-post-list">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('sls-entry'); ?>>
                    <h2>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    <?php the_excerpt(); ?>
                </article>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
