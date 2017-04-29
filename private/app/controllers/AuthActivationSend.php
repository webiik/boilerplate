<?php
namespace Webiik;

class AuthActivationSend extends AuthBase
{
    private $flash;
    private $connection;
    private $render;
    private $PHPMailer;

    public function __construct(
        Flash $flash,
        Connection $connection,
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
        $this->connection = $connection;
        $this->render = $render;
        $this->PHPMailer = $PHPMailer;
    }

    public function run()
    {
        $resArr = $this->activationStepOne();

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

        if(!$resArr['err']) {

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
                $this->flash->addFlashNext('err', $this->translation->_t('auth.msg.errSend'));
            } else {
                $this->flash->addFlashNext('ok', $this->translation->_p('auth.msg.activation-sent', ['email' => htmlspecialchars($email)]));
            }
        }

        $this->auth->redirect($resArr['redirectUrl']);
    }
}