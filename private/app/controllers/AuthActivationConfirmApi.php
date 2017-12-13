<?php
namespace Webiik;

class AuthActivationConfirmApi extends AuthBase
{
    public function __construct(
        AuthExtended $auth,
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

        // Get new csrf token
        $resArr['csrf']['tokenName'] = $this->csrf->getTokenName();
        $resArr['csrf']['token'] = $this->csrf->getToken();

        // Set JSON header
        $response = new Response();
        $response->setContentType('json');

        // Print out response as JSON
        echo json_encode($resArr);
    }
}