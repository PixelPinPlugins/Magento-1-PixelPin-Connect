<?php
/**
* Pixelpin
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@magentocommerce.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Please do not edit or add to this file if you wish to upgrade
* Magento or this extension to newer versions in the future.
** Pixelpin *give their best to conform to
* "non-obtrusive, best Magento practices" style of coding.
* However,* Pixelpin *guarantee functional accuracy of
* specific extension behavior. Additionally we take no responsibility
* for any possible issue(s) resulting from extension usage.
* We reserve the full right not to provide any kind of support for our free extensions.
* Thank you for your understanding.
*
* @category Pixelpin
* @package Connect
* @original-author Marko Martinović <marko.martinovic@inchoo.net>
* @author Callum@PixelPin <callum@pixelpin.co.uk>
* @copyright Copyright (c) Pixelpin (https://www.pixelpin.co.uk/)
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*/

class Pixelpin_Connect_Model_Pixelpin_Client
{
    const REDIRECT_URI_ROUTE = 'connect/pixelpin/connect';
    const REQUEST_TOKEN_URI_ROUTE = 'connect/pixelpin/request';

    const OAUTH2_TOKEN_URI = 'https://login.pixelpin.io/connect/token';
    const OAUTH2_AUTH_URI = 'https://login.pixelpin.io/connect/authorize';
    const OAUTH2_SERVICE_URI = 'https://login.pixelpin.io/connect/';    

    const XML_PATH_ENABLED = 'customer/pixelpin_connect_pixelpin/enabled';
    const XML_PATH_CLIENT_ID = 'customer/pixelpin_connect_pixelpin/client_id';
    const XML_PATH_CLIENT_SECRET = 'customer/pixelpin_connect_pixelpin/client_secret';

    const XML_PATH_USERDATAENABLED = 'customer/pixelpin_connect_pixelpin/user_data';
    const XML_PATH_USERDATAUPDATEENABLED = 'customer/pixelpin_connect_pixelpin/user_data_update';

	/**
	 *
	 * @var string
	 */
    protected $clientId = null;
	
	/**
	 *
	 * @var string
	 */
    protected $clientSecret = null;
	
	/**
	 *
	 * @var string
	 */
    protected $redirectUri = null;
	
	/**
	 *
	 * @var string
	 */
    protected $state = '';
	
	/**
	 *
	 * @var json 
	 */
    protected $token = null;

	/**
	 * Constructor. Set variables.
	 */
    public function __construct()
     {
        if(($this->isEnabled = $this->_isEnabled())) {
            $this->isManualUserDataEnabled = $this->_isManualUserDataEnabled();
            $this->isAutoUserDataEnabled = $this->_isAutoUserDataEnabled();
            $this->clientId = $this->_getClientId();
            $this->clientSecret = $this->_getClientSecret();
            $this->redirectUri = Mage::getModel('core/url')->sessionUrlVar(
                Mage::getUrl(self::REDIRECT_URI_ROUTE)
            ); 
         }
            if(!empty($params['state'])) {
                $this->state = $params['state'];
            }
    }
	
	/**
	 * Checks if the admin has enabled PixelPin as a SSO option.
	 * 
	 * @return bool
	 */
    public function isEnabled()
    {
        return (bool) $this->isEnabled;
    }
	
	/**
	 * Checks if the admin has enabled users to manually update their user information.
	 * 
	 * @return bool
	 */
    public function isManualUserDataEnabled()
    {
        return (bool) $this->isManualUserDataEnabled;
    }
	
	/**
	 * Checks if the admin has enabled the functionality for the user's data to be updated whenever they sign in.
	 * 
	 * @return bool
	 */
    public function  isAutoUserDataEnabled()
    {
        return (bool) $this->isAutoUserDataEnabled;
    }

	/**
	 * Gets the client ID.
	 * 
	 * @return string
	 */
    public function getClientId()
    {
        return $this->clientId;
    }
	
	/**
	 * Gets the Client Secret.
	 * 
	 * @return string
	 */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

	/**
	 * Gets the Redirect URI.
	 * 
	 * @return string
	 */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }
	
	/**
	 * Gets the state value.
	 * 
	 * @return string
	 */
    public function getState()
    {
        return $this->state;
    }
	
	/**
	 * Sets the state value.
	 * 
	 * @param type $state
	 */
    public function setState($state)
    {
        $this->state = $state;
    }

	/**
	 * Sets the access token from the token json. 
	 * 
	 * @param type $token
	 */
    public function setAccessToken($token)
    {
       $this->token = json_decode($token);
    }
	
	/**
	 * Gets the Access Token by calling fetchAccessToken(). 
	 * 
	 * @return json
	 */
     public function getAccessToken()
    {
        if(empty($this->token)) {
            $this->fetchAccessToken();
        }

        return json_encode($this->token);
    }

	/**
	 * Creates the AuthUrl according to http://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
	 * 
	 * @return string
	 */
    public function createAuthUrl()
    {
        $url = self::OAUTH2_AUTH_URI.'?'.
            http_build_query(
                array(
                    'scope' => 'openid profile email phone address',
                    'response_type' => 'code',
                    'redirect_uri' => $this->redirectUri,
                    'client_id' => $this->clientId,
                    'state' => $this->state,
                     )
            );
        return $url;
    }

	/**
	 * UserInfo Request according to http://openid.net/specs/openid-connect-core-1_0.html#UserInfoRequest
	 * 
	 * @param type $endpoint
	 * @param type $method
	 * @param type $params
	 * @return type
	 */
    public function api($endpoint, $method = 'GET', $params = array())
    {
        if(empty($this->token)) {
            $this->fetchAccessToken();
       }

        $url = self::OAUTH2_SERVICE_URI.$endpoint;

        $method = strtoupper($method);

        $httpHeader = array();
        $httpHeader['Authorization'] = 'Bearer '.$this->token->access_token;

        $params = array_merge(array(
            'access_token' => $this->token->access_token
        ), $params);

        $response = $this->_httpRequest($url, $method, $params, $httpHeader);

        return $response;
    }
	
	/**
	 * Access Token Request according to http://openid.net/specs/openid-connect-core-1_0.html#TokenRequest
	 * 
	 * @throws Exception
	 */
    protected function fetchAccessToken()
    {
        $code = Mage::app()->getRequest()->getParam('code');
        if(empty($code)) {
            throw new Exception(
                Mage::helper('pixelpin_connect')
                    ->__('Unable to retrieve access code.')
            );
        }

        $response = $this->_httpRequest(
            self::OAUTH2_TOKEN_URI,
            'POST',
            array(
                'code' => Mage::app()->getRequest()->getParam('code'),
                'redirect_uri' => $this->redirectUri,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'authorization_code'
            )
        ); 

        $response->created = time();

        $this->token = $response;
    }
	
	/**
	 * Http Request function.
	 * 
	 * @param type $url
	 * @param type $method
	 * @param type $params
	 * @param type $httpHeader
	 * @return type
	 * @throws Exception
	 * @throws Pixelpin_Connect_PixelpinOAuthException
	 */
    protected function _httpRequest($url, $method = 'GET', $params = array(), $httpHeader = array())
    {
        $client = new Zend_Http_Client($url, array('timeout' => 60));

        switch ($method) {
            case 'GET':
                $client->setParameterGet($params);
                $client->setHeaders($httpHeader);
                break;
            case 'POST':
                $client->setParameterPost($params);
                $client->setHeaders($httpHeader);
                break;
            case 'DELETE':
                break;
            default:
                throw new Exception(
                    Mage::helper('pixelpin_connect')
                        ->__('Required HTTP method is not supported.')
                );
        }
        $response = $client->request($method);
        $decoded_response = json_decode($response->getBody());

        if($response->isError()) {
            $status = $response->getStatus();
            if(($status == 400 || $status == 401 || $status == 429)) {
                if(isset($decoded_response->error->message)) {
                    $message = $decoded_response->error->message;
                } else {
                    $message = Mage::helper('pixelpin_connect')
                        ->__('Unspecified OAuth error occurred.');
                }

                throw new Exception($message);
            } else {
                $message = sprintf(
                    Mage::helper('pixelpin_connect')
                        ->__('HTTP error %d occurred while issuing request.'),
                    $status
                );

                throw new Exception($message);
            }
        }

        return $decoded_response;
    }
	
	/**
	 * gets the 'client' value from the store config.
	 * 
	 * @return string
	 */
    protected function _isEnabled()
    {
        return $this->_getStoreConfig(self::XML_PATH_ENABLED);
    }
	
	/**
	 * gets the 'manualuserdata' value from the store config.
	 * 
	 * @return string
	 */
    protected function _isManualUserDataEnabled()
    {
        return $this->_getStoreConfig(self::XML_PATH_USERDATAENABLED);
    }
	
	/**
	 * gets the 'autouserdata' value from the store config.
	 * 
	 * @return string
	 */
    protected function _isAutoUserDataEnabled()
    {
        return $this->_getStoreConfig(self::XML_PATH_USERDATAUPDATEENABLED);
    }
	
	/**
	 * gets the Client ID from the store config.
	 * 
	 * @return string
	 */
    protected function _getClientId()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_ID);
    }

	/**
	 * Gets the Client Secret from the store config.
	 * 
	 * @return string
	 */
    protected function _getClientSecret()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_SECRET);
    }
	
	/**
	 * Gets the store config. 
	 * 
	 * @param type $xmlPath
	 * @return type
	 */
    protected function _getStoreConfig($xmlPath)
    {
        return Mage::getStoreConfig($xmlPath, Mage::app()->getStore()->getId());
    }

}
