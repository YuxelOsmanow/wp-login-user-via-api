<?php
add_action( 'rest_api_init', 'mm_rest_user_endpoints' );
function mm_rest_user_endpoints() {
    register_rest_route( 'wp/v2', 'user/login', array(
        'methods' => 'POST',
        'callback' => 'mm_login_user',
    ) );
}

function mm_login_user( $request = null ) {
    $response = array();
    $parameters = $request->get_json_params();

    isset( $parameters[ 'user_login' ] ) ? $user_login = sanitize_text_field( $parameters[ 'user_login' ] ) : $user_login = '';
    isset( $parameters[ 'user_password' ] ) ? $user_password = sanitize_text_field( $parameters[ 'user_password' ] ) : $user_password = '';
    isset( $parameters[ 'redirect' ] ) ? $redirect = $parameters[ 'redirect' ] : $redirect = '';

    $error = new WP_Error();

    if ( empty( $user_login ) ) {
        $error->add( 400, __( "user_login field is required.", 'mm' ), array( 'status' => 400 ) );
        return $error;
    }

    if ( empty( $user_password ) ) {
        $error->add( 400, __( "user_password field is required.", 'mm' ), array( 'status' => 400 ) );
        return $error;
    }

    $user = get_user_by( 'email', $user_login );

    if ( empty( $user ) ) {
        $error->add( 400, __( "Invlid Username.", 'mm' ), array( 'status' => 400 ) );
        return $error;
    }

    $creds = array(
        'user_login'    => $user->user_login,
        'user_password' => $user_password,
        'remember'      => true
    );

    if ( ! $user ) {
        $error->add( 400, __( "Invlid Username.", 'mm' ), array( 'status' => 400 ) );
        return $error;
    }

    $is_user_signed = wp_signon( $creds, false );
    $user_code = get_user_meta( $user->ID, 'login_code', true );

    if( empty( $user_code ) ) {
        update_user_meta( $user->ID, 'login_code', mm_generate_random_user_login_code( $user->ID ) );
        $user_code = mm_generate_random_user_login_code( $user->ID );
    }

    $login_link = get_site_url() . '/wp-login.php?user_id=' . $user->ID . '&login_code=' . $user_code;

    if( !empty( $redirect ) ) {
        $login_link .= '&redirect=' . $redirect;
    }

    if ( is_wp_error( $is_user_signed ) ) {
        $error->add( 400, __( "Invlid Username or Password.", 'mm' ), array( 'status' => 400 ) );
        return $error;
    }

    $auth_cookie = wp_generate_auth_cookie( $user->ID, mm_keep_me_logged_in_for_1_year(), 'auth', '' );
    $auth_cookie_logged_in = wp_generate_auth_cookie( $user->ID, mm_keep_me_logged_in_for_1_year(), 'logged_in', '' );
    $parsed_cookie = wp_parse_auth_cookie();

    $response[ 'user_code' ] = $user_code;
    $response[ 'user_login_link' ] = $login_link;
    $response[ 'auth_cookie' ] = $auth_cookie;
    $response[ 'auth_cookie_logged_in' ] = $auth_cookie_logged_in;
    $response[ 'code' ] = 200;
    $response[ 'message' ] = __( "User Can be Logged In. Link to Login - $login_link ", "mm" );

    return new WP_REST_Response( $response, 200 );
}

function mm_generate_random_user_login_code( $user_id ){
    $bytes = random_bytes( 10 );
    $value = bin2hex( $bytes );
    update_user_meta( $user_id, 'login_code', $value );

    return $value;
}
