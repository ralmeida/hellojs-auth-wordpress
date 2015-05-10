<?php  
# -*- coding: utf-8 -*-
/**
 * Register endpoint for Hello.js.
 *
 * modified from author toscho http://toscho.de 
 *  ref: http://wordpress.stackexchange.com/questions/86960/using-the-rewrite-api-to-construct-a-restful-url
 *
 * @author ralmeida
 *
 */
require_once('hellojs-auth-views.php');

class HelloJSAuthModel
{
    protected $options;

    /**
     * Read options and register endpoint actions and filters.
     *
     * @wp-hook plugins_loaded
     * @param   array $options
     */
    public function __construct( Array $options )
    {
        $default_options = array (
            'callback' => array ( 'HelloJSAuthView', '__construct' ),
            'name'     => 'hellojsauth',
            'position' => EP_ROOT
        );

        $this->options = wp_parse_args( $options, $default_options );

        add_action( 'init', array ( $this, 'register_endpoint' ), 1000 );

        // endpoints work on the front end only
        if ( is_admin() )
            return;

        add_filter( 'request', array ( $this, 'set_query_var' ) );
        // Hook in late to allow other plugins to operate earlier.
        add_action( 'template_redirect', array ( $this, 'render' ), 100 );
    }

    /**
     * Add endpoint and deal with other code flushing our rules away.
     *
     * @wp-hook init
     * @return void
     */
    public function register_endpoint()
    {
        add_rewrite_endpoint(
            $this->options['name'],
            $this->options['position']
        );
        $this->fix_failed_registration(
            $this->options['name'],
            $this->options['position']
        );
    }

    /**
     * Fix rules flushed by other peoples code.
     *
     * @wp-hook init
     * @param string $name
     * @param int    $position
     */
    protected function fix_failed_registration( $name, $position )
    {
        global $wp_rewrite;

        if ( empty ( $wp_rewrite->endpoints ) )
            return flush_rewrite_rules( FALSE );

        foreach ( $wp_rewrite->endpoints as $endpoint )
            if ( $endpoint[0] === $position && $endpoint[1] === $name )
                return;

        flush_rewrite_rules( FALSE );
    }

    /**
     * Set the endpoint variable to TRUE.
     *
     * If the endpoint was called without further parameters it does not
     * evaluate to TRUE otherwise.
     *
     * @wp-hook request
     * @param   array $vars
     * @return  array
     */
    public function set_query_var( Array $vars )
    {
        if ( ! empty ( $vars[ $this->options['name'] ] ) )
            return $vars;

        if (!$vars) return $vars;

        // When a static page was set as front page, the WordPress endpoint API
        // does some strange things. Let's fix that.
        if ( //isset( $vars[ $this->options['name'] ] )
            ( isset( $vars['pagename'] ) and $this->options['name'] === $vars['pagename'] )
            or ( isset( $vars['page'] )  and isset( $vars['name'] ) and $this->options['name'] === $vars['name'] )
            or ( isset( $vars['pagename'] ) and stripos($vars['pagename'], $this->options['name']."/") !== FALSE )
            )
        {
            // In some cases WP misinterprets the request as a page request and
            // returns a 404.
            $vars['page'] = $vars['pagename'] = $vars['name'] = FALSE;
            $vars[ $this->options['name'] ] = TRUE;
        }
        
        return $vars;
    }

    /**
     * Prepare API requests and hand them over to the callback.
     *
     * @wp-hook template_redirect
     * @return  void
     */
    public function render()
    {   
        $api = get_query_var( $this->options['name'] );
        $api = trim( $api, '/' );
        $parts  = explode( '/', $api );
        $action = array_shift( $parts );
        $type   = array_shift( $parts );

        //var_dump($api);

        if ( '' === $api )
            return;

        $settings = get_option('hellojsauth');

        $values['success'] = false;
        $values['message'] = "Login Default";

        if(check_ajax_referer( 'hellojs-auth-login', 'itsit', false )) {

            $user_email = (isset($_POST['email'])) ? $_POST['email'] : null;

            if($user_email) {
                //atempt to find the WordPress user
                $args = array(
                    'search'         => $user_email,
                    'search_columns' => array( 'user_email' )
                );
                $user_query = new WP_User_Query( $args );

                $total_result = count($user_query->results);
                //if we found a user lets login them in
                if($total_result) {
                    wp_set_auth_cookie($user_query->results[0]->data->ID);
                    $values['success'] = true;
                    $values['message'] = 'User Login!';
                } 
                //else we need to add the user to the site
                else {

                    //lets get some basic vars
                    $user_full_name = (isset($_POST['name'])) ? $_POST['name'] : null;
                    $user_first_name = (isset($_POST['first_name'])) ? $_POST['first_name'] : null;
                    $user_last_name = (isset($_POST['last_name'])) ? $_POST['last_name'] : null;

                    //TODO: here we can add in options for how the username is sorted out
                    //          as you will not always have a username field
                    $user_name = (isset($_POST['username'])) ? $_POST['username'] : null;
                    $user_name = $this->clean_username($user_name);
                    if($user_name == null) {
                        $user_name = (isset($user_email)) ? $this->clean_username($user_email) : null;
                        if($user_name == null) {
                            $user_name = $this->clean_username($user_full_name);
                        }
                    }

                    //do a check here for user based on above
                    $user_id = username_exists( $user_name );

                    //if we fail to get a username from the above we will just auto generate one
                    if($user_id || $user_name == null) {
                        $user_name = wp_generate_password( 8, false );
                        $user_id = false;
                    }
                    
                    //do a check here for user based on above (better safe than sorry)
                    $user_id = username_exists( $user_name );

                    if ( !$user_id and email_exists($user_email) == false ) {
                        
                        $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
                        $user_id = wp_create_user( $user_name, $random_password, $user_email );
                        
                        if($user_id) {

                            wp_set_auth_cookie($user_id);
                            $values['success'] = true;
                            $values['message'] = 'User Login!';

                            /*
                            for now we will allow the error to be slient
                            if ( is_wp_error( $user_id ) ) {
                                // There was an error, probably that user doesn't exist.
                            } 
                            else {
                                // Success!
                            }
                            */
                        }
                        else {
                            $values['message'] = 'Could not create new user, contract support.';
                        }
                    } 
                    else {
                        $values['message'] = 'User already exists.';
                    }
                }

                if($user_id) {
                    $is_new = ($total_result==0);

                    if($is_new || $settings->update_user_on_login) {
                        $user_data = array( 'ID' => $user_id );

                        if($user_full_name) {
                            $user_data['display_name'] = $user_full_name;
                            $user_data['user_nicename'] = $user_full_name;
                        }
                        if($user_first_name) $user_data['first_name'] = $user_first_name;
                        if($user_last_name) $user_data['last_name'] = $user_last_name;

                        $user_id = wp_update_user($user_data);
                    }
                }
            }
            else {
                $values['message'] = 'Could not locate E-Mail address in user data.';
            }
        }
        else {
            $values['message'] = 'Invalid request, please refresh the page and try again.';
        }

        $callback = $this->options['callback'];

        if ( is_string( $callback ) ) {
            call_user_func( $callback, $type, $values );
        }
        elseif ( is_array( $callback ) ) {
            if ( '__construct' === $callback[1] )
                new $callback[0]( $type, $values );
            elseif ( is_callable( $callback ) )
                call_user_func( $callback, $type, $values );
        }
        else {
            trigger_error(
                'Cannot call your callback: ' . var_export( $callback, TRUE ),
                E_USER_ERROR
            );
        }

        // Important. WordPress will render the main page if we leave this out.
        exit;
    }

    /**
     * Parse request URI into associative array.
     *
     * @wp-hook template_redirect
     * @param   string $request
     * @return  array
     */
    protected function get_api_values( $request )
    {
        $keys    = $values = array();
        $count   = 0;
        $request = trim( $request, '/' );
        $tok     = strtok( $request, '/' );

        while ( $tok !== FALSE )
        {
            0 === $count++ % 2 ? $keys[] = $tok : $values[] = $tok;
            $tok = strtok( '/' );
        }

        // fix odd requests
        if ( count( $keys ) !== count( $values ) )
            $values[] = '';

        return array_combine( $keys, $values );
    }

    /**
     * Clean a string for use as a username
     */
    protected function clean_username( $in_username )
    {
        return str_replace(array('.','-','_'), '', array_shift(explode('@', $in_username)));
    }
}