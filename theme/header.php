<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="sr-only" href="#main-content">Skip to main content</a>

<header class="site-header">
	<div class="container">
		<?php if ( has_custom_logo() ) : ?>
			<a href="https://berghs.se" class="site-header__logo" aria-label="Berghs School of Communication">
				<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full' ); ?>
			</a>
		<?php else : ?>
			<a href="https://berghs.se" class="site-header__logo">Berghs Festival 2026</a>
		<?php endif; ?>

		<button class="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Toggle navigation">Menu</button>

		<nav id="primary-nav" class="site-header__nav" aria-label="Primary navigation">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'fallback_cb'    => 'berghs_fallback_menu',
					'depth'          => 1,
				)
			);
			$ticket_url  = get_theme_mod( 'berghs_ticket_url', '' );
			$ticket_text = get_theme_mod( 'berghs_ticket_cta_text', 'Get Tickets' );
			if ( $ticket_url ) :
				?>
				<a href="<?php echo esc_url( $ticket_url ); ?>" class="site-header__cta" target="_blank" rel="noopener"><?php echo esc_html( $ticket_text ); ?></a>
			<?php endif; ?>
		</nav>
	</div>
</header>

<main id="main-content">
<?php
function berghs_fallback_menu() {
	$links = array(
		''               => 'Home',
		'projects/'      => 'Projects',
		'installations/' => 'Installations',
		'schedule/'      => 'Schedule',
		'map/'           => 'Map',
		'about/'         => 'About Berghs',
	);
	foreach ( $links as $slug => $label ) {
		echo '<a href="' . esc_url( home_url( '/' . $slug ) ) . '">' . esc_html( $label ) . '</a>';
	}
}
