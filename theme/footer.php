<?php
$sponsors  = berghs_get_sponsors();
$address   = get_theme_mod( 'berghs_footer_address', "Berghs School of Communication\nSveavägen 56\n111 34 Stockholm" );
$phone     = get_theme_mod( 'berghs_footer_phone', '' );
$instagram = get_theme_mod( 'berghs_social_instagram', '' );
$facebook  = get_theme_mod( 'berghs_social_facebook', '' );
$linkedin  = get_theme_mod( 'berghs_social_linkedin', '' );
$tiktok    = get_theme_mod( 'berghs_social_tiktok', '' );
?>
</main>

<?php if ( ! empty( $sponsors ) ) : ?>
<div class="container">
    <div class="sponsors-row">
        <?php foreach ( $sponsors as $sponsor ) :
            $logo = get_the_post_thumbnail( $sponsor->ID, 'sponsor-logo' );
            $url  = get_post_meta( $sponsor->ID, 'sponsor_url', true );
        ?>
            <div class="sponsor-logo">
                <?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php endif; ?>
                <?php echo $logo ?: esc_html( $sponsor->post_title ); ?>
                <?php if ( $url ) : ?></a><?php endif; ?>
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
                    <p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
                <?php endif; ?>
            </div>
            <div class="site-footer__col">
                <h4>Follow</h4>
                <ul>
                    <?php if ( $instagram ) : ?><li><a href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener">Instagram</a></li><?php endif; ?>
                    <?php if ( $facebook ) : ?><li><a href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener">Facebook</a></li><?php endif; ?>
                    <?php if ( $linkedin ) : ?><li><a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener">LinkedIn</a></li><?php endif; ?>
                    <?php if ( $tiktok ) : ?><li><a href="<?php echo esc_url( $tiktok ); ?>" target="_blank" rel="noopener">TikTok</a></li><?php endif; ?>
                </ul>
            </div>
            <div class="site-footer__col">
                <h4>Navigate</h4>
                <?php wp_nav_menu( [ 'theme_location' => 'footer', 'container' => false, 'depth' => 1, 'fallback_cb' => false ] ); ?>
            </div>
        </div>
        <div class="site-footer__bottom">
            &copy; <?php echo date( 'Y' ); ?> Berghs School of Communication
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
