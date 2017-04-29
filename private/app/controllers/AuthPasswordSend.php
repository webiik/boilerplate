<?php
namespace Webiik;

class AuthPasswordSend extends AuthBase
{
    private $flash;
    private $render;
    private $PHPMailer;

    public function __construct(
        Flash $flash,
        WRender $render,
        \PHPMailer $PHPMailer,
        Auth $auth,
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

        // Try to proceed step one in password renewal process
        $resArr = $this->userPswdUpdateStepOne();

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
                        $this->flash->addFlashNow($type, $message);
                    }
                }
            }
        }

        // If password renewal request has been successfully generated, send password renewal email
        if (!$resArr['err']) {

            // Prepare email message
            $data = [
                'confirmationUrl' => $resArr['confirmationUrl']
            ];
            $message = $this->render->renderWithTranslation('password-send', $data, 'emails');

            // Send email message
            // It is always needed to prepare message before sending the message
            $this->PHPMailer->isHTML();
            $this->PHPMailer->addAddress($resArr['email']);
            $this->PHPMailer->Subject = $this->translation->_t('email.subject');
            $this->PHPMailer->Body = $message;
            if(!$this->PHPMailer->send()) {
                $this->flash->addFlashNow('err', $translations['auth']['msg']['errSend']);
                //echo 'Mailer Error: ' . $this->PHPMailer->ErrorInfo;
            }
        }

        // Render template
        echo $this->render->render(['password-send', $translations]);
    }
}