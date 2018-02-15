<?php
class Pixelpin_Connect_Model_Pixelpin_PpssoSize
{
	/**
	 * Generates the redirect uri for the admin to view when enabling PixelPin OpenID Connect
	 * 
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	public function toOptionArray()
   {
       $size = array(
           array('value' => '0', 'label' => 'Large'),
           array('value' => '1', 'label' => 'Medium'),
           array('value' => '2', 'label' => 'Small'),
       );

       return $size;
   }
}