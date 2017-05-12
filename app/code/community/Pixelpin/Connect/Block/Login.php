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
* @author Marko Martinović <marko.martinovic@pixelpin.net>
* @copyright Copyright (c) Pixelpin (http://pixelpin.net/)
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*/

class Pixelpin_Connect_Block_Login extends Mage_Core_Block_Template
{

    protected $clientPixelpin = null;

    protected $numEnabled = 0;
    protected $numDescShown = 0;
    protected $numButtShown = 0;

    protected function _construct() {
        parent::_construct();

        $this->clientPixelpin = Mage::getSingleton('pixelpin_connect/pixelpin_client');

	if ( $this->clientPixelpin === null )
    {
	
    }

        if( !$this->_pixelpinEnabled()) 
            return;
		
        if($this->_pixelpinEnabled()) {
            $this->numEnabled++;
        }

        Mage::register('pixelpin_connect_button_text', $this->__('Login'));

        $this->setTemplate('pixelpin/connect/login.phtml');
    }

    protected function _getColSet()
    {
        return 'col'.$this->numEnabled.'-set';
    }

    protected function _getDescCol()
    {
        return 'col-'.++$this->numDescShown;
    }

    protected function _getButtCol()
    {
        return 'col-'.++$this->numButtShown;
    }

    protected function _pixelpinEnabled()
    {
        return $this->clientPixelpin->isEnabled();
    }
}
