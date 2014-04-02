<?php
include('../../../exec_in_joomla.inc');
$txtUserName= str_replace(".","",str_replace("-","",$_REQUEST['txtUserName']));
$hoje 		= date('Y-m-d');
$hoje_str 	= date('Ymd');
file_put_contents('backoffice_'.$hoje_str.'.log',print_r($_REQUEST,true),FILE_APPEND);

function template_eval(&$template, &$vars) {
		return strtr($template, $vars);
}
	
try {
	$html = "	<tr>
				<td width=\"100%\" valign=\"top\"><center><p><strong>Nenhum registro encontrado!</strong></p></center></td>
			</tr>
			";

	
	if($txtUserName!=''){
		$db = & JFactory::getDBO();
		
		$query = 'SELECT u.id, u.name, u.email, u.username, COALESCE(d.fone_fixo,0) AS fone_fixo, COALESCE(d.fone_cel,0) AS fone_cel
					FROM wow_users AS u
					LEFT JOIN wow_users_details AS d ON
						d.userid=u.id
					WHERE	REPLACE(REPLACE(username,".",""),"-","") LIKE '.$db->Quote($txtUserName);
		
		$db->setQuery($query);
		$rs_users = $db->loadObjectList();
		//print(count($rs_users));//exit;
		//print((utf8_decode($result[0]->name)));exit;
	
		if(count($rs_users)>0){
			$params = array(
				'{ID}' 		=> $rs_users[0]->id,
				'{NOME}' 	=> $rs_users[0]->name,
				'{EMAIL}' 	=> $rs_users[0]->email,
				'{CPF}' 	=> $rs_users[0]->username,
				'{FONE_F}' 	=> $rs_users[0]->fone_fixo,
				'{FONE_C}' 	=> $rs_users[0]->fone_cel
			);
	
			$tr_template	= file_get_contents('../pages/tr_userDados.html');
			$tr_html = template_eval($tr_template,$params);
			
			$params = array(
				'{TR.DADOS_PESSOAIS}'	=> $tr_html
			);
			
			$div_table	= file_get_contents('../pages/div_table.html');
			$html = template_eval($div_table,$params);
	
			//########################
			//AKI PESQUISO OS BOLETOS
			$query = "SELECT 
							id, evento, tipo_evento, cnab, endereco_remessa,
							DATE_FORMAT(data_geracao,'%d/%m/%Y') AS data_geracao, 
							DATE_FORMAT(data_vencimento,'%d/%m/%Y') AS data_vencimento, 
							nosso_numero, valor_cobrado, compensado, 
							DATE_FORMAT(data_compensado,'%d/%m/%Y') AS data_compensado		
						FROM body2013_body2013.boletos_bs WHERE userid LIKE ".$db->Quote($rs_users[0]->id) ." ORDER BY id DESC";
			$db->setQuery($query);
			$rs_boletos = $db->loadObjectList();
			
			//print $query;
			//print(count($rs_boletos));//exit;
			
			$tr	= "";
			
			if(count($rs_boletos)>0){
				$tr_boletos	= file_get_contents('../pages/tr_boletos.html');
			}
			
			$pago = array('AGUARDANDO', 'PAGO');
			$cor = array("#e7e9f2","#f7f8fc");$i=0;
			
			foreach($rs_boletos as $boleto){
				$params = array(
					'{id}' 				=> $boleto->id,
					'{evento}' 			=> $boleto->evento,
					'{tipo_evento}' 	=> $boleto->tipo_evento,
					'{cnab}' 			=> $boleto->cnab,
					'{data_geracao}' 	=> $boleto->data_geracao,
					'{data_vencimento}' => $boleto->data_vencimento,
					'{nosso_numero}'	=> $boleto->nosso_numero,
					'{valor_cobrado}'	=> $boleto->valor_cobrado,
					'{compensado}'		=> $pago[$boleto->compensado],
					'{data_compensado}' => $boleto->data_compensado,
					'{endereco_remessa}'=> $boleto->endereco_remessa
				);
				
				$tr ="<tr>
						<td width=\"10%\" style=\"background-color:".$cor[$i%2]."\">{data_vencimento}</td>
						<td width=\"15%\" style=\"background-color:".$cor[$i%2]."\">{nosso_numero}</td>
						<td width=\"20%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 11px;\">{endereco_remessa}</font></td>
						<td width=\"10%\" style=\"background-color:".$cor[$i%2]."\">R$ {valor_cobrado}</td>
						<td width=\"20%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 11px;\">{tipo_evento} - {evento}</font></td>
						<td width=\"15%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 12px;\">{cnab}</font></td>
						<td width=\"10%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 11px;\">{compensado}</font></td>
					  </tr>
					  ";
				$tr 	  = template_eval($tr,$params);
				$boletos .= $tr;
				$i++;
			}		
			
			$params = array(
				'{TR.CO_DETALHES}'	=> $boletos,
				'{TOTAL}'			=> str_pad(count($rs_boletos),2,'0',STR_PAD_LEFT)
			);		
			$tr_boletos = template_eval($tr_boletos,$params);
	
			//print $tr_boletos;exit;
			
			$params = array(
				'{TR.BOLETOS}'	=> $tr_boletos
			);		
			$html = template_eval($html,$params);
			//########################
			//FIM BOLETOS
			
			//########################
			//INICIO PAGSEGURO
			$query = "SELECT 
						id, userid, IdCliente, TransacaoID, VendedorEmail, 
						CliNome, CliEmail, CliEndereco, CliNumero, CliComplemento, CliBairro, CliCidade, CliEstado, CliCEP,
						Referencia, TipoFrete, ValorFrete, Extras, 
						Anotacao, TipoPagamento, StatusTransacao, NumItens, 
						DATE_FORMAT(Data,'%d/%m/%Y') AS Data, 
						status, ProdID, ProdDescricao, 
						ROUND(ProdValor,2) AS ProdValor, ProdQuantidade, ProdFrete, Parcelas, 
						Trailer, PromoID, ProgNegativo, 
						DATE_FORMAT(DataGeracao,'%d/%m/%Y') AS DataGeracao
						FROM body2013_body2013.PagSeguroTransacoes 
						WHERE userid LIKE ".$db->Quote($rs_users[0]->id) ." ORDER BY id DESC";
		$db->setQuery($query);
		$rs_pagseguro = $db->loadObjectList();
	
		//print "<pre>";
		//print (count($rs_pagseguro).' - ');
		//print_r($rs_pagseguro);
		//print "</pre>";
		//exit;
			if(count($rs_pagseguro)>0){
				$tr_pag	= file_get_contents('../pages/tr_pagseguro.html');
			}
			
			$transacoes_pag="";$i=0;
			
			foreach($rs_pagseguro as $pagseguro){
				$params = array(
					'{id}' 				=> $pagseguro->id,
					'{Data}' 			=> $pagseguro->Data,
					'{ProdID}' 			=> $pagseguro->ProdID,
					'{ProdDescricao}' 	=> $pagseguro->ProdDescricao,
					'{ProdValor}' 		=> $pagseguro->ProdValor,
					'{TipoPagamento}' 	=> $pagseguro->TipoPagamento,
					'{StatusTransacao}' => $pagseguro->StatusTransacao,
					'{endereco_remessa}'=> str_ireplace("  "," ",$pagseguro->CliEndereco.", ".$pagseguro->CliNumero." ".$pagseguro->CliComplemento." ".$pagseguro->CliBairro." ".$pagseguro->CliCidade." ".$pagseguro->CliEstado." - CEP ".$pagseguro->CliCEP)
				);
				$tr = "<tr>
						<td width=\"10%\" style=\"background-color:".$cor[$i%2]."\">{Data}</td>
						<td width=\"15%\" style=\"background-color:".$cor[$i%2]."\">{TipoPagamento}</td>
						<td width=\"20%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 11px;\">{endereco_remessa}</font></td>
						<td width=\"10%\" style=\"background-color:".$cor[$i%2]."\">R$ {ProdValor}</td>
						<td width=\"20%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 11px;\">{ProdDescricao}</font></td>
						<td width=\"15%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 12px;\">{ProdID}</font></td>
						<td width=\"10%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 11px;\">{StatusTransacao}</font></td>
					  </tr>
					  ";
				$tr 	  = template_eval($tr,$params);
				$transacoes_pag .= $tr;			  
				//print "<pre>";
				//print_r($params);
				//print "</pre>";	
				$i++;
			}
			//print ($transacoes_pag);exit;
			$params = array(
				'{TR.PAG_DETALHES}'	=> $transacoes_pag,
				'{TOTAL}'			=> str_pad(count($rs_pagseguro),2,'0',STR_PAD_LEFT)
			);		
			$tr_pag = template_eval($tr_pag,$params);
			$params = array(
				'{TR.PAGSEGURO}'	=> $tr_pag
			);		
			$html = template_eval($html,$params);
			//########################
			//FIM PAGSEGURO		
	
			//########################
			//INICIO MERCADOPAGO
			$query = "SELECT 
							id, transacaoid, userid, evoid, 
							cpf_cnpj, remessa_tipo, 
							remessa_cep, remessa_logradouro, remessa_numero, remessa_complemento, 
							remessa_bairro, remessa_cidade, remessa_uf, remessa_tipo,							
							DATE_FORMAT(data_criado,'%d/%m/%Y') 	AS data_criado, 
							DATE_FORMAT(data_aprovado,'%d/%m/%Y') 	AS data_aprovado, 
							DATE_FORMAT(data_alterado,'%d/%m/%Y') 	AS data_alterado, 
							DATE_FORMAT(data_liberado,'%d/%m/%Y') 	AS data_liberado, 
							cnab, produto_descricao, 
							pagador_email, pagador_fone, 
							compra_id, 
							valor_transacao, 
							valor_frete, 
							tipo_pagamento, status, 
							status_detalhes, status_evo, conta_vendedor, 
							codigo_promotor, nsa, prefs, conta
						FROM body2013_body2013.MercadoPagoTransacoes 
						WHERE userid LIKE ".$db->Quote($rs_users[0]->id) ." ORDER BY id DESC";
		$db->setQuery($query);
		$rs_mercadopago = $db->loadObjectList();
	
			//print "<pre>";
			//print (count($rs_pagseguro).' - ');
			//print_r($rs_mercadopago);
			//print "</pre>";
			//exit;
	
			if(count($rs_mercadopago)>0){
				$tr_mp	= file_get_contents('../pages/tr_mercadopago.html');
			}
			
			$transacoes_mp="";$i=0;
			
			foreach($rs_mercadopago as $mercadopago){
				$params = array(
					'{id}' 					=> $mercadopago->id,
					'{remessa_tipo}' 		=> $mercadopago->remessa_tipo,
					'{data_criado}' 		=> $mercadopago->data_criado,
					'{data_aprovado}' 		=> $mercadopago->data_aprovado,
					'{data_liberado}' 		=> $mercadopago->data_liberado,
					'{cnab}' 				=> $mercadopago->cnab,
					'{produto_descricao}' 	=> $mercadopago->produto_descricao,
					'{compra_id}' 			=> $mercadopago->compra_id,
					'{valor_transacao}' 	=> $mercadopago->valor_transacao,
					'{tipo_pagamento}' 		=> $mercadopago->tipo_pagamento,
					'{status}' 				=> $mercadopago->status,
					'{codigo_promotor}' 	=> $mercadopago->codigo_promotor,
					'{endereco_remessa}'	=> str_ireplace("  "," ",$mercadopago->remessa_logradouro.", ".$mercadopago->remessa_numero." ".$mercadopago->remessa_complemento." ".$mercadopago->remessa_bairro." ".$mercadopago->remessa_cidade." ".$mercadopago->remessa_uf." - CEP ".$mercadopago->remessa_cep)
				);
				$tr = "<tr>
						<td width=\"10%\" style=\"background-color:".$cor[$i%2]."\">{data_criado}</td>
						<td width=\"15%\" style=\"background-color:".$cor[$i%2]."\">{tipo_pagamento}</td>
						<td width=\"20%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 11px;\">{endereco_remessa}</font></td>
						<td width=\"10%\" style=\"background-color:".$cor[$i%2]."\">R$ {valor_transacao}</td>
						<td width=\"20%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 11px;\">{produto_descricao}</font></td>
						<td width=\"15%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 12px;\">{cnab}</font></td>
						<td width=\"10%\" style=\"background-color:".$cor[$i%2]."\"><font style=\"font-size: 11px;\">{status}</font></td>
					  </tr>
					  ";
				$tr 	  = template_eval($tr,$params);
				$transacoes_mp .= $tr;			  
				$i++;
				//print "<pre>";
				//print_r($params);
				//print "</pre>";
			}
			//print ($transacoes_pag);exit;
			$params = array(
				'{TR.MP_DETALHES}'	=> $transacoes_mp,
				'{TOTAL}'			=> str_pad(count($rs_mercadopago),2,'0',STR_PAD_LEFT)
			);		
			$tr_mp = template_eval($tr_mp,$params);
			$params = array(
				'{TR.MERCADOPAGO}'	=> $tr_mp
			);		
			$html = template_eval($html,$params);
			//########################
			//FIM MERCADOPAGO		
					
			$ret = array('tr' => $html);	
		}
	}	
	$ret = array('tr' => $html);
    echo json_encode($ret);
} catch (Exception $e) {
    echo $e->getMessage();
}

?>