<?php

class Infobip_Storesms_Block_Adminhtml_Storesms_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        
        $form = new Varien_Data_Form();       
        $this->setForm($form);
        
        $fieldset = $form->addFieldset('storesms_form', array('legend'=>Mage::helper('storesms')->__('SMS Content')));

        $fieldset->addField('sms_message', 'editor', array(
            'name'      => 'sms_message',
            'label'     => Mage::helper('storesms')->__('Message'),
            'title'     => Mage::helper('storesms')->__('Message'),
            'style'     => 'width:500px; height:15em;',
            'after_element_html' => Mage::helper('storesms')->__('Message (max. 160 characters).'),
            'wysiwyg'   => false,
            'required'  => true,
            'class'       => 'validate-length maximum-length-160'
        ));
        
        $fieldset->addField('note2', 'note', array(
            'text'     => Mage::helper('storesms')->__('Bulk messaging is the process of sending a large number of SMS messages to various mobile number databases (mobile numbers). Bulk SMS is commonly used for alerts, reminders, marketing but also for providing information and communication between both staff and customers.')
        ));

        $fieldset->addField('note', 'note', array(
          'text'     => Mage::helper('storesms')->__('<br /><p><strong>Using other Infobip services with Infobip StoreSMS</strong><br /><br /> Infobip is a global provider of mobile solutions connecting mobile network operators and enterprises through an in-house developed and operated mobile services cloud. SMS offers an ideal opportunity for business expansion because of its ubiquity, marketing effectiveness and wide reach.<br /> To find out more about SMS messaging solution visit <a href="http://www.infobip.com/" target="_blank">www.infobip.com</a><br />Magento users can go to <strong>System &gt; Import/export &gt; Dataflow Profiles &gt; Export Customers &gt; Run Profile</strong> to create a file that could later be imported to all of Infobip\'s other tools. This way, you can also use Infobip\'s Worx platform and inform your customers over SMS.</p>')
        ));

        return parent::_prepareForm();
    }
}