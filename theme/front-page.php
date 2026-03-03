<?php
get_header();
$page_id       = get_the_ID();
$hero_title    = get_post_meta( $page_id, 'hero_title', true );
$hero_subtitle = get_post_meta( $page_id, 'hero_subtitle', true );
$hero_date     = get_post_meta( $page_id, 'hero_date', true );
$hero_cta_text = get_post_meta( $page_id, 'hero_cta_text', true );
$hero_cta_url  = get_post_meta( $page_id, 'hero_cta_url', true );
?>

<section class="hero">
    <div class="container">
        <h1><?php echo esc_html( $hero_title ?: 'Berghs Festival 2026' ); ?></h1>
        <p><?php echo esc_html( $hero_subtitle ?: 'Where students become creators.' ); ?></p>
        <?php if ( $hero_cta_url ) : ?>
            <a href="<?php echo esc_url( $hero_cta_url ); ?>" class="hero__cta" target="_blank" rel="noopener">
                <?php echo esc_html( $hero_cta_text ?: 'Get Tickets' ); ?>
            </a>
        <?php endif; ?>
        <?php if ( $hero_date ) : ?>
            <p class="hero__date"><?php echo esc_html( $hero_date ); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
