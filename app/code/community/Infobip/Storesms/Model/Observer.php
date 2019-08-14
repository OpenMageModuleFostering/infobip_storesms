<?php
//File:  app/code/community/Infobip/Storesms/Model/Observer.php

/**
 * @category   Infobip
 * @package    Storesms API
 * @copyright  Copyright (c) 2012 Infobip (http://infobip.pl/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Marek Jasiukiewicz <dev@jasiukiewicz.pl>
* ...
*/
class Infobip_Storesms_Model_Observer {
    
    
    
    
    /**
     * 
     * @param type $observer
     * @return type
     */
    
    public function handleStatus($observer) {
        
        $config =   Mage::getModel('storesms/config');
        if ($config->isApiEnabled()==0) return; //do nothing if api is disabled
                
        $order = $observer->getEvent()->getOrder();
        $newStatus =  $order->getData('status');
        $origStatus =  $order->getOrigData('status');
        
        
        //if status has changed run action
        if ($newStatus!=$origStatus) {
            
            $message = $config->getMessageTemplate($newStatus); //get template for new status (if active and exists)
            if (!$message)  //return if no active message template
                return;

            
            //getting last tracking number
            $trackings = Mage::getResourceModel('sales/order_shipment_track_collection')->setOrderFilter($order)->getData();
                        
            if (!empty($trackings)) {
                $last = count($trackings)-1;
                $last_tracking_number = $trackings[$last]['track_number'];
            }
            else 
                $last_tracking_number = 'no_tracking'; //if no tracking number set "no_tracking" message for {TRACKINGNUMBER} template

                        
            //getting order data to generate message template
            $messageOrderData['{NAME}'] = $order->getShippingAddress()->getData('firstname');
            $messageOrderData['{ORDERNUMBER}'] = $order->getIncrement_id();
            $messageOrderData['{ORDERSTATUS}']  = $newStatus;
            $messageOrderData['{TRACKINGNUMBER}'] = $last_tracking_number;
            $messageOrderData['{STORENAME}'] = $config->getStoreName();
            
            $message = strtr($message,$messageOrderData);
            



            $api = Mage::getModel('storesms/apiClient');

            //prepare sms content
            $msg['recipients'][]    = $order->getShippingAddress()->getData('telephone'); //or getBillingAddress
            $msg['message']         = $message;
            $msg['single_message']  = $config->isSingle(); //allow_long_sms
            $msg['sender']          = $config->getSender();//sender


            //sending sms and getting API response

            try {
                
                    $apiClient = Mage::getModel('storesms/apiClient');
                    $savedIds = Mage::getModel('storesms/storesms')->saveMessages($msg);
                    $msg['ids'] = $savedIds;

                    $response = $apiClient->sendByCurl($msg);
                    $responseVerbally = Mage::helper('storesms')->getStatusVerbally(Mage::helper('storesms/xml')->getStatusCode($response));
                   
                    if ($responseVerbally!='SEND_OK')
                        throw new Exception (Mage::helper('storesms')->__('Error sending Message:').' '.$responseVerbally);

                    //@successs add comment to order
                    $newComment = Mage::helper('storesms')->__('SMS notification sent (SMS id:').$msg['ids'][$msg['recipients'][0]].') ' ;
                    $order->addStatusToHistory($order->getStatus(),$newComment,true);
                    $this->checkCreditLimit();

                } catch (Exception $e) {
                    $newComment = Mage::helper('storesms')->__('SMS notification sending error:').' "'.$e->getMessage().'"';
                    $order->addStatusToHistory($order->getStatus(),$newComment,false);
                }


                

            
        }
        
    }
      

        /**
     * Generating alert notification if storesmsAPI account balance is low
     * 
     * @return none
     */
    
    public function checkCreditLimit() {
        
        Mage::getModel('storesms/apiClient')->checkCreditLimit();           
        
    }
    
    
    
    /**
     * Check if authorization data is ok
     * 
     * @return none
     */
    public function checkAuthorizationData() {
        
        $config =   Mage::getModel('storesms/config');
        
        if ($config->isApiEnabled()==0) return;
        
        try {
            $credits = Mage::getModel('storesms/apiClient')->getCredits();

            if ($credits=='UNKNOWN_COMMAND') {
                throw new Exception(Mage::helper('storesms')->__($config::WRONG_AUTH_DATA));
            }
            else {
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('storesms')->__('Success. Logged into Storesms API.'));
            }

        }
        catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        
    }
    
}