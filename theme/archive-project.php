<?php get_header(); ?>
<div class="section">
	<div class="container">
		<h1>Student Projects</h1>

		<?php
		$programs = get_terms(
			array(
				'taxonomy'   => 'program',
				'hide_empty' => true,
			)
		);
		if ( ! empty( $programs ) && ! is_wp_error( $programs ) ) :
			?>
			<div class="filter-bar">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>" class="<?php echo ! is_tax( 'program' ) ? 'is-active' : ''; ?>">All</a>
				<?php foreach ( $programs as $program ) : ?>
					<a href="<?php echo esc_url( get_term_link( $program ) ); ?>" class="<?php echo is_tax( 'program', $program->slug ) ? 'is-active' : ''; ?>"><?php echo esc_html( $program->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="card-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="card">
						<a href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="card__image"><?php the_post_thumbnail( 'project-card' ); ?></div>
							<?php endif; ?>
							<h2 class="card__title"><?php the_title(); ?></h2>
							<?php $company = get_post_meta( get_the_ID(), 'project_company', true ); ?>
							<?php
							if ( $company ) :
								?>
								<p class="card__meta"><?php echo esc_html( $company ); ?></p><?php endif; ?>
						</a>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p>No projects published yet.</p>
		<?php endif; ?>
	</div>
</div>
<?php get_footer(); ?>
