<?php
get_header();
while ( have_posts() ) : the_post();
?>
<div class="section">
    <div class="container page-content">
        <h1><?php the_title(); ?></h1>
        <p><small><?php echo get_the_date(); ?></small></p>
        <?php if ( has_post_thumbnail() ) : ?>
            <div style="margin:1.5rem 0"><?php the_post_thumbnail( 'project-hero' ); ?></div>
        <?php endif; ?>
        <?php the_content(); ?>
    </div>
</div>
<?php endwhile;
get_footer();
