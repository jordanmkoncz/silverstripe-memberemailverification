<?php

/**
 * Alter the default SilverStripe login form
 */
Authenticator::register('EmailVerificationAuthenticator');
Authenticator::unregister('MemberAuthenticator');