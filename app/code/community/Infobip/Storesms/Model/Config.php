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
    const API_HOST = 'api.infobip.com';
    const WRONG_AUTH_DATA = 'Storesms API: Wrong Password and/or Username' ;
    const DR_LIMIT = 7654;
    
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
    

    
    
}