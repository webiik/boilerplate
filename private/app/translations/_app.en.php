<?php
/**
 * App wide translations for current language.
 * These translations will be available on every page. Great for header, footer etc.
 * Don't place here translations for separate pages!
 */
return [
    // Special key 'routes' - here you write URI translations of routes.
    // Only routes with translated URIs will be active.
    'routes' => [
        // User account service routes
        'signup' => '/signup',
        'login' => '/login',
        'social-facebook' => '/social-facebook',
        'social-pairing-send' => '/social-account-pairing-send',
        'social-pairing-confirm' => '/social-account-pairing-confirm',
        'password-send' => '/password-renewal-send',
        'password-confirm' => '/password-renewal-confirm',
        'activation-send' => '/activation-send',
        'activation-confirm' => '/activation-confirm',
        'logout' => '/logout',
        // Account service routes as private API
        'api-signup' => '/api/auth/signup',
        'api-login' => '/api/auth/login',
        'api-social-facebook' => '/api/auth/social-facebook',
        'api-social-pairing-send' => '/api/auth/social-account-pairing-send',
        'api-social-pairing-confirm' => '/api/auth/social-account-pairing-confirm',
        'api-password-send' => '/api/auth/password-renewal-send',
        'api-password-confirm' => '/api/auth/password-renewal-confirm',
        'api-activation-send' => '/api/auth/activation-send',
        'api-activation-confirm' => '/api/auth/activation-confirm',
        'api-logout' => '/api/auth/logout',
        // Website routes
        'home' => '/',
        'account' => '/account',
        'admin' => '/admin',
    ],
    'nav' => [
        // 0 - always show nav item
        // 1 - show nav item only when user is not logged in
        // 2 - show nav item only when user is logged in
        'home' => ['Home page', 0],
        'login' => ['Log in', 1],
        'signup' => ['Sign up', 1],
        'password-send' => ['Forgot password?', 1],
        'account' => ['My account', 2],
        'admin' => ['Admin', 2],
        'logout' => ['Logout', 2],
    ],
    'auth' => [
        'msg' => [
            'csrf-mismatch' => 'CSRF token mismatch.',
            'csrf-form' => 'Form can be sent only using the button.',
            'correct-red-fields' => 'Correct red marked fields.',
            'welcome-first' => 'Welcome! It\'s great that you are here.',
            'welcome-again' => 'Welcome! It\'s great to see you again.',
            'user-banned' => 'You have a ban to access this site.',
            'too-many-attempts' => 'Too many attempts. Try it later.',
            'user-already-exists' => 'Same account already exists.',
            'user-does-not-exist-1' => 'Account does not exist.',
            'user-does-not-exist-2' => 'Sign up first.',
            'user-activate-1' => 'First login requires activation. An activation email has been sent to your email box. Open this email and confirm the activation.',
            'user-activate-2' => 'Resend the activation email?',
            'entry-required' => 'Required field.',
            'entry-invalid' => 'Invalid format.',
            'entry-long' => 'Entry is too long.',
            'entry-short' => 'Entry is too short',
            'pswd-confirm-request' => 'We have sent you an email with link, where you can change your password.',
            'pswd-changed' => 'Your password has been successfully changed. Log in with the new password.',
            'pswd-incorrect' => 'Incorrect password.',
            'pswd-not-set-1' => 'Password is not set. Login with {providers} and set the password.',
            'pswd-not-set-2' => 'or',
            'social-pair-1' => 'Social account is not paired with main account.',
            'social-pair-2' => 'Send pairing email?',
            'user-has-no-permissions' => 'You don\'t have sufficient permissions to view this site.',
            'user-not-logged' => 'Log in to view content of this site.',
            'unexpected-err' => 'Unexpected error({operation}{errNum}).',
            'key-not-set' => 'Unauthorised request.',
            'key-invalid-format' => 'Invalid key format.',
            'key-invalid' => 'Invalid key.',
            'key-cant-generate' => 'Can\'t generate key.',
            'provider-not-set' => 'Provider is not set.',
            'provider-unsupported' => 'Unsupported provider.',
            'account-is-already-activated' => 'You have already activated your account.',
            'account-activated-by-another-user' => 'Account has been already activated by another user. You cannot use this account any more.',
            'account-activated' => 'Your account has been activated. Just log in.',
            'account-paired' => 'Your main account has been paired with {provider}. Now you can use {provider} to login.',
            'activation-sent' => 'Activation message has been sent to {email}.',
            'pairing-sent' => 'Pairing message has been send to {email}.',
            'logout' => 'You\'ve been successfully logged out.',
            'errSend' => 'Unexpected error, email message could not be sent.',
        ],
    ],
];