<?php

class Infobip_Storesms_Model_Storesms extends Mage_Core_Model_Abstract {
    

    public function _construct() {
        
        $this->_init("storesms/storesms");        
    }
  

    
    /**
     * Get all phone numbers from customer address collection
     * 
     * 
     * @return type
     */
    
    public function getPhoneNumbers() {

        $col = Mage::getModel('customer/address')->getCollection()->addAttributeToSelect('telephone')->getItems();
        foreach ($col as $address) {
            $phones[] = Mage::helper('storesms')->getPhoneNumber($address->getTelephone());
        }
        $phones = array_unique($phones);
        
        return $phones;

    }
    
    
    public function sendBulkSMS($messageContent) {
        
        $numbers = $this->getPhoneNumbers();
        
        $message = array(
            'recipients'    => $numbers,
            'message'       => $messageContent
        );
        
        
        try {
                $apiClient = Mage::getModel('storesms/apiClient');
                
                $response = $apiClient->sendByCurl($message);
                $httpStatusCode = $response["httpStatusCode"];

                $responseVerbally = Mage::helper('storesms')->getStatusVerbally($httpStatusCode);
                $errSendMessage = Mage::helper('storesms')->__('Error sending Message:');
                if ($responseVerbally!='OK')
                    throw new Exception ($errSendMessage.' '.$responseVerbally);

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('storesms')->__('Message sent successfully'));
                
            }
            catch (Exception $e) {

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            }

        
    }
    
    

}