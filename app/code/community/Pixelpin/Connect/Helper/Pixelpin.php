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

class Pixelpin_Connect_Helper_Pixelpin extends Mage_Core_Helper_Abstract
{
	/**
	 * When called, it will disconnect(sign/log out) the user from the Magento website.
	 * 
	 * @param Mage_Customer_Model_Customer $customer
	 */
    public function disconnect(Mage_Customer_Model_Customer $customer) {
        Mage::getSingleton('customer/session')->unsPixelpinConnectPixelpinUserinfo();
        
        $pictureFilename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                .DS
                .'pixelpin'
                .DS
                .'connect'
                .DS
                .'pixelpin'
                .DS                
                .$customer->getPixelpinConnecttPPid();
        
        if(file_exists($pictureFilename)) {
            @unlink($pictureFilename);
        }        
        
        $customer->setPixelpinConnectPPid(null)
        ->setPixelpinConnectPPtoken(null)
        ->save();   
    }
    
	/**
	 * When called, it will connect the existing user by their sub id
	 * 
	 * @param Mage_Customer_Model_Customer $customer
	 * @param type $pixelpinId
	 * @param type $token
	 */
    public function connectByPixelpinId(
            Mage_Customer_Model_Customer $customer,
            $pixelpinId,
            $token)
    {
        $customer->setPixelpinConnectPPid($pixelpinId)
                ->setPixelpinConnectPPtoken($token)
                ->save();
        
        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
    }

	/**
	 * When called, it will update the existing user's information by their customer id.
	 * 
	 * @param type $pixelpinId
	 * @param type $token
	 * @param type $email
	 * @param type $given_name
	 * @param type $family_name
	 * @param type $gender
	 * @param type $birthdate
	 */
    public function updateUserInfo(
            $pixelpinId,
            $token,
            $email,
            $given_name,
            $family_name,
            $gender,
            $birthdate)
    {

        $_customer = array (
            'firstname'         => $given_name,
            'lastname'          => $family_name,
            'email'             => $email,
            'birth_date'        => $birthdate,
            'gender'            => $gender,
        );

        $websiteId = Mage::app()->getWebsite()->getId();
        $store     = Mage::app()->getStore();

        $customer = Mage::getModel('customer/customer');

        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($_customer['email']);

        if($customer->getId()){
            $customer->setWebsiteId($websiteId)
            ->setStore($store)
            ->setFirstname(   $_customer['firstname'])
            ->setLastname(    $_customer['lastname'])
            ->setEmail(       $_customer['email'])
            ->setDob(         $_customer['birth_date'])
            ->setGender(      
                    Mage::getResourceModel('customer/customer')
                    ->getAttribute('gender')
                    ->getSource()
                    ->getOptionId($_customer['gender'])
            )
            ->save();
        }

        $customer->setConfirmation(null);
        $customer->save();
    }

	/**
	 * When called, it will update the existing user's information when they sign in. 
	 * 
	 * @param type $email
	 * @param type $given_name
	 * @param type $family_name
	 * @param type $gender
	 * @param type $birthdate
	 * @param type $pixelpinId
	 * @param type $token
	 */
    public function updateUserInfoOnSignIn(
            $email,
            $given_name,
            $family_name,
            $gender,
            $birthdate,
            $pixelpinId,
            $token
        )
    {
        $jsonAddress = $address;

        $decodedAddress = json_decode($jsonAddress);
        
        $countryID = strtoupper($decodedAddress->country);

        $_customer = array (
            'firstname'         => $given_name,
            'lastname'          => $family_name,
            'email'             => $email,
            'birth_date'        => $birthdate,
            'gender'            => $gender,
            // billing address
            'street'            => $decodedAddress->street_address,
            'street_address'    => '',
            'address_info'      => '',
            'city'              => $decodedAddress->locality,
            'postcode'          => $decodedAddress->postal_code,
            'country_id'        => $countryID,
            'region'            => $decodedAddress->region,
            'telephone'         => $phone_number,
            'fax'               => '',
        );

        $websiteId = Mage::app()->getWebsite()->getId();
        $store     = Mage::app()->getStore();

        $customer = Mage::getModel('customer/customer');

        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($_customer['email']);

        if($customer->getId()){
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname(   $_customer['firstname'])
                ->setLastname(    $_customer['lastname'])
                ->setEmail(       $_customer['email'])
                ->setDob(         $_customer['birth_date'])
                ->setGender(      
                    Mage::getResourceModel('customer/customer')
                        ->getAttribute('gender')
                        ->getSource()
                        ->getOptionId($_customer['gender'])
                )
                ->setPixelpinConnectPPid($pixelpinId)
                ->setPixelpinConnectPPtoken($token)
                ->setPassword($customer->generatePassword(10))
                ->save();

            $customer->setConfirmation(null);
            $customer->save();
        }


        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);        
    }
    
	/**
	 * When called, it will create a new user account and sign them in. 
	 * 
	 * @param type $email
	 * @param type $given_name
	 * @param type $family_name
	 * @param type $gender
	 * @param type $birthdate
	 * @param type $phone_number
	 * @param type $address
	 * @param type $pixelpinId
	 * @param type $token
	 */
    public function connectByCreatingAccount(
            $email,
            $given_name,
            $family_name,
            $gender,
            $birthdate,
            $phone_number,
            $address,
            $pixelpinId,
            $token)
    {
        $jsonAddress = $address;

        $decodedAddress = json_decode($jsonAddress);
		
		$countryID = strtoupper($decodedAddress->country);

        $_customer = array (
            'firstname'         => $given_name,
            'lastname'          => $family_name,
            'email'             => $email,
            'birth_date'        => $birthdate,
            'gender'            => $gender,
            // billing address
            'street'            => $decodedAddress->street_address,
            'street_address'    => '',
            'address_info'      => '',
            'city'              => $decodedAddress->locality,
            'postcode'          => $decodedAddress->postal_code,
            'country_id'        => $countryID,
            'region'            => $decodedAddress->region,
            'telephone'         => $phone_number,
            'fax'               => '',
        );

        $websiteId = Mage::app()->getWebsite()->getId();
        $store     = Mage::app()->getStore();

        $customer = Mage::getModel('customer/customer');

        $customer->setWebsiteId($websiteId)
            ->setStore($store)
            ->setFirstname(   $_customer['firstname'])
            ->setLastname(    $_customer['lastname'])
            ->setEmail(       $_customer['email'])
            ->setDob(         $_customer['birth_date'])
            ->setGender(      
					Mage::getResourceModel('customer/customer')
					->getAttribute('gender')
					->getSource()
					->getOptionId($_customer['gender'])
			)
            ->setPixelpinConnectPPid($pixelpinId)
            ->setPixelpinConnectPPtoken($token)
            ->setPassword($customer->generatePassword(10))
            ->save();

        $customer->setConfirmation(null);
        $customer->save();

		if(!empty($decodedAddress->street_address)) {
            $customAddress   = Mage::getModel('customer/address');
			$customAddress->setCustomerId($customer->getId())
						  ->setFirstname($customer->getFirstname())
						  ->setLastname($customer->getLastname())
						  ->setCountryId($_customer['country_id'])
						  ->setStreet($_customer['street'])
						  ->setPostcode($_customer['postcode'])
						  ->setCity($_customer['city'])
						  ->setRegion($_customer['region'])
						  ->setTelephone($_customer['telephone'])
						  ->setIsDefaultBilling('1')
						  ->setIsDefaultShipping('1')
						  ->setSaveInAddressBook('1');
			$customAddress->save(); 
        }
        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);            
    }
    
	/**
	 * When called, it will sign the user in by their customer ID.
	 * 
	 * @param Mage_Customer_Model_Customer $customer
	 */
    public function loginByCustomer(Mage_Customer_Model_Customer $customer)
    {
        if($customer->getConfirmation()) {
            $customer->setConfirmation(null);
            $customer->save();
        }

        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);        
    }
    
	/**
	 * When called, it will get the user by their sub ID.
	 * 
	 * @param type $pixelpinId
	 * @return type
	 */
    public function getCustomersByPixelpinId($pixelpinId)
    {
        $customer = Mage::getModel('customer/customer');

        $collection = $customer->getCollection()
            ->addAttributeToFilter('pixelpin_connect_ppid', $pixelpinId)
            ->setPageSize(1);

        if($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                Mage::app()->getWebsite()->getId()
            );
        }

        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id',
                array('neq' => Mage::getSingleton('customer/session')->getCustomerId())
            );
        }

        return $collection;
    }
    
	/**
	 * When called, it will get the user by their email.
	 * 
	 * @param type $email
	 * @return type
	 */
    public function getCustomersByEmail($email)
    {
        $customer = Mage::getModel('customer/customer');

        $collection = $customer->getCollection()
                ->addFieldToFilter('email', $email)
                ->setPageSize(1);

        if($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                Mage::app()->getWebsite()->getId()
            );
        }  
        
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id',
                array('neq' => Mage::getSingleton('customer/session')->getCustomerId())
            );
        }        
        
        return $collection;
    }
}