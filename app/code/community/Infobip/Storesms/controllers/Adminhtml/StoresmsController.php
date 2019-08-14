<?php
 
class Infobip_Storesms_Adminhtml_StoresmsController extends Mage_Adminhtml_Controller_Action
{
 
    public function indexAction() {
        $this->loadLayout()
             ->_setActiveMenu('storesms/items');
        $this->renderLayout();
        
    }

    
    
    public function editAction() {

        $this->loadLayout()
             ->_setActiveMenu('storesms/items');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('storesms/adminhtml_storesms_edit'))
                ->_addLeft($this->getLayout()->createBlock('storesms/adminhtml_storesms_edit_tabs'));

        $this->renderLayout();

    }

    
    
    public function newAction() {
        
            $this->_forward('edit');      
            
    }
    
    
    
    public function saveAction() {
        
        Mage::getModel('storesms/storesms')->sendBulkSMS($this->getRequest()->getParam('sms_message'));
        $this->_redirect('*/*/new');        
                                  
    }
    
    
}