<?php
namespace Webiik;

class AuthSocialPairingConfirmApi extends AuthBase
{
    public function __construct(
        Auth $auth,
        Csrf $csrf,
        WRouter $router,
        WTranslation $translation
    )
    {
        parent::__construct($auth, $csrf, $router, $translation);
    }

    public function run()
    {
        // Try to log in the user
        $resArr = $this->pairingStepTwo();

        // Set JSON header
        $response = new Response();
        $response->setContentType('json');

        // Print out response as JSON
        echo json_encode($resArr);
    }
}