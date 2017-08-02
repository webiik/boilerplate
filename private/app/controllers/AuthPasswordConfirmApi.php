<?php
namespace Webiik;

class AuthPasswordConfirmApi extends AuthBase
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
        $resArr = $this->userPswdUpdateStepTwo();

        // Set JSON header
        $response = new Response();
        $response->setContentType('json');

        // Print out response as JSON
        echo json_encode($resArr);
    }
}