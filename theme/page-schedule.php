<?php
/**
 * Template Name: Schedule
 */
get_header();
while ( have_posts() ) : the_post();
$items = berghs_get_schedule_items();
?>
<div class="section">
    <div class="container page-content">
        <h1><?php the_title(); ?></h1>
        <?php the_content(); ?>

        <?php if ( ! empty( $items ) ) : ?>
            <ul class="schedule-list">
                <?php foreach ( $items as $item ) :
                    $time = get_post_meta( $item->ID, 'schedule_time', true );
                    $desc = get_post_meta( $item->ID, 'schedule_description', true );
                ?>
                    <li class="schedule-item">
                        <span class="schedule-item__time"><?php echo esc_html( $time ); ?></span>
                        <div>
                            <p class="schedule-item__title"><?php echo esc_html( $item->post_title ); ?></p>
                            <?php if ( $desc ) : ?>
                                <p class="schedule-item__desc"><?php echo esc_html( $desc ); ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>The schedule will be published soon.</p>
        <?php endif; ?>
    </div>
</div>
<?php endwhile;
get_footer();
