<?php
get_header();
while ( have_posts() ) : the_post();
?>
<div class="section">
    <div class="container page-content">
        <h1><?php the_title(); ?></h1>
        <?php the_content(); ?>
    </div>
</div>
<?php endwhile;
get_footer();
