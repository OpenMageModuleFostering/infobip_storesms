<?php

//File:  app/code/community/Infobip/Storesms/Model/ApiClient.php

/**
 * SMS API client class
 * 
 * @category   Infobip
 * @package    StoresmsApi
 * @copyright  Copyright (c) 2012 Infobip (http://infobip.pl/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Marek Jasiukiewicz <dev@jasiukiewicz.pl>
 * ...
 */
class Infobip_Storesms_Model_ApiClient {

  
    public function sendByCurl(array $smsData) {
        
        
        if (empty($smsData['recipients']))
            throw new Exception(Mage::helper('storesms')->__('No recipients found'));
        
        $config = Mage::getModel('storesms/config');
        $postUrl = "http://api2.infobip.com/api/sendsms/xml";
        
        // XML-formatted data
        
        $xmlLongSms = ($config->isSingle()==1) ? '':'<type>longSMS</type>';
        $sender = $config->getSender();
        $xmlSender  = ($config->isPro() && !empty($sender)) ? '<sender>'.$config->getSender().'</sender>':'';    

        $xmlRecipients  = '<recipients>'."\r\n";
        foreach ($smsData['recipients'] as $recipient) {
            $messageId = ($smsData['ids'][$recipient]) ? ' messageId="'.$smsData['ids'][$recipient].'"':'';
            $xmlRecipients .= '<gsm'.$messageId.'>'.$recipient.'</gsm>'."\r\n";
        }
        $xmlRecipients .= '</recipients>'."\r\n";
        
        $xmlString =
'<SMS>
<authentification>
<username>'.$config->getLogin().'</username>
<password>'.$config->getPassword().'</password>
</authentification>
<message>
'.$xmlSender.'
'.$xmlLongSms.'
<text>'.$smsData['message'].'</text>
</message>
'.$xmlRecipients.'
</SMS>';

        // previously formatted XML data becomes value of "XML" POST variable
        $fields = "XML=" . urlencode($xmlString);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        
        // response of the POST request
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;        
    }
    
    
    
    
    
    public function getCredits() {
        
        $getUrl = 'http://api.infobip.com/api/command?username='.Mage::getModel('storesms/config')->getLogin().'&password='.Mage::getModel('storesms/config')->getPassword().'&cmd=CREDITS';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $getUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
        
        
    }
    
    
    
    
    
    public function checkCreditLimit() {
        
        $config =   Mage::getModel('storesms/config');
        if ($config->isApiEnabled()==0) return;
            
        $limit = $config->creditAllertLimit();
        if ($limit==0) return; //If limit allert is turned off
        
        
        try {
            
            $credits = $this->getCredits();
            
            if ($credits=='UNKNOWN_COMMAND') {
                Mage::getSingleton('core/session')->addError(Mage::helper('storesms')->__($config::WRONG_AUTH_DATA));
            }
            elseif($credits < $limit) {
                Mage::getSingleton('core/session')->addError(Mage::helper('storesms')->__($config::LOW_CREDITS_WARNING_MESSAGE));
            }

        }
        catch (Exception $e) {
             Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        
    }
    
    
    
    public function getDelieveryReport() {
        
        $config =  Mage::getModel('storesms/config');
        $getUrl = 'http://api2.infobip.com/api/dlrpull?user='.$config->getLogin().'&password='.$config->getPassword();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $getUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
        
    }


    
    public function saveDelieveryReport($report = false) {
        
        if ($report === false)
            $report = $this->getDelieveryReport ();
        
        if ($report == 'NO_DATA' || !$report)
            return;
        
        //if delievery report exists save it to db
        $delieveryStatuses = Mage::helper('storesms/xml')->getStatusesFromXml($report);
        $model = Mage::getModel('storesms/storesms');
        
        //save delievery status for each message
        foreach ($delieveryStatuses as $message) {
            $model->setNewDeliveryStatus($message['ID'],$message['STATUS']);
            $model->unsetData();
        }
        
        
    }
    

}