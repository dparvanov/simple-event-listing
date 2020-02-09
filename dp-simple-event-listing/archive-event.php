<?php
get_header();
?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="container">

                <?php if ( have_posts() ) : ?>
    
                    <header class="page-header">
                        <?php
                        the_archive_title( '<h1 class="page-title">', '</h1>' );
                        ?>
                    </header><!-- .page-header -->
    
                 
                    
                    <?php
                    // Start the Loop.
                    while ( have_posts() ) : the_post();
                        $date = post_custom('event-date');
                        $url = post_custom('event-url');
                        $lat = post_custom('event-glat');
                        $lng = post_custom('event-glng');
                        ?>
                    
                        <div class="event-element">
                            <div class="event-title">
                                <h3><?php the_title(); ?></h3>
                            </div>
                            <div class="event-info">
                               
                                <div class="event-date">
                                    <?php echo date('d.m.Y', $date) ?>
                                </div>
                                <div class="event-url">
                                    <a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a>
                                </div>
                                <div class="event-button">
                                    <a href="<?php echo dp_get_google_calendar_link(get_the_title(), $date, $lat, $lng) ?>" target="_blank">
                                        <button type="button" class="calendar-btn">Add to my calendar</button>
                                    </a>
                                </div>
                            </div>
                            <div class="maparea-parent">
                                <div class="maparea-view"></div>
                                <input type="hidden" class="lat" value="<?php echo $lat; ?>">
                                <input type="hidden" class="lng" value="<?php echo $lng; ?>">
                            </div>
                        </div>
                    <?php
    
                        // End the loop.
                    endwhile;
                    
                    ?>
    
                   
                
                <?php
    
                // If no content, include the "No posts found" template.
                else :
                    get_template_part( 'template-parts/content/content', 'none' );
    
                endif;
                ?>
            </div>
        </main><!-- #main -->
    </div><!-- #primary -->

<?php
get_footer();




