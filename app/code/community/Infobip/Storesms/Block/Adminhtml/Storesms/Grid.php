<?php

class Infobip_Storesms_Block_Adminhtml_Storesms_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('storesms_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('storesms/storesms')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {        
        
        $this->addColumn('id', array(
            'header'    => Mage::helper('storesms')->__('ID'),
            'align'     =>'left',
            'width'     => '40px',
            'index'     => 'id',
        ));
        $this->addColumn('created', array(
            'header'    => Mage::helper('storesms')->__('Created date'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'created',
            'type'      => 'date'
        ));
        $this->addColumn('sender', array(
            'header'    => Mage::helper('storesms')->__('Sender'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'sender',
        ));
        $this->addColumn('telephone', array(
            'header'    => Mage::helper('storesms')->__('Phone number'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'telephone',
        ));
        $this->addColumn('message', array(
            'header'    => Mage::helper('storesms')->__('Message'),
            'align'     =>'left',
            'width'     => '250px',
            'index'     => 'message',
        ));
        $this->addColumn('delivery_status', array(
            'header'    => Mage::helper('storesms')->__('Delivery Status'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'delivery_status',
            'type'      => 'options',
            'options'   => Mage::getModel('storesms/config')->getMessageStatuses()
        ));

 
        return parent::_prepareColumns();
    }
 
}