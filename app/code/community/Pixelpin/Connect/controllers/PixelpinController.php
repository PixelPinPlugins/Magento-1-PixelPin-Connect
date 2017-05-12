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

class Pixelpin_Connect_PixelpinController extends Mage_Core_Controller_Front_Action
{
    protected $referer = null;

    public function connectAction()
    {
        try {
            $this->_connectCallback();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        if(!empty($this->referer)) {
            $this->_redirectUrl($this->referer);
        } else {
            Mage::helper('pixelpin_connect')->redirect404($this);
        }
    }

    public function disconnectAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        try {
            $this->_disconnectCallback($customer);
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        if(!empty($this->referer)) {
            $this->_redirectUrl($this->referer);
        } else {
            Mage::helper('pixelpin_connect')->redirect404($this);
        }
    }

    protected function _disconnectCallback(Mage_Customer_Model_Customer $customer) {
        $this->referer = Mage::getUrl('connect/account/pixelpin');        
        
        Mage::helper('pixelpin_connect/pixelpin')->disconnect($customer);
        
        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your Pixelpin account from our store account.')
            );
    }

    protected function _connectCallback() {
        $errorCode = $this->getRequest()->getParam('error');
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');
        if(!($errorCode || $code) && !$state) {
            return;
        }
        
        $this->referer = Mage::getSingleton('core/session')->getPixelpinRedirect();

        if(!$state) {
            return;
        }

        if($state != Mage::getSingleton('core/session')->getPixelpinCsrf()) {
            return;
        }


        if($errorCode) {
            // Pixelpin API red light - abort
            if($errorCode === 'access_denied') {
                Mage::getSingleton('core/session')
                    ->addNotice(
                        $this->__('Pixelpin Connect process aborted.')
                    );
                return;
            }

            throw new Exception(
                sprintf(
                    $this->__('Sorry, "%s" error occured. Please try again.'),
                    $errorCode
                )
            );


            return;
        }

        if ($code) {
            // Pixelpin API green light - proceed
            $client = Mage::getSingleton('pixelpin_connect/pixelpin_client');

            $userInfo = $client->api('userinfo');

            $token = $client->getAccessToken();


            $customersByPixelpinId = Mage::helper('pixelpin_connect/pixelpin')
                ->getCustomersByPixelpinId($userInfo->sub);


            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                // Logged in user
                if($customersByPixelpinId->count()) {
                    // Pixelpin account already connected to other account - deny
                    Mage::getSingleton('core/session')
                        ->addNotice(
                            $this->__('Your Pixelpin account is already connected to one of our store accounts.')
                        );


                    return;
                }

                // Connect from account dashboard - attach
                $customer = Mage::getSingleton('customer/session')->getCustomer();

                Mage::helper('pixelpin_connect/pixelpin')->connectByPixelpinId(
                    $customer,
                    $userInfo->sub,
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Your Pixelpin account is now connected to your store account. You can now login using our Pixelpin Connect button or using store account credentials you will receive to your email address.')
                );


                return;
            }

            if($customersByPixelpinId->count()) {
                // Existing connected user - login
                $customer = $customersByPixelpinId->getFirstItem();

                Mage::helper('pixelpin_connect/pixelpin')->loginByCustomer($customer);

                Mage::getSingleton('core/session')
                    ->addSuccess(
                        $this->__('You have successfully logged in using your Pixelpin account.')
                    );


                return;
            }

            if(empty($userInfo->email)) {
                throw new Exception(
                    $this->__('Sorry, we require your email to register you. We could not retrieve your email from PixelPin. Please try again.')
                );
            }


            $customersByEmail = Mage::helper('pixelpin_connect/pixelpin')
                ->getCustomersByEmail($userInfo->email);

            if($customersByEmail->count())  {
                // Email account already exists - attach, login
                $customer = $customersByEmail->getFirstItem();
                
                Mage::helper('pixelpin_connect/pixelpin')->connectByPixelpinId(
                    $customer,
                    $userInfo->sub,
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('We have discovered you already have an account at our store. Your Pixelpin account is now connected to your store account.')
                );
                return;
            }

            // New connection - create, attach, login

            if(empty($userInfo->given_name)) {
                throw new Exception(
                    $this->__('Sorry, we require your first name to register you. We could not retrieve your first name from PixelPin. Please try again.')
                );
            }

            if(empty($userInfo->family_name)) {
                throw new Exception(
                    $this->__('Sorry, we require your last name to register you. We could not retrieve your last name from PixelPin. Please try again.')
                );
            }

            if(empty($userInfo->gender)) {
                $userInfo->gender = '';
            }

            if(empty($userInfo->birthdate)) {
                $userInfo->birthdate = '';
            }

            if(empty($userInfo->phone_number)) {
                $userInfo->phone_number = '';
            }

            if(empty($userInfo->address)) {
                $address = array(
                        "street_address" => "",
                        "locality" => "",
                        "postal_code" => "",
                        "country" => "",
                        "region" => "",
                        "street_address" => " ",
                        "locality" => " ",
                        "postal_code" => " ",
                        "country" => " ",
                        "region" => " ",
                    );

                $jsonAddress = json_encode($address);

                $userInfo->address = $jsonAddress;

                Mage::getSingleton('core/session')->addNotice(
                    $this->__('We\'ve noticed that you have no address set. We recommend adding a new address into your address book before proceeding.')
                );
            }

            
            Mage::helper('pixelpin_connect/pixelpin')->connectByCreatingAccount(
                $userInfo->email,
                $userInfo->given_name,
                $userInfo->family_name,
                $userInfo->gender,
                $userInfo->birthdate,
                $userInfo->phone_number,
                $userInfo->address,
                $userInfo->sub,
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your Pixelpin account is now connected to your new user account at our store. Now you can login using our Pixelpin Connect button or using store account credentials you will receive to your email address.')
            );
        }
    }

}
