<?php

$installer = $this; 
$installer->startSetup();
$table = $installer->getConnection()->newTable($installer->getTable('infobip_storesms'))
      ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
          'identity'  => true,
          'unsigned'  => true,
          'nullable'  => false,
          'primary'   => true
        ), 'SMS ID')
      ->addColumn('sender', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
          'nullable'  => false,
        ), 'Sender')
      ->addColumn('telephone', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
          'nullable'  => false,
        ), 'Telephone')
      ->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, 1000, array(
          'nullable'  => false,
        ), 'Message')
      ->addColumn('attempts_number', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
          'nullable'  => false,
        ), 'Send Attempts')
      ->addColumn('delivery_status', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
          'nullable'  => false,
        ), 'Delivery Status')
      ->addColumn('created', Varien_Db_Ddl_Table::TYPE_DATE , null, array(
          'nullable'  => false,
        ), 'First send date')
      ->setComment('Storesms API SMSes');
$installer->getConnection()->createTable($table);
$installer->endSetup();