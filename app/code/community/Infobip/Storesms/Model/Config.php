<?php
//File:  app/code/community/Infobip/StoresmsApi/Model/Config.php

/**
* Storesms API config class
* 
* 
 * @category   Infobip
 * @package    Storesms API
 * @copyright  Copyright (c) 2012 Infobip (http://infobip.pl/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Marek Jasiukiewicz <dev@jasiukiewicz.pl>
* ...
*/


class Infobip_Storesms_Model_Config {
    
    const LOW_CREDITS_WARNING_MESSAGE = 'Warning: Low credit level at your Storesms account. Buy credits.';
    const API_HOST = 'api2.infobip.com';
    const WRONG_AUTH_DATA = 'Storesms API: Wrong Password and/or Username' ;
    
    public $contacts = array (
        'en_US'=>'http://www.infobip.com/contact', //default international
        'pt_BR'=>'http://br.infobip.com/contact', //brasil
        'fr_FR'=>'http://www.infobip.fr/contact', //france
        'ru_RU'=>'http://www.infobip.com.ru/contact', //russia
        'af_ZA'=>'http://www.infobip.co.za/contact', //south africa
        'pl_PL'=>'http://www.infobip.com.pl/contact', //poland
        'tr_TR'=>'http://www.infobip.com.tr/contact'//Turkey
        );
    

    
    public function getContactUrl($localeCode) {
        
        return (array_key_exists($localeCode, $this->contacts)) ? $this->contacts[$localeCode] : $this->contacts['en_US'];
        
    }


    
    /**
     * getting API login from main configuration
     * @return string
     */ 
    public function getLogin() {
        return Mage::getStoreConfig('storesms/main_conf/apilogin');


    }


    /**
     * getting API password from main configuration
     * @return string
     */ 
    public function getPassword() {
        $encrypted_pass = Mage::getStoreConfig('storesms/main_conf/apipassword');
        return Mage::helper('core')->decrypt($encrypted_pass);

    }
    

    /**
     * getting message sender from main configuration
     * @return string
     */ 
    public function getSender() {
        return Mage::getStoreConfig('storesms/main_conf/sender');

    }
    
    public function isPro() {
        return Mage::getStoreConfig('storesms/main_conf/sender_active');
    }

    
     /**
     * Checks if allowed only single message
     * @return int
     */ 
    public function isSingle() {
        $confRule = Mage::getStoreConfig('storesms/main_conf/allow_long_sms');
        
        return ($confRule == 1) ? 0:1;
        
    }

    
    public function getCountryPrefix() {
        
        return Mage::getStoreConfig('storesms/main_conf/country_prefix');

    }
    
    public function getStoreName() {
        
        return Mage::getStoreConfig('storesms/main_conf/storename');

    }
    
    
     /**
     * checks if Storesms API module is enabled
     * @return boolean
     */ 
    public function isApiEnabled() {
        
        return (Mage::getStoreConfig('storesms/main_conf/active')==0) ? 0:1;
        
    }
    
    
    
    public function creditAllertLimit() {
        return floatval(str_replace(',','.',Mage::getStoreConfig('storesms/main_conf/credit_alert_limit')));
    }
    
    
    
   
     /**
     * getting SMS templates from config
     * @return string
     */ 
    public function getMessageTemplate($template) {
        
        $templateContent = Mage::getStoreConfig('storesms/templates/status_'.$template);
        
        if (Mage::getStoreConfig('storesms/templates/status_'. $template .'_active') && !empty($templateContent))
            return $templateContent;
        
    }
    
    
    
   public function getMessageStatuses() {
       $statuses = array(   'SEND_OK'       =>'SEND_OK',
                            'AUTH_FAILED'   =>'AUTH_FAILED',
                            'XML_ERROR'     =>'XML_ERROR',
                            'NOT_ENOUGH_CREDITS'    =>'NOT_ENOUGH_CREDITS',
                            'NO_RECIPIENTS' =>'NO_RECIPIENTS',
                            'GENERAL_ERROR' =>'GENERAL_ERROR',
                            'WAITING_FOR_DR' =>'WAITING_FOR_DR',
                            'NOT_SENT'=>'NOT_SENT',
                            'SENT'=>'SENT',
                            'NOT_DELIVERED'=>'NOT_DELIVERED',
                            'DELIVERED'=>'DELIVERED',
                            'NOT_ALLOWED'=>'NOT_ALLOWED',
                            'INVALID_DESTINATION_ADDRESS'=>'INVALID_DESTINATION_ADDRESS',
                            'INVALID_SOURCE_ADDRESS'=>'INVALID_SOURCE_ADDRESS',
                            'ROUTE_NOT_AVAILABLE'=>'ROUTE_NOT_AVAILABLE',
                            'NOT_ENOUGH_CREDITS'=>'NOT_ENOUGH_CREDITS',
                            'INVALID_MESSAGE_FORMAT'=>'INVALID_MESSAGE_FORMAT');
       
       return $statuses;
   }
    
    
}