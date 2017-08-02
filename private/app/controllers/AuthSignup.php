<?php
namespace Webiik;

class AuthSignup extends AuthBase
{
    private $flash;
    private $render;
    private $PHPMailer;

    public function __construct(
        Flash $flash,
        WRender $render,
        \PHPMailer $PHPMailer,
        AuthExtended $auth,
        Csrf $csrf,
        WRouter $router,
        WTranslation $translation
    )
    {
        parent::__construct($auth, $csrf, $router, $translation);
        $this->flash = $flash;
        $this->render = $render;
        $this->PHPMailer = $PHPMailer;
    }

    public function run()
    {
        // Get merged translations
        // We always get all shared translations and translations only for current page,
        // because Skeleton save resources and adds only these data to Translation class
        $translations = $this->translation->_tAll(false);

        // Try to sign up the user
        $resArr = $this->signup();

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
                    if ($type == 'ok' || $type == 'inf') {
                        $this->flash->addFlashNext($type, $message);
                    }
                }
            }
        }

        // User was successfully signed up
        if (!$resArr['err']) {

            // Determine redirect URL
            if (!$resArr['redirectUrl']) {
                $resArr['redirectUrl'] = isset($resArr['activationUrl']) ? $this->router->getUrlFor('login') : $this->router->getUrlFor('account');
            }

            // If sign-up requires activation, send activation email
            if (isset($resArr['activationUrl'])) {

                // Prepare email message
                $data = [
                    'activationUrl' => $resArr['activationUrl'],
                ];
                $message = $this->render->renderWithTranslation('signup', $data, 'emails');

                // Send email message
                // It is always needed to prepare message before sending the message
                $this->PHPMailer->isHTML();
                $this->PHPMailer->addAddress($resArr['form']['data']['email']);
                $this->PHPMailer->Subject = $this->translation->_t('email.subject');
                $this->PHPMailer->Body = $message;
                if(!$this->PHPMailer->send()) {
                    $this->flash->addFlashNow('err', $this->translation->_t('auth.msg.errSend'));
                }

            }

            $this->auth->redirect($resArr['redirectUrl']);
        }

        // Render template
        echo $this->render->render(['signup', $translations]);
    }
}