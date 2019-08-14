<?php

class Infobip_Storesms_Helper_Data extends Mage_Core_Helper_Abstract
{



    public function  getPhoneNumber($phoneNumber) {

        $config = Mage::getModel('storesms/config');
        $prefix = $config->getCountryPrefix();
        $toStrip = '+,0() ';

        if ($prefix) {
            $pos = strpos($phoneNumber, $prefix);
            if ($pos !== false && $pos <= 1) {
                $phoneNumber = substr_replace($phoneNumber, '', $pos, strlen($prefix));
            }
        }

        return $prefix . ltrim($phoneNumber,$toStrip);

    }


    public function getStatusVerbally($httpStatusCode) {

        $httpStatusCode = ($httpStatusCode>=200 && $httpStatusCode<300) ? 200:$httpStatusCode;
        $httpStatusCode = ($httpStatusCode>=300 && $httpStatusCode<400) ? 300:$httpStatusCode;
        $httpStatusCode = ($httpStatusCode>=405 && $httpStatusCode<500) ? 402:$httpStatusCode;
        $httpStatusCode = ($httpStatusCode>=500 && $httpStatusCode<600) ? 500:$httpStatusCode;

        $httpStatusesVerbal = array(
            '200'     =>'OK',
            '300'     =>'REDIRECTION',
            '400'     =>'BAD_REQUEST',
            '401'     =>'UNAUTHORIZED',
            '402'     =>'CLIENT_ERROR',
            '403'     =>'FORBIDDEN',
            '404'     =>'NOT_FOUND',
            '500'     =>'SERVER_ERROR'
        );

        return $httpStatusesVerbal[$httpStatusCode];

    }

    public function getLogs() {

        $responseArray = Mage::getModel('storesms/apiClient') -> getMessageLogs();
        $result= $responseArray["responseBodyXml"] -> results -> result;
        foreach ($result as $message) {
            $formatedSentAt = date("M d, Y - H:i:s P T", strtotime($message -> sentAt));

            $sentMessageLog = array(
                "message_id" => $message -> messageId,
                "to" => $message -> to,
                "from" => $message -> from,
                "text" => $message -> text,
                "sent_at" => $formatedSentAt,
                "general_status" => $message -> status -> groupName,
                "status_description" => $message -> status -> description
            );
            $arrayOfMessageLogs[] = $sentMessageLog;
        }

        return $arrayOfMessageLogs;

    }

}
