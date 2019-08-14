<?php

class Sendmachine_Sendmachine_Block_AppContainer_Main extends Mage_Adminhtml_Block_Widget_Form_Container {

	private $_tab;

	public function __construct($data = NULL) {

		$this->_blockGroup = NULL;
		parent::__construct();

		$this->_removeButton('delete');
		$this->_removeButton('back');

		$this->_tab = isset($data['tab']) ? $data['tab'] : NULL;
	}

	public function getHeaderText() {

		$apiConnected = Mage::registry('sm_model')->get('api_connected');
		$status = $apiConnected ? Mage::helper('sendmachine')->__('connected') : Mage::helper('sendmachine')->__('disconnected');
		$style = "display:inline-block;color:white;padding:2px 5px;margin-left:-20px;background-color:#eb5e00;border-radius:3px;";

		return '<span style="' . $style . '" >' . Mage::helper('sendmachine')->__('Api status: ') . $status . '</span>';
	}

	protected function _prepareLayout() {

		$this->setChild('form', $this->getLayout()->createBlock('sendmachine/appContainer_tab_' . $this->_tab));

		return parent::_prepareLayout();
	}

}
