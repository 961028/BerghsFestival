<?php get_header(); ?>
<div class="section">
    <div class="container">
        <h1>News</h1>
        <?php if ( have_posts() ) : ?>
            <div class="card-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="card">
                        <a href="<?php the_permalink(); ?>">
                            <h2 class="card__title"><?php the_title(); ?></h2>
                            <p class="card__meta"><?php echo get_the_date(); ?></p>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p>No posts found.</p>
        <?php endif; ?>
    </div>
</div>
<?php get_footer(); ?>
