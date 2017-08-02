<?php
namespace Webiik;

class AuthActivationSendApi extends AuthBase
{
    private $connection;
    private $render;
    private $PHPMailer;

    public function __construct(
        Connection $connection,
        WRender $render,
        \PHPMailer $PHPMailer,
        AuthExtended $auth,
        Csrf $csrf,
        WRouter $router,
        WTranslation $translation
    )
    {
        parent::__construct($auth, $csrf, $router, $translation);
        $this->connection = $connection;
        $this->render = $render;
        $this->PHPMailer = $PHPMailer;
    }

    public function run()
    {
        // Try to log in the user
        $resArr = $this->activationStepOne();

        // If password renewal request has been successfully generated
        if (!$resArr['err']) {

            // Send email with activation link...

            // Get user email
            $pdo = $this->connection->connect();
            $q = $pdo->prepare('SELECT email FROM auth_users WHERE id = ?');
            $q->execute([$resArr['uid']]);
            $email = $q->fetchColumn();

            // Prepare email message
            $data = [
                'activationUrl' => $resArr['activationUrl'],
            ];
            $message = $this->render->renderWithTranslation('activation', $data, 'emails');

            // Send email message
            // It is always needed to prepare message before sending the message
            $this->PHPMailer->isHTML();
            $this->PHPMailer->addAddress($email);
            $this->PHPMailer->Subject = $this->translation->_t('email.subject');
            $this->PHPMailer->Body = $message;
            if(!$this->PHPMailer->send()) {
                $resArr['err'] = true;
                $resArr['msg']['err'][] = $this->translation->_t('auth.msg.errSend');
            } else {
                $resArr['msg']['ok'][] = $this->translation->_p('auth.msg.activation-sent', ['email' => htmlspecialchars($email)]);
            }
        }

        // Set JSON header
        $response = new Response();
        $response->setContentType('json');

        // Print out response as JSON
        echo json_encode($resArr);
    }
}