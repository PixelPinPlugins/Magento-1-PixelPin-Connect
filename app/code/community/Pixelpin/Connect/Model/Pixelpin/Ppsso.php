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

class Pixelpin_Connect_Model_Pixelpin_Ppsso
{
	const XML_PATH_PPSSO_ENABLED = 'customer/pixelpin_connect_pixelpin/ppsso_customise';
    const XML_PATH_PPSSO_SIZE = 'customer/pixelpin_connect_pixelpin/ppsso_size';
    const XML_PATH_PPSSO_COLOUR = 'customer/pixelpin_connect_pixelpin/ppsso_colour';
    const XML_PATH_PPSSO_SHOW_TEXT = 'customer/pixelpin_connect_pixelpin/ppsso_show_text';
    const XML_PATH_PPSSO_SHOW_LOGIN_TEXT = 'customer/pixelpin_connect_pixelpin/login_button_text';
    const XML_PATH_PPSSO_SHOW_REGISTER_TEXT = 'customer/pixelpin_connect_pixelpin/register_button_text';

    protected function _getStoreConfig($xmlPath)
    {
        return Mage::getStoreConfig($xmlPath, Mage::app()->getStore()->getId());
    }
	
	public function _getButton($url, $text)
    {
		$ppssoEnabled = $this->_getStoreConfig(self::XML_PATH_PPSSO_ENABLED);
		$ppssoSize = $this->_getStoreConfig(self::XML_PATH_PPSSO_SIZE);
		$ppssoColour = $this->_getStoreConfig(self::XML_PATH_PPSSO_COLOUR);
		$ppssoTextEnabled = $this->_getStoreConfig(self::XML_PATH_PPSSO_SHOW_TEXT);
		$ppssoLoginText = $this->_getStoreConfig(self::XML_PATH_PPSSO_SHOW_LOGIN_TEXT);
        $ppssoRegisterText = $this->_getStoreConfig(self::XML_PATH_PPSSO_SHOW_REGISTER_TEXT);
        
        //class
        if(empty($ppssoEnabled)) {
            $class = 'ppsso-btn';
        } else {
			switch ($ppssoColour){
				case 0:
					$colour = '';
					break;
				case 1:
					$colour = 'ppsso-cyan';
					break;
				case 2:
					$colour = 'ppsso-pink';
					break;
				case 3:
					$colour = 'ppsso-white';
					break;
			}

			switch ($ppssoSize){
				case 0:
					$size = 'ppsso-logo-lg';
					break;
				case 1:
					$size = 'ppsso-md ppsso-logo-md';
					break;
				case 2:
					$size = 'ppsso-sm ppsso-logo-sm';
					break;
			}

            $class = 'ppsso-btn ' . $size . ' ' . $colour;
        }
        
        $connectButton = '<a class="'. $class .'" href="' . $url .'">' . $text . ' <span class="ppsso-logotype">PixelPin</span></a>';
        $checkoutButton = '<a class="'. $class .'" href="' . $url .'">Checkout Using <span class="ppsso-logotype">PixelPin</span></a>';
        $editAccountButton = '<a class="'. $class .'" href="' . $url .'">Get Your Latest Information From <span class="ppsso-logotype">PixelPin</span></a>';

        if(empty($ppssoEnabled)) {
            $loginButton = '<a class="'. $class .'" href="' . $url .'">Login With <span class="ppsso-logotype">PixelPin</span></a>';
            $registerButton = '<a class="'. $class .'" href="' . $url .'">Register Using <span class="ppsso-logotype">PixelPin</span></a>';
        } else {
            if(empty($ppssoTextEnabled)) {
                $loginButton = '<a class="'. $class .'" href="' . $url .'"></a>';
                $registerButton = '<a class="'. $class .'" href="' . $url .'"></a>';
            } else {
                $loginButton = '<a class="'. $class .'" href="' . $url .'">' . $ppssoLoginText . ' <span class="ppsso-logotype">PixelPin</span></a>';
                $registerButton = '<a class="'. $class .'" href="' . $url .'">' . $ppssoRegisterText . ' <span class="ppsso-logotype">PixelPin</span></a>';
            }
		}
		
		$button = array(
			'loginButton' => $loginButton,
            'registerButton' => $registerButton,
            'connectButton' => $connectButton,
            'checkoutButton' => $checkoutButton,
            'editAccountButton' => $editAccountButton
		);
        
        return $button;
    }
}