<?php

class Sendmachine_Sendmachine_Block_AppContainer_Tab_Lists extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {

		$sm = Mage::registry('sm_model');
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/save'),
			'method' => 'post'
				)
		);

		$form->setUseContainer(true);

		$fieldset = $form->addFieldset('lists_fieldset', ['legend' => Mage::helper('sendmachine')->__('List Settings')]);
		$popupfieldset = $form->addFieldset('lists_fieldset_popup', ['legend' => Mage::helper('sendmachine')->__('Popup Settings')]);
		$importexport = $form->addFieldset('lists_fieldset_importexport', ['legend' => Mage::helper('sendmachine')->__('Import/Export Users')]);

		$fieldset->addField('tab_lists', 'hidden', [
			'name' => 'tab',
			'value' => 'list'
		]);

		$fieldset->addField('selected_contact_list', 'select', [
			'name' => 'selected_contact_list',
			'label' => Mage::helper('sendmachine')->__('Contact List'),
			'title' => Mage::helper('sendmachine')->__('Contact List'),
			'values' => Mage::getModel('sendmachine/source_contactlist')->toOptionArray(),
			'after_element_html' => '<button style="margin-top:10px;" onClick="smRefreshCachedLists(\'' . Mage::helper('adminhtml')->getUrl('adminhtml/sendmachine/refreshCachedLists') . '\');return false;" >' . Mage::helper('sendmachine')->__('Refresh contact lists') . '</button><br><small>' . Mage::helper('sendmachine')->__("Contact lists do not update each time you visit this page. Click 'refresh' to update them when needed") . '</small>'
		]);

		$fieldset->addField('keep_users_synced', 'select', [
			'name' => 'keep_users_synced',
			'label' => Mage::helper('sendmachine')->__('Keep subscribers synced'),
			'title' => Mage::helper('sendmachine')->__('Keep subscribers synced'),
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		]);

		$fieldset->addField('import_subscribers_limit', 'select', [
			'name' => 'import_subscribers_limit',
			'label' => Mage::helper('sendmachine')->__('Import subscribers limit'),
			'title' => Mage::helper('sendmachine')->__('Import subscribers limit'),
			'values' => Mage::getModel('sendmachine/source_importExportLimit')->toOptionArray()
		]);

		$fieldset->addField('export_subscribers_limit', 'select', [
			'name' => 'export_subscribers_limit',
			'label' => Mage::helper('sendmachine')->__('Export subscribers limit'),
			'title' => Mage::helper('sendmachine')->__('Export subscribers limit'),
			'values' => Mage::getModel('sendmachine/source_importExportLimit')->toOptionArray()
		]);

		$popupfieldset->addField('enable_subscribe_popup', 'select', [
			'name' => 'enable_subscribe_popup',
			'label' => Mage::helper('sendmachine')->__('Subscribe popup'),
			'title' => Mage::helper('sendmachine')->__('Subscribe popup'),
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		]);

		$popupfieldset->addField('popup_show_after_page', 'text', [
			'name' => 'popup_show_after_page',
			'label' => Mage::helper('sendmachine')->__('Show On Page View'),
			'title' => Mage::helper('sendmachine')->__('Show On Page View'),
		]);

		$popupfieldset->addField('popup_delay', 'text', [
			'name' => 'popup_delay',
			'label' => Mage::helper('sendmachine')->__('Popup Delay (ms)'),
			'title' => Mage::helper('sendmachine')->__('Popup Delay (ms)'),
		]);

		$popupfieldset->addField('hide_after_subscribe', 'text', [
			'name' => 'hide_after_subscribe',
			'label' => Mage::helper('sendmachine')->__('Dismiss popup (s)'),
			'title' => Mage::helper('sendmachine')->__('Dismiss popup (s)'),
			'after_element_html' => '<small>' . Mage::helper('sendmachine')->__('Dismiss popup box after successful subscribe or no activity. 0 means no dismiss') . '</small>'
		]);

		$popupfieldset->addField('popup_show_on_store', 'select', array(
			'name' => 'popup_show_on_store',
			'label' => Mage::helper('sendmachine')->__('Show in store'),
			'title' => Mage::helper('sendmachine')->__('Show in store'),
			'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
		));

		$popupfieldset->addField('popup_text_header', 'text', array(
			'name' => 'popup_text_header',
			'label' => Mage::helper('sendmachine')->__("Popup header text"),
			'title' => Mage::helper('sendmachine')->__("Popup header text"),
		));

		$popupfieldset->addField('popup_custom_fields', 'checkbox', array(
			'style' => 'display:none',
			'label' => Mage::helper('sendmachine')->__("Popup custom fields"),
			'title' => Mage::helper('sendmachine')->__("Popup custom fields"),
			'after_element_html' => Mage::helper('sendmachine')->customfields2checkbox($sm->get('list_custom_fields'))
		));

		$importexport->addField('sm_import_export_store', 'select', array(
			'name' => 'sm_import_export_store',
			'label' => Mage::helper('sendmachine')->__("Store"),
			'title' => Mage::helper('sendmachine')->__("Store"),
			'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
		));

		$importexport->addField('smImportToNewsletter', 'button', [
			'label' => Mage::helper('sendmachine')->__("Import subscribers"),
			'value' => 'Import to Newsletter',
			'class' => 'form-button',
			'onclick' => "smImport('" . Mage::helper('adminhtml')->getUrl('adminhtml/sendmachine/importToNewsletter') . "')",
			'after_element_html' => '<br><small>' . Mage::helper('sendmachine')->__("Import your sendmachine subscribers to 'Newsletter Subscribers' page.") . '<br><b>' . Mage::helper('sendmachine')->__('Note!') . '</b> ' . Mage::helper('sendmachine')->__("You must select a contact list first.") . '<br>' . Mage::helper('sendmachine')->__("If 'All store views' option is selected, users will be subscribed to the default store") . '</small>'
		]);

		$importexport->addField('smExportToSendmachine', 'button', [
			'label' => Mage::helper('sendmachine')->__('Export subscribers'),
			'value' => 'Export to Sendmachine',
			'class' => 'form-button',
			'onclick' => "smExport('" . Mage::helper('adminhtml')->getUrl('adminhtml/sendmachine/exportToSendmachine') . "')",
			'after_element_html' => '<br><small>' . Mage::helper('sendmachine')->__("Export your subscribed customers to a contact list in your sendmachine account.") . '<br><b>' . Mage::helper('sendmachine')->__("Note!") . '</b> ' . Mage::helper('sendmachine')->__("You must select a contact list first") . '</small>'
		]);

		$form->addValues($sm->get());
		$this->setForm($form);
		return parent::_prepareForm();
	}

	protected function _afterToHtml($html) {

		$extra = $this->getLayout()->createBlock('sendmachine/appContainer_grid')->toHtml();
		return $html . $extra;
	}

}
