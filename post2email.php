<?php

/*
Plugin Name: post2email
Plugin URI: http://halfelf.org/plugins/post2email
Description: Allows admin to set an email address to which all new posts are sent a copy.
Version: 1.2
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

global $wp_version;
	if (version_compare($wp_version,"3.5","<")) { exit( __('This plugin requires WordPress 3.5', 'ippy-post2email') ); }


if (!class_exists('post2emailHELF')) {
	class post2emailHELF {

		var $post2email_defaults;
		var $post2email_sitename;
		var $post2email_fromemail;
	
	    public function __construct() {
	        add_action( 'init', array( &$this, 'init' ) );

	        // Set the default FROM email to wordpress@example.com
		    $this->post2email_sitename = strtolower( $_SERVER['SERVER_NAME'] );
		    if ( substr( $this->post2email_sitename, 0, 4 ) == 'www.' ) {$this->post2email_sitename = substr( $this->post2email_sitename, 4 );}
		    $this->post2email_from_email = 'wordpress@' . $this->post2email_sitename;
	        
	    	// Setting plugin defaults here:
			$this->post2email_defaults = array(
		        'emailto'  => get_option('admin_email'),
		        'emailfrom' => $this->post2email_from_email,
		        'namefrom' => get_option('blogname'),
		        'readmore' => 'Read more:',
		    );
	    }

	    public function init() {
	
		    add_action('transition_post_status', array( $this, 'post2email_send'), 10, 3);

			add_action( 'admin_init', array( $this, 'admin_init'));
			add_action( 'init', array( $this, 'internationalization' ));
	        
	        add_filter('plugin_row_meta', array( $this, 'donate_link'), 10, 2);
	        add_filter( 'plugin_action_links', array( $this, 'add_settings_link'), 10, 2 );
	    }

		// Send an email when a post is published, but ONLY if it's New
		public function post2email_send( $new_status, $old_status, $post_id ) {
		
		    if ( 'publish' != $new_status || 'publish' == $old_status ) // If the post isn't newly published, STFU
		        return;
		
		    $page_data = get_page( $post_id );
		    if ($page_data->post_type != 'post') // If it's not a POST, STFU
		        return;
		        
		    if ( get_option('rss_use_excerpt') ) :
		    	if ( $page_data->post_excerpt != '' ) :
		    		$message = strip_tags($page_data->post_excerpt);
		    	else :
		    		$message = wp_trim_words( strip_tags($page_data->post_content), $num_words = 55, $more = '[...]' );
		    	endif;
		    else :
		    	$message = strip_tags($page_data->post_content);
		    endif; 
		
		    $options = wp_parse_args(get_option( 'ippy_post2email_options'), $this->post2email_defaults );
		
			$headers = "From: ".$options['namefrom']." <".$options['emailfrom'].">" . "\r\n";
		    $to = $options['emailto'];
		    $subject = strip_tags($page_data->post_title);
		    $message .= "\r\n\r\n".$options['readmore']." ".get_permalink($post_id);
		    wp_mail($to, $subject, $message, $headers );
		}
		
		// Register and define the settings	
		function admin_init(){
		
			register_setting(
				'reading',                            // settings page
				'ippy_post2email_options',            // option name
				array( $this, 'validate_options')     // validation callback
			);
			
			add_settings_field(
				'ippy_post2email_email',          	  // id
				__('post2email', 'ippy-post2email'),  // setting title
				array( $this, 'setting_input'),  	  // display callback
				'reading',                        	  // settings page
				'default'                         	  // settings section
			);
		}
		
		// Display and fill the form field
		function setting_input() {
		
		    if (!current_user_can('delete_users'))
		        $return;
		
			// get option value from the database with defaults, if not already set!
			$options = wp_parse_args(get_option( 'ippy_post2email_options'), $this->post2email_defaults );
		
			// echo the field
			?>
			<a name="post2email" value="post2email"></a>
			<input id='emailto' name='ippy_post2email_options[emailto]' type='text' value='<?php echo esc_attr( $options['emailto'] ); ?>' /> <?php printf( __( 'Address to get a mail when a new post is published (defaults to %1$s)', 'ippy-post2email' ), get_option('admin_email') ); ?><br />
			<input id='emailfrom' name='ippy_post2email_options[emailfrom]' type='text' value='<?php echo esc_attr( $options['emailfrom'] ); ?>'> <?php printf( __( 'Address to send email from (defaults to %1$s)', 'ippy-post2email' ), $this->post2email_sitename ); ?><br />
			<input id='namefrom' name='ippy_post2email_options[namefrom]' type='text' value='<?php echo esc_attr( $options['namefrom'] ); ?>'> <?php printf( __( 'Name from which emails are sent (defaults to "wordpress@%1$s")', 'ippy-post2email' ), get_option('blogname') ); ?><br />
			<p><input id='readmore' name='ippy_post2email_options[readmore]' type='text' value='<?php echo esc_attr( $options['readmore'] ); ?>'> <?php _e('Text that prefixes to your URL (defaults to "Read more")', 'ippy-post2email'); ?><br />
			<?php
		}
		
		// Validate user input
		function validate_options( $input ) {

    	    $options = wp_parse_args(get_option( 'ippy_post2email_options'), $this->post2email_defaults );
    		$valid = array();

    	    foreach ($options as $key=>$value) {
        	    if (!isset($input[$key])) $input[$key]=$this->post2email_defaults[$key];
            }

			$valid['emailto'] = sanitize_email( $input['emailto'] );
			$valid['emailfrom'] = sanitize_email( $input['emailfrom'] );
			$valid['namefrom'] = sanitize_text_field($input['namefrom']);
			$valid['readmore'] = sanitize_text_field($input['readmore']);
		
		    // Something dirty entered? Warn user.
		    
		    // Checking email TO
		    if( $valid['emailto'] != $input['emailto'] ) {
		        add_settings_error(
		            'ippy_post2email_email',       							// setting title
		            'ippy_post2email_texterror',   							// error ID
		            __('Invalid "to" email, please fix', 'ippy-post2email'),	// error message
		            'error'                        							// type of message
		        );        
		    }
		
		    // Checking email FROM
		    if( $valid['emailfrom'] != $input['emailfrom'] ) {
		        add_settings_error(
		            'ippy_post2email_email',       							// setting title
		            'ippy_post2email_texterror',   							// error ID
		            __('Invalid "from" email, please fix', 'ippy-post2email'),	// error message
		            'error'                        							// type of message
		        );        
		    }
		    
			return $valid;
		}

		function donate_link($links, $file) {
			if ($file == plugin_basename(__FILE__)) {
				$donate_link = '<a href="https://store.halfelf.org/donate/">' . __( 'Donate', 'ippy-post2email' ) . '</a>';
				$links[] = $donate_link;
		    }
		    return $links;
		}

		function add_settings_link( $links, $file ) {
			if ( plugin_basename( __FILE__ ) == $file ) {
				$settings_link = '<a href="' . admin_url( 'options-reading.php' ) . '#post2email">' . __( 'Settings', 'ippy-post2email' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
			return $links;
		}
	}
}

//instantiate the class
if (class_exists('post2emailHELF')) {
	new post2emailHELF();
}