<?php
get_header();
while ( have_posts() ) :
	the_post();
	$pid           = get_the_ID();
	$company       = get_post_meta( $pid, 'project_company', true );
	$background    = get_post_meta( $pid, 'project_background', true );
	$solution      = get_post_meta( $pid, 'project_solution', true );
	$result        = get_post_meta( $pid, 'project_result', true );
	$case_film     = get_post_meta( $pid, 'project_case_film', true );
	$members       = berghs_get_project_members( $pid );
	$is_individual = get_post_meta( $pid, 'project_is_individual', true );
	$programs      = get_the_terms( $pid, 'program' );
	?>

<article>
	<div class="project-hero">
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="project-hero__image"><?php the_post_thumbnail( 'project-hero' ); ?></div>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<?php
			if ( $company ) :
				?>
				<p style="color:#666"><?php echo esc_html( $company ); ?></p><?php endif; ?>
			<?php if ( $programs && ! is_wp_error( $programs ) ) : ?>
				<div class="project-tags">
					<?php foreach ( $programs as $prog ) : ?>
						<a href="<?php echo esc_url( get_term_link( $prog ) ); ?>"><?php echo esc_html( $prog->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="project-content">
		<div class="container">
			<?php
			if ( $background ) :
				?>
				<h2>Background</h2><p><?php echo esc_html( $background ); ?></p><?php endif; ?>
			<?php
			if ( $solution ) :
				?>
				<h2>Solution</h2><p><?php echo esc_html( $solution ); ?></p><?php endif; ?>
			<?php
			if ( $result ) :
				?>
				<h2>Result</h2><p><?php echo esc_html( $result ); ?></p><?php endif; ?>
			<?php
			$content = get_the_content();
			if ( $content ) :
				the_content();
endif;
			?>
		</div>
	</div>

	<?php if ( $case_film ) : ?>
		<div class="container project-video">
			<?php echo berghs_get_video_embed( $case_film ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $members ) ) : ?>
		<div class="container project-members">
			<h2><?php echo $is_individual ? 'Creator' : 'Team Members'; ?></h2>
			<ul>
				<?php foreach ( $members as $m ) : ?>
					<li>
						<strong><?php echo esc_html( $m['name'] ); ?></strong>
						<?php if ( ! empty( $m['program_class'] ) ) : ?>
							<br><small><?php echo esc_html( $m['program_class'] ); ?></small>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<div class="section">
		<div class="container">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>">&larr; All projects</a>
		</div>
	</div>
</article>

	<?php
endwhile;
get_footer();
