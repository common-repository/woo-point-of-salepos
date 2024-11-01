jQuery(document).ready(function($) {
	var form_processing = false;
	
	jQuery("#screen-options-wrap").removeClass('hidden');
	
	jQuery('form#master_form').submit(function(){
		
		if(form_processing) return false;
		
		jQuery('div#form_messages').show();
		jQuery('div#form_messages').removeClass('notice-error');
		jQuery('div#form_messages').removeClass('notice-success');
		
		jQuery('div#form_messages').addClass('notice-info').html("<p>Please Wait!</p>");
		
		form_processing = true;
		
		jQuery.ajax({
			type		: "POST",
			url		 : ic_ajax_object.ajax_url,
			data		: jQuery(this).serialize(),
			//dataType	: 'json',
			success		:function(data) {				
				//console.log(data);				
				data = JSON.parse(data);
				//console.log(data);
				
				jQuery('div#form_messages').removeClass('notice-info');
				
				if(data.error == true){
					jQuery('div#form_messages').addClass('notice-error').html(data.error_message);
				}
				
				if(data.success == true){
					jQuery('div#form_messages').addClass('notice-success').html(data.success_message);
				}
				
				form_processing = false;
				
			},
			error: function(jqxhr, textStatus, error ){
				form_processing = false;
			}
		});//End On Ajax
		return false;
	});
	
	var state_catche = {};
	
	
	jQuery('select#country').change(function(){
		
		if(form_processing) return false;
		
		var country = jQuery(this).val();		
		if(country == ""){
			form_processing = false;
			return false;
		}
		
		if ( country in state_catche ) {
			form_processing = false;
			jQuery("div.field_input_state").html(state_catche[country]);
			return false;
		}
		
		form_processing = true;
		jQuery('div.field_input_state').html("<p>Please Wait!</p>");
		//alert(country)
		//console.log(country);
		var form_data = {
			'action' : ic_ajax_object.ajax_action
			,'sub_action' : 'location_page'
			,'form_action' : 'country_state_field'
			,'country' : country
			,'country_key' : 'country'
			,'state_key' : 'state'
		}
		
		jQuery.ajax({
			type		: "POST",
			url		 : ic_ajax_object.ajax_url,
			data		: form_data,
			success	 : function(data) {
				
				jQuery("div.field_input_state").html(data);
				form_processing = false;
				jQuery('div#form_messages').hide();
				
				state_catche[country] = data;
			},
			error: function(jqxhr, textStatus, error ){
				form_processing = false;
			}
		});//End On Ajax
		return false;
	});
	
	/* IC Popup */
	//jQuery(document).on('click','.ic_open_popup', function(){
	jQuery('.ic_open_popup').click(function(){
		
		jQuery('div.alert_msg p.ic_popup_please_wait').hide();		
		jQuery('div.alert_msg p.ic_popup_notice').show();
		jQuery('div.alert_msg p.ic_popup_results').hide();
		
		popup_id = jQuery(this).attr('data-popup_id');
		popup_open 	= true;		
		showPopup();
		return false;			
	});
	
	jQuery('.ic_close_popup').click(function(){
		hidePopup();
	});
	
	jQuery(window).resize(function(){
		//center();
	});
	
	jQuery('input#btnConfirmDeleteYES').click(function(){
		
		if(form_processing) return false;
		
		//jQuery('div#form_messages').show();
		//jQuery('div#form_messages').removeClass('notice-error');
		//jQuery('div#form_messages').removeClass('notice-success');
		
		//jQuery('div#form_messages').addClass('notice-info').html("<p>Please Wait!</p>");
		
		jQuery('div.alert_msg p.ic_popup_please_wait').show();		
		jQuery('div.alert_msg p.ic_popup_notice').hide();
		jQuery('div.alert_msg p.ic_popup_results').hide();
		
		form_processing = true;
		
		var form_data = {
			'action' 	   : ic_ajax_object.ajax_action
			,'sub_action'  : jQuery(this).attr('data-sa')
			,'form_action' : jQuery(this).attr('data-fa')
			,'id' 		  : jQuery(this).attr('data-id')
		}
		
		jQuery('input#btnConfirmDeleteYES').hide();
		jQuery('input#btnConfirmDeleteNo').hide();		
		
		jQuery.ajax({
			type		: "POST",
			url		 : ic_ajax_object.ajax_url,
			data		: form_data,
			success	 : function(data) {
				data = JSON.parse(data);
				
				if(data.error == true){
					jQuery('div#form_messages').addClass('notice-error').html(data.error_message);
					jQuery('input#btnConfirmDeleteOK').show();
					jQuery('p.ic_popup_results').html(data.error_message).show();
				}
				
				if(data.success == true){
					jQuery('div#form_messages').addClass('notice-success').html(data.success_message);
					jQuery('p.ic_popup_results').html(data.success_message).show();
					window.location = data.redirect_url;
				}
				
				
				jQuery('p.ic_popup_please_wait').hide();
				
				form_processing = false;
			},
			error: function(jqxhr, textStatus, error ){
				form_processing = false;
			}
		});//End On Ajax
		return false;
	});
	
	
});

