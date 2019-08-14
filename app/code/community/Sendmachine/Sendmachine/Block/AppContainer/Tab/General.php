<?php

class Sendmachine_Sendmachine_Block_AppContainer_Tab_General extends Mage_Adminhtml_Block_Widget_Form {

	private $store = null;
    private $website = null;
    
	protected function _prepareForm() {

		$sm = Mage::registry('sm_model');
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/save'),
			'method' => 'post'
		));
		
		$request = Mage::app()->getRequest();
		$this->store = $request->getParam('store');
        $this->website = $request->getParam('website');
		
		$form->setUseContainer(true);
        
        $defaults = $sm->get("is_default");
        
        $disabled_general = isset($defaults['general']) ? $defaults['general'] : true;
        $checkbox_general = $sm->resetvaluescheckbox($this->store, $this->website, $disabled_general, "general");
		$fieldset = $form->addFieldset('general_fieldset', array('legend' => Mage::helper('sendmachine')->__('General Settings' . $checkbox_general)));

		$fieldset->addField('tab_general', 'hidden', array(
			'name' => 'tab',
			'value' => 'index'
		));
		
		$fieldset->addField('website_general', 'hidden', array(
			'name' => 'website',
			'value' => $request->getParam('website')
		));
		
		$fieldset->addField('store_general', 'hidden', array(
			'name' => 'store',
			'value' => $request->getParam('store')
		));
        
		$fieldset->addField('plugin_enabled', 'select', array(
			'name' => 'plugin_enabled',
			'label' => Mage::helper('sendmachine')->__('Plugin enabled'),
			'title' => Mage::helper('sendmachine')->__('Plugin enabled'),
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));

		$fieldset->addField('api_username', 'text', array(
			'name' => 'api_username',
			'label' => Mage::helper('sendmachine')->__('Api username'),
			'title' => Mage::helper('sendmachine')->__('Api username'),
			'after_element_html' => '<button id="button_api_username" onClick="smToggleCredentialsVisibility(\'api_username\');return false;" >' . Mage::helper('sendmachine')->__('Show') . '</button>'
		));

		$fieldset->addField('api_password', 'text', array(
			'name' => 'api_password',
			'label' => Mage::helper('adminhtml')->__('API password'),
			'title' => Mage::helper('adminhtml')->__('API password'),
			'after_element_html' => '<button id="button_api_password" onClick="smToggleCredentialsVisibility(\'api_password\');return false;" >' . Mage::helper('sendmachine')->__('Show') . '</button><script>smInitCredentialsBlur()</script>'
		));

		$form->addValues($sm->get());
		$this->setForm($form);
		return parent::_prepareForm();
	}

}
