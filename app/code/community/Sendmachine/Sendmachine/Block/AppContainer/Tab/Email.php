<?php

class Sendmachine_Sendmachine_Block_AppContainer_Tab_Email extends Mage_Adminhtml_Block_Widget_Form {

	private $store = null;
	
	protected function _prepareForm() {

		$sm = Mage::registry('sm_model');
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/save'),
			'method' => 'post'
				)
		);

		$request = Mage::app()->getRequest();
		$this->store = $request->getParam('store');
		
		$form->setUseContainer(true);


		$fieldset = $form->addFieldset('email_fieldset', array('legend' => Mage::helper('sendmachine')->__('Email Settings')));
        $tranzactionalfieldset = $form->addFieldset('tranzactional_fieldset', array('legend' => Mage::helper('sendmachine')->__('Transactional groups settings')));
		if($this->store) {
			$testfieldset = $form->addFieldset('emailtest_fieldset', array('legend' => Mage::helper('sendmachine')->__('Test Configuration')));
		}

		$fieldset->addField('tab_email', 'hidden', array(
			'name' => 'tab',
			'value' => 'email'
		));
		
		$fieldset->addField('website_email', 'hidden', array(
			'name' => 'website',
			'value' => $request->getParam('website')
		));
		
		$fieldset->addField('store_email', 'hidden', array(
			'name' => 'store',
			'value' => $request->getParam('store')
		));

		$fieldset->addField('email_enabled', 'select', array(
			'name' => 'email_enabled',
			'label' => Mage::helper('sendmachine')->__('Enable email sending'),
			'title' => Mage::helper('sendmachine')->__('Enable email sending'),
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));

		$fieldset->addField('smtp_encryption', 'select', array(
			'name' => 'smtp_encryption',
			'label' => Mage::helper('sendmachine')->__('SMTP Encryption'),
			'title' => Mage::helper('sendmachine')->__('SMTP Encryption'),
			'values' => Mage::getModel('sendmachine/source_smtp')->toOptionArray()
		));

		$fieldset->addField('from_email', 'text', array(
			'name' => 'from_email',
			'label' => Mage::helper('sendmachine')->__('From Email'),
			'title' => Mage::helper('sendmachine')->__('From Email'),
			'after_element_html' => '<small>' . Mage::helper('sendmachine')->__("Note! If you set a 'From email' here, all other 'from emails' will be overriden") . '<small>'
		));

		$fieldset->addField('from_name', 'text', array(
			'name' => 'from_name',
			'label' => Mage::helper('sendmachine')->__('From Name'),
			'title' => Mage::helper('sendmachine')->__('From Name'),
			'after_element_html' => '<small>' . Mage::helper('sendmachine')->__("Note! If you set a 'From name' here, all other 'from names' will be overriden") . '<small>'
		));

		$tranzactionalfieldset->addField('transactional_campaigns_enabled', 'select', array(
			'name' => 'transactional_campaigns_enabled',
			'label' => Mage::helper('sendmachine')->__('Group transactional emails'),
			'title' => Mage::helper('sendmachine')->__('Group transactional emails'),
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));

		$tranzactionalfieldset->addField('transactional_campaign_prefix', 'text', array(
			'name' => 'transactional_campaign_prefix',
			'label' => Mage::helper('sendmachine')->__('Transactional emails prefix'),
			'title' => Mage::helper('sendmachine')->__('Transactional emails prefix'),
			'after_element_html' => '<small>Grouped email\'s prefix<small>'
		));

		$tranzactionalfieldset->addField('transactional_campaign_areas', 'multiselect', array(
			'name' => 'transactional_campaign_areas',
			'label' => Mage::helper('sendmachine')->__('Events to group'),
			'title' => Mage::helper('sendmachine')->__('Events to group'),
			'values' => Mage::getModel('sendmachine/source_transactionalCampAreas')->toOptionArray(),
			'after_element_html' => '<small>' . Mage::helper('sendmachine')->__("Note! Hold 'ctrl' and click the area you want to select/deselect for multiple selection") . '</small>'
		));

		$tranzactionalfieldset->addField('transactional_campaign_suffix', 'text', array(
			'name' => 'transactional_campaign_suffix',
			'label' => Mage::helper('sendmachine')->__('Transactional emails suffix'),
			'title' => Mage::helper('sendmachine')->__('Transactional emails suffix'),
			'after_element_html' => '<small>Grouped email\'s suffix<small>'
		));

		if ($this->store) {
			$testfieldset->addField('smMageTestEmail', 'text', array(
				'label' => Mage::helper('sendmachine')->__('Send a test email'),
				'title' => Mage::helper('sendmachine')->__('Send a test email'),
				'style' => 'width:240px',
				'after_element_html' => '<button onClick=\'smSendTestEmail("' . Mage::helper('adminhtml')->getUrl('adminhtml/sendmachine/sendtestmail') . '");return false;\' >' . Mage::helper('sendmachine')->__('Send') . '</button><small>' . Mage::helper('sendmachine')->__('Send a test email to make sure that settings were applied correctly') . '<small>',
			));
		}

		$form->addValues($sm->get());
		$this->setForm($form);
		return parent::_prepareForm();
	}

}
