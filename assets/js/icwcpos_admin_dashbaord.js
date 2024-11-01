jQuery(document).ready( function($) {
	get_last_30_days_sales();
	get_location_collection();
	//alert(JSON.stringify(last_30_days_sales));
});
function get_location_collection(){
	
	try{
		var chart = AmCharts.makeChart("_location_collection", {
		  "type": "serial",
		  "theme": "light",
		   "language": ic_ajax_object.language,
		  "marginRight": 70,		 
		  "dataProvider": location_collection,
		  "valueAxes": [{
			"axisAlpha": 0,
			"position": "left",
			"title": ic_ajax_object.location_collection
		  }],
		  "startDuration": 1,
		  "graphs": [{
			"balloonText": "<b>[[category]]: [[value]]</b>",
			"fillColorsField": "color",
			"fillAlphas": 0.9,
			"lineAlpha": 0.2,
			"type": "column",
			"valueField": "order_total"
		  }],
		  "chartCursor": {
			"categoryBalloonEnabled": false,
			"cursorAlpha": 0,
			"zoomable": false
		  },
		  "categoryField": "location_name",
		  "categoryAxis": {
			"gridPosition": "start",
			"labelRotation": 45
		  },
		  "export": {
			"enabled": true
		  }
		
		});
	
	}catch(e){
		alert(e.message);
	}	
}
function get_last_30_days_sales(){
	var chart = AmCharts.makeChart( "_last_30_days_sales", {
	  "type": "serial",
	  "theme": "light",
	   "language": ic_ajax_object.language,
	  "marginRight": 50,
	  "marginLeft": 50,
	  "autoMarginOffset": 20,
	  "dataDateFormat": "YYYY-MM-DD",
	  "valueAxes": [ {
		"id": "v1",
		"axisAlpha": 0,
		"position": "left",
		"ignoreAxisWidth": true
	  } ],
	  "balloon": {
		"borderThickness": 1,
		"shadowAlpha": 0
	  },
	  "graphs": [ {
		"id": "g1",
		"balloon": {
		  "drop": true,
		  "adjustBorderColor": false,
		  "color": "#ffffff",
		  "type": "smoothedLine"
		},
		"fillAlphas": 0.2,
		"bullet": "round",
		"bulletBorderAlpha": 1,
		"bulletColor": "#FFFFFF",
		"bulletSize": 5,
		"hideBulletsCount": 50,
		"lineThickness": 2,
		"title": "red line",
		"useLineColorForBulletBorder": true,
		"valueField": "order_total",
		"balloonText": "<span style='font-size:18px;'>[[order_total]]</span>"
	  } ],
	  /*
	  "chartCursor": {
		"valueLineEnabled": true,
		"valueLineBalloonEnabled": true,
		"cursorAlpha": 0,
		"zoomable": false,
		"valueZoomable": true,
		"valueLineAlpha": 0.5
	  },
	  */
	  "valueScrollbar": {
		"autoGridCount": true,
		"color": "#000000",
		"scrollbarHeight": 50
	  },
	  "categoryField": "order_date",
	  "categoryAxis": {
		"parseDates": true,
		"dashLength": 1,
		"minorGridEnabled": true
	  },
	  "export": {
		"enabled": true
	  },
	  "dataProvider": last_30_days_sales
	} );
}