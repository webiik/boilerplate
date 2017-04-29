<?php
namespace Webiik;

class AuthActivationConfirmApi extends AuthBase
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
        $resArr = $this->activationStepTwo();

        // Set JSON header
        $response = new Response();
        $response->setContentType('json');

        // Print out response as JSON
        echo json_encode($resArr);
    }
}