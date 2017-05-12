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

class Pixelpin_Connect_Helper_Pixelpin extends Mage_Core_Helper_Abstract
{

    public function disconnect(Mage_Customer_Model_Customer $customer) {
        Mage::getSingleton('customer/session')->unsPixelpinSocialconnectPixelpinUserinfo();
        
        $pictureFilename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                .DS
                .'pixelpin'
                .DS
                .'connect'
                .DS
                .'pixelpin'
                .DS                
                .$customer->getPixelpinSocialconnectPPid();
        
        if(file_exists($pictureFilename)) {
            @unlink($pictureFilename);
        }        
        
        $customer->setPixelpinSocialconnectPPid(null)
        ->setPixelpinSocialconnectPPtoken(null)
        ->save();   
    }
    
    public function connectByPixelpinId(
            Mage_Customer_Model_Customer $customer,
            $pixelpinId,
            $token)
    {
        $customer->setPixelpinSocialconnectPPid($pixelpinId)
                ->setPixelpinSocialconnectPPtoken($token)
                ->save();
        
        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
    }
    
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
            'country_id'        => $decodedAddress->country,
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
            ->setGender(      $_customer['gender'])
            ->setPixelpinSocialconnectPPid($pixelpinId)
            ->setPixelpinSocialconnectPPtoken($token)
            ->setPassword($customer->generatePassword(10))
            ->save();

        $customer->setConfirmation(null);
        $customer->save();

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


        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);            
    }
    
    public function loginByCustomer(Mage_Customer_Model_Customer $customer)
    {
        if($customer->getConfirmation()) {
            $customer->setConfirmation(null);
            $customer->save();
        }

        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);        
    }
    
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

    public function getProperDimensionsPictureUrl($pixelpinId, $pictureUrl)
    {
        $pictureUrl = str_replace('_normal', '', $pictureUrl);
        
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                .'pixelpin'
                .'/'
                .'connect'
                .'/'
                .'pixelpin'
                .'/'                
                .$pixelpinId;

        $filename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                .DS
                .'pixelpin'
                .DS
                .'connect'
                .DS
                .'pixelpin'
                .DS                
                .$pixelpinId;

        $directory = dirname($filename);

        if (!file_exists($directory) || !is_dir($directory)) {
            if (!@mkdir($directory, 0777, true))
                return null;
        }

        if(!file_exists($filename) || 
                (file_exists($filename) && (time() - filemtime($filename) >= 3600))){
            $client = new Zend_Http_Client($pictureUrl);
            $client->setStream();
            $response = $client->request('GET');
            stream_copy_to_stream($response->getStream(), fopen($filename, 'w'));

            $imageObj = new Varien_Image($filename);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepFrame(false);
            $imageObj->resize(150, 150);
            $imageObj->save($filename);
        }
        
        return $url;
    }
    
}