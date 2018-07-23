<?php

class User_Login_Code {
    static $instance = null;
    var $user_meta = 'login_code';

    static function & get_instance() {
        if ( null == User_Login_Code::$instance ) {
            User_Login_Code::$instance = new User_Login_Code();
        }
        return User_Login_Code::$instance;
    }

    function __construct(){
        add_action( 'login_head', array( $this, 'check_login_code' ) );
    }

    function check_login_code(){
        $user_id = filter_input( INPUT_GET, 'user_id' );
        $login_code = filter_input( INPUT_GET, 'login_code' );
        $redirect_from_url = filter_input( INPUT_GET, 'redirect', FILTER_SANITIZE_URL );
        $action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_URL );
        $redirect_url = get_admin_url();

        if( !empty( $redirect_from_url ) ) {
            $redirect_url .= $redirect_from_url;
        }

        if ( !empty( $action ) ) {
            $redirect_url .= '&action=' . $action ;
        }

        if ( $user_id && $login_code && $login_code == get_user_meta( $user_id, $this->user_meta, true ) ){
            wp_set_auth_cookie( $user_id, true );
            wp_redirect( $redirect_url );
            exit();
        }
    }

    function get_user_login_code( $user_id ){
        $login_code = get_user_meta( $user_id, $this->user_meta, true );

        if ( empty( $login_code ) ){
            $login_code = $this->mm_generate_random_user_login_code( $user_id );
        }

        return $login_code;
    }

    function get_user_login_url( $user_id ){
        $params = array (
            'user_id' => $user_id,
            'login_code' => $this->get_user_login_code( $user_id )
        );

        $url_login = wp_login_url();
        $separator = strstr( '?', $url_login ) ? '&' : '?';

        return $url_login . $separator . http_build_query( $params );
    }
}

$ULC_instance = User_Login_Code::get_instance();
