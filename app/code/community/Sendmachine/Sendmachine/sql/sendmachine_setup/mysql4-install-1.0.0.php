<?php

$installer = $this;

$installer->startSetup();

$installer->run("
	
CREATE TABLE IF NOT EXISTS `{$this->getTable('sm_import_export_log')}` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `action` ENUM('import','export') NOT NULL,
  `state` ENUM('pending','completed','failed') NOT NULL,
  `number` int(100) NULL DEFAULT '0',
  `list_name` varchar(100) NOT NULL,
  `sdate` DATETIME NOT NULL,
  `edate` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
    ");

$installer->endSetup();
