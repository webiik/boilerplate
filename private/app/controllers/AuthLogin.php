<?php

namespace Webiik;

class AuthLogin extends AuthBase
{
    private $flash;
    private $render;

    public function __construct(
        Flash $flash,
        WRender $render,
        AuthExtended $auth,
        Csrf $csrf,
        WRouter $router,
        WTranslation $translation
    )
    {
        parent::__construct($auth, $csrf, $router, $translation);
        $this->render = $render;
        $this->flash = $flash;
    }

    public function run($onPageLogin = false)
    {
        // Get merged translations
        // We always get all shared translations and translations only for current page,
        // because Skeleton save resources and adds only these data to Translation class
        $translations = $this->translation->_tAll(false);

        // Get referrer for social login
        if ($ref = $this->getRedirectUrl($onPageLogin)) {
            $translations['social']['qs'] = '?ref=' . urlencode($ref);
        }

        // Try to login the user
        $resArr = $this->login($onPageLogin);

        // Add formatted form data to translation which we use further in template
        if (isset($resArr['form']['data'])) {
            $translations['form']['data'] = $resArr['form']['data'];
        }

        // Add the form inline error messages
        if (isset($resArr['form']['msg'])) {
            $translations['form']['msg'] = $resArr['form']['msg'];
        }

        // Add flash messages if there are some
        if (isset($resArr['msg'])) {
            foreach ($resArr['msg'] as $type => $messages) {
                foreach ($messages as $message) {
                    if ($type == 'err') {
                        $this->flash->addFlashNow($type, $message);
                    }
                    if ($type == 'ok') {
                        $this->flash->addFlashNext($type, $message);
                    }
                }
            }
        }

        // If user is successfully logged in
        if (!$resArr['err']) {
            $redirectUrl = $resArr['redirectUrl'] ? $resArr['redirectUrl'] : $this->router->getUrlFor('account');
            $this->auth->redirect($redirectUrl);
        }

        // Render template
        echo $this->render->render(['login', $translations]);
    }
}