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

    const CONTENT_TYPE_HEADER = 'Content-Type:application/xml';
    const ACCEPT_HEADER = 'Accept:application/xml';
    const USER_AGENT_HEADER = 'User-Agent:Magento-';

    public function sendByCurl(array $smsData) {
        
        if (empty($smsData['recipients']))
            throw new Exception(Mage::helper('storesms')->__('No recipients found'));
        
        $config = Mage::getModel('storesms/config');
        $postUrl = "http://api.infobip.com/sms/1/text/single";
        
        // XML-formatted data

        $sender = $config->getSender();
        $xmlSender  = ($config->isPro() && !empty($sender)) ? $sender:'';
        $xmlRecipients = '';

        foreach ($smsData['recipients'] as $recipient) {
            $xmlRecipients .= '<to>';
            $xmlRecipients .= $recipient;
            $xmlRecipients .= '</to>'."\r\n";
        }

        $xmlString =
'<request>
<from>'.$xmlSender.'</from>
<to>'.$xmlRecipients.'</to>
<text>'.$smsData['message'].'</text>
</request>';

        // previously formatted XML data becomes value of "XML" POST variable
        $fields = "XML=" . urlencode($xmlString);
        
        $ch = curl_init();
        $headers = array(self::CONTENT_TYPE_HEADER, self::ACCEPT_HEADER,
            self::USER_AGENT_HEADER . Infobip_Storesms_Model_Config::PLUGIN_VERSION);

        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER , $headers);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD , $config->getLogin() . ":" . $config->getPassword());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS,2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlString);


        // response of the POST request
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseBodyXml = new SimpleXMLElement($response);
        $responseArray = array(
            "httpStatusCode" => $httpcode,
            "responseBodyXml" => $responseBodyXml
        );
        curl_close($ch);
        return $responseArray;
    }

    public function getCredits() {

        $config =  Mage::getModel('storesms/config');
        $getUrl = 'http://api.infobip.com//account/1/balance';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $getUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER , array(self::ACCEPT_HEADER,
            self::USER_AGENT_HEADER . Infobip_Storesms_Model_Config::PLUGIN_VERSION));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD , $config->getLogin() . ":" . $config->getPassword());
        curl_setopt($ch, CURLOPT_HTTPGET , TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseBodyXml = new SimpleXMLElement($response);
        $responseArray = array(
            "httpStatusCode" => $httpcode,
            "responseBodyXml" => $responseBodyXml
        );
        curl_close($ch);
        return $responseArray;
        
        
    }
    
    public function checkCreditLimit() {

        if (!Mage::getSingleton('admin/session')->isLoggedIn()) //checks credit limit only for logged admin
            return;
        
        $config =   Mage::getModel('storesms/config');
        if ($config->isApiEnabled()==0) return;
            
        $limit = $config->creditAllertLimit();
        if ($limit==0) return; //If limit allert is turned off

        try {
            $creditsArray = $this->getCredits();
            $responseBodyXml = $creditsArray["responseBodyXml"];
            $httpStatusCode = $creditsArray["httpStatusCode"];
            
            if ($httpStatusCode==401) {
                Mage::getSingleton('core/session')->addError(Mage::helper('storesms')->__($config::WRONG_AUTH_DATA));
            }
            elseif($httpStatusCode==200) {
                $balance = $responseBodyXml -> balance;
                if ($balance < $limit) {
                    Mage::getSingleton('core/session')->addError(Mage::helper('storesms')->__($config::LOW_CREDITS_WARNING_MESSAGE));
                }
            }

        }
        catch (Exception $e) {
             Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        
    }

    public function getMessageLogs() {
        
        $config =  Mage::getModel('storesms/config');
        $limit = $config::DR_LIMIT;
        $getUrl = 'http://api.infobip.com/sms/1/logs?limit='.$limit;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $getUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER , array(self::ACCEPT_HEADER,
            self::USER_AGENT_HEADER . Infobip_Storesms_Model_Config::PLUGIN_VERSION));
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD , $config->getLogin() . ":" . $config->getPassword());
        curl_setopt($curl, CURLOPT_HTTPGET , TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $responseBodyXml = new SimpleXMLElement($response);
        $responseArray = array(
            "httpStatusCode" => $httpcode,
            "responseBodyXml" => $responseBodyXml
        );
        curl_close($curl);
        return $responseArray;
        
    }



    

}