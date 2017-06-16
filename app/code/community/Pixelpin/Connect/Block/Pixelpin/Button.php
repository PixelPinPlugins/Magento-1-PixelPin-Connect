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

class Pixelpin_Connect_Block_Pixelpin_Button extends Mage_Core_Block_Template
{
	/**
	 * /Model/Pixelpin/Client.php
	 * 
	 * @var $client 
	 */
    protected $client = null;
	
	/**
	 * /Model/Pixelpin/Userinfo.php
	 * 
	 * @var $userInfo 
	 */
    protected $userInfo = null;
	
	/**
	 * Constructor. Set variables and template.
	 * 
	 * @return bool 
	 */
    protected function _construct() {
        parent::_construct();

        $this->client = Mage::getSingleton('pixelpin_connect/pixelpin_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = Mage::registry('pixelpin_connect_pixelpin_userinfo');

        // CSRF protection
        Mage::getSingleton('core/session')->setPixelpinCsrf($csrf = md5(uniqid(rand(), TRUE)));
        $this->client->setState($csrf);

        if(!($redirect = Mage::getSingleton('customer/session')->getBeforeAuthUrl())) {
            $redirect = Mage::helper('core/url')->getCurrentUrl();      
        }

        // Redirect uri
        Mage::getSingleton('core/session')->setPixelpinRedirect($redirect);

        $this->setTemplate('pixelpin/connect/pixelpin/button.phtml');
    }
	
	/**
	 * Gets the href for the pixelpin sso button.
	 * 
	 * Used in the setTemplate. 
	 * 
	 * @return string.
	 */
    protected function _getButtonUrl()
    {
        if(empty($this->userInfo)) {
            return $this->client->createAuthUrl();
        } else {
            return $this->getUrl('connect/pixelpin/disconnect');
        }
    }
	
	/**
	 * Gets the text for the pixelpin sso button.
	 * 
	 * Used in the setTemplate. 
	 * 
	 * @return string.
	 */
    protected function _getButtonText()
    {
        if(empty($this->userInfo)) {
            if(!($text = Mage::registry('pixelpin_connect_button_text'))){
                $text = $this->__('Connect to PixelPin');
            }
        } else {
            $text = $this->__('Disconnect from PixelPin');
        }
        
        return $text;
    }

}
