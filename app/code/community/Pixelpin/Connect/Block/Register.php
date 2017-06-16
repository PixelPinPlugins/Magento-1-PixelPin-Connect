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

class Pixelpin_Connect_Block_Register extends Mage_Core_Block_Template
{
	/**
	 * /Model/Pixelpin/Client.php
	 * 
	 * @var $client 
	 */
	protected $client = null;
    
	/**
	 *
	 * @var int 
	 */
    protected $numEnabled = 0;
	
	/**
	 *
	 * @var int 
	 */
    protected $numShown = 0;
	
	/**
	 * Constructor. Set variables and template.
	 * 
	 * @return bool
	 */
    protected function _construct() {
        parent::_construct();

		$this->$client = Mage::getSingleton('pixelpin_connect/pixelpin_client');

        if( !$this->_pixelpinEnabled())
            return;
		
        if($this->_pixelpinEnabled()) {
            $this->numEnabled++;
        }

        Mage::register('pixelpin_connect_button_text', $this->__('Register Using PixelPin'));

        $this->setTemplate('pixelpin/connect/register.phtml');
    }
	
	/**
	 * Sets the col-set number
	 * 
	 * Used in the setTemplate. 
	 * 
	 * @return string
	 */
    protected function _getColSet()
    {
        return 'col'.$this->numEnabled.'-set';
    }
	
	/**
	 * Sets the col number
	 * 
	 * Used in the setTemplate. 
	 * 
	 * @return string
	 */
    protected function _getCol()
    {
        return 'col-'.++$this->numShown;
    }
	
	/**
	 * Checks if the client is enabled
	 * 
	 * Used in the setTemplate
	 * 
	 * @return bool
	 */
	protected function _pixelpinEnabled()
    {
       return (bool) $this->$client->isEnabled();
    }

}