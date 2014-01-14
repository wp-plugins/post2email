<?php

/*
Plugin Name: Post2Email
Plugin URI: http://halfelf.org/plugins/post2email
Description: Allows admin to set an email address to which all new posts are sent a copy.
Version: 1.0
Author: Mika A. Epstein
Author URI: http://halfelf.org/

    Hat tip to Notifly - http://wordpress.org/extend/plugins/notifly/

    Copyright 2013 Mika Epstein

    This file is part of Post 2 Email, a plugin for WordPress.

    Post 2 Email is free software: you can redistribute it and/or 
    modify it under the terms of the GNU General Public License as published 
    by the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    Post 2 Email is distributed in the hope that it will be
    useful, but WITHOUT ANY WARRANTY; without even the implied warranty
    of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WordPress.  If not, see <http://www.gnu.org/licenses/>.

*/


// Send an email when a post is published, but ONLY if it's New
add_action('transition_post_status', 'ippy_post2email_send', 10, 3);

function ippy_post2email_send( $new_status, $old_status, $post_id ) {

    if ( 'publish' != $new_status || 'publish' == $old_status ) // If the post isn't newly published, STFU
        return;

    $page_data = get_page( $post_id );
    if ($page_data->post_type != 'post') // If it's not a POST, STFU
        return;
        
    if ( get_option('rss_use_excerpt') ) :
    	if ( $page_data->post_excerpt != '' ) :
    		$message = $page_data->post_excerpt;
    	else :
    		$message = wp_trim_words( $page_data->post_content, $num_words = 55, $more = '[...]' );
    	endif;
    else :
    	$message = $page_data->post_content;
    endif; 

    $options = get_option( 'ippy_post2email_options' );

	$headers = "From: ".$options['namefrom']." <".$options['emailfrom'].">" . "\r\n";
    $to = $options['emailto'];
    $subject = get_the_title($post_id);
    $message .= "\r\n\r\nRead more at ".get_permalink($post_id);
    wp_mail($to, $subject, $message, $headers );
}

// Register and define the settings

add_action('admin_init', 'ippy_post2email_admin_init');

function ippy_post2email_admin_init(){

	register_setting(
		'reading',                            // settings page
		'ippy_post2email_options',            // option name
		'ippy_post2email_validate_options'    // validation callback
	);
	
	add_settings_field(
		'ippy_post2email_email',          // id
		'Post2Email',                     // setting title
		'ippy_post2email_setting_input',  // display callback
		'reading',                        // settings page
		'default'                         // settings section
	);
}

register_activation_hook( __FILE__, 'ippy_post2email_activate' );

function ippy_post2email_activate() {

    // Set the default FROM email to wordpress@example.com
    $sitename = strtolower( $_SERVER['SERVER_NAME'] );
    if ( substr( $sitename, 0, 4 ) == 'www.' ) {$sitename = substr( $sitename, 4 );}
    $from_email = 'wordpress@' . $sitename;

    $options = get_option( 'ippy_post2email_options' );
    $options['emailto'] = get_option('admin_email');       // default mail-to is the site admin
    $options['emailfrom'] = $from_email;                   // Default from is default from (unused right now)
    $options['namefrom'] = get_option('blogname');         // Default name from is site name.
    add_option('ippy_post2email_options', $options);
}

// Display and fill the form field
function ippy_post2email_setting_input() {

    if (!current_user_can('delete_users'))
        $return;

	// get option value from the database
	$options = get_option( 'ippy_post2email_options' );
	$valueemailto = $options['emailto'];
	$valueemailfrom = $options['emailfrom'];
	$valuenamefrom = $options['namefrom'];
		
	// echo the field
	?>
	<p><input id='emailto' name='ippy_post2email_options[emailto]' type='text' value='<?php echo esc_attr( $valueemailto ); ?>' /> Address to get a mail when a new post is published (defaults to site admin)</p>
	<p><input id='emailfrom' name='ippy_post2email_options[emailfrom]' type='text' value='<?php echo esc_attr( $valueemailfrom ); ?>'> Address to send email from (defaults to wordpress@example.com)</p>
	<p><input id='namefrom' name='ippy_post2email_options[namefrom]' type='text' value='<?php echo esc_attr( $valuenamefrom ); ?>'> 'Name' from which emails are sent (defaults to blogname)</p>
	<?php
}

// Validate user input
function ippy_post2email_validate_options( $input ) {
	$valid = array();
	$valid['emailto'] = sanitize_email( $input['emailto'] );
	$valid['emailfrom'] = sanitize_email(  $input['emailfrom'] );
	$valid['namefrom'] = $input['namefrom'];

    // Something dirty entered? Warn user.
    if( $valid['emailto'] != $input['emailto'] ) {
        add_settings_error(
            'ippy_post2email_email',       // setting title
            'ippy_post2email_texterror',   // error ID
            'Invalid "to" email, please fix',   // error message
            'error'                        // type of message
        );        
    }

    if( $valid['emailfrom'] != $input['emailfrom'] ) {
        add_settings_error(
            'ippy_post2email_email',       // setting title
            'ippy_post2email_texterror',   // error ID
            'Invalid "from" email, please fix',   // error message
            'error'                        // type of message
        );        
    }

	return $valid;
}


// donate link on manage plugin page
add_filter('plugin_row_meta', 'ippy_post2email_donate_link', 10, 2);

function ippy_post2email_donate_link($links, $file) {
	if ($file == plugin_basename(__FILE__)) {
		$donate_link = '<a href="https://store.halfelf.org/donate/">Donate</a>';
		$links[] = $donate_link;
    }
    return $links;
}
