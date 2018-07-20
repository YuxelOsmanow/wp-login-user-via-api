# wp-login-user-via-api
Enables the user to be logged via REST API.

# instructions

On accessing wp/v2/user/login with provided user_login ( user email atm ) and user_password returns an URL that can be used to log users in WP dashboard.

The URL supports the "redirect" parameter that will allow you to redirect user to preferred destination in admin panel after login.

# Example usage:

    $login_url = get_site_url() . '/wp-json/wp/v2/user/login';

    $creds = array(
        'user_login'    => 'test@example.com',
        'user_password' => 'demo',
    );

    $post_response = wp_remote_post( $login_url,
        array(
            'method' => 'POST',
            'blocking' => true,
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode( $creds ),
            'cookies' => array()
        )
    );

Here the $post_response->body will contain the Login Link. example :

{WP_SITE_LINK}/?wp-login.php?user_id={some_user_ID}&login_code={some_random_code}

You can add "redirect" to this link to make it redirect to desired destination in admin panel:

{WP_SITE_LINK}/?wp-login.php?user_id={some_user_ID}&login_code={some_random_code}&redirect=edit.php
