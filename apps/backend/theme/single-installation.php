<?php
get_header();
while ( have_posts() ) :
	the_post();
	?>
<article>
	<div class="project-hero">
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="project-hero__image"><?php the_post_thumbnail( 'project-hero' ); ?></div>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
		</div>
	</div>
	<div class="project-content">
		<div class="container">
			<?php the_content(); ?>
		</div>
	</div>
	<div class="section">
		<div class="container">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'installation' ) ); ?>">&larr; All installations</a>
		</div>
	</div>
</article>
	<?php
endwhile;
get_footer();
