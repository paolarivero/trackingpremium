/* Funciones para buscar recibos sin guías 
<script type="text/javascript">
*/
    var $thisCusTable=null;
    var $thisRecTable=null;
    var $thisPoboxTable=null;
    function showRecTable(thedata) {
	if ($thisRecTable) {
		$thisRecTable.clear();
		$thisRecTable.destroy();
	}
	var tableHead = document.getElementById('receiptTable').getElementsByTagName('thead')[0];

	var nh = tableHead.rows.length;
	for (i=0; i < nh; i++) {
		tableHead.deleteRow(0);
	}
	var newhead = tableHead.insertRow(0); 

	var newH0  = newhead.insertCell(0);
	var newH1  = newhead.insertCell(1);
	var newH2  = newhead.insertCell(2);	
	var newH3  = newhead.insertCell(3);
	var newH4  = newhead.insertCell(4);
	var newH5  = newhead.insertCell(5);	
	var newH6  = newhead.insertCell(6);
	var newH7  = newhead.insertCell(7);
	var newH8  = newhead.insertCell(8);
	var newH9  = newhead.insertCell(9);

	var h0 = document.createElement("h4");
	var h1 = document.createElement("h4");
	var h2 = document.createElement("h4");
	var h3 = document.createElement("h4");
	var h4 = document.createElement("h4");
	var h5 = document.createElement("h4");
	var h6 = document.createElement("h4");
	var h7 = document.createElement("h4");
	var h8 = document.createElement("h4");
	var h9 = document.createElement("h4");
	
	var t0 = document.createTextNode("Id");
	var t1 = document.createTextNode("Número");
	var t2 = document.createTextNode("Fecha");
	var t3 = document.createTextNode("Remitente");
	var t4 = document.createTextNode("Destinatario");
	var t5 = document.createTextNode("Carrier");
	var t6 = document.createTextNode("Recibido por");
	var t7 = document.createTextNode("Peso");
	var t8 = document.createTextNode("Valor");
	var t9 = document.createTextNode("Marcar");
	
	h0.appendChild(t0);
	h1.appendChild(t1);
	h2.appendChild(t2);
	h3.appendChild(t3);
	h4.appendChild(t4);
	h5.appendChild(t5);
	h6.appendChild(t6);
	h7.appendChild(t7);
	h8.appendChild(t8);
	h9.appendChild(t9);

	newH0.appendChild(h0);
	newH1.appendChild(h1);
	newH2.appendChild(h2);
	newH3.appendChild(h3);
	newH4.appendChild(h4);
	newH5.appendChild(h5);
	newH6.appendChild(h6);
	newH7.appendChild(h7);
	newH8.appendChild(h8);
	newH9.appendChild(h9);

	var tableRef = document.getElementById('receiptTable').getElementsByTagName('tbody')[0];
	var nf = tableRef.rows.length;
	for (i=0; i < nf; i++) {
		tableRef.deleteRow(0);
	}
	var nc = thedata.length;
	if ( nc > 0) {
		$('.bupdate').show();
		$('.bguia').show();
	}
	for (i = 0; i < nc; i++) {
		var recid = thedata[i]['id'];
		var number = thedata[i]['number'];
		var date = thedata[i]['creationdate'];
		var shipper = thedata[i]['shipper'];
		var receiver = thedata[i]['receiver'];
		var carrier = thedata[i]['carrier'];
		var receiptdby = thedata[i]['receiptdby'];
		var weight = thedata[i]['weight'];
		var value = thedata[i]['value'];
		
		// Insert a row in the table at the last row
		var newRow   = tableRef.insertRow(tableRef.rows.length);
		var newCell0  = newRow.insertCell(0);
		var newCell1  = newRow.insertCell(1);
		var newCell2  = newRow.insertCell(2);	
		var newCell3  = newRow.insertCell(3);
		var newCell4  = newRow.insertCell(4);
		var newCell5  = newRow.insertCell(5);
		var newCell6  = newRow.insertCell(6);
		var newCell7  = newRow.insertCell(7);
		var newCell8  = newRow.insertCell(8);
		var newCell9  = newRow.insertCell(9);
		
		var newText0 = document.createTextNode(recid);
		newCell0.appendChild(newText0);
		var newText1 = document.createTextNode(number);
		newCell1.appendChild(newText1);
		var newText2 = document.createTextNode(date);
		newCell2.appendChild(newText2);
		var newText3 = document.createTextNode(shipper);
		newCell3.appendChild(newText3);
		var newText4 = document.createTextNode(receiver);
		newCell4.appendChild(newText4);
		var newText5 = document.createTextNode(carrier);
		newCell5.appendChild(newText5);
		var newText6 = document.createTextNode(receiptdby);
		newCell6.appendChild(newText6);
		var newText7 = document.createTextNode(weight);
		newCell7.appendChild(newText7);
		var newText8 = document.createTextNode(value);
		newCell8.appendChild(newText8);	
		var btn = document.createElement("input");        
		btn.type = "checkbox";
		btn.value = "si";
    		btn.checked = true; 
		
		newCell9.appendChild(btn);	
	}
	$thisRecTable=$('#receiptTable').DataTable({
			searching: false,
			"info":     false,
			"lengthChange": false,
			pageLength: 4,
    			language: {				
      				emptyTable: "<div style=\"color:red;\">No hay resultados</div>",
        			paginate: {
					first:      'Primero',
        				last:       'Último',
            				previous: '‹',
            				next:     '›'
        			},
        			aria: {
            				paginate: {
                				previous: 'Anterior',
                				next:     'Siguiente'
            				}
        			}	
    			}
	});
    }
    function returnSelectCustomer(vidcus){
	if ($('#receipt_type_selcustomer').val() === "1") {
		var sender=vidcus;
		var receiver='';		
		//console.log(vidcus);
	} else {
		var sender='';
		var receiver=vidcus;
		//console.log(vidcus);
	}
	// console.log(sender);
	// console.log(receiver);
	$.ajax({
        	type: "GET",
               	url: "{{ url('receipt_customer') }}?sender_id=" + sender + "&receiver_id=" + receiver,
               	success: function(data) {
			showRecTable(data);
		}
	});
	$("#closemodalcus1").click()    
        return true;
    }
    function returnSelectPobox(vidpobox){	
	$.ajax({
        	type: "GET",
               	url: "{{ url('receipt_pobox') }}?pobox_id=" + vidpobox,
               	success: function(data) {
			showRecTable(data);
		}
	});
	$("#closemodalpobox1").click()    
        return true;
    }
    $(document).ready(function () {
	$('.bupdate').hide();
	$('.bguia').hide();
	$('.bcustomer').click(function(e) {
	        e.preventDefault();
    		var thecustomer = $(this).data('thecustomer');
    		$('#receipt_type_selcustomer').val(thecustomer);
		return true;
	});
	$('.bupdate').click(function(e) {
	        e.preventDefault();
    		var tableRef = document.getElementById('receiptTable').getElementsByTagName('tbody')[0];
		var nf = tableRef.rows.length;
		var todel = [];
    		for (i=0; i < nf; i++) {
			var mark = tableRef.rows[i].cells[9].childNodes[0];
			// console.log(mark.checked);
			if (!mark.checked) {
				todel[i]=true;
			} else {
				todel[i]=false;
			}
			// tableRef.deleteRow(0);
		}
		for (i=0; i < nf; i++) {
			if (todel[i]) {
				tableRef.deleteRow(i);
			}
		}
		nf = tableRef.rows.length;
		if (nf == 0) {
			var tableHead = document.getElementById('receiptTable').getElementsByTagName('thead')[0];
			var nh = tableHead.rows.length;
			for (i=0; i < nh; i++) {
				tableHead.deleteRow(0);
			}
			$thisRecTable.clear();
			$thisRecTable.destroy();
			$('.bupdate').hide();
			$('.bguia').hide();
		}
		return false;
	});
	$('#receipt_type_searchpobox').click(function(){
		var pobox = $('#receipt_type_pobox').val();
		// console.log(pobox);
		if (!pobox) {
			alert('Suministre el código del casillero');
		} else {
			$.ajax({
                	type: "GET",
                	url: "{{ url('pobox_list') }}?pobox_number=" + pobox,
                	success: function(data) {
				if ($thisPoboxTable) {
					$thisPoboxTable.clear();
					$thisPoboxTable.destroy();
				}
				var tableHead = document.getElementById('poboxTable').getElementsByTagName('thead')[0];

				var nh = tableHead.rows.length;
				for (i=0; i < nh; i++) {
					tableHead.deleteRow(0);
				}
				var newhead = tableHead.insertRow(0); 

				var newH0  = newhead.insertCell(0);
				var newH1  = newhead.insertCell(1);
				var newH2  = newhead.insertCell(2);	
				
				var h0 = document.createElement("h4");
				var h1 = document.createElement("h4");
				var h2 = document.createElement("h4");
				
				
				var t0 = document.createTextNode("Número");
				var t1 = document.createTextNode("Cliente");
				var t2 = document.createTextNode("Ubicación");

				h0.appendChild(t0);
				h1.appendChild(t1);
				h2.appendChild(t2);
				
				newH0.appendChild(h0);
				newH1.appendChild(h1);
				newH2.appendChild(h2);
				
				var tableRef = document.getElementById('poboxTable').getElementsByTagName('tbody')[0];
				var nf = tableRef.rows.length;
				for (i=0; i < nf; i++) {
					tableRef.deleteRow(0);
				}
				var nc = data.length;
		
				for (i = 0; i < nc; i++) {
					var poboxid = data[i]['poboxid'];
					var number = data[i]['number'];
					var customer = data[i]['customer'];
					var location = data[i]['location'];

					var newRow   = tableRef.insertRow(tableRef.rows.length);
	
					var newCell0  = newRow.insertCell(0);
					var newCell1  = newRow.insertCell(1);
					var newCell2  = newRow.insertCell(2);
					var btn = document.createElement("input");       

					var tag = number;
    					btn.type = "button";
    					btn.value = tag;
					btn.onclick = (function(){ var vidpobox = poboxid; 	return function() 
				  		{returnSelectPobox(vidpobox)}})();  

		 			newCell0.appendChild(btn);
					var newText1 = document.createTextNode(customer);
					newCell1.appendChild(newText1);

					var newText2 = document.createTextNode(location);
					newCell2.appendChild(newText2);
				}
				$thisPoboxTable=$('#poboxTable').DataTable({
					searching: false,
					"info":     false,
					"lengthChange": false,
					pageLength: 4,
    					language: {				
      						emptyTable: "<div style=\"color:red;\">No hay resultados</div>",
        					paginate: {
							first:      'Primero',
        						last:       'Último',
            						previous: '‹',
            						next:     '›'
        					},
        					aria: {
            						paginate: {
                					previous: 'Anterior',
                					next:     'Siguiente'
            						}
        					}	
    					}
				});
		
			}
			});
		}
	});
	$('#receipt_type_searchcustomer').click(function(){
	    // console.log('VOY');
	    var name = $('#receipt_type_namecustomer').val();
	    var lastname = $('#receipt_type_lastnamecustomer').val();	
	    if ((!name) && (!lastname)) {
		alert('Suministre nombre y/o apellido');
	    } else {
		$.ajax({
                type: "GET",
                url: "{{ url('customer_list') }}?customer_name=" + name + "&customer_lastname=" + lastname,
                success: function(data) {
		if ($thisCusTable) {
			//console.log('La tabla ya existía');
			$thisCusTable.clear();
			$thisCusTable.destroy();
		}

		var tableHead = document.getElementById('customerTable').getElementsByTagName('thead')[0];

		var nh = tableHead.rows.length;
		for (i=0; i < nh; i++) {
			tableHead.deleteRow(0);
		}

		var newhead = tableHead.insertRow(0); 

		var newH0  = newhead.insertCell(0);
		var newH1  = newhead.insertCell(1);
		var newH2  = newhead.insertCell(2);	
		var newH3  = newhead.insertCell(3);
		var newH4  = newhead.insertCell(4);
		var newH5  = newhead.insertCell(5);	
		var newH6  = newhead.insertCell(6);
		
		var h0 = document.createElement("h4");
		var h1 = document.createElement("h4");
		var h2 = document.createElement("h4");
		var h3 = document.createElement("h4");
		var h4 = document.createElement("h4");
		var h5 = document.createElement("h4");
		var h6 = document.createElement("h4");
		
		var t0 = document.createTextNode("Id");
		var t1 = document.createTextNode("Nombre");
		var t2 = document.createTextNode("Apellido");
		var t3 = document.createTextNode("Email");
		var t4 = document.createTextNode("Ciudad");
		var t5 = document.createTextNode("Estado");
		var t6 = document.createTextNode("País");
		
		h0.appendChild(t0);
		h1.appendChild(t1);
		h2.appendChild(t2);
		h3.appendChild(t3);
		h4.appendChild(t4);
		h5.appendChild(t5);
		h6.appendChild(t6);

		newH0.appendChild(h0);
		newH1.appendChild(h1);
		newH2.appendChild(h2);
		newH3.appendChild(h3);
		newH4.appendChild(h4);
		newH5.appendChild(h5);
		newH6.appendChild(h6);

		var tableRef = document.getElementById('customerTable').getElementsByTagName('tbody')[0];
		var nf = tableRef.rows.length;
		for (i=0; i < nf; i++) {
			tableRef.deleteRow(0);
		}
		var nc = data.length;
		//console.log(nc);
		//console.log(tableRef.rows.length);
		for (i = 0; i < nc; i++) {
			var customerid = data[i]['customerid'];
			var namecus = data[i]['name'];
			var lastnamecus = data[i]['lastname'];
			var address = data[i]['address'];
			var cityid = data[i]['cityid'];
			var cityname = data[i]['cityname'];
			var state = data[i]['state'];
			var country = data[i]['country'];
			var phone = data[i]['phone'];
			var mobile = data[i]['mobile'];
			var barrio = data[i]['barrio'];
			var email = data[i]['email'];
			var zip = data[i]['zip'];
			// Insert a row in the table at the last row
			var newRow   = tableRef.insertRow(tableRef.rows.length);

			// Insert a cell in the row at index 0
			var newCell0  = newRow.insertCell(0);
			var newCell1  = newRow.insertCell(1);
			var newCell2  = newRow.insertCell(2);	
			var newCell3  = newRow.insertCell(3);
			var newCell4  = newRow.insertCell(4);
			var newCell5  = newRow.insertCell(5);
			var newCell6  = newRow.insertCell(6);
			// Append a text node to the cell
			
			var btn = document.createElement("input");        // Create a <button> element

			var tag = "Cliente_" + customerid;
			var t = document.createTextNode(tag);
			
    			btn.type = "button";
    			btn.value = tag;
			
			btn.onclick = (function(){ var vidcus = customerid; 	return function() 
				  {returnSelectCustomer(vidcus)}})();  

		 	newCell0.appendChild(btn);

			var newText1 = document.createTextNode(namecus);
			newCell1.appendChild(newText1);

			var newText2 = document.createTextNode(lastnamecus);
			newCell2.appendChild(newText2);

			var newText3 = document.createTextNode(email);
			newCell3.appendChild(newText3);		
			
			var newText4 = document.createTextNode(cityname);
			newCell4.appendChild(newText4);

			var newText5 = document.createTextNode(state);
			newCell5.appendChild(newText5);

			var newText6 = document.createTextNode(country);
			newCell6.appendChild(newText6);
                }
		$thisCusTable=$('#customerTable').DataTable({
			searching: false,
			"info":     false,
			"lengthChange": false,
			pageLength: 4,
    			language: {				
      				emptyTable: "<div style=\"color:red;\">No hay resultados</div>",
        			paginate: {
					first:      'Primero',
        				last:       'Último',
            				previous: '‹',
            				next:     '›'
        			},
        			aria: {
            				paginate: {
                				previous: 'Anterior',
                				next:     'Siguiente'
            				}
        			}	
    			}
		});
	     }
	    });	
	   } 
           return false;
	});
});
/*
</script>
*/
