<?php

class Sendmachine_Sendmachine_Block_AppContainer_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {

		parent::__construct();
		$this->setId('sm_import_export_log');
		$this->setDefaultSort('id');
		$this->setDefaultDir('desc');
	}

	protected function _getCollectionClass() {

		return 'sendmachine/sendmachine_collection';
	}

	protected function _prepareCollection() {

		$collection = Mage::getResourceModel($this->_getCollectionClass());
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$helper = Mage::helper('sendmachine');

		$this->addColumn('id', array(
			'header' => $helper->__('Index'),
			'index' => 'id'
		));

		$this->addColumn('action', array(
			'header' => $helper->__('Action'),
			'index' => 'action'
		));

		$this->addColumn('state', array(
			'header' => $helper->__('State'),
			'index' => 'state'
		));

		$this->addColumn('number', array(
			'header' => $helper->__('Customer number'),
			'index' => 'number'
		));

		$this->addColumn('list_name', array(
			'header' => $helper->__('Contact list'),
			'index' => 'list_name'
		));

		$this->addColumn('sdate', array(
			'header' => $helper->__('Start date'),
			'index' => 'sdate'
		));

		$this->addColumn('edate', array(
			'header' => $helper->__('End date'),
			'index' => 'edate'
		));

		return parent::_prepareColumns();
	}

}
