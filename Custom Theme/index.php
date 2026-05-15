<?php get_header(); ?>

<section class="hero">
    <div class="container">

        <h1>Welcome to My Website</h1>

        <p>
            Simple lightweight WordPress theme.
        </p>

    </div>
</section>

<div class="container">

    <?php if(have_posts()) : ?>

        <?php while(have_posts()) : the_post(); ?>

            <article>

                <h2><?php the_title(); ?></h2>

                <?php the_content(); ?>

            </article>

        <?php endwhile; ?>

    <?php endif; ?>

</div>

<?php get_footer(); ?>
