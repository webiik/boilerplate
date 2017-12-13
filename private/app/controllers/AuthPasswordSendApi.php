<?php
namespace Webiik;

class AuthPasswordSendApi extends AuthBase
{
    private $render;
    private $PHPMailer;

    public function __construct(
        WRender $render,
        \PHPMailer $PHPMailer,
        AuthExtended $auth,
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
        // Try to log in the user
        $resArr = $this->userPswdUpdateStepOne();

        // Get new csrf token
        $resArr['csrf']['tokenName'] = $this->csrf->getTokenName();
        $resArr['csrf']['token'] = $this->csrf->getToken();

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
                $resArr['err'] = true;
                $resArr['msg']['err'][] = $this->translation->_t('auth.msg.errSend');
            }
        }

        // Set JSON header
        $response = new Response();
        $response->setContentType('json');

        // Print out response as JSON
        echo json_encode($resArr);
    }
}