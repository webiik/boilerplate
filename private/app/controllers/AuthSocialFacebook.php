<?php
namespace Webiik;

class AuthSocialFacebook extends AuthBase
{
    private $flash;
    private $token;
    private $WConfig;

    public function __construct(
        Flash $flash,
        Token $token,
        AuthExtended $auth,
        Csrf $csrf,
        WRouter $router,
        WTranslation $translation,
        $WConfig
    )
    {
        parent::__construct($auth, $csrf, $router, $translation);
        $this->WConfig = $WConfig;
        $this->flash = $flash;
        $this->token = $token;
    }

    public function run()
    {
        // Initial settings

        // We need to obtain email address from successful social login
        // so set default value of email address to false
        $email = false;
        $provider = 'facebook';

        // Get login URL, we will use this URL later in code
        $loginUrl = $this->getRedirectUrl(false);
        $loginUrl = $loginUrl ? $loginUrl : $this->router->getUrlFor('login');

        // Prepare additional query string with referrer and permanent login
        $qsArr = [];
        if(isset($_GET['permanent']) && $_GET['permanent']){
            $qsArr['permanent'] = $_GET['permanent'];
        }
        if ($ref = $this->auth->getReferrer()) {
            $qsArr['ref'] = $ref;
        }
        $qs = count($qsArr) > 0 ? '?' . http_build_query($qsArr) : '';

        // Instantiate OAuth2Client
        $http = new CurlHttpClient();
        $oauth = new OAuth2Client($http);

        // Set authorization callback URL
        $oauth->setRedirectUri($this->router->getUrlFor('social-facebook') . $qs);

        // Set API end points
        $oauth->setAuthorizeUrl('https://www.facebook.com/v2.8/dialog/oauth');
        $oauth->setAccessTokenUrl('https://graph.facebook.com/v2.8/oauth/access_token');
        $oauth->setValidateTokenUrl('https://graph.facebook.com/debug_token');

        // Set API credentials
        $oauth->setClientId($this->WConfig['Facebook']['clientId']);
        $oauth->setClientSecret($this->WConfig['Facebook']['clientSecret']);

        // Make API calls

        // Build log in URL with specific scope and response type
        $apiLoginUrl = $oauth->getLoginUrl(
            [
                'email',
            ],
            'code'
        );

        // Redirect user to login URL
        // Disable current domain check, because we redirect to outside
        if (!isset($_GET['code']) || empty($_GET['code'])) {
            $this->auth->redirect($apiLoginUrl, false);
        }

        // Try to get Access token
        $data = $oauth->getAccessTokenByCode($_GET['code'], 'GET');
        if (!isset($data['access_token']) || empty($data['access_token'])) {
            // Err: Can't obtain access token
            $this->flash->addFlashNext('err', $this->translation->_t('auth.msg.err-access-token'));
            $this->auth->redirect($loginUrl);
        }

        // Try to get token info
        $info = $oauth->getTokenInfo($data['access_token'], $data['access_token'], 'GET');
        if (!isset($info['data']['user_id']) || empty($info['data']['user_id'])) {
            // Err: Can't obtain user id
            $this->flash->addFlashNext('err', $this->translation->_t('auth.msg.err-user-id'));
            $this->auth->redirect($loginUrl);
        }

        // Access protected resources
        $query = [
            'access_token' => $data['access_token'],
            'fields' => 'id,name,email',
        ];
        $apiLoginUrl = 'https://graph.facebook.com/v2.8/' . $info['data']['user_id'] . '?' . http_build_query($query);
        $res = $http->get($apiLoginUrl);

        // Try to obtain email address from response
        if (!$res['err'] && isset($res['body'])) {
            $body = json_decode($res['body'], true);
            if (isset($body['email'])) {
                $email = $body['email'];
            }
        }

        if (!$email) {
            // Err: Can't obtain user email
            $this->flash->addFlashNext('err', $this->translation->_t('auth.msg.err-email'));
            $this->auth->redirect($loginUrl);
        }

        // Try to login the user
        $resArr = $this->socialLogin($email, $provider, isset($qsArr['permanent']) ? true : false);

        // Add flash messages if there are some
        if (isset($resArr['msg'])) {
            foreach ($resArr['msg'] as $type => $messages) {
                foreach ($messages as $message) {
                    $this->flash->addFlashNext($type, $message);
                }
            }
        }

        // If user is successfully logged in
        if (!$resArr['err']) {

            if (!$resArr['redirectUrl']) {
                $resArr['redirectUrl'] = isset($resArr['activationUrl']) ? $this->router->getUrlFor('login') : $this->router->getUrlFor('account');
            }

            $this->auth->redirect($resArr['redirectUrl']);
        }

        // Login failed
        $this->auth->redirect($loginUrl);
    }
}