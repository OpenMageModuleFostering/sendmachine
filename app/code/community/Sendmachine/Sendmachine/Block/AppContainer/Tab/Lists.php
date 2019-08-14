<?php

class Sendmachine_Sendmachine_Block_AppContainer_Tab_Lists extends Mage_Adminhtml_Block_Widget_Form {

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
        
        $disabled_lists = isset($defaults['lists']) ? $defaults['lists'] : true;
        $checkbox_lists = $sm->resetvaluescheckbox($this->store, $this->website, $disabled_lists, "lists");
		$fieldset = $form->addFieldset('lists_fieldset', array('legend' => Mage::helper('sendmachine')->__('List Settings' . $checkbox_lists)));
        
		if($this->store) {
			$importexport = $form->addFieldset('lists_fieldset_importexport', array('legend' => Mage::helper('sendmachine')->__('Import/Export Users')));
		}

		$fieldset->addField('tab_lists', 'hidden', array(
			'name' => 'tab',
			'value' => 'list'
		));
		
		$fieldset->addField('website_lists', 'hidden', array(
			'name' => 'website',
			'value' => $request->getParam('website')
		));
		
		$fieldset->addField('store_lists', 'hidden', array(
			'name' => 'store',
			'value' => $this->store
		));

		$fieldset->addField('selected_contact_list', 'select', array(
			'name' => 'selected_contact_list',
			'label' => Mage::helper('sendmachine')->__('Contact List'),
			'title' => Mage::helper('sendmachine')->__('Contact List'),
			'values' => Mage::getModel('sendmachine/source_contactlist')->toOptionArray(),
			'after_element_html' => '<button style="margin-top:10px;" onClick="smRefreshCachedLists(\'' . Mage::helper('adminhtml')->getUrl('adminhtml/sendmachine/refreshCachedLists') . '\');return false;" >' . Mage::helper('sendmachine')->__('Refresh contact lists') . '</button><br><small>' . Mage::helper('sendmachine')->__("Contact lists do not update each time you visit this page. Click 'refresh' to update them when needed") . '</small>'
		));

		$fieldset->addField('keep_users_synced', 'select', array(
			'name' => 'keep_users_synced',
			'label' => Mage::helper('sendmachine')->__('Keep subscribers synced'),
			'title' => Mage::helper('sendmachine')->__('Keep subscribers synced'),
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));

		$fieldset->addField('import_subscribers_limit', 'select', array(
			'name' => 'import_subscribers_limit',
			'label' => Mage::helper('sendmachine')->__('Import subscribers limit'),
			'title' => Mage::helper('sendmachine')->__('Import subscribers limit'),
			'values' => Mage::getModel('sendmachine/source_importExportLimit')->toOptionArray()
		));

		$fieldset->addField('export_subscribers_limit', 'select', array(
			'name' => 'export_subscribers_limit',
			'label' => Mage::helper('sendmachine')->__('Export subscribers limit'),
			'title' => Mage::helper('sendmachine')->__('Export subscribers limit'),
			'values' => Mage::getModel('sendmachine/source_importExportLimit')->toOptionArray()
		));

		$fieldset->addField('enable_subscribe_popup', 'select', array(
			'name' => 'enable_subscribe_popup',
			'label' => Mage::helper('sendmachine')->__('Subscribe popup'),
			'title' => Mage::helper('sendmachine')->__('Subscribe popup'),
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));

		$fieldset->addField('popup_show_after_page', 'text', array(
			'name' => 'popup_show_after_page',
			'label' => Mage::helper('sendmachine')->__('Show On Page View'),
			'title' => Mage::helper('sendmachine')->__('Show On Page View')
		));

		$fieldset->addField('popup_delay', 'text', array(
			'name' => 'popup_delay',
			'label' => Mage::helper('sendmachine')->__('Popup Delay (ms)'),
			'title' => Mage::helper('sendmachine')->__('Popup Delay (ms)')
		));

		$fieldset->addField('hide_after_subscribe', 'text', array(
			'name' => 'hide_after_subscribe',
			'label' => Mage::helper('sendmachine')->__('Dismiss popup (s)'),
			'title' => Mage::helper('sendmachine')->__('Dismiss popup (s)'),
			'after_element_html' => '<small>' . Mage::helper('sendmachine')->__('Dismiss popup box after successful subscribe or no activity. 0 means no dismiss') . '</small>'
		));

		$fieldset->addField('popup_text_header', 'text', array(
			'name' => 'popup_text_header',
			'label' => Mage::helper('sendmachine')->__("Popup header text"),
			'title' => Mage::helper('sendmachine')->__("Popup header text")
		));

		$fieldset->addField('popup_custom_fields', 'checkbox', array(
			'style' => 'display:none',
			'label' => Mage::helper('sendmachine')->__("Popup custom fields"),
			'title' => Mage::helper('sendmachine')->__("Popup custom fields"),
			'after_element_html' => Mage::helper('sendmachine')->customfields2checkbox($sm->get('list_custom_fields'))
		));
		
		if ($this->store) {

			$importexport->addField('smImportToNewsletter', 'button', array(
				'label' => Mage::helper('sendmachine')->__("Import subscribers"),
				'value' => 'Import to Newsletter',
				'class' => 'form-button',
				'onclick' => "smImport('" . Mage::helper('adminhtml')->getUrl('adminhtml/sendmachine/importToNewsletter') . "')",
				'after_element_html' => '<br><small>' . Mage::helper('sendmachine')->__("Import your sendmachine subscribers to 'Newsletter Subscribers' page.") . '<br><b>' . Mage::helper('sendmachine')->__('Note!') . '</b> ' . Mage::helper('sendmachine')->__("You must select a contact list first.") . '<br>' . Mage::helper('sendmachine')->__("If 'All store views' option is selected, users will be subscribed to the default store") . '</small>'
			));

			$importexport->addField('smExportToSendmachine', 'button', array(
				'label' => Mage::helper('sendmachine')->__('Export subscribers'),
				'value' => 'Export to Sendmachine',
				'class' => 'form-button',
				'onclick' => "smExport('" . Mage::helper('adminhtml')->getUrl('adminhtml/sendmachine/exportToSendmachine') . "')",
				'after_element_html' => '<br><small>' . Mage::helper('sendmachine')->__("Export your subscribed customers to a contact list in your sendmachine account.") . '<br><b>' . Mage::helper('sendmachine')->__("Note!") . '</b> ' . Mage::helper('sendmachine')->__("You must select a contact list first") . '</small>'
			));
		}

		$form->addValues($sm->get());
		$this->setForm($form);
		return parent::_prepareForm();
	}

	protected function _afterToHtml($html) {

		$extra = "";
		if($this->store) $extra = $this->getLayout()->createBlock('sendmachine/appContainer_grid')->toHtml();
		return $html . $extra;
	}

}
