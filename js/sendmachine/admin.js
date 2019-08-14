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
	} else if (state === 'show') {
		style = '';
		text = 'hide';
	}

	button.setAttribute('class', text);
	button.innerHTML = text;
	document.getElementById(id).setAttribute('style', style + ';width:222px;');
}

function smSendTestEmail(url) {

	var emailAddress = document.getElementById('smMageTestEmail');
	var website = document.getElementById('website_email');
	var store = document.getElementById('store_email');

	var data = {
		emailAddress: emailAddress.value,
		website: website.value,
		store: store.value
	};
	new Ajax.Request(url, {
		method: 'post',
		parameters: data,
		onSuccess: function () {
			location.reload();
		}
	});
}

function smImport(url) {

	var store = document.getElementById('store_lists').value;
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

	var store = document.getElementById('store_lists').value;
	new Ajax.Request(url, {
		method: 'post',
		parameters: {store: store},
		onSuccess: function () {
			location.reload();
		}
	});
}

function smRefreshCachedLists(url) {
    
    var website = document.getElementById('website_lists').value;
    var store = document.getElementById('store_lists').value;
	new Ajax.Request(url, {
		method: 'post',
        parameters: {
            website: website,
			store: store
		},
		onSuccess: function () {
			location.reload();
		}
	});
}

function initResetToParent(disable, area) {
    
    var fieldset = document.getElementById(area + '_fieldset');
    
    if(fieldset) {
        
        fieldset.style.position = "relative";
        
        var node = document.createElement("DIV");
        node.setAttribute("style", "position:absolute;top:0px;left:0px;width:100%;height:100%;background:rgba(255,255,255,0.3);display:none;");
        node.setAttribute("id", "disabled_overlay_" + area);
        fieldset.appendChild(node);
        
        resetToParent(disable, area);
    }
}

function resetToParent(disable, area) {

    var overlay = document.getElementById("disabled_overlay_" + area);

    if (overlay) {

        overlay.style.display = disable ? "block" : "none";
    }
}