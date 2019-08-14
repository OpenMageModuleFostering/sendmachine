function smInitCredentialsBlur() {

	smToggleCredentialsVisibility('api_username', 'hide');
	smToggleCredentialsVisibility('api_password', 'hide');

	document.getElementById('api_username').onfocus = function () {
		smToggleCredentialsVisibility('api_username', 'show');
	};
	document.getElementById('api_password').onfocus = function () {
		smToggleCredentialsVisibility('api_password', 'show');
	};
}

function smToggleCredentialsVisibility(id, state) {

	if (id !== 'api_username' && id !== 'api_password') {
		return;
	}

	var style = '';
	var text = '';

	var button = document.getElementById('button_' + id);

	if (!state) {
		state = button.getAttribute('class');
	}

	if (state === 'hide') {
		style = 'color:transparent;text-shadow:0 0 5px rgba(0,0,0,0.5)';
		text = 'show';
	}
	else if (state === 'show') {
		style = '';
		text = 'hide';
	}

	button.setAttribute('class', text);
	button.innerHTML = text;
	document.getElementById(id).setAttribute('style', style + ';width:222px;');
}

function smSendTestEmail(url) {

	var emailAddress = document.getElementById('smMageTestEmail');
	new Ajax.Request(url, {
		method: 'post',
		parameters: {emailAddress: emailAddress.value},
		onSuccess: function () {
			location.reload();
		}
	});
}

function smImport(url) {

	var store = document.getElementById('sm_import_export_store').value;
	new Ajax.Request(url, {
		method: 'post',
		parameters: {
			store: store
		},
		onSuccess: function () {
			location.reload();
		}
	});
}

function smExport(url) {

	var store = document.getElementById('sm_import_export_store').value;
	new Ajax.Request(url, {
		method: 'post',
		parameters: {sm_import_export_store: store},
		onSuccess: function () {
			location.reload();
		}
	});
}

function smRefreshCachedLists(url) {

	new Ajax.Request(url, {
		method: 'post',
		onSuccess: function () {
			location.reload();
		}
	});
}