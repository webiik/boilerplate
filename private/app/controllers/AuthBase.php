<?php

namespace Webiik;

/**
 * Implementation of Webiik/AuthExtended class. It provides all functionality
 * of Webiik/AuthExtended prepared for easy use on website. In other words, it
 * process forms and does all dirty work: gets/validates/prepares data needed
 * by Webiik/AuthExtended and finally displays translated user-friendly messages.
 * Read comments for more info.
 *
 * Class AuthBase
 * @package Webiik
 */
class AuthBase
{
    /**
     * @var AuthExtended
     */
    protected $auth;

    /**
     * @var Csrf
     */
    protected $csrf;

    /**
     * @var WRouter
     */
    protected $router;

    /**
     * @var WTranslation
     */
    protected $translation;

    /**
     * It determines if login form is part of protected page or if it is on separate page.
     * @var bool
     */
    private $onPageLogin = false;

    /**
     * It determines if user will be logged permanently or not.
     * @var bool
     */
    private $permanent = false;

    public function __construct(
        AuthExtended $auth,
        Csrf $csrf,
        WRouter $router,
        WTranslation $translation
    )
    {
        $this->auth = $auth;
        $this->csrf = $csrf;
        $this->router = $router;
        $this->translation = $translation;
    }

    /**
     * Basic sign-up requires email and password. This method searches these values in $_POST.
     * and it validates format of these values and then it tries to sign up the user using
     * Auth->userSet() method. If everything is ok and sign-up doesn't require activation
     * it'll log the user in.
     *
     * @return array
     * 'err' - (bool|int) False on success, otherwise numeric err of auth->userSet().
     * 'msg' - (array) Array of messages related to the auth->userSet() result.
     * 'form['data']' - (array) Array of formatted (not sanitized) form data.
     * 'form['msg']' - (array) Array of messages related to individual form fields. May be not set.
     * 'redirectUrl' - (string|bool) Look at getRedirectUrl() for more info. May be not set.
     * 'activationUrl' - (string) Url for account activation. May be not set.
     */
    protected function signup()
    {
        // Set default values
        $resArr = [];
        $resArr['err'] = true;
        $resArr['msg'] = [];

        // Format data
        $email = isset($_POST['email']) ? mb_strtolower(trim($_POST['email'])) : '';
        $pswd = isset($_POST['pswd']) ? str_replace(' ', '', trim($_POST['pswd'])) : '';

        // Add formatted data to response
        $resArr['form']['data']['email'] = $email;
        $resArr['form']['data']['pswd'] = $pswd;

        // CSRF protection
        $csrf = $this->csrf();
        if (!$csrf) {
            // Err: Token mismatch
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.csrf-form');
        }

        if ($csrf && $_POST) {

            // Validate form data
            $validator = new Validator();

            $validator->addData('email', $email)
                ->filter('required', ['msg' => $this->translation->_t('auth.msg.entry-required')])
                ->filter('email', ['msg' => $this->translation->_t('auth.msg.entry-invalid')])
                ->filter('maxLength', ['msg' => $this->translation->_t('auth.msg.entry-long'), 'length' => 60]);

            $validator->addData('pswd', $pswd)
                ->filter('required', ['msg' => $this->translation->_t('auth.msg.entry-required')])
                ->filter('minLength', ['msg' => $this->translation->_t('auth.msg.entry-short'), 'length' => 6]);

            $err = $validator->validate();

            // Iterate form validation errors and prepare error messages
            if (isset($err['err'])) {

                $resArr['msg']['err'][] = $this->translation->_t('auth.msg.correct-red-fields');

                foreach ($err['err'] as $formFieldName => $messages) {
                    $resArr['form']['msg']['err'][$formFieldName] = $messages;
                }
            }

            // All form inputs are correct...
            if (!isset($err['err'])) {

                $userSet = $this->auth->userSet($email, $pswd, 1);

                // Process userSet() errors
                unset($resArr['err']);
                $resArr = array_merge_recursive($this->handleUserSetRes($userSet), $resArr);
            }
        }

        return $resArr;
    }

    /**
     * Basic log-in requires email and password. This method searches these values in $_POST.
     * It validates format of these values and it tries to check the user using Auth->userGet() method.
     * If everything is ok it'll log the user in.
     *
     * @param bool $onPageLogin
     * It determines if login form is part of protected page or if it is on separate page.
     *
     * @return array
     * 'err' - (bool|int) False on success, otherwise true or numeric err of auth->userGet().
     * 'msg' - (array) Array of messages related to the form result.
     * 'form['data']' - (array) Array of formatted (not sanitized) form data.
     * 'form['msg']' - (array) Array of messages related to individual form fields.
     * 'redirectUrl' - (string) Look at getRedirectUrl() for more info.
     */
    protected function login($onPageLogin = false)
    {
        // Set default values
        $resArr = [];
        $this->onPageLogin = $onPageLogin;
        $resArr['err'] = true;
        $resArr['msg'] = [];

        // Format form data
        $email = isset($_POST['email']) ? mb_strtolower(trim($_POST['email'])) : '';
        $pswd = isset($_POST['pswd']) ? str_replace(' ', '', trim($_POST['pswd'])) : '';
        $this->permanent = isset($_POST['permanent']) ? true : false;

        // Add formatted data to response
        $resArr['form']['data']['email'] = $email;
        $resArr['form']['data']['pswd'] = $pswd;
        $resArr['form']['data']['permanent'] = $this->permanent;

        // CSRF protection
        $csrf = $this->csrf();
        if (!$csrf) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.csrf-form');
        }

        if ($csrf && $_POST) {

            // Validate form data
            $validator = new Validator();

            $validator->addData('email', $email)
                ->filter('required', ['msg' => $this->translation->_t('auth.msg.entry-required')])
                ->filter('email', ['msg' => $this->translation->_t('auth.msg.entry-invalid')]);

            $validator->addData('pswd', $pswd)
                ->filter('required', ['msg' => $this->translation->_t('auth.msg.entry-required')]);

            $err = $validator->validate();

            // Iterate form validation errors and prepare error messages
            if (isset($err['err'])) {

                $resArr['msg']['err'][] = $this->translation->_t('auth.msg.correct-red-fields');

                foreach ($err['err'] as $formFieldName => $messages) {
                    $resArr['form']['msg']['err'][$formFieldName] = $messages;
                }
            }

            // All form inputs are correct...
            if (!isset($err['err'])) {

                // Try to get user from database
                $userGet = $this->auth->userGet($email, $pswd);

                // Handle userGet() response
                unset($resArr['err']);
                $resArr = array_merge_recursive($this->handleUserGetRes($userGet), $resArr);
            }
        }

        return $resArr;
    }

    /**
     * Social log-in/signup requires email and provider. This method checks the user using
     * Auth->userGet() method. If user doesn't exist it signs the user up. If everything
     * is ok it'll log the user in.
     *
     * @param $email - Email address associated with account.
     * @param $provider - Eg. 'facebook', 'google'
     * @param bool $permanent
     * @return array
     * Read handleUserSetRes() and handleUserGetRes() comment for more info.
     */
    protected function socialLogin($email, $provider, $permanent = false)
    {
        // Set default values
        $resArr = [];
        $this->permanent = $permanent;

        // Try to get user from database
        $userGet = $this->auth->userGet($email, false, $provider);

        // Process userGet() errors
        $resArr = array_merge_recursive($this->handleUserGetRes($userGet), $resArr);

        if ($userGet['err'] == 5) {

            // User does not exist, sign up the user
            $resArr = $this->socialSignup($email, $provider);
            $resArr['signup'] = true;

        }

        return $resArr;
    }

    /**
     * Password change is divided into two steps for better security. First step requires email
     * address of affected account. It validates email/account and if everything is ok it'll return
     * URL address where user can change the password.
     *
     * @return array
     * 'err' - (bool|int) False on success, otherwise numeric err of auth->userPswdUpdateStepOne().
     * 'msg' - (array) Array of messages related to the result.
     * 'form['data']' - (array) Array of formatted (not sanitized) form data.
     * 'form['msg']' - (array) Array of messages related to individual form fields.
     * 'confirmationUrl' - (string) Url for password change update/confirmation. May be not set.
     * 'email' - (string) Email associated with password change. May be not set.
     */
    protected function userPswdUpdateStepOne()
    {
        // Set default values
        $resArr = [];
        $resArr['err'] = true;
        $resArr['msg'] = [];

        // Format data
        $email = isset($_POST['email']) ? mb_strtolower(trim($_POST['email'])) : '';

        // Add formatted data to response
        $resArr['form']['data']['email'] = $email;

        // CSRF protection
        $csrf = $this->csrf();
        if (!$csrf) {
            // Err: Token mismatch
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.csrf-form');
        }

        if ($csrf && $_POST) {

            // Validate form data
            $validator = new Validator();

            $validator->addData('email', $email)
                ->filter('required', ['msg' => $this->translation->_t('auth.msg.entry-required')])
                ->filter('email', ['msg' => $this->translation->_t('auth.msg.entry-invalid')]);

            $err = $validator->validate();

            // Iterate form validation errors and prepare error messages
            if (isset($err['err'])) {

                $resArr['msg']['err'][] = $this->translation->_t('auth.msg.correct-red-fields');

                foreach ($err['err'] as $formFieldName => $messages) {
                    $resArr['form']['msg']['err'][$formFieldName] = $messages;
                }
            }

            // All inputs are correct...
            if (!isset($err['err'])) {

                $upr = $this->auth->userPswdUpdateStepOne($email);

                // Process userPswdUpdateStepOne() errors
                unset($resArr['err']);
                $resArr = array_merge_recursive($this->handlePswdUpdateStepOneRes($upr), $resArr);
            }
        }

        return $resArr;
    }

    /**
     * Password change is divided into two steps. Second step validates key and new password
     * and if everything is ok it'll update the user password.
     *
     * @return array
     * 'err' - (bool|int) False on success, otherwise numeric err of auth->userPswdUpdateStepTwo().
     * 'msg' - (array) Array of messages related to the result.
     * 'form['data']' - (array) Array of formatted (not sanitized) form data.
     * 'form['msg']' - (array) Array of messages related to individual form fields.
     * 'redirectUrl' - Url where user should be redirected after successful password change.
     */
    protected function userPswdUpdateStepTwo()
    {
        // Set default values
        $resArr = [];
        $resArr['err'] = true;

        // Format data
        $pswd = isset($_POST['pswd']) ? str_replace(' ', '', trim($_POST['pswd'])) : '';

        // Add formatted data to response
        $resArr['form']['data']['pswd'] = $pswd;

        // CSRF protection
        $csrf = $this->csrf();
        if (!$csrf) {
            // Err: Token mismatch
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.csrf-form');
        }

        if ($csrf && $_POST) {

            // Validate form data
            $validator = new Validator();

            $validator->addData('pswd', $pswd)
                ->filter('required', ['msg' => $this->translation->_t('auth.msg.entry-required')])
                ->filter('minLength', ['msg' => $this->translation->_t('auth.msg.entry-short'), 'length' => 6]);

            $err = $validator->validate();

            // Iterate form validation errors and prepare error messages
            if (isset($err['err'])) {

                $resArr['msg']['err'][] = $this->translation->_t('auth.msg.correct-red-fields');

                foreach ($err['err'] as $formFieldName => $messages) {
                    $resArr['form']['msg']['err'][$formFieldName] = $messages;
                }
            }

            // Try to get key
            if (!isset($_GET['key'])) {
                // Err: Key is not set
                $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-not-set');
                $err['err'] = true;
            }

            // Try to get selector and token from key
            if (isset($_GET['key'])) {
                $key = explode('.', $_GET['key'], 2);
                if (!isset($key[1])) {
                    // Err: Invalid key format
                    $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-invalid-format');
                    $err['err'] = true;
                }
            }

            if (!isset($err['err']) && isset($key[1])) {

                // All inputs are correct...

                $upr = $this->auth->userPswdUpdateStepTwo($key[0], $key[1], $pswd);

                // Process userPswdUpdateStepTwo() errors
                unset($resArr['err']);
                $resArr = array_merge_recursive($this->handlePswdUpdateStepTwoRes($upr), $resArr);
            }
        }

        return $resArr;
    }

    /**
     * Account activation is divided in two steps. In first step we will provide the activation
     * link to the user. Creating of activation link is possible only with valid re-activation token.
     * Re-activation token is generated during the login or signup. This method validates re-activation
     * token and if everything is ok it generates activation token and returns activation link.
     *
     * @return array
     * 'err' - (bool) False on success.
     * 'msg' - (array) Array of messages related to the result.
     * 'activationUrl' - Url for account activation.
     * 'redirectUrl' - Url where user should be redirected.
     */
    protected function activationStepOne()
    {
        // Set default values
        $resArr = [];
        $resArr['err'] = true;
        $resArr['redirectUrl'] = $this->router->getUrlFor('login');

        // Get token
        if (!isset($_GET['key'])) {
            // Err: Key is not set
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-not-set');
            return $resArr;
        }

        // Explode key
        $key = explode('.', $_GET['key'], 2);
        if (!isset($key[1])) {
            // Err: Invalid key format
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-invalid-format');
            return $resArr;
        }

        // Validate key
        $rToken = $this->auth->tokenValidate($key[0], $key[1], 'auth_tokens_re_activation');
        if ($rToken['err']) {
            // Err: Invalid re-activation key
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-invalid');
            return $resArr;
        }

        // Generate new activation token
        $token = $this->auth->tokenGenerate($rToken['uid'], 'auth_tokens_activation', $rToken['expires']);

        if ($token['err']) {
            // Err: Error during generating token
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-cant-generate');
            return $resArr;
        }

        $aKey = $token['selector'] . '.' . $token['token'];
        $resArr['activationUrl'] = $this->router->getUrlFor('activation-confirm') . '?key=' . $aKey;
        $resArr['uid'] = $rToken['uid'];
        $resArr['err'] = false;

        return $resArr;
    }

    /**
     * Account activation is divided in two steps. In second step user activate account through
     * the activation link obtained in step one. This method validates activation token and if token
     * is valid it'll tries to activate the user.
     *
     * @return array
     * 'err' - (bool|int) False on success, otherwise numeric err of auth->userActivate().
     * 'msg' - (array) Array of messages related to the result.
     * 'redirectUrl' - Url where user should be redirected.
     */
    protected function activationStepTwo()
    {
        // Set default values
        $resArr = [];
        $resArr['err'] = true;
        $resArr['redirectUrl'] = $this->router->getUrlFor('login');

        // Get token
        if (!isset($_GET['key'])) {
            // Err: Key is not set
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-not-set');
            return $resArr;
        }

        // Explode key
        $key = explode('.', $_GET['key'], 2);
        if (!isset($key[1])) {
            // Err: Invalid key format
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-invalid-format');
            return $resArr;
        }

        // Try to activate user
        $userActivateArr = $this->auth->userActivate($key[0], $key[1]);

        // Err: Too many attempts
        if ($userActivateArr['err'] == 1) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.too-many-attempts');
        }

        // Err: Invalid token
        if ($userActivateArr['err'] == 2) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-invalid');
        }

        // Err: User has already activated the account
        if ($userActivateArr['err'] == 3) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.account-is-already-activated');
        }

        // Err: Another user has already activated the account
        if ($userActivateArr['err'] == 4) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.account-activated-by-another-user');
        }

        // Err: Unable to update user status
        if ($userActivateArr['err'] == 5) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 'a', 'errNum' => $resArr['err']]);
        }

        // Err: User is banned
        if ($userActivateArr['err'] == 6) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.user-banned');
        }

        // Ok: User account has been activated
        if (!$userActivateArr['err']) {
            $resArr['msg']['ok'][] = $this->translation->_t('auth.msg.account-activated');
        }

        return $resArr;
    }

    /**
     * If user is signed up through classic signup form and then he/she tries to log in with
     * social account, he/she needs to pair this social account with already created account.
     * Pairing is divided in two steps. In first step we will provide the pairing link to
     * the user. Creating of pairing link is possible only with valid re-pairing token.
     * Re-pairing token is generated during the social login. This method validates re-pairing
     * token and if everything is ok it generates pairing token and returns pairing link.
     *
     * @return array
     * 'err' - (bool) False on success.
     * 'msg' - (array) Array of messages related to the result.
     * 'redirectUrl' - Url where user should be redirected.
     * 'provider' - Social provider.
     * 'pairingUrl' - Url for social account pairing.
     */
    protected function pairingStepOne()
    {
        // Set default values
        $resArr = [];
        $resArr['err'] = true;
        $resArr['redirectUrl'] = $this->router->getUrlFor('login');

        // Get token
        if (!isset($_GET['key'])) {
            // Err: Key is not set
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-not-set');
            return $resArr;
        }

        // Get provider
        if (!isset($_GET['provider']) || !$_GET['provider']) {
            // Err: Provider is not set
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.provider-not-set');
            return $resArr;
        }
        $provider = $_GET['provider'];
        $resArr['provider'] = $provider;

        // Explode key
        $key = explode('.', $_GET['key'], 2);
        if (!isset($key[1])) {
            // Err: Invalid key formatt
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-invalid-format');
            return $resArr;
        }

        // Validate key
        $tableName = 'auth_tokens_re_pairing_' . $provider;
        $rToken = $this->auth->tokenValidate($key[0], $key[1], $tableName);
        if ($rToken['err']) {
            // Err:...
            if ($rToken['err'] == 2) {
                // ...Unsupported provider
                $resArr['msg']['err'][] = $this->translation->_t('auth.msg.provider-unsupported');
            } else {
                // ...Invalid re-pairing key
                $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-invalid');
            }
            return $resArr;
        }

        // Generate new re-pairing token
        $tableName = 'auth_tokens_pairing_' . $provider;
        $token = $this->auth->tokenGenerate($rToken['uid'], $tableName, $rToken['expires']);

        if ($token['err']) {
            // Err - can't generate token
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-cant-generate');
            return $resArr;
        }

        $aKey = $token['selector'] . '.' . $token['token'];
        $resArr['pairingUrl'] = $this->router->getUrlFor('social-pairing-confirm') . '?key=' . $aKey . '&provider=' . $provider;
        $resArr['uid'] = $token['uid'];
        $resArr['err'] = false;

        return $resArr;
    }

    /**
     * Social account pairing is divided in two steps. In second step the user pairs account through
     * the pairing link obtained in step one. This method validates pairing token and if token
     * is valid it'll tries to pair social account with main account.
     *
     * @return array
     * 'err' - (bool|int) False on success, otherwise numeric err of auth->userPairSocialAccount().
     * 'msg' - (array) Array of messages related to the result.
     * 'redirectUrl' - Url where user should be redirected.
     */
    protected function pairingStepTwo()
    {
        // Set default values
        $resArr = [];
        $resArr['err'] = true;
        $resArr['redirectUrl'] = $this->router->getUrlFor('login');

        // Get token
        if (!isset($_GET['key'])) {
            // Err: Key is not set
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-not-set');
            return $resArr;
        }

        // Get provider
        if (!isset($_GET['provider']) || !$_GET['provider']) {
            // Err: Provider is not set
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.provider-not-set');
            return $resArr;
        }
        $provider = $_GET['provider'];

        // Explode key
        $key = explode('.', $_GET['key'], 2);
        if (!isset($key[1])) {
            // Err: Invalid key format
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-invalid-format');
            return $resArr;
        }

        // Try to activate user
        $tableName = 'auth_tokens_pairing_' . $provider;
        $userPairSocialAccount = $this->auth->userPairSocialAccount($key[0], $key[1], $provider, $tableName);

        // Err: Too many attempts
        if ($userPairSocialAccount['err'] == 1) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.too-many-attempts');
        }

        // Err: Invalid provider
        if ($userPairSocialAccount['err'] == 2) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.provider-unsupported');
        }

        // Err: Invalid token
        if ($userPairSocialAccount['err'] == 3) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-invalid');
        }

        // Err: Unable to update user status
        if ($userPairSocialAccount['err'] == 4) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 'a', 'errNum' => $resArr['err']]);
        }

        // Err: User is banned
        if ($userPairSocialAccount['err'] == 5) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.user-banned');
        }

        // Ok: User account has been activated
        if (!$userPairSocialAccount['err']) {
            // Redirect back to login page
            $provider = htmlspecialchars(ucfirst(strtolower($provider)));
            $resArr['msg']['ok'][] = $this->translation->_p('auth.msg.account-paired', ['provider' => $provider]);
        }

        return $resArr;
    }

    /**
     * Logs the user out.
     * @return array
     * 'msg' - (array) Array of messages related to the result.
     * 'redirectUrl' - Url where user should be redirected after logout.
     */
    protected function logout()
    {
        // Set default values
        $resArr = [];

        // Log out the user
        $this->auth->logout();

        $resArr['msg']['ok'][] = $this->translation->_t('auth.msg.logout');
        $resArr['redirectUrl'] = $this->router->getUrlFor('login');

        return $resArr;
    }

    /**
     * @param $onPageLogin
     * It determines if login form is part of protected page or if it is on separate page.
     *
     * @return bool|string
     * It tries to get and return referrer from $_POST['ref'] or $_GET['ref'].
     * If $onPageLogin is true and there is no referrer it will return current URL.
     * In other cases it returns false.
     */
    protected function getRedirectUrl($onPageLogin)
    {
        $redirectUrl = false;

        if ($ref = $this->auth->getReferrer()) {
            $redirectUrl = $ref;
        }

        if (!$ref && $onPageLogin) {
            $redirectUrl = $this->router->getUrlFor($this->router->routeInfo['name']);
        }

        return $redirectUrl;
    }

    /**
     * @param $email - Email address associated with account.
     * @param $provider - Eg. 'facebook', 'google'
     * @return array
     * Read handleUserSetRes() comment for more info.
     */
    private function socialSignup($email, $provider)
    {
        // Set default values
        $resArr = [];

        // Try to set user in database
        $userSet = $this->auth->userSet($email, false, 1, $provider);

        // Process userSet() errors
        $resArr = array_merge_recursive($this->handleUserSetRes($userSet), $resArr);

        return $resArr;
    }

    /**
     * Set CSRF token and if $_POST is not empty, validate token
     * @return bool
     */
    private function csrf()
    {
        $err = false;

        if ($_POST) {

            if (!isset($_POST[$this->csrf->getTokenName()])
                || !$this->csrf->validateToken($_POST[$this->csrf->getTokenName()])
            ) {
                $err = true;
            }
        }

        $this->csrf->setToken();

        return !$err;
    }

    /**
     * Handles response from auth->userSet(). If everything is ok it'll log the user in.
     *
     * @param $userSet
     * Result of auth->userSet() method.
     *
     * @return array
     * 'err' - (bool|int) False on success, otherwise numeric err of auth->userSet().
     * 'uid' - (bool|int) False on error, otherwise user id.
     * 'msg' - (array) Array of messages related to the auth->userSet() result.
     * 'form['msg']' - (array) Array of messages related to individual form fields. May be not set.
     * 'redirectUrl' - (string|bool) Look at getRedirectUrl() for more info. May be not set.
     * 'activationUrl' - (string) Url for account activation. May be not set.
     */
    private function handleUserSetRes($userSet)
    {
        $resArr = [
            'uid' => false,
        ];

        // Handle errors...

        // Err: Unexpected
        if ($userSet['err'] == 1) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 's', 'errNum' => $userSet['err']]);
        }

        // Err: To many attempts
        if ($userSet['err'] == 2) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.too-many-attempts');
        }

        // Err: User is banned
        if ($userSet['err'] == 3) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.user-banned');
        }

        // Err: Unexpected error, can't generate activation token
        if ($userSet['err'] == 4) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 's', 'errNum' => $userSet['err']]);
        }

        // Err: User already exists
        if ($userSet['err'] == 5 || $userSet['err'] == 7) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.user-already-exists');
            $resArr['form']['msg']['err']['email'][] = '';
            $resArr['form']['msg']['err']['pswd'][] = '';
        }

        // Err: User already exists under different suffix
        if ($userSet['err'] == 6 || $userSet['err'] == 8) {
            $suffix = '<a href="' . $this->router->getUrlFor('login', $userSet['suffix']) . '">' . $userSet['suffix'] . '</a>';
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.user-already-exists-suffix-set', ['suffix' => $suffix]);
            $resArr['form']['msg']['err']['email'][] = '';
            $resArr['form']['msg']['err']['pswd'][] = '';
        }

        // Err: Unexpected error, unable to store user in social database
        if ($userSet['err'] == 9) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 's', 'errNum' => $userSet['err']]);
        }

        // Err: Unexpected error, unable to store user in database
        if ($userSet['err'] == 10) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 's', 'errNum' => $userSet['err']]);
        }

        // Ok: User has been signed up
        if (!$userSet['err']) {

            // Get redirect URL
            $resArr['redirectUrl'] = $this->getRedirectUrl(false);

            if (isset($userSet['tokens'])) {

                // Sign up requires activation...

                // Prepare message with the send activation link
                $rKey = $userSet['tokens']['re-activation']['selector'];
                $rKey .= '.';
                $rKey .= $userSet['tokens']['re-activation']['token'];
                $rUrl = $this->router->getUrlFor('activation-send') . '?key=' . $rKey;
                $rMsg = $this->translation->_t('auth.msg.user-activate-1') . ' ';
                $rMsg .= '<a href="' . $rUrl . '">';
                $rMsg .= $this->translation->_t('auth.msg.user-activate-2');
                $rMsg .= '</a>';
                $resArr['msg']['inf'][] = $rMsg;

                // Prepare activation confirmation link
                $aKey = $userSet['tokens']['activation']['selector'];
                $aKey .= '.';
                $aKey .= $userSet['tokens']['activation']['token'];
                $resArr['activationUrl'] = $this->router->getUrlFor('activation-confirm') . '?key=' . $aKey;

            } else {

                // Sign up doesn't require activation...
                $this->auth->login($userSet['uid'], $this->permanent);
                $resArr['msg']['ok'][] = $this->translation->_t('auth.msg.welcome-first');
            }

            $resArr['uid'] = $userSet['uid'];
        }

        if (isset($userSet['provider'])) {
            $resArr['provider'] = $userSet['provider'];
        }

        $resArr['err'] = $userSet['err'];

        return $resArr;
    }

    /**
     * Handle response from auth->userGet(). If everything is ok it'll log the user in.
     *
     * @param $userGet
     * Result of auth->userGet() method.
     *
     * @return array
     * 'err' - (bool|int) False on success, otherwise numeric err of auth->userGet().
     * 'uid' - (bool|int) False on error, otherwise user id.
     * 'msg' - (array) Array of messages related to the auth->userGet() result.
     * 'form['msg']' - (array) Array of messages related to individual form fields. May be not set.
     * 'redirectUrl' - (string|bool) Look at getRedirectUrl() for more info. May be not set.
     */
    private function handleUserGetRes($userGet)
    {
        $resArr = [
            'uid' => false,
        ];

        // Handle errors...

        // Err: Unexpected error
        if ($userGet['err'] == 1) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 'l', 'errNum' => $userGet['err']]);
        }

        // Err: Too many login attempts
        if ($userGet['err'] == 2) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.too-many-attempts');
        }

        // Err: User is banned
        if ($userGet['err'] == 3) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.user-banned');
        }

        // Err: Can't generate token
        if ($userGet['err'] == 4) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 'l', 'errNum' => $userGet['err']]);
        }

        // Err: User does not exist
        if ($userGet['err'] == 5) {

            $rUrl = $this->router->getUrlFor('signup');
            $rMsg = $this->translation->_t('auth.msg.user-does-not-exist-1') . ' ';
            $rMsg .= '<a href="' . $rUrl . '">';
            $rMsg .= $this->translation->_t('auth.msg.user-does-not-exist-2');
            $rMsg .= '</a>';
            $resArr['msg']['err'][] = $rMsg;
            $resArr['form']['msg']['err']['email'][] = '';
            $resArr['form']['msg']['err']['pswd'][] = '';
        }

        // Err: User exist with different suffix
        if ($userGet['err'] == 6) {
            $suffix = '<a href="' . $this->router->getUrlFor('login', $userGet['suffix']) . '">' . $userGet['suffix'] . '</a>';
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.user-already-exists-suffix-get', ['suffix' => $suffix]);
            $resArr['form']['msg']['err']['email'][] = '';
            $resArr['form']['msg']['err']['pswd'][] = '';
        }

        // Err: Account is not activated
        if ($userGet['err'] == 7) {
            $rKey = $userGet['tokens']['re-activation']['selector'] . '.' . $userGet['tokens']['re-activation']['token'];
            $rUrl = $this->router->getUrlFor('activation-send') . '?key=' . $rKey;
            $rMsg = $this->translation->_t('auth.msg.user-activate-1') . ' ';
            $rMsg .= '<a href="' . $rUrl . '">';
            $rMsg .= $this->translation->_t('auth.msg.user-activate-2');
            $rMsg .= '</a>';
            $resArr['msg']['err'][] = $rMsg;
        }

        // Err: Password is not set
        if ($userGet['err'] == 8) {

            // Concatenate providers message
            $providers = '';
            $providersCount = count($userGet['providers']);
            $i = 0;
            foreach ($userGet['providers'] as $provider) {
                $i++;
                if ($providersCount > 1 && $i == $providersCount) {
                    $or = ' ' . $this->translation->_t('auth.msg.pswd-not-set-2') . ' ';
                    $providers .= $or . $provider;
                } elseif ($providersCount > 1) {
                    $providers .= $provider . ',';
                } else {
                    $providers .= $provider;
                }
            }
            $resArr['form']['msg']['err']['pswd'][] = $this->translation->_p('auth.msg.pswd-not-set-1', ['providers' => $providers]);
        }

        // Err: Social is not paired with main account
        if ($userGet['err'] == 9) {
            $rKey = $userGet['tokens']['re-pairing']['selector'];
            $rKey .= '.';
            $rKey .= $userGet['tokens']['re-pairing']['token'];
            $provider = $userGet['tokens']['re-pairing']['provider'];
            $rUrl = $this->router->getUrlFor('social-pairing-send') . '?key=' . $rKey . '&provider=' . $provider;
            $rMsg = $this->translation->_t('auth.msg.social-pair-1') . ' ';
            $rMsg .= '<a href="' . $rUrl . '">';
            $rMsg .= $this->translation->_t('auth.msg.social-pair-2');
            $rMsg .= '</a>';
            $resArr['msg']['err'][] = $rMsg;
        }

        // Err: Unexpected error, more users exist
        if ($userGet['err'] == 10) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 'l', 'errNum' => $userGet['err']]);
        }

        // Err: Incorrect password
        if ($userGet['err'] == 11 || $userGet['err'] == 12) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.pswd-incorrect');
            $resArr['form']['msg']['err']['pswd'][] = '';
        }

        // Ok: User is valid
        if (!$userGet['err']) {
            $this->auth->login($userGet['uid'], $this->permanent);
            $resArr['uid'] = $userGet['uid'];
            $resArr['msg']['ok'][] = $this->translation->_t('auth.msg.welcome-again');
            $resArr['redirectUrl'] = $this->getRedirectUrl($this->onPageLogin);
        }

        $resArr['err'] = $userGet['err'];

        return $resArr;
    }

    /**
     * Handle response from auth->userPswdUpdateStepOne()
     *
     * @param $userPswdUpdateStepOne
     * Result of auth->userPswdUpdateStepOne() method.
     *
     * @return array
     * 'err' - (bool|int) False on success, otherwise numeric err of auth->userPswdUpdateStepOne().
     * 'msg' - (array) Array of messages related to the auth->userPswdUpdateStepOne() result.
     * 'confirmationUrl' - (string) Url for password change update/confirmation. May be not set.
     * 'email' - (string) Email associated with password change.
     */
    private function handlePswdUpdateStepOneRes($userPswdUpdateStepOne)
    {
        $resArr = [];

        // Handle errors...

        // Err: To many attempts
        if ($userPswdUpdateStepOne['err'] == 1) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.too-many-attempts');
        }

        // Err: User is banned
        if ($userPswdUpdateStepOne['err'] == 2) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.user-banned');
        }

        // Err: User does not exist
        if ($userPswdUpdateStepOne['err'] == 3) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.user-does-not-exist-1');
        }

        // Err: Unexpected error, can't generate token
        if ($userPswdUpdateStepOne['err'] == 4) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 'p', 'errNum' => $userPswdUpdateStepOne['err']]);
        }

        // Ok
        if (!$userPswdUpdateStepOne['err']) {

            // Prepare activation confirmation link
            $aKey = $userPswdUpdateStepOne['tokens']['pswd-renewal']['selector'];
            $aKey .= '.';
            $aKey .= $userPswdUpdateStepOne['tokens']['pswd-renewal']['token'];
            $resArr['confirmationUrl'] = $this->router->getUrlFor('password-confirm') . '?key=' . $aKey;
            $resArr['email'] = $userPswdUpdateStepOne['email'];
            $resArr['msg']['ok'][] = $this->translation->_t('auth.msg.pswd-confirm-request');
        }

        $resArr['err'] = $userPswdUpdateStepOne['err'];

        return $resArr;
    }

    /**
     * Handle response from auth->userPswdUpdateStepTwo()
     *
     * @param $userPswdUpdateStepTwo
     * Result of auth->userPswdUpdateStepTwo() method.
     *
     * @return array
     * 'err' - (bool|int) False on success, otherwise numeric err of auth->userPswdUpdateStepTwo().
     * 'msg' - (array) Array of messages related to the auth->userPswdUpdateStepTwo() result.
     * 'redirectUrl' - Url where user should be redirected after successful password change.
     */
    private function handlePswdUpdateStepTwoRes($userPswdUpdateStepTwo)
    {
        $resArr = [];

        // Handle errors...

        // Err: To many attempts
        if ($userPswdUpdateStepTwo['err'] == 1) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.too-many-attempts');
        }

        // Err: Invalid token
        if ($userPswdUpdateStepTwo['err'] == 2) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.key-invalid');
        }

        // Err: Unexpected error, can't update the password token
        if ($userPswdUpdateStepTwo['err'] == 3) {
            $resArr['msg']['err'][] = $this->translation->_p('auth.msg.unexpected-err', ['operation' => 'p', 'errNum' => $userPswdUpdateStepTwo['err']]);
        }

        // Err: User is banned
        if ($userPswdUpdateStepTwo['err'] == 4) {
            $resArr['msg']['err'][] = $this->translation->_t('auth.msg.user-banned');
        }

        // Ok
        if (!$userPswdUpdateStepTwo['err']) {
            $this->auth->logout();
            $resArr['msg']['ok'][] = $this->translation->_t('auth.msg.pswd-changed');
            $resArr['redirectUrl'] = $this->router->getUrlFor('login');
        }

        $resArr['err'] = $userPswdUpdateStepTwo['err'];

        return $resArr;
    }
}