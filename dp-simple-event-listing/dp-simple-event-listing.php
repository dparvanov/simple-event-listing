<?php
/*
Plugin Name: DP Simple event listing
Description: Add post types for custom event
Author: Denislav Parvanov
*/

define('MAP_API_KEY', 'AIzaSyAUio3kigwA5ZOLTVQMwNTCxskUnukAfxU');
define('ROOT', plugins_url('', __FILE__));
define('ROOT_DIR', plugin_dir_path(__FILE__));
define('STYLES', ROOT . '/css/');
define('SCRIPTS', ROOT . '/js/');

add_action('init', 'dp_custom_post_custom_event');
add_action('admin_enqueue_scripts', 'dp_admin_script_style');
add_action('add_meta_boxes', 'dp_add_event_info_metabox');
add_action('save_post', 'dp_save_event_info');
add_filter('manage_edit-event_columns', 'dp_custom_columns_head', 10);
add_action('manage_event_posts_custom_column', 'dp_custom_columns_content', 10, 2);
add_filter('template_include', 'dp_event_template');
add_action('pre_get_posts', 'dp_change_events_sort_order');

function dp_change_events_sort_order($query){
    if(is_archive() && is_post_type_archive( 'event')):
        $query->set( 'order', 'ASC');
        $query->set( 'meta_key', 'event-date' );
        $query->set( 'orderby', 'meta_value' );
        
    endif;
};


function dp_custom_post_custom_event() {
    $labels = array(
        'name' => __('Custom Events'),
        'singular_name' => __('Custom Event'),
        'add_new' => __('Add New Custom Event'),
        'add_new_item' => __('Add New Custom Event'),
        'edit_item' => __('Edit Custom Event'),
        'new_item' => __('New Custom Event'),
        'all_items' => __('All Custom Events'),
        'view_item' => __('View Custom Event'),
        'search_items' => __('Search Custom Event')
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'menu_position' => 5,
        'supports' => array('title'),
        'has_archive' => true,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'exclude_from_search' => true,
        'query_var' => false,
    );

    register_post_type('event', $args);
}

function dp_admin_script_style($hook) {
    global $post_type, $post;

    if (('post.php' == $hook || 'post-new.php' == $hook) && ('event' == $post_type)) {

        wp_enqueue_script(
            'upcoming-events',
            SCRIPTS . 'script.js',
            array('jquery', 'jquery-ui-datepicker'),
            '1.0',
            true
        );

        wp_enqueue_style(
            'jquery-ui-calendar',
            STYLES . 'jquery-ui.min.css',
            false,
            '1.12.1',
            'all'
        );

        wp_enqueue_script(
            'google-maps-native',
            'http://maps.googleapis.com/maps/api/js?key=' . MAP_API_KEY
        );

        wp_enqueue_script(
            'gmaps-meta-box',
            SCRIPTS . 'maps.js');
        $helper = array(
            'lat' => get_post_meta($post->ID, 'dp-event-glat', true),
            'lng' => get_post_meta($post->ID, 'dp-event-glng', true)
        );

        wp_localize_script(
            'gmaps-meta-box',
            'helper', $helper);

        wp_enqueue_style(
            'gmaps-style',
            STYLES . 'simple-event-admin.css'
        );
    }
}

function dp_add_event_info_metabox() {
    add_meta_box(
        'dp-event-info-metabox',
        __('Event Info', 'dp'),
        'dp_render_event_info_metabox',
        'event',
        'normal',
        'high'
    );
}

function dp_event_date_html($event_date) {
    ?>
    <label for="dp-event-date"><?php _e('Event Date:', 'dp'); ?></label>
    <input class="widefat dp-event-date-input" id="dp-event-date" type="text" name="dp-event-date"
           value="<?php echo date('d.m.Y', $event_date); ?>"/>
    <?php
}

function dp_event_location_html($lat, $lng) {
    ?>
    <label for="dp-event-location"><?php _e('Event location:', 'dp'); ?></label>
    <div id="dp-event-location" class="maparea"></div>
    <input type="hidden" name="dp-event-glat" id="latitude" value="<?php echo $lat; ?>">
    <input type="hidden" name="dp-event-glng" id="longitude" value="<?php echo $lng; ?>">
    <?php
}

function dp_event_url_html($event_url) {
    ?>
    <label for="dp-event-url"><?php _e('Event Url:', 'dp'); ?></label>
    <input class="widefat" id="dp-event-url" type="text" name="dp-event-url"
           value="<?php echo $event_url; ?>"/>
    <?php
}

function dp_render_event_info_metabox($post) {

    // generate a nonce field
    wp_nonce_field(basename(__FILE__), 'dp-event-info-nonce');

    // get previously saved meta values (if any)
    $event_date = get_post_meta($post->ID, 'event-date', true);
    $event_url = get_post_meta($post->ID, 'event-url', true);
    $lat = get_post_meta($post->ID, 'event-glat', true);
    $lng = get_post_meta($post->ID, 'event-glng', true);

    // if there is previously saved value then retrieve it, else set it to default one
    $event_date = !empty($event_date) ? $event_date : time();
    if(empty($lat) || empty($lng)){
        $lat = 42.706953;
        $lng = 23.3211915;
    }
    
    dp_event_date_html($event_date);
    dp_event_location_html($lat, $lng);
    dp_event_url_html($event_url);

}

function dp_save_event_info($post_id) {

    // checking if the post being saved is an 'event',
    // if not, then return
    if ('event' != $_POST['post_type']) {
        return;
    }

    // checking for the 'save' status
    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);
    $is_valid_nonce = (isset($_POST['dp-event-info-nonce']) && (wp_verify_nonce($_POST['dp-event-info-nonce'], basename(__FILE__)))) ? true : false;

    // exit depending on the save status or if the nonce is not valid
    if ($is_autosave || $is_revision || !$is_valid_nonce) {
        return;
    }

    // checking for the values and performing necessary actions
    if (isset($_POST['dp-event-date'])) {
        update_post_meta($post_id, 'event-date', strtotime($_POST['dp-event-date']));
    }

    if (isset($_POST['dp-event-glat'])) {
        update_post_meta($post_id, 'event-glat', sanitize_text_field($_POST['dp-event-glat']));
    }

    if (isset($_POST['dp-event-glng'])) {
        update_post_meta($post_id, 'event-glng', sanitize_text_field($_POST['dp-event-glng']));
    }

    if (isset($_POST['dp-event-url'])) {
        update_post_meta($post_id, 'event-url', sanitize_text_field($_POST['dp-event-url']));
    }
}

function dp_custom_columns_head($defaults) {
    unset($defaults['date']);

    $defaults['event_date'] = __('Date', 'dp');
    $defaults['event_url'] = __('Url', 'dp');

    return $defaults;
}

function dp_custom_columns_content($column_name, $post_id) {

    if ('event_date' == $column_name) {
        $date = get_post_meta($post_id, 'event-date', true);
        echo date('d.m.Y', $date);
    }

    if ('event_url' == $column_name) {
        $url = get_post_meta($post_id, 'event-url', true);
        echo $url;
    }
}

function dp_event_template($template) {
    if (is_post_type_archive('event')) {

        wp_enqueue_style(
            'event-style',
            STYLES . 'simple-event.css'
        );

        wp_enqueue_script('google-maps-native', 'http://maps.googleapis.com/maps/api/js?key=' . MAP_API_KEY);

        wp_enqueue_script('gmaps-view',
            SCRIPTS . 'maps-view.js');

        $theme_files = array('archive-event.php');
        $exists_in_theme = locate_template($theme_files, false);
        if ($exists_in_theme != '') {
            return $exists_in_theme;
        } else {
            return ROOT_DIR . '/archive-event.php';
        }

    }
    return $template;
}

function dp_get_google_calendar_link($title, $date, $lat, $lng) {

    return 'https://www.google.com/calendar/render?action=TEMPLATE' .
        '&text=' . $title .
        '&dates=' . date('Ymd', $date) . '/' . date('Ymd', $date) .
        '&location=' . $lat . ',' . $lng;
}