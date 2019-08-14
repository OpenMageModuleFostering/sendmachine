var smSubscribeHasActivity = false;

function smDisplayPopup(popupDelay) {

	setTimeout(function () {
		document.getElementById('sm_popup_box_wrapper').style.visibility = 'visible';
		document.getElementById('sm_popup_box_wrapper').style.opacity = 1;
	}, popupDelay);
}

function smDismissNoActivity(di) {

	if (di) {
		setTimeout(function () {
			if (!smSubscribeHasActivity)
				smDismissPopup();
		}, di*1000);
	}
}

function smDismissPopup() {

	document.getElementById('sm_popup_box_wrapper').style.opacity = 0;

	setTimeout(function () {
		document.getElementById('sm_popup_box_wrapper').style.display = 'none';
		document.getElementById('sm_popup_box_wrapper').style.visibility = 'hidden';
	}, 300);
}

function smStopEventPropagation() {

	if (event.stopPropagation) {
		event.stopPropagation();
	}
	event.cancelBubble = true;
}

function smSubscribeCustomer(el, dismissSuccess) {

	var request_params = {};

	for (var i = 0; i < el.elements.length; i++) {

		if (el.elements[i].name) {
			request_params[el.elements[i].name] = [el.elements[i].value];
		}
	}

	document.getElementById('sm_popup_box_loader').style.display = 'block';

	new Ajax.Request(el.action, {
		requestHeaders: {Accept: 'application/json'},
		method: 'post',
		parameters: request_params,
		onSuccess: function (response) {

			document.getElementById('sm_popup_box_loader').style.display = 'none';
			var resp = response.responseText.evalJSON(true);

			if (resp.code === 1) {
				el.reset();
				document.getElementById('sm_popup_box_notification_area').innerHTML = "<div id='sm_popup_box_notification_success' >" + resp.message + "</div>";
				if (dismissSuccess) {
					setTimeout(function () {
						smDismissPopup();
					}, dismissSuccess*1000);
				}
			}
			else {
				document.getElementById('sm_popup_box_notification_area').innerHTML = "<div id='sm_popup_box_notification_error' >" + resp.message + "</div>";
			}
		},
		onError: function () {

			document.getElementById('sm_popup_box_loader').style.display = 'none';
			document.getElementById('sm_popup_box_notification_area').innerHTML = "<div id='sm_popup_box_notification_error' >Something went wrong.You were not subscribed</div>";
		}
	});

	return false;
}