<?php
// Load app configuration file
$config = \Webiik\WConfig::loadConfig(__DIR__ . '/config');

// Micro framework example
//$app = new \Webiik\Webiik($config);
//$app->router()->map(['GET'], '/', function() {
//    echo 'Hello World!';
//}, 'home');
//$app->run();
//exit;

// Create configured Skeleton instance
$app = new \Webiik\WebiikFW($config);

// Add Twig template engine service
$app->container()->addService('WTemplateEngine', function ($c) {

    // Set Twig basic settings
    $loader = new Twig_Loader_Filesystem(__DIR__ . '/views');
    $twig = new Twig_Environment($loader, array(
        'cache' => __DIR__ . '/tmp/cache/views',
        'debug' => !$c['WConfig']['Error']['silent'],
    ));

    // Add Twig extension(s)
    $twig->addExtension(new \Twig_Extension_Debug());

    // Add additional params, functions, etc.

    /* @var $router \Webiik\Request */
    $router = $c['Webiik\Request'];

    // Add root URL
    $twig->addGlobal('ROOT', $router->getWebRootUrl());

    // Add router functions

    /* @var $router \Webiik\WRouter */
    $router = $c['Webiik\WRouter'];

    // Return uri for given route name
    $function = new \Twig_SimpleFunction('uriFor', function ($routeName, $params = [], $lang = false) use ($router) {
        return $router->getUriFor($routeName, $lang, $params);
    });
    $twig->addFunction($function);

    // Return url for given route name
    $function = new \Twig_SimpleFunction('urlFor', function ($routeName, $params = [], $lang = false) use ($router) {
        return $router->getUrlFor($routeName, $lang, $params);
    });
    $twig->addFunction($function);

    // Return current route name
    $function = new \Twig_SimpleFunction('currentRoute', function () use ($router) {
        return $router->routeInfo['name'];
    });
    $twig->addFunction($function);

    // Return current URL
    $function = new \Twig_SimpleFunction('currentUrl', function ($withQs = true, $params = [], $lang = false) use ($router) {
        $qs = $_GET ? '?' . http_build_query($_GET) : '';
        $qs = $withQs ? $qs : '';
        return $router->getUrlFor($router->routeInfo['name'], $lang, $params) . $qs;
    });
    $twig->addFunction($function);

    // Check if given route name is current route
    $function = new \Twig_SimpleFunction('isCurrentRoute', function ($routeName) use ($router) {
        return $routeName == $router->routeInfo['name'] ? true : false;
    });
    $twig->addFunction($function);

    // Add translation functions

    /* @var $translation \Webiik\WTranslation */
    $translation = $c['Webiik\WTranslation'];

    // Return translation for given key
    $function = new \Twig_SimpleFunction('_t', function ($key) use ($translation) {
        return $translation->_t($key);
    });
    $twig->addFunction($function);

    // Return parsed translation for given key
    $function = new \Twig_SimpleFunction('_p', function ($key, $val) use ($translation) {
        return $translation->_p($key, $val);
    });
    $twig->addFunction($function);

    // Add auth functions

    /* @var $auth \Webiik\AuthExtended */
    $auth = $c['Webiik\AuthExtended'];

    // Return user id on success otherwise false
    $function = new \Twig_SimpleFunction('isUserLogged', function () use ($auth) {
        return $auth->isLogged();
    });
    $twig->addFunction($function);

    // Return user id if user can do the action otherwise false
    $function = new \Twig_SimpleFunction('userCan', function ($action) use ($auth) {
        return $auth->userCan($action);
    });
    $twig->addFunction($function);

    // Add flash functions

    /* @var $flash \Webiik\Flash */
    $flash = $c['Webiik\Flash'];

    // Return flash messages for current request
    $function = new \Twig_SimpleFunction('messages', function () use ($flash) {
        return $flash->getFlashes();
    });
    $twig->addFunction($function);

    // Add CSRF functions

    /* @var $csrf \Webiik\Csrf */
    $csrf = $c['Webiik\Csrf'];

    // Return csrf hidden input field
    $function = new \Twig_SimpleFunction('csrfInput', function () use ($csrf) {
        return $csrf->getHiddenInput();
    }, [
        'is_safe' => ['html']
    ]);
    $twig->addFunction($function);

    // Return csrf token
    $function = new \Twig_SimpleFunction('csrfToken', function () use ($csrf) {
        return $csrf->getToken();
    });
    $twig->addFunction($function);

    // Return csrf token
    $function = new \Twig_SimpleFunction('csrfTokenName', function () use ($csrf) {
        return $csrf->getTokenName();
    });
    $twig->addFunction($function);

    // Return configured template engine
    return $twig;
});

// Add Twig's render method as Webiik's render handler
$app->container()->addService('Webiik\WRender', function ($c) {

    $renderHandler = function ($template, $arr) use ($c) {
        return $c['WTemplateEngine']->render($template . '.twig', $arr);
    };

    $render = new \Webiik\WRender($c['Webiik\WTranslation']);
    $render->addRenderHandler($renderHandler);

    return $render;
});

// There are two possibilities how to display login page. First one is redirect user to login page.
// Second one is display login page at URL of protected content. Webiik uses first way as default.
// If you prefer second way, uncomment the following lines. If you use second way, don't forget to
// move translations for routes that require authentication to _app... translation file.
// Add Auth middleware
//$app->addService('Webiik\AuthMw', function ($c) use ($app) {
//    return new \Webiik\AuthMw(...$app::DIconstructor('Webiik\AuthMw', $c));
//});

// Add own error routes handlers
$app->router()->map404('Webiik\Error404:run');
$app->router()->map405('Webiik\Error405:run');

// Run Skeleton
$app->run();