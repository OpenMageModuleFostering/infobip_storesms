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

    public static $lastExecutionTime; //to avoid multiple SMS if status was changed more than one time per 2 second


    public function _construct() {
        if (!self::$lastExecutionTime)
            self::$lastExecutionTime = time();
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


    public function orderStatusHistorySave($observer) {

        $config =   Mage::getModel('storesms/config');
        if ($config->isApiEnabled()==0) return; //do nothing if api is disabled

        $history = $observer->getEvent()->getStatusHistory();

        //only for new status
        if (!$history->getId()) {

            $order = $history->getOrder();
            $newStatus =  $order->getData('status');
            $origStatus =  $order->getOrigData('status');


            if (time()-self::$lastExecutionTime<=2)
                return;

            self::$lastExecutionTime = time();

            //if status has changed run action
            if ($newStatus!=$origStatus) {

                $message = $config->getMessageTemplate($newStatus); //get template for new status (if active and exists)
                if (!$message)  //return if no active message template
                return;


                //getting last tracking number
                $tracking = Mage::getResourceModel('sales/order_shipment_track_collection')->setOrderFilter($order)->getData();

                if (!empty($tracking)) {
                    $last = count($tracking)-1;
                    $last_tracking_number = $tracking[$last]['track_number'];
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

                //prepare sms content
                $msg['recipients'][]    = Mage::helper('storesms')->getPhoneNumber($order->getShippingAddress()->getData('telephone')); //or getBillingAddress
                $msg['message']         = $message;
                $msg['single_message']  = $config->isSingle(); //allow_long_sms
                $msg['sender']          = $config->getSender();//sender


                //sending sms and getting API response

                try {

                    $apiClient = Mage::getModel('storesms/apiClient');
                    $model = Mage::getModel('storesms/storesms');
                    $savedIds = $model->saveMessages($msg);
                    $msg['ids'] = $savedIds;
                    $response = $apiClient->sendByCurl($msg);
                    if ($response===false)
                    {

                        //save delievery status for each message
                        foreach ($msg['ids'] as $messageId) {
                            $model->setNewDeliveryStatus($messageId,'API CONNECTION ERROR.');
                            $model->unsetData();
                            Mage::throwException('API CONNECTION ERROR.');
                        }

                    }
                    else {

                        $responseVerbally = Mage::helper('storesms')->getStatusVerbally(Mage::helper('storesms/xml')->getStatusCode($response));
                        if ($responseVerbally!='SEND_OK')
                            Mage::throwException(Mage::helper('storesms')->__('Error sending Message:').' '.$responseVerbally);
                        //@successs add comment to order
                        $newComment = Mage::helper('storesms')->__('SMS notification sent (SMS id:').$msg['ids'][$msg['recipients'][0]].') ' ;
                        $history->setComment($newComment);
                        //Mage::getSingleton('core/session')->addSuccess($newComment);
                        $this->checkCreditLimit();
                    }


                } catch (Exception $e) {
                    $newComment = Mage::helper('storesms')->__('SMS notification sending error:').' "'.$e->getMessage().'"';
                    $history->setComment($newComment);
                    //Mage::getSingleton('core/session')->addError($newComment);
                }

            }
        }





    }
    
}