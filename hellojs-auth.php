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

//require_once('hellojs-auth-helper.php');
require_once('inc/hellojs-providers.php');

add_action( 'admin_menu', 'hellojsauth_menu' );

// Add Options to Admin Menu
function hellojsauth_menu() {

	add_options_page( 'Hello.js Auth Options', 'Hello.js Auth', 'manage_options', 'hellojs-auth-options', 'hellojsauth_options' );
}

// Options Menu
function hellojsauth_options() {

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	$ops_data = array();
	$providers = HelloJSProviders::$data;

	foreach($providers as $provider => $p_data) {
    	// Read in existing option value from database
    	$ops_data[$provider] = get_option( $p_data['app_id'] );
	}

    // variables for the field and option names 
    $hidden_field_name = 'hellojs_whatwhat';

    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {

	    // See if the user has posted us some information
	    // If they did, this hidden field will be set to 'Y'
    	if(check_admin_referer( 'hellojs-auth_settings' )) {
	        
	        update_option('hellojsauth-enabled', isset($_POST['hellojsauth_enabled']));

	        foreach($providers as $provider => $p_data) {
		    	// Read their posted value
		    	$ops_data[$provider] = $_POST[ $provider ][ $p_data['app_id'] ];
			}

			foreach ($ops_data as $op => $op_val) {
		        // Save the posted value in the database
		        update_option( $providers[$op]['app_id'], $op_val );
			}

	        // Put an settings updated message on the screen
			?>
			<div class="updated"><p><strong><?php _e('settings saved.', 'hellojs-auth-options' ); ?></strong></p></div>
			<?php
	    } 
    } ?>

	<form name="hellojs_auth_options" method="post" action="">	
	<?php echo wp_nonce_field( 'hellojs-auth_settings' ); ?>
	<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
	<?php

		// Now display the settings editing screen
		echo '<div class="wrap">';

		// header
		echo "<h2>" . __( 'Hello.js Social Provider Settings', 'hellojs-auth-options' ) . "</h2>";

		$is_enabled = get_option('hellojsauth-enabled', false);
		$is_enabled = ($is_enabled) ? "CHECKED" : "";
		?>
		<p> Enabled &nbsp;
			<input type="checkbox" name="hellojsauth_enabled" <?php _e($is_enabled); ?>>
		</p>
		<?php
		//print_r($ops_data);
		// settings form
		foreach($providers as $provider => $p_data) {
			//print_r(array('provider' => $provider, 'data' => $p_data));
			?>
			<p>
				<?php _e("{$p_data['label']} App ID", 'menu-test' ); ?> 
			</p>
			<p>
				<?php $field_name = $provider . "[{$p_data['app_id']}]"; ?>
				<input type="text" name="<?php _e($field_name); ?>" value="<?php _e($ops_data[$provider]); ?>" size="50">
			</p>	
			<hr />
			<?php
		}
	?>	
	<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
	</p>

	</form>
	</div>

		<?php
}

// Login page tie-in
add_action( 'login_enqueue_scripts', 'hellojsauth_enqueue_login_scripts' );

function hellojsauth_enqueue_login_scripts( $page ) {

    wp_enqueue_script( 'hellojs-script', '//cdnjs.cloudflare.com/ajax/libs/hellojs/1.5.1/hello.all.min.js'); //, null, null, true );

    wp_enqueue_script( 'hellojs-auth',  plugin_dir_url( __FILE__ ) . 'js/hellojs-auth.js'); //, null, null, false );

	wp_register_style( 'zocial-styles',  plugin_dir_url( __FILE__ ) . 'css/zocial.css' );
	wp_enqueue_style( 'zocial-styles' );

	wp_enqueue_style( 'googleapis-pompiere', '//fonts.googleapis.com/css?family=Pompiere');
}

add_action( 'login_form', 'hellojsauth_login_add_providers');

function hellojsauth_login_add_providers() {

	if(!get_option('hellojsauth-enabled', false)) return;

	$is_loggedout = (isset($_GET['loggedout'])) ? $_GET['loggedout'] : false;

    if($is_loggedout): ?>
    <script type="text/javascript">
		localStorage.removeItem('hello');
	</script>
    <?php endif;

	$out_buttons = array();
	?>
	<div id="profile_facebook"></div>

	<div style="padding:3px; margin:2px;">
		<h3>- or -</h3>
		<br />
		<script type="text/javascript">
			hello.init({
			//Here we will loop through the providers we have, for each one with a value we will add to the init
			<?php foreach(HelloJSProviders::$data as $provider => $p_data): ?>
			<?php $app_id = get_option( $p_data['app_id'] ); ?>
				<?php if($app_id): ?>
				<?php echo $provider; ?>: '<?php echo $app_id; ?>',
				<?php $out_buttons[$provider] = $p_data; ?>
				<?php endif; ?>
			<?php endforeach; ?>
			}, { scope : 'email', redirect_uri: 'wp-login.php' });
		</script>
		<?php foreach($out_buttons as $button => $data): ?>
		<button onclick="hello('<?php echo $button; ?>').login()" title="Sign in with <?php echo $data['label']; ?>" class="zocial icon <?php echo $data['icon']; ?>"></button>
		<?php endforeach; ?>
		<?php if(!$out_buttons): ?>
			<h4 style="color:red;">You do not have any Hello.js Auth Providers Setup</h4>
		<?php endif; ?>
	</div>
	<br />
	<?php
}

//Process the login after the hello.js login
add_action( 'plugins_loaded', 'hellojsauth_endpoint_init' );

function hellojsauth_endpoint_init() {

    require dirname( __FILE__ ) . '/inc/hellojs-auth-models.php';

    $options = array (
        'callback' => array ( 'HelloJSAuthView', '__construct' ),
        'name'     => 'hellojsauth',
        'position' => EP_ROOT
    );

    new HelloJSAuthModel( $options );
}