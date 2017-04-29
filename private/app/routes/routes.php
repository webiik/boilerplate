<?php
/**
 * Route definitions for all languages
 * Signature: 'routeName' => ['methods' => array, 'controller' => string, (optional)'middlewares' => array]
 * You can also define routes for specific language in /routes/routes.{lang}.php
 * To make routes working properly you also need to add translations of these routes in /translations/_app.{lang}.php
 */
return [
    // User account service routes
    'signup' => [
        'methods' => ['GET', 'POST'],
        'controller' => 'Webiik\AuthSignup:run',
    ],
    'login' => [
        'methods' => ['GET', 'POST'],
        'controller' => 'Webiik\AuthLogin:run',
    ],
    'social-facebook' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthSocialFacebook:run',
    ],
    'social-pairing-send' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthSocialPairingSend:run',
    ],
    'social-pairing-confirm' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthSocialPairingConfirm:run',
    ],
    'password-send' => [
        'methods' => ['GET', 'POST'],
        'controller' => 'Webiik\AuthPasswordSend:run',
    ],
    'password-confirm' => [
        'methods' => ['GET', 'POST'],
        'controller' => 'Webiik\AuthPasswordConfirm:run',
    ],
    'activation-send' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthActivationSend:run',
    ],
    'activation-confirm' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthActivationConfirm:run',
    ],
    'logout' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthLogout:run',
    ],
    // User account service routes as private API
    'api-signup' => [
        'methods' => ['POST'],
        'controller' => 'Webiik\AuthSignupApi:run',
    ],
    'api-login' => [
        'methods' => ['POST'],
        'controller' => 'Webiik\AuthLoginApi:run',
    ],
    'api-social-facebook' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthSocialFacebookApi:run',
    ],
    'api-social-pairing-send' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthSocialPairingSendApi:run',
    ],
    'api-social-pairing-confirm' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthSocialPairingConfirmApi:run',
    ],
    'api-password-send' => [
        'methods' => ['GET', 'POST'],
        'controller' => 'Webiik\AuthPasswordSendApi:run',
    ],
    'api-password-confirm' => [
        'methods' => ['GET', 'POST'],
        'controller' => 'Webiik\AuthPasswordConfirmApi:run',
    ],
    'api-activation-send' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthActivationSendApi:run',
    ],
    'api-activation-confirm' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthActivationConfirmApi:run',
    ],
    'api-logout' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\AuthLogoutApi:run',
    ],
    // Website routes
    'home' => [
        'methods' => ['GET'],
        'controller' => 'Webiik\Page:run',
    ],
    'account' => [
        'methods' => ['GET', 'POST'],
        'controller' => 'Webiik\Account:run',
        'middlewares' => [
            'Webiik\MwAuth:can' => ['access-account'],
        ],
    ],
    'admin' => [
        'methods' => ['GET', 'POST'],
        'controller' => 'Webiik\Admin:run',
        'middlewares' => [
            'Webiik\MwAuth:can' => ['access-admin'],
        ],
    ],
];