<?php
class Pixelpin_Connect_Model_Pixelpin_PpssoColour
{
	/**
	 * Generates the redirect uri for the admin to view when enabling PixelPin OpenID Connect
	 * 
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	public function toOptionArray()
   {
       $colour = array(
           array('value' => '0', 'label' => 'Purple'),
           array('value' => '1', 'label' => 'Cyan'),
           array('value' => '2', 'label' => 'Pink'),
           array('value' => '3', 'label' => 'White'),
       );
       
       return $colour;
   }
}