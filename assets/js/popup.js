/* Vars */
var popup_open 			= false;
var popup_id 			= null;

/* functions */
function center() {
	if (popup_open == true) {
		if(popup_id == null) return;
		
		obj = popup_id;
		var windowWidth = document.documentElement.clientWidth;
		var windowHeight = document.documentElement.clientHeight;
		var popupHeight = jQuery(obj).height();
		var popupWidth = jQuery(obj).width();
		jQuery(obj).css({
			"position": "fixed",
			"top": windowHeight / 2 - popupHeight / 2,
			"left": windowWidth / 2 - popupWidth / 2
		}).fadeIn();
	}
}

function hidePopup() {
	if (popup_open == true) {
		jQuery(".ic_popup_mask").fadeOut("slow");
		jQuery(".ic_popup_box").fadeOut("slow");
		popup_open = false;
		popup_id = null;
	}
}

function showPopup() {//alert(1)
	if (popup_open == true) {
		jQuery(".ic_popup_mask").fadeIn("slow");
		jQuery(popup_id).fadeIn("slow");
		center();
	}
}
