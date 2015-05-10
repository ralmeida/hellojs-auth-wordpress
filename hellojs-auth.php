<?php
/**
 * Plugin Name: Hellojs Auth
 * Plugin URI: https://github.com/ralmeida/hellojsauth_wordpress
 * Description: Plugin to provide Hello.js as Auth/Register for Wordpress
 * Version: 1.0.0
 * Author: Ronald Almeida II
 * Author URI: https://www.linkedin.com/in/ralmeidaii\
 * Network: true
 * License: GPL2
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once('inc/hellojs-auth-settings.php');
require_once('inc/hellojs-providers.php');

register_activation_hook( __FILE__, 'hellojsauth_activate' );

function hellojsauth_activate() {

	add_option('hellojsauth', new HelloJSAuthSettings());
	add_option('hellojs_app_ids', array());
}

register_deactivation_hook( __FILE__, 'hellowjsauth_deactivation' );

function hellowjsauth_deactivation() {
	
	delete_option( 'hellojsauth' );
	delete_option( 'hellojs_app_ids' );
}

add_action( 'admin_enqueue_scripts', 'hellojsauth_admin_init_scripts');

function hellojsauth_admin_init_scripts() {
	
	$settings = get_option('hellojsauth');

	wp_enqueue_style( 'zocial-styles',  $settings->source['zocial']);
	wp_enqueue_style( 'googleapis-pompiere', $settings->source['google_font']);
}

add_action( 'admin_menu', 'hellojsauth_menu' );

// Add Options to Admin Menu
function hellojsauth_menu() {

	add_options_page( 'Hello.js Auth Options', 'Hello.js Auth', 'manage_options', 'hellojs-auth-options', 'hellojsauth_options' );
}

// Options Menu triggered from the Admin Menu above
function hellojsauth_options() {

	//load our settings
	$settings = get_option('hellojsauth');

	//make srue we have the correct permissions
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	$providers = HelloJSProviders::$data;
	$ops_data = get_option( 'hellojs_app_ids' );

    // variables for the field and option names 
    $hidden_field_name = 'hellojs_whatwhat';
    $jquery_source_field = 'hellojsauth_jquery_source';
    $hellojs_source_field = 'hellojsauth_hellojs_source';
    $zocial_source_field = 'hellojsauth_zocial_source';
    $google_font_source_field = 'hellojsauth_google_font_source';

    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {

	    // See if the user has posted us some information
	    // If they did, this hidden field will be set to 'Y'
    	if(check_admin_referer( 'hellojs-auth_settings' )) {
	        
			$settings->enabled = isset($_POST['hellojsauth_enabled']);

			$settings->update_user_on_login = isset($_POST['hellojsauth_update_user_on_login']);

			//possible override of the jquery
	        //if(isset($_POST[$jquery_source_field]) && $_POST[$jquery_source_field])
	        //	$settings->source['jquery'] = sanitize_text_field($_POST[$jquery_source_field]);

	        if(isset($_POST[$hellojs_source_field]) && $_POST[$hellojs_source_field]) 
	        	$settings->source['hellojs'] = sanitize_text_field($_POST[$hellojs_source_field]);

	        if(isset($_POST[$zocial_source_field]) && $_POST[$zocial_source_field])
	        	$settings->source['zocial'] = sanitize_text_field($_POST[$zocial_source_field]);

	        if(isset($_POST[$google_font_source_field]) && $_POST[$google_font_source_field])
	        	$settings->source['google_font'] = sanitize_text_field($_POST[$google_font_source_field]);

			update_option('hellojsauth', $settings);

	        foreach($providers as $provider => $p_data) {
		    	// Read their posted value
		    	$val = (isset($_POST[ 'hellojsauth_app_ids' ])) ? 
		    				(isset($_POST[ 'hellojsauth_app_ids' ][ $provider ])) ?
		    						$_POST[ 'hellojsauth_app_ids' ][ $provider ] : "" : "";
				//set the provider id in the ops_data
		    	$ops_data[$provider] = sanitize_text_field($val);
			}

			//update the app_ids option
			update_option( "hellojs_app_ids", $ops_data);

	        // Put a settings updated message on the screen
			?><div class="updated"><p><strong><?php _e('settings saved.', 'hellojs-auth-options' ); ?></strong></p></div><?php
	    } 
    } 

    ?><form name="hellojs_auth_options" method="post" action="">	
	<?php echo wp_nonce_field( 'hellojs-auth_settings' ); ?>
	<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
	<div class="wrap">
		
		<h2><?php _e( 'Hello.js Social Provider Settings' ); ?></h2>
		<hr />
		<br />
		
		<div style="width:250px; float:left;">
		<fieldset>
			<legend><h3>Global Settings</h3></legend>
			<hr />

			<?php $is_enabled = ($settings->enabled) ? "CHECKED" : "";
				  $update_user_on_login = ($settings->update_user_on_login) ? "CHECKED" : ""; ?>
		  	<table>
		  		<tr>
		  			<td style="text-align:right;">Enabled &nbsp; </td>
					<td><input type="checkbox" name="hellojsauth_enabled" <?php _e($is_enabled); ?>></td>
				</tr>
				<tr>
					<td style="text-align:right;">Update User on Login &nbsp; </td>
					<td><input type="checkbox" name="hellojsauth_update_user_on_login" <?php _e($update_user_on_login); ?>></td>
				</tr>
			</table>
		</fieldset>
		</div>

		<div style="width:300px; float:left;">
		<fieldset>
			<legend><h3>Source Settings</h3></legend>
			<hr />
			<!--<p>jQuery source <br />
				<input type="text" name="hellojsauth_jquery_source" value="<?php _e($settings->source['jquery']); ?>" size="75" /></p>-->
			<p>hello.js source <br />
				<input type="text" name="<?php _e($hellojs_source_field); ?>" value="<?php _e($settings->source['hellojs']); ?>" size="75" /></p>
			<p>zocial source <br />
				<input type="text" name="<?php _e($zocial_source_field); ?>" value="<?php _e($settings->source['zocial']); ?>" size="75" /></p>
			<p>Google Font source <br />
				<input type="text" name="<?php _e($google_font_source_field); ?>" value="<?php _e($settings->source['google_font']); ?>" size="75" /></p>
		</fieldset>
		</div>

		<div style="clear:both;"></div>
		<div>
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
			</p>
		</div>
		<hr />
		<br />

		<fieldset>
			<legend>
				<h3>App ID Settings</h3>
				<small>Show Provider Names</small> &nbsp; 
				<input type="checkbox" onclick="jQuery('.provider_name').toggle(); jQuery('.zocial').toggle();" />
			</legend>
			<br />
			<table cellpadding="5" cellspacing="0">
			<?php // generate settings form
				$count = 0;
				$apps_per_row = 3;
				$apps_row_target = $apps_per_row-1;
				foreach($providers as $provider => $p_data): ?>
				<?php if(($count % $apps_per_row)==0): ?><tr><?php endif; ?>
				<td style="border:thin rgb(219, 219, 219) solid;">
					<div class="zocial icon <?php echo $p_data['icon']; ?>"></div>
					<div class="provider_name" style="font-size:medium; display:inline; margin-left:12px; width: 100px;">
						<?php _e("{$p_data['label']} App ID", 'menu-test' ); ?>
					</div>
					<input type="text" name="hellojsauth_app_ids[<?php _e($provider); ?>]" value="<?php _e($ops_data[$provider]); ?>" size="40">
				</td>
			  	<?php if(($count % $apps_per_row)==$apps_row_target): ?></tr><?php endif; ?>
				<?php $count++; ?>
			<?php endforeach; 
				  if(!(($count % $apps_per_row)==1)): ?><!--</tr>--><?php endif; ?>

			</table>
			<hr />
		</fieldset>

		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
		</p>
	</div>
	</form>
	<script>
		jQuery(document).ready(function() {
			jQuery('.provider_name').toggle();
		});
	</script><?php
}

// Login page tie-in
add_action( 'login_enqueue_scripts', 'hellojsauth_enqueue_login_scripts' );

function hellojsauth_enqueue_login_scripts( $page ) {

	$settings = get_option('hellojsauth');
	if(!$settings->enabled) return;

	//cdn scripts pulled form configured source
    wp_enqueue_script( 'hellojs-script', $settings->source['hellojs']);
	wp_enqueue_style( 'zocial-styles',  $settings->source['zocial']);
	wp_enqueue_style( 'googleapis-pompiere', $settings->source['google_font']);

	//local scripts
    wp_enqueue_script( 'hellojs-auth',  plugin_dir_url( __FILE__ ) . 'js/hellojs-auth.js', array('jquery'));
    wp_enqueue_style( 'hellojs-auth-css',  plugin_dir_url( __FILE__ ) . 'css/hellojs-auth.css');
}

add_action( 'login_form', 'hellojsauth_login_add_providers');

function hellojsauth_login_add_providers() {

	$settings = get_option('hellojsauth');
	if(!$settings->enabled) return;

	$providers = get_option('hellojs_app_ids');

	$is_loggedout = (isset($_GET['loggedout'])) ? $_GET['loggedout'] : false;

    if($is_loggedout): ?>
    <script type="text/javascript">
		localStorage.removeItem('hello');
	</script>
    <?php endif;
	$out_buttons = array();
	?><div style="padding:3px; margin:2px;">
		<h3>- or -</h3>
		<br />
		<script type="text/javascript">
			var hellojsauth_req_key = '<?php _e(wp_create_nonce( "hellojs-auth-login" )); ?>';
			jQuery(document).ready(function() {

				jQuery("#user_pass").val("");
				hello.init({
				//Here we will loop through the providers we have, for each one with a value we will add to the init
				<?php foreach($providers as $provider => $app_id): ?>
					<?php if($app_id): ?>
						<?php if(isset(HelloJSProviders::$data[$provider])): ?>
							<?php echo $provider; ?>: '<?php echo $app_id; ?>',
							<?php $out_buttons[$provider] = HelloJSProviders::$data[$provider]; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				}, { scope : 'email', redirect_uri: 'wp-login.php' });
			});
		</script>
		<?php foreach($out_buttons as $button => $data): ?>
		<button type="button" tabindex = "-1" onclick="hello('<?php echo $button; ?>').login();" title="Sign in with <?php echo $data['label']; ?>" class="zocial icon <?php echo $data['icon']; ?>"></button>
		<?php endforeach; ?>
		<?php if(!$out_buttons): ?>
			<h4 style="color:red;">You do not have any Hello.js Auth Providers Setup</h4>
		<?php endif; ?>
	</div>
	<br /><?php
}

//Process the login after the hello.js login
function hellojsauth_endpoint_init() {

	$settings = get_option('hellojsauth');
	if(!$settings->enabled) return;

    require dirname( __FILE__ ) . '/inc/hellojs-auth-models.php';

    $options = array (
        'callback' => array ( 'HelloJSAuthView', '__construct' ),
        'name'     => 'hellojsauth',
        'position' => EP_ROOT
    );

    new HelloJSAuthModel( $options );
}

add_action( 'plugins_loaded', 'hellojsauth_endpoint_init' );
