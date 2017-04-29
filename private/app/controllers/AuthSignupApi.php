<?php
namespace Webiik;

class AuthSignupApi extends AuthBase
{
    private $render;
    private $PHPMailer;

    public function __construct(
        WRender $render,
        \PHPMailer $PHPMailer,
        Auth $auth,
        Csrf $csrf,
        WRouter $router,
        WTranslation $translation
    )
    {
        parent::__construct($auth, $csrf, $router, $translation);
        $this->render = $render;
        $this->PHPMailer = $PHPMailer;
    }

    public function run()
    {
        // Try to login the user
        $resArr = $this->signup();

        // Set JSON header
        $response = new Response();
        $response->setContentType('json');

        // If sign-up requires activation, send activation email
        if (isset($resArr['activationUrl'])) {

            // Prepare email message
            $data = [
                'activationUrl' => $resArr['activationUrl']
            ];
            $message = $this->render->renderWithTranslation('signup', $data, 'emails');

            // Send email message
            // It is always needed to prepare message before sending the message
            $this->PHPMailer->isHTML();
            $this->PHPMailer->addAddress($resArr['form']['data']['email']);
            $this->PHPMailer->Subject = $this->translation->_t('email.subject');
            $this->PHPMailer->Body = $message;
            if(!$this->PHPMailer->send()) {
                $resArr['err'] = true;
                $resArr['msg']['err'][] = $this->translation->_t('auth.msg.errSend');
            }
        }

        // Print out response as JSON
        echo json_encode($resArr);
    }
}