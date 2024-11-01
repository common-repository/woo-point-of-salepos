jQuery(document).ready(function($) {
	
	var cart_items    	 = [];
	var temp_items    	 = [];
	var cart_shipping 	  = [];
	var cart_fee      	   = [];	
	var custom_meta   		= [];
	var item_meta     	  = [];	
	var order_details 	  = {};
	var order_details 	  = {};
	var post_content  	   = {};	
	var sub_stotal         = 0;
	var discount           = 0;
	var order_total        = 0;
	var shipping_number    = 0;
	var fee_number         = 0;
	var last_page	      = 'cart';
	var payment_method_id  = '';
	var meta_number        = 0;	
	var form_processing    = false;	
	var customer_label	 = '';
	var customer_id		= 0;
	var user_cache	     = {};
	var state_catche	   = {};
	var product_page 	   = 0;
	var product_loaded 	 = false;
	var all_product_loaded = false;
	var xhr_product_ajax   = null;	
	var tax_rates 		  = [];
	var tax__rates 		  = [];
	var new_tax_rates	  = [];
	var settings 		   = [];
	var shipping_methods   = [];
	var payment_gateways   = [];
	var employee		   = [];
	var i18n 			   = [];
	var customer_fields 	= [];
	var location_name 	  = '';
	var ajax_action    	= "point_of_sale";
	var pending_search 	 = false;
	var last_searched	  = 'test';
	var enable_employee_column    = false;
	var df	= 5; /*Default Columns*/
	var print_preview	   = false;
	var print_window	    = null;
	var new_receipt_id	  = "";
	df = (enable_employee_column == true) ? (df + 1) :df;
	
	
	var pos_cart_items = jQuery.cookie("pos_cart_items");
	if(pos_cart_items){
		cart_items = JSON.parse(pos_cart_items);
	}
	
	var pos_cart_shipping = jQuery.cookie("pos_cart_shipping");
	if(pos_cart_shipping){
		cart_shipping = JSON.parse(pos_cart_shipping);
	}	
	
	var pos_cart_fee = jQuery.cookie("pos_cart_fee");
	if(pos_cart_fee){
		cart_fee = JSON.parse(pos_cart_fee);
	}
	
	var pos_custom_meta = jQuery.cookie("pos_custom_meta");
	if(pos_custom_meta){
		custom_meta = JSON.parse(pos_custom_meta);
	}
	
	var pos_item_meta = jQuery.cookie("pos_item_meta");
	if(pos_item_meta){
		item_meta = JSON.parse(pos_item_meta);
	}
	
	customer_label = jQuery.cookie("pos_customer_label");
	customer_id 	= jQuery.cookie("pos_customer_id");
	
	
	jQuery(document).on('click','button#print_receipt',function(){
		if(print_preview){
			window.print() ;
		}else{
			var h = jQuery(document).height();
			print_window = window.open(settings.post_url+"/#/print_preview/"+new_receipt_id, "print_preview", "directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,addressbar=no,width=1000,height="+h);
		}
	});
	
	jQuery(document).on('click','button#close_print_window',function(){
		window.close();
	});
	
	jQuery(document).on('click','button#add_new_order',function(){
		jQuery('div.pages').hide();
		jQuery('div.cart_page').show();
		form_processing = false;
		clean_cart();
		
		var loc = window.location.href,
			index = loc.indexOf('#');
		
		if (index > 0) {
		  window.location.hash = "";
		}
		
		new_receipt_id    = "";
		receipt_id    	= "";
		print_preview     = false;
		
	});
	
	jQuery(document).on('focus','input.quantity, input.shipping, input.fee',function(){
		var v = this.value;
		if(v == 0){
			this.value = "";
		}
	});
	
	jQuery(document).on('blur','input.quantity',function(){
		var v = this.value;
		this.value = this.value.replace(/[^\d]/g, '');
		if(v == ""){
			this.value = "1";
		}
	});
	
	jQuery(document).on('blur','input.shipping, input.fee',function(){
		var shipping = this.value;
		if(shipping == ""){
			this.value = "0.00";
		}else{			
			shipping = shipping.replace(/[^\d.]/g, '') ;            // numbers and decimals only
			shipping = shipping.replace(/(\..*)\./g, '$1');        // decimal can't exist more than once
			shipping = shipping.replace(/(\.[\d]{2})./g, '$1');
			shipping = parseFloat(shipping);
			shipping = get_decimal_value(shipping);
			this.value = shipping
		}
	});
	
	jQuery(document).on('click','a.add_to_cart',function(){
		
		if(form_processing){return false;}
		
		var attrs 			= jQuery(this).data();
		var cart_count   	   = cart_items.length;
		var price 			= attrs.price;
		var regular_price 	= attrs.regular_price;
		var post_id 	  	  = attrs.post_id;
		var found 			= false;
		var new_cart_item	= [];
		if(cart_items.length > 0){
			temp_items = [];
			jQuery.each(cart_items,function(cart_key,cart_item){
				if(cart_item.post_id == attrs.post_id){
					cart_item = get_item_total(cart_item,cart_item.quantity+1);
					found 	 = true;
					new_cart_item = cart_item;
				}
				temp_items.push(cart_item);
			});
		}
		
		if(found == false){
			new_cart_item = get_item_total(attrs,1);
			cart_items.push(new_cart_item);
			add_item_to_cart(new_cart_item,'add');
		}else{
			cart_items = temp_items;
			add_item_to_cart(new_cart_item,'edit');
		}
				
		jQuery.cookie("pos_cart_items", JSON.stringify(cart_items));
		
		ic_create_cart_footer();
		
		return false;
				
	});
	
	jQuery(document).on('keyup','input.quantity',function(){
		var qty 		= jQuery(this).val();
		var attrs 	  = jQuery(this).data();
		var that	   = this;
		
		if(qty == "" || qty == "0" || qty == 0 || qty == '0'){
			qty = "1";
		}
		
		qty = qty.replace(/[^\d]/g, '');
		
		//jQuery(this).val(qty);
		
		//jQuery(that).width(qty.length*9);
		
		qty = parseInt(qty);
		
		if(cart_items.length > 0){
			temp_items = [];
			jQuery.each(cart_items,function(cart_key,cart_item){
				if(cart_item.post_id == attrs.post_id){					
					cart_item = get_item_total(cart_item,qty);						
					var total_item_price = get_item_formated_price(cart_item);					
					jQuery(that).parent().parent().find('td.total_item_price').html(total_item_price);
				}
				temp_items.push(cart_item);				
			});
			
			cart_items = temp_items;
			
			ic_create_cart_footer();
		}
		return false;
	});
	
	jQuery(document).on('click','a.remove_from_cart',function(){
		var attrs 			= jQuery(this).data();
		if(cart_items.length > 0){
			temp_items = [];
			jQuery.each(cart_items,function(cart_key,cart_item){
				if(cart_item.post_id != attrs.post_id){
					temp_items.push(cart_item);
				}
			});
			
			cart_items = temp_items;
			
			jQuery(this).parent().parent().addClass("remvoe_class");
			
			jQuery(this).parent().parent().hide('fast',function(){
				ic_create_cart_footer();
				jQuery(this).remove();
				jQuery('tr.item_option_'+attrs.post_id).hide().remove();				
			});
			
			if(cart_items.length <= 0){
				clean_cart();
			}
		}
		return false;
	});
	
	
	
	jQuery(document).on('change','select.employee_name',function(){
		var attrs 			= jQuery(this).data();
		var post_id 	  	  = attrs.post_id;
		var found			= false;
		var meta_value = jQuery(this).val();
		var meta_title = jQuery(this).find("option:selected").text();
		
		if(custom_meta.length > 0){
			temp_items = [];
			jQuery.each(custom_meta,function(custom_meta_key,custommeta){
				if(custommeta.post_id == post_id){
					custommeta.meta_value = meta_value;
					custommeta.meta_title = meta_title;
					custommeta.post_id = post_id;
					found = true;
					if(meta_value){
						temp_items.push(custommeta);
					}
				}else{
					temp_items.push(custommeta);
				}
			});
			
		}
		if(found){
			custom_meta = temp_items;
		}else{
			custom_meta.push({'meta_value':meta_value,'meta_title':meta_title,'post_id' :post_id});
		}
		//console.log(custom_meta)
		jQuery.cookie("pos_custom_meta", JSON.stringify(custom_meta));
		return false;
	});
	
	jQuery(document).on('click','button.clear_cart',function(){
		
		jQuery("table.cart").find("tbody").hide('fast',function(){
			clean_cart();
		});		
		return false;
	});
	
	jQuery(document).on('click','a.add_meta',function(){
		var post_id = jQuery(this).attr('data-post_id');
		var output = "";
		meta_number = meta_number + 1;
		output +=		"<div class=\"meta_form\">";
		output += 		"<input type=\"text\" name=\"meta_key\" class=\"item_meta_key\" data-post_id=\""+post_id+"\" data-meta_number=\""+meta_number+"\" />";
		output += 		"<br><textarea name=\"meta_value\" class=\"item_meta_value\" data-post_id=\""+post_id+"\" data-meta_number=\""+meta_number+"\"></textarea>";
		output += "		<a href=\"#\" class=\"remove_meta\" data-meta_number=\""+meta_number+"\"><i class=\"fa fa-times\" aria-hidden='true'></i></a>";
		output +=		"</div>";
		
		jQuery(this).parent().parent().parent().find('td.item_meta_fields').append(output);
		return false;
	});
	
	jQuery(document).on('click','a.remove_meta',function(){
		var meta_number = jQuery(this).attr('data-meta_number');
		var found = false;
		if(item_meta.length > 0){
			temp_items = [];
			jQuery.each(item_meta,function(key,itemmeta){
				if(itemmeta.meta_number != meta_number){
					temp_items.push(itemmeta);
				}
			});
			item_meta = temp_items;
			jQuery.cookie("pos_item_meta", JSON.stringify(item_meta));
		}
		
		jQuery(this).parent().addClass("remvoe_class");
		jQuery(this).parent().hide('fast',function(){
			jQuery(this).remove();
		});
		return false;
	});
	
	jQuery(document).on('keyup','input.item_meta_key',function(){
		var post_id = jQuery(this).attr('data-post_id');
		var meta_number = jQuery(this).attr('data-meta_number');
		var meta_key = jQuery.trim(jQuery(this).val());
		var found = false;
		
		if(item_meta.length > 0){
			temp_items = [];
			jQuery.each(item_meta,function(key,itemmeta){
				if(itemmeta.meta_number == meta_number){
					itemmeta.meta_key = meta_key;
					found = true;
				}
				temp_items.push(itemmeta);
			});
		}
		
		if(found){
			item_meta = temp_items;
		}else{
			item_meta.push({'meta_key':meta_key,'meta_value':'','post_id' :post_id,'meta_number' :meta_number});
		}		
		
		jQuery.cookie("pos_item_meta", JSON.stringify(item_meta));		
	});
	
	jQuery(document).on('keyup','textarea.item_meta_value',function(){
		var post_id = jQuery(this).attr('data-post_id');
		var meta_number = jQuery(this).attr('data-meta_number');
		var meta_value = jQuery.trim(jQuery(this).val());
		var found = false;
		
		if(item_meta.length > 0){
			temp_items = [];
			jQuery.each(item_meta,function(key,itemmeta){
				if(itemmeta.meta_number == meta_number){
					itemmeta.meta_value = meta_value;
					found = true;
				}
				temp_items.push(itemmeta);
			});
		}
		
		if(found){
			item_meta = temp_items;
		}else{
			item_meta.push({'meta_key':'','meta_value':meta_value,'post_id' :post_id,'meta_number' :meta_number});
		}		
		console.log(item_meta)
		jQuery.cookie("pos_item_meta", JSON.stringify(item_meta));		
	});
	
	jQuery(document).on('click','button.add_order_note',function(){
		jQuery("tbody.order_note").show();
	});
	
	jQuery(document).on('keyup','textarea.order_note',function(){		
		jQuery.cookie("pos_order_note",  jQuery(this).val());
	});
	
	jQuery(document).on('click','button.add_shipping',function(){
		
		shipping_number = shipping_number + 1;
		
		var shipping_name = "";
		var shipping_title = "";
		
		var cartshipping = {
			'number' : shipping_number
			,'amount' : 0
			,'name' : shipping_name
			,'title' : shipping_title
		}
		
		cart_shipping.push(cartshipping);
		
		jQuery.cookie("pos_cart_shipping", JSON.stringify(cart_shipping));
		add_shippingto_cart(cartshipping,'add');
		
		jQuery("tbody.shippings").show();
		
		jQuery("tbody.shippings tr.shipping_row:last").show("slow");
		return false;
	});
	
	jQuery(document).on('click','a.remove_shipping',function(){
		var attrs 	  = jQuery(this).data();		
		if(cart_shipping.length > 0){
			temp_items = [];
			jQuery.each(cart_shipping,function(shipping_key,cartshipping){
				if(cartshipping.number != attrs.number){
					temp_items.push(cartshipping);
				}
			});
			cart_shipping = temp_items;			
			ic_create_cart_footer();			
			jQuery.cookie("pos_cart_shipping", JSON.stringify(cart_shipping));
		}
		
		jQuery(this).parent().parent().hide('fast',function(){
			jQuery(this).remove();
			jQuery('tr.shipping_option_'+attrs.number).hide().remove();
		});
		return false;
	});
	
	jQuery(document).on('keyup','input.shipping',function(){
		var shipping   = jQuery(this).val();
		var attrs 	  = jQuery(this).data();
		var that	   = this;
		
		if(shipping == "" || shipping == "0" || shipping == 0 || shipping == '0'){
			shipping = 0;
		}else{
			shipping = shipping.replace(/[^\d.]/g, '') ;            // numbers and decimals only
			shipping = shipping.replace(/(\..*)\./g, '$1');        // decimal can't exist more than once
			shipping = shipping.replace(/(\.[\d]{2})./g, '$1');
			shipping = parseFloat(shipping);
			shipping = get_decimal_value(shipping);
		}
		if(cart_shipping.length > 0){
			shipping = parseFloat(shipping);
			temp_items = [];
			jQuery.each(cart_shipping,function(shipping_key,cartshipping){
				if(cartshipping.number == attrs.number){
					cartshipping.amount = shipping;					
					jQuery(that).parent().parent().find("td.total_shipping_amount").html(get_ic_price(cartshipping.amount,true));
				}
				temp_items.push(cartshipping);				
			});			
			cart_shipping = temp_items;			
			ic_create_cart_footer();			
			jQuery.cookie("pos_cart_shipping", JSON.stringify(cart_shipping));
		}
	});
	
	
	
	jQuery(document).on('click','a.shipping_option',function(){
		var number = jQuery(this).attr('data-number');
		if(jQuery(this).hasClass("active")){
			jQuery("tr.shipping_option_"+number).hide();
			jQuery(this).removeClass("active");
		}else{
			jQuery("tr.shipping_option_"+number).show();
			jQuery(this).addClass("active");
		}
		return false;
	});
	
	
	
	jQuery(document).on('click','select.shipping_method',function(){
		var number = jQuery(this).attr('data-number');
		var method_id = jQuery(this).val();
		var shipping_title = jQuery(this).find("option:selected").text();
		temp_items = [];
		jQuery.each(cart_shipping,function(shipping_key,cartshipping){
			if(cartshipping.number == number){
				cartshipping.name = method_id;
				cartshipping.title = shipping_title;
			}
			temp_items.push(cartshipping);
		});
		cart_shipping = temp_items;
		jQuery.cookie("pos_cart_shipping", JSON.stringify(cart_shipping));
		
	});
	
	jQuery(document).on('click','button.add_fee',function(){
		fee_number = fee_number + 1;
		
		
		var cartfee = {
			'number' : fee_number
			,'amount' : 0
		}
		
		cart_fee.push(cartfee);
		
		jQuery.cookie("pos_cart_fee", JSON.stringify(cart_fee));
		
		add_fee_to_cart(cartfee,'add');
		
		jQuery("tbody.fee").show();
		jQuery("tbody.fee tr.fee_row:last").show("slow");		
		return false;
	});
	
	jQuery(document).on('click','a.remove_fee',function(){
		var attrs 	  = jQuery(this).data();		
		if(cart_fee.length > 0){
			temp_items = [];
			jQuery.each(cart_fee,function(fee_key,cartfee){
				if(cartfee.number != attrs.number){
					temp_items.push(cartfee);
				}
			});
			cart_fee = temp_items;			
			ic_create_cart_footer();			
			jQuery.cookie("pos_cart_fee", JSON.stringify(cart_fee));
		}
		
		jQuery(this).parent().parent().hide('fast',function(){
			jQuery(this).remove();
		});
		return false;
	});
	
	jQuery(document).on('keyup','input.fee',function(){
		var fee   		= jQuery(this).val();
		var attrs 	  = jQuery(this).data();
		var that	   = this;
		
		if(fee == "" || fee == "0" || fee == 0 || fee == '0'){
			fee = 0;
		}else{
			fee = fee.replace(/[^\d.]/g, '') ;            // numbers and decimals only
			fee = fee.replace(/(\..*)\./g, '$1');        // decimal can't exist more than once
			fee = fee.replace(/(\.[\d]{2})./g, '$1');
			fee = parseFloat(fee);
			fee = get_decimal_value(fee);
		}
		if(cart_fee.length > 0){
			fee = parseFloat(fee);
			temp_items = [];
			jQuery.each(cart_fee,function(fee_key,cartfee){
				if(cartfee.number == attrs.number){
					cartfee.amount = fee;					
					jQuery(that).parent().parent().find("td.total_fee_amount").html(get_ic_price(cartfee.amount,true));
				}
				temp_items.push(cartfee);				
			});			
			cart_fee = temp_items;			
			ic_create_cart_footer();			
			jQuery.cookie("pos_cart_fee", JSON.stringify(cart_fee));
		}
	});
	
	jQuery(document).on('click','button.add_customer',function(){
		jQuery('td.pos_right div.pages').hide();
		ic_create_customer();
		
		jQuery("input#copy_billing").prop('checked', false);
		jQuery('table.customer_form_table_shipping').hide();
		
		jQuery("input[name=loaded_customer_id]").val(0);
		jQuery("input[name=form_loaded]").val('no');
		jQuery("input[name=form_action]").val('creater_customer');
		
		jQuery("select#billing_country").find("option:selected").removeAttr("selected");
		jQuery("select#shipping_country").find("option:selected").removeAttr("selected");
		
		jQuery.each(customer_fields,function(form_type, customer_forms){
			jQuery.each(customer_forms.fields,function(field_name, field){
				jQuery("input[name="+field_name+"]").val('');
			});
		});
	});
	
	jQuery(document).on('click','a.load_customer_details',function(){
		ic_load_customer_details();
		return false;
	});
	
	jQuery(document).on('click','a.clear_customer',function(){
		clear_user();
		
		jQuery.each(customer_fields,function(form_type, customer_forms){
			jQuery.each(customer_forms.fields,function(field_name, field){
				jQuery("input[name="+field_name+"]").val('');
			});
		});
		return false;
	});
	
	jQuery(document).on('click','button._checkout',function(){
		jQuery('div.cart_page').hide();
		jQuery('div.checkout_page').show();
		ic_create_checkout();
	});
	
	jQuery(document).on('click','button.return_to_sale',function(){
		jQuery('div.cart_page').show();
		jQuery('div.checkout_page').hide();
		jQuery('div.customer_page').hide();
	});
	
	
	
	jQuery(document).on('click','a.show_item_option',function(){
		var attrs 	  = jQuery(this).data();
		var post_id	= attrs.post_id;
		if(jQuery(this).hasClass('active')){
			jQuery(this).removeClass("active");
			jQuery('tr.item_option_'+post_id).hide();
		}else{
			jQuery('tr.item_option_'+post_id).show();
			jQuery(this).addClass("active");
		}
		return false;
	});
	
	
	jQuery(document).on('focus','input.customer_selection',function(){
		//jQuery("input.customer_selection").removeClass('error_textbox');
	});
	
	jQuery(document).on('click','button.place_order',function(){
		
		if(form_processing){return false;}		
		
		customer_id = jQuery("input.customer_id").val();
		if(customer_id <= 0 || customer_id == ""){
			
			popup_id	= '#user_alert';
			popup_open  = true;		
			showPopup();
			
			jQuery("input.customer_selection").focus();
			return false;
		}else{
			jQuery("input.customer_selection").removeClass('error_textbox');
		}		
		place_order(customer_id);
		
	});
	
	jQuery(document).on('click','input#btnPlaceOrder',function(){
		place_order(0);
	});	
	
	jQuery(document).on('click','button.add_new_customer',function(){
		
		//console.log("1");
		
		if(form_processing) return false;
		
		form_processing = true;
		
		var customer_details = [];
		var copy_billing     = jQuery('input#copy_billing').is(':checked');		
		var enable_shipping  = settings.ship_to_countries;
		var billing_filelds  = {};
		
		var error_log		= [];
		
		jQuery.each(customer_fields,function(form_type, customer_forms){
			if(form_type == 'billing'){
				billing_filelds = customer_forms.fields;
			}
		});
		
		//console.log("2");
		
		jQuery.each(customer_fields,function(form_type, customer_forms){
			if(form_type == 'billing'){
				jQuery.each(customer_forms.fields,function(field_name, field){
					var field_value = jQuery("[name='"+field_name+"']").val();
					field_value = jQuery.trim(field_value);					
					customer_details.push({'field':field_name,'value':field_value});
					error_log = get_validate(error_log,field_name,field_value,field);
				});
			}else if(form_type == 'shipping'){
				if(enable_shipping != 'disabled'){
					if(copy_billing){
						jQuery.each(customer_forms.fields,function(field_name, field){
							var field_value = jQuery("[name='"+field_name+"']").val();
							//customer_details[field_name] = field_value;
							//customer_details.push(field_name,field_value);
							customer_details.push({'field':field_name,'value':field_value});
							error_log = get_validate(error_log,field_name,field_value,field);
						});
					}else{
						jQuery.each(billing_filelds,function(field_name, field){
							switch(field_name){
								case "billing_email":
								case "billing_phone":
									break;
								default:								
									var field_value = jQuery("[name='"+field_name+"']").val();
									field_name = field_name.replace('billing_','shipping_');				
									//customer_details[new_name] = field_value;
									//customer_details.push(new_name,field_value);
									customer_details.push({'field':field_name,'value':field_value});
								break;
							}
						});
					}
				}
			}else{
				jQuery.each(customer_forms.fields,function(field_name, field){
					var field_value = jQuery("[name='"+field_name+"']").val();
					//customer_details[field_name] = field_value;
					//customer_details.push(new_name,field_value);
					customer_details.push({'field':field_name,'value':field_value});
					error_log = get_validate(error_log,field_name,field_value,field);
				});
			}
		});
		
		
		//console.log("3");
		
		//console.log(customer_details);
		
		//console.log(error_log);
		
		jQuery("div.form_messages").removeClass('notice-error').html(i18n.please_wait).addClass('notice-info').show();
		jQuery("div.form_messages").removeClass('notice-success');		
			
		if(error_log.length >0){
			var error_string = "";
			jQuery.each(error_log,function(key, item){
				error_string += "<p>"+item.error+"</p>";
			});			
			jQuery("div.form_messages").html(error_string).removeClass('notice-info').addClass('notice-error');
			form_processing = false;
			return false;
		}else{
			form_processing = true;
		
			var form_data = {
				'action' 	   : 'point_of_sale'
				,'sub_action'  : 'creater_customer'
				,'loaded_customer_id' : jQuery("input[name=loaded_customer_id]").val()
				,'form_loaded' : jQuery("input[name=form_loaded]").val()
				,'form_action' : jQuery("input[name=form_action]").val()
				,'customer_data' : customer_details
			}
			
			//console.log(form_data);
			
			//console.log("4");
			
			jQuery.ajax({
				type		: "POST",
				url		 : ajax_url,
				data		: form_data,
				success	 : function(data) {
					
					//	console.log(data);
					
					data = JSON.parse(data);
					
					//console.log(data);
					
					if(data.error == true){
						jQuery('div#form_messages').addClass('notice-error').html(data.error_message);
					}
					
					if(data.success == true){
						jQuery('div#form_messages').addClass('notice-success').html(data.success_message);
					}
					
					if(data.customer_id > 0){
						
						//console.log(data);
												
						customer_id = data.customer_id;
						customer_label = data.customer_label;
						
						jQuery.cookie("pos_customer_id", customer_id);
						jQuery.cookie("pos_customer_label", customer_label);				
						
						jQuery('input.customer_id').val(customer_id);
						jQuery('input.customer_selection').val(customer_label);
						jQuery('input.loaded_customer_id').val(customer_id);
						
						jQuery.cookie("pos_order_tax_country", data.customer_country);
						jQuery.cookie("pos_order_tax_state", data.customer_state);
						jQuery.cookie("pos_order_tax_city", data.customer_city);
						jQuery.cookie("pos_order_tax_postcode", data.customer_postcode);
						ic_create_cart_footer();
					}
					
					form_processing = false;
				},
				error: function(jqxhr, textStatus, error ){
					form_processing = false;
				}
			});//End On Ajax
			return false;
		}
	});
	
	jQuery(document).on('change','input#copy_billing',function(){
		if(jQuery(this).is(':checked')){
			jQuery('table.customer_form_table_shipping').fadeIn();
		}else{
			jQuery('table.customer_form_table_shipping').fadeOut();
		}
	});
	
	jQuery(document).on('change','select#billing_country, select#shipping_country',function(){
		
		if(form_processing) return false;
		
		var country = jQuery(this).val();		
		if(country == ""){
			form_processing = false;
			return false;
		}
		
		var form_type = jQuery(this).attr('data-form_type');
		
		if ( country in state_catche ) {
			form_processing = false;
			jQuery("td.field_"+form_type+"_state").html(state_catche[country]);
			return false;
		}
		
		form_processing = true;
		jQuery("td.field_"+form_type+"_state").html("<p>"+i18n.please_wait+"</p>");
		
		var form_data = {
			'action' 	   : ajax_action
			,'sub_action'  : 'search_user'
			,'form_action' : 'country_state_field'
			,'country' 	 : country
			,'form_type'   : form_type
		}
		
		jQuery.ajax({
			type		: "POST",
			url		 : ajax_url,
			data		: form_data,
			success	 : function(data) {
				jQuery("td.field_"+form_type+"_state").html(data);
				state_catche[country] = data;
				form_processing = false;
				
			},
			error: function(jqxhr, textStatus, error ){
				form_processing = false;
			}
		});//End On Ajax
		
		return false;
		
	});
	
	var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();
	
	jQuery(document).on('keyup','input.search_product',function(){
		
		var new_search = jQuery("input#search_product").val();
		
		if(last_searched == new_search){
			form_processing = false;
			return true;
		};
		
		if(xhr_product_ajax != null){
			xhr_product_ajax.abort();
			search_product();
		}else{
			if(form_processing){
				pending_search = true;
			}else{
				 delay(function(){
					search_product();
				}, 500 );
			}
		}
	});
	
	jQuery("div.left_pages").scroll(function () {
		if(all_product_loaded) return false;
		if (jQuery("div.left_pages").scrollTop() == (jQuery("div.product_list_box").height() - jQuery("div.left_pages").height())) {			
			load_products_grid();
		}
	});
	
	jQuery('.ic_close_popup').click(function(){
		hidePopup();
	});
	
	function place_order(customer_id){
		payment_method_id = jQuery("select#payment_gateway").val();
		
		var new_data = {
			'action' 		      : 'point_of_sale'
			,'sub_action'         : 'place_order'
			,'order_details'      : order_details
			,'discount'           : discount
			,'sub_stotal'         : sub_stotal
			,'order_total'   		: order_total
			,'payment_method_id'  : payment_method_id			
			,'order_note'         : jQuery.cookie("pos_order_note")
			,'cart_items'         : cart_items
			,'cart_shipping'      : cart_shipping
			,'cart_fee'           : cart_fee
			,'custom_meta'        : custom_meta
			,'item_meta'          : item_meta
			,'user_id'            : customer_id
			,'customer_id'        : customer_id
			,'customer_user'      : customer_id			
			,'tax_country'        : jQuery.cookie("pos_order_tax_country")
			,'tax_state'       	  : jQuery.cookie("pos_order_tax_state")
			,'tax_city'           : jQuery.cookie("pos_order_tax_city")
			,'tax_postcode'       : jQuery.cookie("pos_order_tax_postcode")
		};
		
		form_processing = true;
		loading_please_wait();
		//alert(customer_id)
		jQuery.ajax({
			type		: "POST",
			url		 : ajax_url,
			data		: new_data,
			//dataType	: 'json',
			success		:function(data) {				
				receipt_data = JSON.parse(data);
				jQuery("div.cart_page").fadeOut();
				jQuery("div.thankyou_page").html(receipt_data.receipt_content).fadeIn();		
				jQuery("div.left_overlay").hide();
				
				new_receipt_id = receipt_data.receipt_id;
				window.location.hash = '/receipt/'+receipt_data.receipt_id;
				
				jQuery.each(cart_items,function(cart_key,cart_item){
					var post_id = cart_item.post_id;
					var quantity = cart_item.quantity;
					var that = jQuery("span.stock_post_id_"+post_id);
					var last_stock = that.attr("data-stock");
					var new_quantity = parseInt(last_stock) - quantity;
					that.html(i18n.n_in_stock.replace("%s",new_quantity));
					that.attr("data-stock",new_quantity);
					if(print_preview){
						jQuery("button.close_print_window").show();
						//window.print();
					}else{
						jQuery("button.close_print_window").hide();
					}
				});
				
				form_processing = false;
				
				clean_cart();
				
			},
			error: function(jqxhr, textStatus, error ){
				form_processing = false;
				jQuery("div.left_overlay").hide();
			}
		});//End On Ajax
	}
	
	function get_receipt_contant(receipt_id){
		/*
		var receipt_contant = jQuery.cookie("receipt_contant_"+receipt_id);
		if(receipt_contant){
			jQuery("div.cart_page").hide("fast");
			jQuery("div.thankyou_page").html(data).fadeIn();		
			jQuery("div.left_overlay").hide();
			form_processing = false;				
			clean_cart();
			load_settings();
			return false;
		}		
		*/
		var new_data = {
			'action' 		      : 'point_of_sale'
			,'sub_action'         : 'receipt_content'
			,'order_details'      : order_details
			,'receipt_id'           : receipt_id
		};
		
		form_processing = true;
		jQuery("div.left_overlay").show().css({"opacity":.5});
		
		jQuery.ajax({
			type		: "POST",
			url		 : ajax_url,
			data		: new_data,
			success		:function(data) {
				
				jQuery.cookie("receipt_contant_"+receipt_id,$.trim(data));				
				//var receipt_contant = jQuery.cookie("receipt_contant_"+receipt_id);				
				jQuery("div.cart_page").hide("fast");
				jQuery("div.thankyou_page").html(data).fadeIn();		
				jQuery("div.left_overlay").hide();
				form_processing = false;				
				clean_cart();
				load_settings();
				if(print_preview){
					jQuery("button.close_print_window").show();
					//window.print();
					//document.addEventListener("contextmenu", function(e){e.preventDefault();}, false);
				}else{
					jQuery("button.close_print_window").hide();
				}
			},
			error: function(jqxhr, textStatus, error ){
				form_processing = false;
				jQuery("div.left_overlay").hide();
			}
		});//End On Ajax
	}
		
	function get_validate(error_log,field_name,field_value,field){
		if(field.required){
			if(field_value == ""){
				switch(field.type){
					case 'text':
						//error_log["required_"+field_name] = i18n.required_text_field.replace('%s',field.label);
						
						error_log.push({'error' : i18n.required_text_field.replace('%s',field.label)});
						break;
					case 'select':
						//error_log["required_"+field_name] = i18n.required_select_field.replace('%s',field.label);
						error_log.push({'error' : i18n.required_select_field.replace('%s',field.label)});
						break;
				}
			}else{
				if(field_name == 'billing_email'){
					if(isEmail(field_value) == false){
						//error_log["email_"+field_name] = i18n.required_email_field;						
						error_log.push({'error' : i18n.required_email_field});
					}
				}
			}
		}
		return error_log;
	}
	
	function isEmail(email) {
	  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	  return regex.test(email);
	}
	
	function ic_create_customer(){
						
		if(jQuery('div.customer_page').hasClass('form_added')){
			jQuery('table.customer_form_table_shipping').hide();
			jQuery('div.customer_page').show();
			
			jQuery("input[name=form_action]").val('creater_customer');
			jQuery("table.customer_form").find("thead tr th").html(i18n.add_new_customer);
			jQuery("button.add_new_customer").html(i18n.btn_add_new_customer);
			
			return true;
		}
		
		var output = "";
		output += "<table class=\"customer_form\">";
		output += "	<thead>";
		output += "		<tr>";
		output += "			<th class=\"center_align\" colspan=\"1\">"+i18n.add_new_customer+"</th>";
		output += "		</tr>";
		output += "	</thead>";
		
		output += "	<tbody>";
		output += "		<tr>";
		output += "			<td class=\"center_align\" colspan=\"1\">";
		
			output += "<div class=\"form_messages\" id=\"form_messages\"></div>";
			
			jQuery.each(customer_fields,function(form_type, customer_forms){
				
					output += "<div class=\"form_type form_type_"+form_type+"\">";
						
							if(form_type == 'shipping'){
								output += "<label for=\"copy_billing\" class=\"label_copy_billing\">" + customer_forms.title;
								output += "</label>";
								output += " <input type=\"checkbox\" id=\"copy_billing\" name=\"copy_billing\" value=\"button\">";
							}else{
								output += "<h3>" + customer_forms.title + "</h3>";
							}
							output += "<table class=\"customer_form_table customer_form_table_"+form_type+"\">";
								jQuery.each(customer_forms.fields,function(field_name, field){
									output += "<tr>";
										output += "<th class=\"form_label\">";
											output += "<label for=\""+field_name+"\" class=\"form_label\">";
												output += field.label + ":";
												if(field.required){
													output += "<strong>*</strong>";
												}
											output += "</label>";
										output += "<td class=\"form_field field_"+field_name+"\">";
											switch(field.type){
												case "text":
													output += "<input type=\"text\" name=\""+field_name+"\" id=\""+field_name+"\" value=\"\" />";
													break;
												case "select":
													output += "<select name=\""+field_name+"\" id=\""+field_name+"\" data-form_type=\""+form_type+"\" >";
														jQuery.each(field.options,function(option_value, option_label){
															output += "<option value=\""+option_value+"\">" + option_label +  "</opiton>";
														});
													output += "</select>";
													break;
												default:
													output += field.type;
													break;
											}
											
										output += "</td>";
									output += "</tr>";
								});
							output += "</table>";
						
					output += "</div>";
				
			});
		output += "			</td>";
		output += "		</tr>";
		output += "	</tbody>";
		
		output += "	<tfoot>";
		output += "		<tr>";
		output += "			<td class=\"left_align\" colspan=\"1\">";
		output += 					" <button type=\"button\" name=\"return_to_sale\" class=\"ic_button return_to_sale\">"+i18n.return_to_sale+"</button> ";
		output += 					" <button type=\"button\" name=\"add_new_customer\" class=\"ic_button add_new_customer\">"+i18n.btn_add_new_customer+"</button> ";
		output += 					" <input type=\"hidden\" name=\"loaded_customer_id\" class=\"ic_button loaded_customer_id\" value=\"0\">";
		output += 					" <input type=\"hidden\" name=\"form_loaded\" class=\"ic_button form_loaded\" value=\"no\">";
		output += 					" <input type=\"hidden\" name=\"form_action\" class=\"ic_button form_action\" value=\"creater_customer\">";
		output += "			</td>";
		output += "		</tr>";
		output += "	</tfoot";
		
		output += "</table>";
		
		jQuery('div.customer_page').addClass('form_added').html(output).show();
		jQuery('table.customer_form_table_shipping').hide();
	}
	
	
	function ic_load_customer_details(){
		
		jQuery('td.pos_right div.pages').hide();
		ic_create_customer();
		
		jQuery("input[name=form_action]").val('update_customer');
		jQuery("table.customer_form").find("thead tr th").html(i18n.update_customer);
		jQuery("button.add_new_customer").html(i18n.btn_update_customer);
		
		var loaded_customer_id = jQuery("input[name=loaded_customer_id]").val();		
		if(loaded_customer_id == customer_id){
			jQuery("div.form_messages").hide();
			return false;
		}
		
		jQuery("div.form_messages").removeClass('notice-error').html(i18n.please_wait).addClass('notice-info').show();
		
		if(form_processing) return false;
		
		jQuery("input[name=form_loaded]").val('no');
		
		form_processing = true;
		
		var form_data = {
			'action' 	   : 'point_of_sale'
			,'sub_action'  : 'search_user'
			,'form_action' : 'customer_details'
			,'customer_id' : customer_id
		}
		
		jQuery.ajax({
			type		: "POST",
			url		 : ajax_url,
			data		: form_data,
			success	 : function(data) {				
				data = JSON.parse(data);
				
				jQuery.each(data.customer_details,function(field_name, field_value){					
					jQuery("[name="+field_name+"]").val(field_value);				
				});
				
				jQuery("td.field_billing_state").html(data.field_billing_state);
				jQuery("td.field_shipping_state").html(data.field_shipping_state);
				
				jQuery("input#copy_billing").prop('checked', true);
				jQuery('table.customer_form_table_shipping').fadeIn();
								
				form_processing = false;
				jQuery("div.form_messages").removeClass('notice-info').html('').hide();
				jQuery("input[name=loaded_customer_id]").val(customer_id);
				jQuery("input[name=form_loaded]").val('yes');
				
				jQuery('span.guest_customer').fadeOut();
			},
			error: function(jqxhr, textStatus, error ){
				form_processing = false;
			}
		});//End On Ajax
	}
	
	function ic_create_checkout(){
		
		var output = "";
		output += "<table class=\"checkout_form\">";
		output += "	<thead>";
		output += "		<tr>";
		output += "			<th class=\"center_align\" colspan=\""+(df-4)+"\">"+ i18n.to_pay+" "+get_ic_price(order_total)+"</th>";
		output += "		</tr>";
		output += "	</thead>";
		
		output += "	<tbody>";
		output += "		<tr>";
		output += "			<td class=\"center_align\" colspan=\"1\">";
		output += "<div id=\"accordion\">";
			var i = 0;
			jQuery.each(payment_gateways,function(method_id,method_name){
				output += " <h3 id=\" "+ method_id + "\">"+method_name+"</h3>";
				output += " <div>";
					output += method_name;
				output += " </div>";					
				if(i == 0){
					payment_method_id = method_id;
				}i++;
			});
		output += "</div>";
		output += "			</td>";
		output += "		</tr>";
		output += "	</tbody>";
		
		output += "	<tfoot>";
		output += "		<tr>";
		output += "			<td class=\"left_align\">";		
		output += 					"<button type=\"button\" name=\"return_to_sale\" class=\"ic_button return_to_sale\" value=\""+i18n.btn_return_to_sale+"\">"+i18n.btn_return_to_sale+"</button>";		
		output += "			</td>";
		
		output += "			<td class=\"right_align\">";
		output += 				"<button type=\"button\" name=\"place_order\" class=\"ic_button place_order\" value=\""+i18n.btn_place_order+"\">"+i18n.btn_place_order+"</button>";		
		output += "			</td>";
		output += "		</tr>";
		output += "	</tfoot";
		
		output += "</table>";
		
		jQuery('div.checkout_page').html(output).show();
		
		jQuery("#accordion").accordion({
			activate: function( event, ui ) {
				payment_method_id = jQuery(ui.newHeader[0]).attr("id");				
			}
		});
	}
	
	function add_item_to_cart(cart_item, action){
		var total_item_price = get_item_formated_price(cart_item);
		var post_id = cart_item.post_id;
		
		/*Edit Action*/
		if(action == 'edit'){
			jQuery('tr.cart_'+post_id).find('td.total_item_price').html(total_item_price);
			jQuery('tr.cart_'+post_id).find('input.quantity').val(cart_item.quantity);
			return 'edited';
		}		
		
		/*Add Action*/
		output = "";
		output += "		<tr  class=\"item cart_"+post_id+"\" style=\"display:none;\">";
		output += "			<td class=\"left_align\">";
		output += "				<input type=\"text\" name=\"qty\" id=\"qty1\" value=\""+ cart_item.quantity+"\" class=\"quantity\"  data-post_id=\"" + cart_item.post_id + "\" maxlength=6 />";
		//output += 				cart_item.quantity;
		output += "			</td>";
		output += "			<td>";
		output += "				<h4>"+cart_item.post_title+"</h4>";
		output += "			</td>";
		if(enable_employee_column){
			output += "	<td class=\"item_meta_custom_field\">";
				var employee_id = 0;
				if(custom_meta.length > 0){
					jQuery.each(custom_meta,function(custom_meta_key,custommeta){
						if(custommeta.post_id == cart_item.post_id){
							employee_id = custommeta.meta_value;
						}
					});
				}
				output += "<select type=\"text\" class=\"employee_name\" name=\"employee_name\" data-post_id=\"" + cart_item.post_id + "\">";
					output += "<option value=\"\">" + i18n.select_employee +  "</opiton>";
					jQuery.each(employee,function(key, option){
						if(employee_id == option.id){
							output += "<option value=\""+option.id+"\" selected=\"selected\">" + option.employee_name +  "</opiton>";
						}else{
							output += "<option value=\""+option.id+"\">" + option.employee_name +  "</opiton>";
						}
					});
				output += "</select>";
				
			output += "</td>";
		}
		output += "			<td class=\"right_align item_price\">";
		//output += "			 <input type=\"text\" name=\"qty\" id=\"qty1\" value=\""+ cart_item.price+"\" />";
		output += 			 	"<a href=\"#\" class=\"show_item_option\" data-post_id=\""+cart_item.post_id+"\">"+i18n.more+"</a>";
		output += 				get_ic_price(cart_item.price,true);
		output += "			</td>";
		output += "			<td class=\"right_align total_item_price\">";
		output += 				total_item_price;
		output += "			</td>";
		output += "			<td class=\"right_align\">";
		output += "				<a href=\"#\" class=\"remove_from_cart\" data-post_id=\"" + cart_item.post_id + "\"><i class=\"fa fa-times\" aria-hidden='true'></i></a>";
		output += "			</td>";
		output += "		</tr>";
		output += "		<tr style=\"display:none\" class=\"item_option_"+cart_item.post_id+"\">";
		output += "			<td colspan=\""+df+"\">";
								output += "<table class=\"item_meta\">";
									output += "<tr>";
										output += "	<td></td>";
										
									output += "</tr>";											
									output += "<tr>";
										output += "	<td></td>";
										output += "	<td class=\"item_meta_fields\">";
											if(custom_meta.length > 0){
												jQuery.each(item_meta,function(key,itemmeta){
													if(itemmeta.post_id == cart_item.post_id){
														meta_number = itemmeta.meta_number;
														console.log(meta_number)
														output +=		"<div>";
														output += 		"<br><input type=\"text\" name=\"meta_key\" class=\"item_meta_key\" data-post_id=\""+post_id+"\" data-meta_number=\""+meta_number+"\" value=\""+ itemmeta.meta_key+"\" />";
														output += 		"<br><textarea name=\"meta_value\" class=\"item_meta_value\" data-post_id=\""+post_id+"\" data-meta_number=\""+meta_number+"\">"+itemmeta.meta_value+"</textarea>";
														output += "		<a href=\"#\" class=\"remove_meta\" data-meta_number=\""+meta_number+"\">Remove Meta</a>";
														output +=		"</div>";
													}
												});
												meta_number = parseInt(meta_number);
											}
										output += "</td>";
									output += "</tr>";
									output += "<tr>";
										output += "	<td></td>";
										output += "	<td class=\"right_align\">";
										output += "				<a href=\"#\" class=\"add_meta\" data-post_id=\"" + cart_item.post_id + "\">"+i18n.add_meta+"</a>";
										output += "   </td>";
									output += "</tr>";
								output += "	</table>";
		
		output += "			</td>";
		output += "		</tr>";
		
		if(action == 'add'){
			jQuery('tbody.item').append(output).show();
			return 'added';
		}
		return output;
	}
	
	function add_shippingto_cart(cartshipping,action){
		
		output = "";
		output += "		<tr class=\"shipping_row\" style=\"display:none;\">";
		output += "			<td class=\"left_align\">";
		output += "			</td>";
		output += "			<td colspan=\""+(df-4)+"\" class=\"right_align\">";
		output += "				<h4>"+i18n.shipping+"</h4>";
		output += "			</td>";
		output += "			<td class=\"right_align shipping_amount\">";
		output += 			 "<a href=\"#\" class=\"shipping_option\" data-number=\""+cartshipping.number+"\">"+i18n.more+"</a>";
		output += "			<input type=\"text\" name=\"shipping["+cartshipping.number+"]\" id=\"shipping\" ";
		output += " 				class=\"shipping\" value=\""+get_decimal_value(cartshipping.amount)+"\"";						
		output += " 				data-number=\""+cartshipping.number+"\"";
		output += " 			/>";
		output += "			</td>";
		output += "			<td class=\"right_align total_shipping_amount\">";
		output += 				get_ic_price(cartshipping.amount,true);
		output += "			</td>";
		output += "			<td class=\"right_align\">";
		output += "				<a href=\"#\" class=\"remove_shipping\" data-number=\""+cartshipping.number+"\"><i class=\"fa fa-times\" aria-hidden='true'></i></a>";
		output += "			</td>";
		output += "		</tr>";
							
		output += "		<tr style=\"display:none\" class=\"shipping_option_"+cartshipping.number+"\">";
		output += "			<td class=\"left_align\"></td>";
		output += "			<td class=\"right_align\" colspan=\""+(df-4)+"\">";
		output += "				<h4>"+i18n.shipping_method+"</h4>";
		
		output += "			</td>";
		output += "			<td class=\"left_align\" colspan=\"3\">";
		output += "			<select class=\"shipping_method\" name=\"shipping_method_id["+cartshipping.number+"]\" data-number=\""+cartshipping.number+"\">";
		
		jQuery.each(shipping_methods,function(method_id,method_label){
			
			if(cartshipping.name == method_id){
				output += "<option value=\""+ method_id +"\" selected=\"selected\">"+ method_label +"</option>";
			}else{
				output += "<option value=\""+ method_id +"\">"+ method_label +"</option>";
			}
		});
		
		output += "			</select>";
		output += "			</td>";
		output += "		</tr>";
		
		if(action == 'add'){
			jQuery('tbody.shippings').append(output).show();
			return 'added';
		}
		return output;
	}
	
	function add_fee_to_cart(cartfee,action){
		
		var output = "";
		output += "		<tr class=\"fee_row\" style=\"display:none;\">";
		output += "			<td class=\"left_align\">";
		output += "			</td>";
		output += "			<td colspan=\""+(df-4)+"\" class=\"right_align\">";
		output += "				<h4>"+i18n.fee+"</h4>";
		output += "			</td>";
		output += "			<td class=\"right_align fee_amount\">";		
		output += 				"<input type=\"text\" name=\"fee["+cartfee.number+"]\" id=\"fee\" class=\"fee\" value=\""+get_decimal_value(cartfee.amount)+"\" data-number=\""+cartfee.number+"\" />"
		output += "			</td>";
		output += "			<td class=\"right_align total_fee_amount\">";
		output += 				get_ic_price(cartfee.amount,true);
		output += "			</td>";
		output += "			<td class=\"right_align\">";
		output += "				<a href=\"#\" class=\"remove_fee\" data-number=\""+cartfee.number+"\"><i class=\"fa fa-times\" aria-hidden='true'></i></a>";
		output += "			</td>";
		output += "		</tr>";
		
		if(action == 'add'){
			jQuery('tbody.fee').append(output).show();
			return 'added';
		}
		
		return output;
	}
	
	function ic_create_cart(){
		
		var output = "";
		output += "<table class=\"cart\">";
		output += "	<thead>";
		output += "		<tr>";
		output += "			<th class=\"left_align\">"+i18n.column_quanity+"</th>";
		output += "			<th>"+i18n.column_product+"</th>";
		if(enable_employee_column){
			output += "			<th>"+i18n.column_employee+"</th>";			
		}
		output += "			<th class=\"right_align\">"+i18n.column_price+"</th>";
		output += "			<th class=\"right_align\">"+i18n.column_total+"</th>";
		output += "			<th class=\"remove_item\"></th>";
		output += "		</tr>";
		output += "	</thead>";
		if(cart_items.length > 0){
			output += "<tbody class=\"item\">";
			jQuery.each(cart_items,function(cart_key,cart_item){
				output += add_item_to_cart(cart_item,'return');
			});
			output += "</tbody>";
		}else{
			output += "<tbody class=\"item\" style=\"display:none\">";
			output += "</tbody>";
		}
		
		if(cart_shipping.length > 0){
			shipping_number = cart_shipping.length;
			output += "<tbody class=\"shippings\">";
				jQuery.each(cart_shipping,function(shipping_key,cartshipping){
					output += add_shippingto_cart(cartshipping,'return');					
				});			
			output += "</tbody>";
		}else{
			output += "<tbody class=\"hide_tbody shippings\">";
			output += "</tbody>";
		}
		
		if(cart_fee.length > 0){
			fee_number = cart_fee.length;
			output += "<tbody class=\"fee\">";
				jQuery.each(cart_fee,function(fee_key,cartfee){
					output += add_fee_to_cart(cartfee,'return');					
				});			
			output += "</tbody>";
		}else{
			output += "<tbody class=\"hide_tbody fee\">";
			output += "</tbody>";
		}
		
		output += "<tbody class=\"cart_total\">";
		output += "<tr>";
			if(cart_items.length <= 0){
				output += "	<td colspan=\""+df+"\" class=\"left_align empty_cart\">";
				output += 		i18n.your_cart_empty;
				output += "	</td>";
			}
			output += "</tr>";
		output += "</tbody>";
		
		var pos_order_note = jQuery.cookie("pos_order_note");
		
		if(pos_order_note == undefined){
			pos_order_note = "";
		}
		
		output += "<tbody class=\"order_note\" style=\"display:none\">";
			output += "<tr>";
			output += "	<td colspan=\""+df+"\">";
			output += 		"<textarea name=\"order_note\" class=\"order_note\">"+pos_order_note+"</textarea>";
			output += "	</td>";
			output += "</tr>";
		output += "</tbody>";
		
		output += "<tbody class=\"cart_buttons\" style=\"display:none\">";
			output += "<tr>";
				output += "	<td colspan=\""+(df)+"\" style=\"padding:0\">";				
				output += "<table style=\"width:100%\" border=\"0\">";
					output += "<tbody>";
					output += "<tr>";
						output += "<td style=\"padding-bottom:0\">";
							output += 		"<input type=\"hidden\" name=\"customer_id\" class=\"customer_id\" value=\"0\">";
							output += 		"<input type=\"text\" name=\"customer_selection\" class=\"customer_selection\" value=\"\" placeholder=\""+ i18n.customer_selection+"\">";
							output += 		" &nbsp; <a href=\"#\" class=\"load_customer_details\" style=\"display:none;\">"+i18n.customer_details+"</a>";
							output += 		" &nbsp; <a href=\"#\" class=\"clear_customer\" style=\"display:none;\">"+i18n.btn_clear_customer+"</a>";
							output += 		" &nbsp; <span class=\"guest_customer\">"+i18n.guest_customer+"</span>";
							output += "	</td>";
							var defalut_payment = 'cod'
							output += "	<td style=\"text-align:right;padding-bottom:0\">";
								output += "<select name=\"payment_gateway\" id=\"payment_gateway\">";
								jQuery.each(payment_gateways,function(method_id,method_name){
									if(defalut_payment == method_id){
										output += "<option value=\""+method_id+"\" selected=\"selected\">"+method_name+"</option>";
									}else{
										output += "<option value=\""+method_id+"\">"+method_name+"</option>";
									}
								});
								output += "</select>";
						output += "</td>";
					output += "</tr>";
					output += "</tbody>";
				output += "</table>";
				output += "	</td>";
			output += "</tr>";
			
			output += "<tr>";
				output += "	<td colspan=\""+(df)+"\" style=\"padding:0\">";
				
						output += "<table style=\"width:100%\" border=\"0\">";
							output += "<tbody>";
							output += "<tr>";
									output += "<td>";
										output += 		"<button type=\"button\" name=\"clear_cart\" class=\"ic_button clear_cart\" value=\""+i18n.btn_clear_cart+"\">"+i18n.btn_clear_cart+"</button>";
									output += "	</td>";
									output += "	<td class=\"right_align\">";
										output += 		"<button type=\"button\" name=\"add_customer\" class=\"ic_button add_customer\" value=\""+i18n.btn_new_customer+"\">"+i18n.btn_new_customer+"</button>";
										output += 		"<button type=\"button\" name=\"add_order_note\" class=\"ic_button add_order_note\" value=\""+i18n.btn_order_note+"\">"+i18n.btn_order_note+"</button>";
										output += 		"<button type=\"button\" name=\"add_fee\" class=\"ic_button add_fee\" value=\"Fee\">"+i18n.btn_fee+"</button>";
										
										if(settings.ship_to_countries != 'disabled'){
											output += 		"<button type=\"button\" name=\"add_shipping\" class=\"ic_button add_shipping\" value=\""+i18n.btn_shipping+"\">"+i18n.btn_shipping+"</button>";
										}
										
										//output += 		"<button type=\"button\" name=\"checkout\" class=\"checkout\" value=\"Checkout\">"+i18n.btn_chekout+"</button>";
									output += "</td>";
							output += "</tr>";
							output += "</tbody>";
						output += "</table>";
						output += "</td>";
						output += "</tr>";
						
						output += "<tr>";
						output += "	<td colspan=\""+df+"\" class=\"right_align\">";				
						//output += 		"<button type=\"button\" name=\"checkout\" class=\"checkout\" value=\""+i18n.btn_chekout+"\">"+i18n.btn_chekout+"</button>";
						output += 		"<button type=\"button\" name=\"checkout\" class=\"ic_button checkout place_order\" value=\""+i18n.btn_place_order+"\">"+i18n.btn_place_order+"</button>";
				output += "	</td>";
				output += "</tr>";				
		output += "</tbody>";
		
	    output += "</table>";
		
		jQuery('div.cart_page').html(output);
		
		if(customer_id > 0){
			jQuery("input.customer_id").val(customer_id);
			jQuery("input.customer_selection").val(customer_label);		
			jQuery('a.load_customer_details').fadeIn();
			jQuery('a.clear_customer').fadeIn();
			jQuery('span.guest_customer').fadeOut();
		}else{
			jQuery('a.load_customer_details').fadeOut();
			jQuery('a.clear_customer').fadeOut();
			jQuery('span.guest_customer').fadeIn();
		}
		
		ic_create_cart_footer();
		
		var serachurl =  ajax_url+"?action="+ajax_action+"&sub_action=search_user&form_action=search_user"
		jQuery("input.customer_selection").autocomplete({
            source: function(request, response){
				var term = request.term;
				if ( term in user_cache ) {
					response(user_cache[term]);
				    return;
				}
				
				jQuery.getJSON(serachurl, request, function( data, status, xhr ) {
					user_cache[term] = data;					
					response(data);
					return;
				});
				
			},select: function(event, ui) {
				
				//console.log(ui.item);
				
				customer_id = ui.item.id;
				customer_label = ui.item.value;
								
				jQuery('input.customer_id').val(customer_id);
				jQuery.cookie("pos_customer_id", customer_id);
				jQuery.cookie("pos_customer_label", customer_label);
				
				
				jQuery.cookie("pos_order_tax_country", ui.item.customer_country);
				jQuery.cookie("pos_order_tax_state", ui.item.customer_state);
				jQuery.cookie("pos_order_tax_city", ui.item.customer_city);
				jQuery.cookie("pos_order_tax_postcode", ui.item.customer_postcode);
				
				jQuery('a.load_customer_details').fadeIn();
				jQuery('a.clear_customer').fadeIn();
				jQuery('span.guest_customer').fadeOut();
				jQuery("input.customer_selection").removeClass('error_textbox');
				
				ic_create_cart_footer();
				
			},
			focus: function() {
			  return true;
			},
            minLength: 1
        });
		
	}
	
	function get_available_customer_tax(){
		
		new_tax_rates = [];
		
		var ot_country = "";
		var ot_state = "";
		var ot_city = "";
		var ot_postcode = "";
		
		if(customer_id <=0 || customer_id ==""){
			jQuery.cookie("pos_order_tax_country", settings.base_country);
			jQuery.cookie("pos_order_tax_state", settings.base_state);
			jQuery.cookie("pos_order_tax_city", settings.base_city);
			jQuery.cookie("pos_order_tax_postcode", settings.base_postcode);
			//console.log("customer_id:-" + customer_id);
		}
		
		if(customer_id > 0 || customer_id =="" || settings.tax_based_on == "base"){
			ot_country 	= jQuery.cookie("pos_order_tax_country");
			ot_state 	  = jQuery.cookie("pos_order_tax_state");
			ot_city 	   = jQuery.cookie("pos_order_tax_city");
			ot_postcode   = jQuery.cookie("pos_order_tax_postcode");
			
			//console.log("customer_id:-" + customer_id);
			
			
			var ot_valid = false;
			jQuery.each(tax__rates,function(arraykey,taxrate){
				if(taxrate.tax_rate_country == "" && taxrate.tax_rate_state == "" && taxrate.tax_rate_city == "" && taxrate.tax_rate_postcode == ""){
					new_tax_rates.push(taxrate);
				}else{
					var v_country = v_state = v_city = v_postcode = true;
					
					if(taxrate.tax_rate_country != ""){
						if(taxrate.tax_rate_country != ot_country){
							v_country = false;
						}
					}
					
					if(taxrate.tax_rate_state != ""){
						if(taxrate.tax_rate_state != ot_state){
							v_state = false;
						}
					}
					
					if(taxrate.tax_rate_city != ""){
						if(taxrate.tax_rate_city != ot_city){
							v_city = false;;
						}
					}
					
					if(taxrate.tax_rate_postcode != ""){
						if(taxrate.tax_rate_postcode != ot_postcode){
							v_postcode = false;;
						}
					}
					
					if(v_country && v_state && v_city && v_postcode){
						new_tax_rates.push(taxrate);
					}						
				}					
			});
			//console.log(settings);
		}else{
			var ot_valid = false;
			jQuery.each(tax__rates,function(arraykey,taxrate){
				new_tax_rates.push(taxrate);	
			});
		}
				
		//console.log(new_tax_rates);		
		return new_tax_rates;
	}
	
	function ic_create_cart_footer(){
		if(cart_items.length > 0){
			sub_stotal  = 0;
			discount 	= 0;
			order_total = 0;
			rowspan 	 = 3;
			jQuery.each(cart_items,function(cart_key,cart_item){
				sub_stotal = sub_stotal + get_ic_price(cart_item.line_total,false);
				discount = discount + (cart_item.line_subtotal - cart_item.line_total);
				
				order_total = sub_stotal;
			});
			
			if(cart_shipping.length > 0){
				jQuery.each(cart_shipping,function(shipping_key,cartshipping){
					order_total = order_total + cartshipping.amount;
				})
			}
			
			if(cart_fee.length > 0){
				jQuery.each(cart_fee,function(fee_key,cartfee){
					order_total = order_total + cartfee.amount;
				})
			}
			
			if(settings.calc_taxes == 'yes'){
				
				//console.log(tax_rates);
				//console.log(tax__rates);
				
				new_tax_rates = get_available_customer_tax();
				jQuery.each(new_tax_rates,function(arraykey,taxrates){
					rowspan 	 = rowspan  + 1;					
				});
				/*
				jQuery.each(tax_rates,function(arraykey,taxrates){
					jQuery.each(taxrates,function(array_key,tax_rate){
						rowspan 	 = rowspan  + 1;
					});
				});
				*/
			}
					
			
			var output = "";
			output += "<tr>";
			output += "	<td colspan=\""+(df-3)+"\" rowspan=\""+rowspan+"\"></td>";
			output += "	<th class=\"right_align sub_stotal\" valign=\"middle\">";
			output += i18n.subtotal
			output += "	</th>";
			output += "	<td class=\"right_align sub_stotal\" valign=\"middle\">";
			output += 		get_ic_price(sub_stotal,true);
			output += "</td>";
			output += "	<td></td>";
			output += "</tr>";
			if(discount > 0){
				output += "<tr>";
				//output += "	<td colspan=\"3\"></td>";
				output += "	<th class=\"right_align discount\" valign=\"middle\">";
				output += i18n.discount
				output += "	</th>";
				output += "	<td class=\"right_align discount\" valign=\"middle\">-";
				output += 		get_ic_price(discount,true);
				output += "</td>";
				output += "	<td></td>";
				output += "</tr>";
			}
			var total_tax = 0;
			var _tax = 0;
			if(settings.calc_taxes == 'yes'){
				
				//console.log(tax_rates);
				//console.log(new_tax_rates);
				
				jQuery.each(new_tax_rates,function(arraykey,tax_rate){
					//jQuery.each(taxrates,function(array_key,tax_rate){
						
						
						
						//console.log(tax_rate.label);
						//console.log(order_total);
						//console.log(tax_rate.rate);						
						//console.log((order_total/100)*tax_rate.rate);
						
						_tax = (order_total/100)*tax_rate.rate;
						
						_tax = get_ic_price(_tax,false);
						
						total_tax = total_tax + _tax;
						
						output += "<tr>";
						//output += "	<td colspan=\"3\"></td>";
						output += "	<th class=\"right_align tax\" valign=\"middle\">";
						output += 		tax_rate.label +":"
						output += "	</th>";
						output += "	<td class=\"right_align tax\" valign=\"middle\">";
						output += 		get_ic_price(_tax,true);
						//output += 		_tax;
						output += "</td>";
						output += "	<td></td>";
						output += "</tr>";
					//});
				});
			}
			
			order_total = order_total + total_tax;
			
			output += "<tr>";
			//output += "	<td colspan=\"3\"></td>";
			output += "	<th class=\"right_align order_total\">";
			output += i18n.order_total
			output += "	</th>";
			output += "	<td class=\"right_align order_total\">";
			output += 		get_ic_price(order_total,true);
			output += "	</td>";
			output += "	<td></td>";
			output += "</tr>";
			jQuery('.pos_right').find("table tbody.cart_total").html(output).show();
			
			var pos_order_note = jQuery.cookie("pos_order_note");
			if(pos_order_note){
				jQuery('tbody.order_note').show();
			}else{
				jQuery('tbody.order_note').hide();
			}
			
			jQuery('tbody.cart_buttons').show();			
			jQuery("tbody.item tr.item").show("slow");
			jQuery("tbody.shippings tr.shipping_row").show("slow");
			jQuery("tbody.fee tr.fee_row").show("slow");
			jQuery("tbody.cart_total tr").show("slow");
		}		
		jQuery.cookie("pos_cart_items", JSON.stringify(cart_items));
	}
	
	function get_item_formated_price(cart_item){
		var output = "";		
		if(cart_item.line_subtotal != cart_item.line_total){
			output = "<span class=\"line_through\">" + get_ic_price(cart_item.line_subtotal,true) + "</span> "+ get_ic_price(cart_item.line_total,true);
		}else{
			output = get_ic_price(cart_item.line_total,true);
		}
		return output;
	}
	
	function get_order_item_meta(){
		
	}
	
	function get_ic_price(number,formated){
		number = number + 0;
		if(formated){
			number = settings.currency_symbol + number.toFixed(2);
		}else{
			number = number.toFixed(2);
			number = parseFloat(number);
		}
		return number;
	}
	
	function get_decimal_value(number){
		number = number + 0;
		number = number.toFixed(2);
		return number;
	}
	
	function clean_cart(){
		cart_items 		= [];
		temp_items 		= [];
		cart_shipping 	 = [];
		cart_fee 		  = [];
		custom_meta 	   = [];
		item_meta 		 = [];
		order_details 	 = {};
		
		form_processing   = false;
		
		jQuery.cookie("pos_cart_items",[]);
		jQuery.cookie("pos_cart_shipping",[]);
		jQuery.cookie("pos_cart_fee",[]);
		
		jQuery.cookie("pos_order_note",'');
		
		jQuery.cookie("pos_custom_meta",[]);
		jQuery.cookie("pos_item_meta",[]);
		
		jQuery('tbody.shippings').hide();
		jQuery('tbody.fee').hide();
		jQuery('tbody.cart_total').hide();
		jQuery('tbody.order_note').hide();
		jQuery('tbody.cart_buttons').hide();
		
		ic_create_cart();
	}
	
	function clear_user(){
		customer_id 	   = 0;
		customer_label    = '';
		
		jQuery.cookie("pos_customer_label",'');
		jQuery.cookie("pos_customer_id",'');
		
		jQuery("a.load_customer_details").hide();
		jQuery("a.clear_customer").hide();
		jQuery('span.guest_customer').fadeIn();
		
		jQuery("input.customer_selection").val('');
		jQuery("input.customer_id").val(0);
		
		jQuery("select#billing_country").find("option:selected").removeAttr("selected");
		jQuery("select#shipping_country").find("option:selected").removeAttr("selected");
	}
	
	function get_item_total(cart_item,quantity){
		var regular_price     = cart_item.regular_price;
		var price 		     = cart_item.price;
		var line_subtotal_tax = 0;
		var line_tax	      = 0;
		//var line_tax_data     = [];
		//var lt = [];
		//var lst = [];
		//jQuery.each(tax_rates,function(arraykey,taxrates){
			//jQuery.each(taxrates,function(rate_id,tax_rate){
				
				//var _lst = (quantity*regular_price/100)*tax_rate.rate;
				//var _lt = (quantity*price/100)*tax_rate.rate;
				
				//line_subtotal_tax 	= line_subtotal_tax + _lst;
				//line_tax 			 = line_tax 		  + _lt;
				
				//lst[rate_id] 	= line_subtotal_tax + (quantity*regular_price/100)*tax_rate.rate;
				//lt[rate_id]	= line_tax 		  + (quantity*price/100)*tax_rate.rate;
				//line_tax_data.push({})
				
				//lst.push({rate_id:_lst});
				//lt.push({rate_id:_lt});
				
				//lst.push({rate_id:_lst});
				//lt.push({rate_id:_lt});
				
			//});
		//});
		
		//line_tax_data['subtotal'] = lst;
		//line_tax_data['total'] = lt;
		
		//line_tax_data = {'line_tax_data' : lst, 'total': lt}
		
		line_subtotal_tax   				 = get_decimal_value(line_subtotal_tax);
		line_tax 						  = get_decimal_value(line_tax);
		
		cart_item.quantity 				= quantity;
		cart_item.variation 			   = '';
		
		cart_item.line_subtotal 		   = quantity*regular_price;
		cart_item.line_total 			  = quantity*price;
		
		//cart_item.line_subtotal_tax 	   = line_subtotal_tax;
		//cart_item.line_tax 				= line_tax;		
		
		//console.log(line_tax_data)		
		//cart_item.line_tax_data 		   	= JSON.stringify(line_tax_data);
		//cart_item.total_qty 				= quantity;
		//cart_item.total_qty_regular_price = quantity*regular_price;
		//cart_item.total_qty_price  		= quantity*price;
		return cart_item;
	}
	
	
	function load_products_grid(){
		
		if(form_processing) return false;
		
		if(all_product_loaded){
			form_processing = false;
			return false;
		}
		
		var new_search = jQuery("input#search_product").val();
		
		last_searched   = new_search;
		
		form_processing = true;
		
		product_page = product_page + 1;
		
		var form_data = {
			'action' 	   : ajax_action
			,'sub_action'  : 'search_product'
			,'form_action' : 'search_product'
			,'term' 	 	: new_search
			,'p'		   : product_page
			,'limit'	   : settings.products_per_page
			,'data_type'   : 'limit_row'
		}
		
		jQuery("td.pos_left").addClass('loading_gif');
		
		jQuery("div.loading_products").fadeIn();
				
		var xhr_product_ajax = jQuery.ajax({
			type		: "POST",
			url		 : ajax_url,
			data		: form_data,
			success	 : function(data) {
				form_processing = false;
				
				if(data){					
					jQuery("table.product_list").find("tbody").append(data);
					
					if(product_loaded == false){
						get_product_grid_on_load();
					}
				}else{
					product_loaded = true;
					all_product_loaded = true;
				}
				
				jQuery("td.pos_left").removeClass('loading_gif');
				jQuery("div.loading_products").hide();
				
				if(pending_search){
					search_product();
				}
			},
			error: function(jqxhr, textStatus, error ){
				form_processing = false;
				jQuery("div.loading_products").hide();
				jQuery("td.pos_left").removeClass('loading_gif');
			}
		});//End On Ajax
		
		return false;
	}
	
	function get_product_grid_on_load(){
		var height_plb =jQuery("div.product_list_box").height();
		var height_lp =jQuery("div.left_pages").height();
		if(height_plb <= height_lp){
			load_products_grid();
		}else{
			product_loaded = true;
		}
	}
	
	function search_product(){
		
		product_page 	   = 0;
		pending_search     = false
		all_product_loaded = false;
		pending_search 	 = false;
		form_processing    = false;
		
		jQuery("table.product_list").find("tbody").html("");
		
		load_products_grid();
	}
	
	function loading_please_wait(){
		jQuery("div.left_overlay").show().css({"opacity":.5});
	}
	
	function set_setting_data(pos_settings){
		
		 tax_rates 	    = pos_settings.tax_rates;
		 tax__rates 	   = pos_settings.tax__rates;
		 settings 		 = pos_settings.settings;
		 shipping_methods = pos_settings.shipping_methods;
		 payment_gateways = pos_settings.payment_gateways;
		 employee 		 = pos_settings.employee;
		 i18n 			 = pos_settings.i18n;
		 location_name 	= pos_settings.location_name;
		 customer_fields  = pos_settings.customer_fields;
		 
		 form_processing  = false;
		 get_product_grid_on_load();
		 ic_create_cart();
		 jQuery("div.left_overlay").hide();
		 jQuery("div.loading_products").hide();
		 
		 if(settings.tax_based_on == "base"){
		 	jQuery.cookie("pos_order_tax_country", settings.base_country);
			jQuery.cookie("pos_order_tax_state", settings.base_state);
			jQuery.cookie("pos_order_tax_city", settings.base_city);
			jQuery.cookie("pos_order_tax_postcode", settings.base_postcode);
		 }
	}
	
	function load_settings(){
		
		if(form_processing){return false;}		
		
		var new_data = {
			'action' 		      : 'point_of_sale'
			,'sub_action'         : 'settings'
			,'form_action'        : 'settings'
		};
		
		form_processing = true;
		loading_please_wait();
		
		jQuery.ajax({
			type		: "POST",
			url		 : ajax_url,
			data		: new_data,
			dataType	: 'json',
			success	 : function(pos_settings) {				
				set_setting_data(pos_settings);
			},
			error: function(jqxhr, textStatus, error ){
				form_processing = false;
			}
		});//End On Ajax
	}
	
	jQuery("a.nav_button").click(function(){
		var x = document.getElementById("pos_nav");
		if (x.style.display === "block") {
			x.style.display = "none";
		} else {
			x.style.display = "block";
		}
	});
	
	var sh = jQuery("input.search_product").height();
	var hh = jQuery("td.top-header").height();
	var th = sh +hh + 20;
	
	var h = jQuery(window).height();
	jQuery('.pos_left div.left_pages').height(h-th);
	
	
	
	jQuery(window).resize(function(){
		var h = jQuery(window).height();
		jQuery('.pos_left div.left_pages').height(h-th);
	});
	
	jQuery("button.close_print_window").hide();
	
	receipt_id = "";
	if(window.location.hash){
		var hash = document.URL.substr(document.URL.indexOf('#')+1) 
		var res = hash.split("/");
		var hash_page = res[1];
		if(hash_page == 'receipt'){
			receipt_id = $.trim(res[2]);
			if(receipt_id){
				print_preview = false;
				new_receipt_id = receipt_id;
				get_receipt_contant(receipt_id);
			}
		}else if(hash_page == 'print_preview'){
			receipt_id = $.trim(res[2]);
			if(receipt_id){
				new_receipt_id = receipt_id;
				print_preview = true;
				jQuery("body").addClass("print_window");
				jQuery("div.right_pages").removeClass("right_pages_overflow");
				jQuery("div.right_pages").height();
				get_receipt_contant(receipt_id);
				
			}
		}
	}
	
	if(print_preview == false){
		jQuery('.pos_right div.right_pages_overflow').height(h-hh-50);
	}
	
	if(receipt_id == ""){
		load_settings();
	}
});