<?php

class EmailVerificationAuthenticator extends MemberAuthenticator
{
    // Tell this Authenticator to use your custom login form
    // The 3rd parameter MUST be 'LoginForm' to fit within the authentication framework
    public static function get_login_form(Controller $controller)
    {
        return Object::create('EmailVerificationLoginForm', $controller, 'LoginForm');
    }
}