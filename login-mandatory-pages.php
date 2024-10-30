<?php
/*
Plugin Name:  Login Mandatory Pages
Description:  Make mandatory login for selected pages
Version:      1.2
Author:       Apex Web Guru
Author URI:   http://apexwebguru.com/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
Domain Path:  /languages
*/
/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

define('LOGINMANDATORYPAGES_URL', plugins_url() . '/login-mandatory-pages');
define('LOGINMANDATORYPAGES_DIR', WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)));
register_activation_hook(__FILE__, 'LOGINMANDATORYPAGES_plugin_activate');
register_deactivation_hook( __FILE__, 'LOGINMANDATORYPAGES_plugin_activate' );
add_action('admin_menu', 'login_mandatory_pages_add_menu');
add_action('admin_init', 'login_mandatory_pages_defaultSetting_function', 5);
add_action('admin_enqueue_scripts', 'login_mandatory_pages_admin_stylesheet', 7);
add_action('admin_init', 'login_mandatory_pages_save_settings', 10);
 /* @return add menu at admin panel
 */
if (!function_exists('login_mandatory_pages_add_menu')) {

    function login_mandatory_pages_add_menu() {
        add_menu_page(__('Login Mandatory Pages', 'login-mandatory-pages'), __('Login Mandatory Pages', 'login-mandatory-pages'), 'administrator', 'ls_settings', 'login_mandatory_pages_menu_function', LOGINMANDATORYPAGES_URL . '/images/lmp.png');
        add_submenu_page('ls_settings', __('Login Mandatory Pages Settings', 'login-mandatory-pages'), __('Login Mandatory Pages Settings', 'login-mandatory-pages'), 'manage_options', 'ls_settings', 'login_mandatory_pages_add_menu');
        add_submenu_page('ls_settings', __('About Login Mandatory Pages', 'login-mandatory-pages'), __('About Login Mandatory Pages', 'login-mandatory-pages'), 'manage_options', 'about_login_mandatory_pages', 'wp_login_mandatory_pages_about_us');
    }

}
 /* Include about page
 */
if (!function_exists('wp_login_mandatory_pages_about_us')) {

    function wp_login_mandatory_pages_about_us() {
        include_once( 'includes/about.php' );
    }
}

/**
 *
 * @global type $wp_version
 * @return html Display setting options
 */
if (!function_exists('login_mandatory_pages_menu_function')) {

    function login_mandatory_pages_menu_function() { 
    	?>
    
    	<div class="lr_setting_form">
            <h1>Login Mandatory Pages!<span>Here you can save pages to view only for logged in users!</span></h1>
            <?php
            if (isset($_GET['updated']) && 'true' == esc_attr($_GET['updated'])) {
                echo '<div class="updated" style="color;green;" ><span>' . __('Login Restrictions settings saved.', 'login-mandatory-pages') . ' </span></div>';
            }
            ?>
    		<form action ="?page=ls_settings&action=save&updated=true" method="post">
            <div class="section"><span>1</span><?php _e('Select Pages ', 'login-mandatory-pages'); ?></div>
                 <div class="inner-wrap">
    			<label><?php _e('Select Pages here: ', 'login-mandatory-pages'); ?></label>
    		     <ul>
    				<?php
    				$page_ids=get_all_page_ids();
                    $selected_pages_ids=get_option('selected_pages');
                   //print_r($selected_pages_ids);
                          // echo '<h3>My Page List :</h3>';
                    foreach($page_ids as $page)
                      {
                        if(in_array($page, $selected_pages_ids))
                        {
                            $checked="checked";
                      } ?>
	                     <label><li><input <?php echo $checked; ?> type="checkbox" name="pages_for_lr[]" value="<?php echo $page; ?>"><?php echo get_the_title($page); ?></li><label>
                   <?php $checked="";  }
                    ?>
                </ul>
                </div>
    			<div class="section"><span>2</span><?php _e('Write Your Custom Content for Selected Pages: ', 'login-mandatory-pages'); ?></div>
                  <div class="inner-wrap">
                     <label><?php _e('You can specify your login form(widgets or shortcode) or login page link here and leave blank step 3: ', 'login-mandatory-pages'); ?> <?php
                $content = get_option('lr_message');
                wp_editor( $content, 'message_for_lr' , $settings = array('textarea_rows'=> '10'));
                ?></label>
                 </div>
    			<div class="section"><span>3</span><?php _e('Login Page Link: ', 'login-mandatory-pages'); ?></div>
                     <div class="inner-wrap">
                          <label><?php _e('Paste your Login Page link here, if you already specified link or login form into your custom content then kindly leave it blank: ', 'login-mandatory-pages'); ?> <input type="text" name="button_link_for_lr" value="<?php echo get_option('lr_button'); ?>" /></label>
                 </div>
    		<div class="button-section">
                   <input type="submit" name="save_setting" value="Save Setting" />
                   
             </div>
    	</form>
    </div>

   <?php }
}
/**
 *
 * @return Set default value
 */
if (!function_exists('login_mandatory_pages_defaultSetting_function')) {

    function login_mandatory_pages_defaultSetting_function() {
        $settings = get_option("login_restrictions_settings");
        if (empty($settings)) {
            $settings = array(
                'selected_pages' => '',
                'lr_message' => 'You Must log in to see this page!',
                'lr_button' => '#'
                );
            add_option('selected_pages','');
            add_option('lr_message','You Must log in to see this page!');
            add_option('lr_button','#');
            add_option('login_restrictions_settings',$settings);
        }
    }
}
if (!function_exists('login_mandatory_pages_save_settings')) {

    function login_mandatory_pages_save_settings() {
        if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'save' && isset($_REQUEST['updated']) && $_REQUEST['updated'] === 'true') {
           $new_pages_value = array();
            if (isset($_POST['pages_for_lr']) && count($_POST['pages_for_lr'])>0) {
                // Loop through the input and sanitize each of the values
                    foreach ( $_POST['pages_for_lr'] as $val ) {
                        $new_pages_value[] = sanitize_text_field( $val );
                    }
                update_option("selected_pages", $new_pages_value);
            }
            if (isset($_POST['message_for_lr']) && !empty($_POST['message_for_lr'])) {
                update_option("lr_message", $_POST['message_for_lr']);
            }
            if (isset($_POST['button_link_for_lr']) && !empty($_POST['button_link_for_lr'])) {
                update_option("lr_button", sanitize_text_field($_POST['button_link_for_lr']));
            }
            else{
                update_option("lr_button", "");
            }
             $settings = array(
                'selected_pages' => $new_pages_value,
                'lr_message' => sanitize_text_field($_POST['message_for_lr']),
                'lr_button' => sanitize_text_field($_POST['button_link_for_lr'])
                );
             update_option('login_restrictions_settings',$settings);
            // echo "Setting Saved Successfully!";
        }
    }
}
/**
 *
 * @return Enqueue admin panel required css
 */
if (!function_exists('login_mandatory_pages_admin_stylesheet')) {

    function login_mandatory_pages_admin_stylesheet() {
    	wp_register_style('login-mandatory-pages-admin-support-stylesheets', plugins_url('css/ls.css', __FILE__));
        wp_enqueue_style('login-mandatory-pages-admin-support-stylesheets');
    }
}
/**
 *
 * @return filter all selected pages for login registriction
 */
if (!function_exists('login_mandatory_pages_filter_pages_forlogin')) {

function login_mandatory_pages_filter_pages_forlogin($content)
{
    $pagesId=get_all_page_ids();
    $restricted_pages=get_option('selected_pages');
    $common_pages=array_intersect($pagesId,$restricted_pages);
    if(!is_user_logged_in() && count($common_pages)>0) {
        if(is_page($common_pages)) {
                $content="";
                $lr_message=get_option('lr_message');
                $lr_button=get_option('lr_button');
                $content=$lr_message;
                if(!empty($lr_button)):
                    $content .= '<a href="'.$lr_button.'">Click Here</a> to sign in.</h4>';
                endif;
        }
    }
    return $content;
}
}
add_filter( 'the_content', 'login_mandatory_pages_filter_pages_forlogin' );
?>