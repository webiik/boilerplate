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

    // Auth class settings
    'Auth' => [

        // Account resolution mode read more at setUserAccountResolution
        // 0 - user can make only one account and access it in every language
        // 1 - user can make only one account and access it only with language where he created it
        // 2 - user can make multiple accounts and access them only with language where he created them
        'accountResolutionMode' => 0, // it affects also AuthExtended

        // Auto logout time in sec
        // If user doesn't interact with his account for autoLogoutTime, he will be automatically logged out.
        // 0 = disabled auto logout
        'autoLogoutTime' => 600,

        // How much sec lasts the permanent login cookie
        // 0 = disabled permanent login
        'permanentCookieExpirationTime' => 0,

        // Name of permanent login cookie
        'permanentCookieName' => 'PC',

        // Name of loggin session
        'loginSessionName' => 'logged',

        // Set permanent login files directory
        'permanentLoginFilesDir' => __DIR__ . '/../tmp/permanent',

        // Automatically delete expired permanent records with 5% chance
        // during isLogged method call, 0 = don't delete them
        'autoDeleteExpiredPermanentRecords' => 5,

    ],

    // AuthExtended class
    'AuthExtended' => [

        // Indicates if user accounts need activation
        'withActivation' => true,

        // Number of attempt to login, sign-up, token actions
        // Allow 60 attempts per 1 hour from same ip
        'attemptsLimit' => [60, 3600],

        // Allow validate token within 24 hours
        'confirmationTime' => 86400,

        // Password salt
        'salt' => 'r489cjd3Xhed',

        // Automatically delete expired tokens and attempts during their creation with 1% chance to delete
        'autoDelete' => true,
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

        // How sessions cookie will be valid in seconds. 0 = till browser is closed
        'cookieLifetime' => 0,

        // How long an unused PHP session will be kept alive
        // If Auth's autoLogoutTime is not 0, this value should be same or greater
        'gcLifetime' => 1440,
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
            'SMTPOptions' => [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ],
            ],
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