<?php

class Infobip_Storesms_Model_Storesms extends Mage_Core_Model_Abstract {
    

    public function _construct() {
        
        $this->_init("storesms/storesms");        
    }
  
    /** Gets list of Partners or Distributors
     * 
     * @param type $ofWhat
     * @return type
     */
    public function getFailedSmses() {
        
        $collection = $this->getCollection();
        $collection->addFieldToFilter("group_id"   ,array("eq"=>$ofWhat));
        $items = $collection->getItems();
        
        foreach ($items as $item) {
            $listToReturn[] = $item->getData();
        }
        
        return $listToReturn;    
        
    }
    
    
    public function saveMessages(array $smsData) {
        
         foreach ($smsData['recipients'] as $gsmNumber) { //save sms to database and retrieve message id's    

            $this->newSms($gsmNumber,$smsData['message'],'WAITING_FOR_DR',Mage::getModel('storesms/config')->getSender());
            $smsId = $this->getData('id');
            $smsIds[$gsmNumber] = $smsId;
            $this->unsetData();

        }
        
        return $smsIds;
    }
    
    
    public function newSms($telephone,$message,$deliveryStatus,$sender = false) {
        
        $this -> setSender($sender);
        $this -> setTelephone($telephone);
        $this -> setDeliveryStatus($deliveryStatus);
        $this -> setMessage($message);
        $this -> setAttemptsNumber(1);
        $this -> setCreated(new Zend_Db_Expr('CURDATE()'));
        $this -> save();    

    }
    
    public function setNewDeliveryStatus($smsId,$deliveryStatus) {
        
        $this -> load($smsId);
        $this -> setDeliveryStatus($deliveryStatus)->save();

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
                $savedIds = Mage::getModel('storesms/storesms')->saveMessages($message);
                $message['ids'] = $savedIds;
                
                $response = $apiClient->sendByCurl($message);
                $responseVerbally = Mage::helper('storesms')->getStatusVerbally(Mage::helper('storesms/xml')->getStatusCode($response));
                $errSendMessage = Mage::helper('storesms')->__('Error sending Message:');
                if ($responseVerbally!='SEND_OK') 
                    throw new Exception ($errSendMessage.' '.$responseVerbally);
                
                //if all is ok get delievery report from api
                $apiClient->saveDelieveryReport();                        
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('storesms')->__('Message sent successfully'));
                
            }
            catch (Exception $e) {

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            }

        
    }
    
    

}