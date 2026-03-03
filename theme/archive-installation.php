<?php get_header(); ?>
<div class="section">
    <div class="container">
        <h1>Installations</h1>

        <?php if ( have_posts() ) : ?>
            <div class="card-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="card">
                        <a href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="card__image"><?php the_post_thumbnail( 'project-card' ); ?></div>
                            <?php endif; ?>
                            <h2 class="card__title"><?php the_title(); ?></h2>
                            <?php if ( has_excerpt() ) : ?>
                                <p class="card__meta"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
                            <?php endif; ?>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p>Installations will be announced soon.</p>
        <?php endif; ?>
    </div>
</div>
<?php get_footer(); ?>
