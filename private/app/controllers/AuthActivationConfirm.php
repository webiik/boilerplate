<?php
namespace Webiik;

class AuthActivationConfirm extends AuthBase
{
    private $flash;

    public function __construct(
        Flash $flash,
        AuthExtended $auth,
        Csrf $csrf,
        WRouter $router,
        WTranslation $translation
    )
    {
        parent::__construct($auth, $csrf, $router, $translation);
        $this->flash = $flash;
    }

    public function run()
    {
        $resArr = $this->activationStepTwo();

        // Add flash messages if there are some
        if (isset($resArr['msg'])) {
            foreach ($resArr['msg'] as $type => $messages) {
                foreach ($messages as $message) {
                    if ($type == 'err') {
                        $this->flash->addFlashNext($type, $message);
                    }
                    if ($type == 'ok') {
                        $this->flash->addFlashNext($type, $message);
                    }
                }
            }
        }

        $this->auth->redirect($resArr['redirectUrl']);
    }
}