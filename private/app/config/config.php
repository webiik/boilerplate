<?php
return [

    /**
     * The following part of configuration is optional for Webiik, but required for WebiikFW.
     * --------------------------------------------------------------------------------------
     */

    // Error class settings
    'Error' => [

        // Indicates that errors will be or not be printed on screen. Set to true on production.
        'silent' => false,
    ],

    // Log class settings
    'Log' => [

        // Time zone for log files. Every log has also unix time stamp, which is timezone agnostic.
        'timeZone' => 'America/New_York',

        // We add LogHandlerRotatingFile to Log, so we need to configure it
        'LogHandlerRotatingFile' => [
            'dir' => __DIR__ . '/../logs',
        ],

        // We add LogHandlerEmail to Log, so we need to configure it
        'LogHandlerEmail' => [
            'dir' => __DIR__ . '/../logs',
            'recipient' => 'SET-YOUR-OWN-ADDRESS',
            'sender' => 'SET-YOUR-OWN-ADDRESS',
            'subject' => 'Webiik system notice',
        ],
    ],

    // Router class settings
    'Router' => [

        // Use default lang in URI? If it set to true, home page for default language will be: /en/
        'dlInUri' => false,

        // Set whether log getUriFor and getUrlFor warnings
        'logWarnings' => true,
    ],

    /**
     * The following part of configuration is required by WebiikFW.
     * ------------------------------------------------------------
     */

    // WebiikFW class settings
    'WebiikFW' => [

        // Folder structure
        'publicDir' => __DIR__ . '/../../../',
        'privateDir' => __DIR__ . '/../../../private',
    ],

    // Translation class settings
    'Translation' => [

        // First language is default
        // Signature is: [language in ISO 639-1, timezone identifier, [array of fallback languages in ISO 639-1]]
        'languages' => [
            'en' => ['America/New_York'],
            'cs' => ['Europe/Prague', ['en']],
        ],

        // Set whether log missing translations
        'logWarnings' => true,
    ],

    // Connection class settings
    'Connection' => [

        // First connection is default connection. Internally Webiik uses the default connection.
        // $dialect, $host, $dbname, $user, $pswd, $encoding
        'user' => ['mysql', 'localhost', 'webiik', 'root', 'root'],
        'admin' => ['mysql', 'localhost', 'webiik', 'root', 'root'],
    ],

    // AuthMwRedirect class settings
    'AuthMwRedirect' => [

        // Route where user will be redirected if he/she is not logged in
        'loginRouteName' => 'login',
    ],

    // Auth class settings
    'Auth' => [

        // Indicates if authentication to account will be common for all language adaptations
        // of website or if each language adaptation will require separate login.
        'distinguishLanguages' => false,

        // Name of loggin session
        'loginSessionName' => 'logged',

        // Name of permanent login cookie
        'permanentLoginCookieName' => 'PC',

        // How much hours lasts the permanent login cookie
        'permanentLoginHours' => 1,

        // Indicates if account needs activation
        'withActivation' => true,
    ],

    // Cookie class settings
    'Cookie' => [

        // The (sub)domain that the cookie is available to. Empty = cookie is valid for all (sub)domains.
        'domain' => '',

        // URI for which cookie is valid
        'uri' => '/',

        // Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client
        'secure' => false,

        // When TRUE the cookie will be made accessible only through the HTTP protocol.
        // This means that the cookie won't be accessible by scripting languages, such as JavaScript.
        'httpOnly' => false, // bool
    ],

    // Sessions class settings
    'Session' => [

        // Session name. string|false = default name
        'name' => 'US',

        // Directory where sessions will be stored. path|false = default path
        'dir' => __DIR__ . '/../tmp/sessions',

        // How long sessions will be valid in seconds. int, 0 = till session is valid
        'lifetime' => 1440,
    ],

    // Csrf class settings
    'Csrf' => [

        // Name of CSRF token
        'tokenName' => 'csrf',
    ],

    // PHPMailer class settings
    'PHPMailer' => [
        'fromName' => 'SET-YOUR-OWN-NAME',
        'fromEmail' => 'SET-YOUR-OWN-ADDRESS',

        // Setting for SMTP socket
        'SMTP' => [
            'isSMPT' => false,
            'timeout' => 2,
            'host' => '',
            'port' => 25,
            'SMTPSecure' => 'tls',
            'SMTPAuth' => false,
            'SMTPAuthUserName' => '',
            'SMTPAuthPswd' => '',
            'SMTPOptions' => [],
        ]
    ],

    /**
     * Below comes configuration for additional classes you will use with Webiik.
     * --------------------------------------------------------------------------
     */

    // Facebook login settings
    'Facebook' => [

        // Folder structure
        'clientId' => 'SET-YOUR-OWN-ID',
        'clientSecret' => 'SET-YOUR-OWN-SECRET',
    ],
];