<?php
namespace Webiik;

class AuthPasswordConfirm extends AuthBase
{
    private $flash;
    private $render;

    public function __construct(
        Flash $flash,
        WRender $render,
        Auth $auth,
        Csrf $csrf,
        WRouter $router,
        WTranslation $translation
    )
    {
        parent::__construct($auth, $csrf, $router, $translation);
        $this->flash = $flash;
        $this->render = $render;
    }

    public function run()
    {
        // Get merged translations
        // We always get all shared translations and translations only for current page,
        // because Skeleton save resources and adds only these data to Translation class
        $translations = $this->translation->_tAll(false);

        // Try to proceed step two in password renewal process
        $resArr = $this->userPswdUpdateStepTwo();

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

        // If password has been successfully updated
        if (!$resArr['err']) {
            $this->auth->redirect($resArr['redirectUrl']);
        }

        // Render template
        echo $this->render->render(['password-confirm', $translations]);
    }
}