<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('sm_import_export_log')}` ADD COLUMN `store` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `id`;");

$installer->endSetup();