<?php

$sponsors = app_sponsors_get();

$address = app_contact_get_address();

$phone = app_contact_get_phone();

$social_services = app_contact_get_social_services();

?>
</main>

<?php if ( ! empty( $sponsors ) ) : ?>
	<div class="container">
		<div class="sponsors-row">
			<?php foreach ( $sponsors as $sponsor ) : ?>
				<div class="sponsor-logo">
					<a
						href="<?php echo esc_url( $sponsor['url'] ); ?>"
						target="_blank"
						rel="noopener"
						title="<?php echo esc_attr( $sponsor['name'] ); ?>"
					>
						<?php app_attachment_img( $sponsor['image'] ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
</div>
<?php endif; ?>

<footer class="site-footer">
	<div class="container">
		<div class="site-footer__cols">
			<div class="site-footer__col">
				<h4>Contact</h4>

				<p><?php echo nl2br( esc_html( $address ) ); ?></p>

				<?php if ( $phone ) : ?>
					<p>
						<a
							href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"
						>
							<?php echo esc_html( $phone ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $social_services ) : ?>
				<div class="site-footer__col">
					<h4>Follow</h4>
					<ul>
						<?php foreach ( $social_services as $social_service ) : ?>
							<li>
								<a
									href="<?php echo esc_url( $social_service['url'] ); ?>"
									target="_blank"
									rel="noopener"
									title="<?php echo esc_attr( $social_service['label'] ); ?>"
								>
									<?php
									app_svg_img(
										$social_service['icon'],
										array(
											'class' => 'site-footer__social-icon',
										)
									);
									?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="site-footer__col">
				<h4>Navigate</h4>

				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</div>
		</div>

		<div class="site-footer__bottom">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Berghs School of Communication
		</div>
	</div>
</footer>

<?php wp_footer(); ?>

</body>

</html>
