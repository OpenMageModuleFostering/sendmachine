<?php

class Sendmachine_Sendmachine_Block_AppContainer_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

	private $suffix = "";
	
	public function __construct() {

		parent::__construct();

		$this->setId('sendmachine_tabs');
		$this->setTitle("<img style='height:44px;' src='" . $this->getSkinUrl('sendmachine/logo.png') . "'>");
		
		$request = Mage::app()->getRequest();
		
		$website = $request->getParam('website');
		$store = $request->getParam('store');
		
		if($website) $this->suffix .= "/website/$website";
		if($store) $this->suffix .= "/store/$store";
	}

	protected function _beforeToHtml() {

		$this->addTab('general_section', array(
			'label' => Mage::helper('sendmachine')->__('General'),
			'title' => Mage::helper('sendmachine')->__('General'),
			'url' => $this->getUrl('*/*/index' . $this->suffix)
		));

		$this->addTab('list_section', array(
			'label' => Mage::helper('sendmachine')->__('List'),
			'title' => Mage::helper('sendmachine')->__('List'),
			'url' => $this->getUrl('*/*/list' . $this->suffix),
			'active' => (Mage::app()->getRequest()->getActionName() == 'list' ) ? true : false
		));

		$this->addTab('email_section', array(
			'label' => Mage::helper('sendmachine')->__('Email'),
			'title' => Mage::helper('sendmachine')->__('Email'),
			'url' => $this->getUrl('*/*/email' .$this->suffix),
			'active' => (Mage::app()->getRequest()->getActionName() == 'email' ) ? true : false
		));

		return parent::_beforeToHtml();
	}

}
