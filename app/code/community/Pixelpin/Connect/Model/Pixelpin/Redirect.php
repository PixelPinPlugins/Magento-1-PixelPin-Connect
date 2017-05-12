<?php
class Pixelpin_Connect_Model_Pixelpin_Redirect extends Mage_Adminhtml_Block_System_Config_Form_Field
{

	const REDIRECT_URI_ROUTE = 'connect/pixelpin/connect';

	public function __construct()
	{

	}

	public function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$this->setElement($element);

		$storeUrl     = Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$html_id      = $element->getHtmlId();
		$redirectUri  = 'connect/pixelpin/connect';

		$html = '<input style="opacity:1;" readonly id="' . $html_id . '" class="input-text admin__control-text" value="'.  $storeUrl . $redirectUri . '" onclick="this.select()" type="text">';

		return $html;
	}

}