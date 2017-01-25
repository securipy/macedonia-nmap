$(document).ready(function(){
	
	addscanstable = function(data){
		var oTable = $('#datatable').dataTable();
		$.each( data, function( index, value ){
			oTable.fnAddData([
				value[0].name,
				value[0].ip_domain,
				value[0].date_execute,
				'',
				'',
				'<button type="button" class="btn btn-default select_audit" data-id="'+value[0].id+'">Details</button><button type="button" class="btn btn-danger delete_audit" data-id="'+value[0].id+'">eliminar</button>'] ); 
		});
	}

	$('#day-scan').datetimepicker({
		format : 'YYYY-MM-DD HH:mm'
	});
	

	$('#datatable').on("click", ".delete_audit", function() {
		
	});

	$("#add-scan").click(function(){

		
		$("#servers").html("");
		$("#day-scan").val(moment().format('YYYY-MM-DD HH:mm'));

		$.ajax({
			url: "/scripts/list/Nmap",
			headers: {
				'GRANADA-TOKEN':readCookie('token'),
			},
			type: "get",
			dataType: "json",
			success: function(data) {
				if(data.response==true){

					$.each( data.result[0], function( index, value ){
						$("#servers").append('<div class="checkbox"><label><input type="checkbox" value="'+value.id_server+'">'+value.name_server+'('+value.ip_domain+')</label></div>');
					});
					console.log(data);
					$("#add-new-scan-modal").modal('show')


				}else{
					new PNotify({
						title: 'Error Audit',
						text: data.message,
						styling: 'bootstrap3'
					});
				}
			},
			error: function(xhr, status, error) {
				new PNotify({
					title: 'Oh No!',
					text: xhr.responseText,
					type: 'error',
					styling: 'bootstrap3'
				});
				var err = eval("(" + xhr.responseText + ")");
				console.log(err);
			}
		});

	});



	$('#add-new-scan-modal').on("click", "#save-device", function() {
		
		var servers_vals = [];
		
		$('#servers input:checkbox:checked').each(function(index) {
			servers_vals.push($(this).val());
		});
		
		var day_scan = $("#day-scan").val();
		
		var data = {
			'day_scan':day_scan,
			'id':id_device,
			'servers':servers_vals,
		}


		$.ajax({
			url: "/device/nmap/new",
			headers: {
				'GRANADA-TOKEN':readCookie('token'),
				'audit':readCookie('audit'),
			},
			type: "POST",
			data:data,
			dataType: "json",
			success: function(data) {
				if(data.response==true){

					addscanstable(data.result)
					$("#add-new-scan-modal").modal('hide');	
				}else{
					if(!$.isEmptyObject(data.result)){
						addscanstable(data.result)
					}
					if(!$.isEmptyObject(data.errors)){
						if(!$.isEmptyObject(data.errors.script)){
							$.each( data.errors.script, function( index, value ){
								if(!$.isEmptyObject(data.errors)){
									new PNotify({
										title: "Error scripts",
										text: value.message,
										styling: 'bootstrap3'
									});
								}
							});	
						}
						if(!$.isEmptyObject(data.errors.set)){
							$.each( data.errors.set, function( index, value ){
								if(!$.isEmptyObject(data.errors)){
									new PNotify({
										title: "Error set",
										text: value.message,
										styling: 'bootstrap3'
									});
								}
							});
						}

					}
					
				}
				$("#add-new-scan-modal").modal('hide');	

			},
			error: function(xhr, status, error) {
				var err = eval("(" + xhr.responseText + ")");
				console.log(err.errors);
				$.each( err.errors, function( index, value ){
					new PNotify({
						title: 'Oh No!',
						text: index+" - "+ value,
						type: 'error',
						styling: 'bootstrap3'
					});
				});
				
			}
		});


	});
});