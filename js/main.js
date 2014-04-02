$(document).ready(function(){
    $("#btPesquisar").click(function()	{
		$("#div_pesquisar").slideDown('slow');
		
		var txtUserName 	= $('#txtUserName').val();
		var url 	= "http://bodysystems.net/_ferramentas/backoffice/services/search.php";
		
		$.ajax({
			type: "POST",
			url: url,
			data: {txtUserName:txtUserName}, // serializes the form's elements.
			dataType: "json" ,
			success: function(data){
				$("#div_pesquisar").slideUp('slow');
				$('#div_table').html(data['tr']);
				//alert('SUCESSO!');
			} ,
			error: function (request, status, error){
				alert("Não foi possivel realizar a tarefa\n" + request.responseText + "\n" + status);
			}
		});
	
	});

});

function Boletos_details(){
		if($('#div_boletos').css('display') == 'none'){ 
		   $('#div_boletos').slideDown('slow'); 
		} else { 
		   $('#div_boletos').slideUp('slow'); 
		}
	}

function PagSeguro_details(){
		if($('#div_pagseguro').css('display') == 'none'){ 
		   $('#div_pagseguro').slideDown('slow'); 
		} else { 
		   $('#div_pagseguro').slideUp('slow'); 
		}
	}

function MercadoPago_details(){
		if($('#div_mercadopago').css('display') == 'none'){ 
		   $('#div_mercadopago').slideDown('slow'); 
		} else { 
		   $('#div_mercadopago').slideUp('slow'); 
		}
	}			
