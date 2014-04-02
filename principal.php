<?php
	
//include('../../exec_in_joomla.inc') ;
//------------------------------
	function template_eval(&$template, &$vars) { return strtr($template, $vars); }
//------------------------------

//JHTML::_( 'behavior.calendar' ); 


$user=&JFactory::getUser();
$document=&JFactory::getDocument();

//$document->addScript('cdscriptegrator/libraries/jquery/js/jquery-noconflict.js') ;
$document->addStyleSheet('http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css');
$document->addScript("http://code.jquery.com/jquery-1.8.2.js");
$document->addScript('http://code.jquery.com/ui/1.9.0/jquery-ui.js') ;

$document->addStyleSheet('_ferramentas/backoffice/css/styles.css');
$document->addScript('_ferramentas/backoffice/js/main.js');

//$document->addScript("/highslide/highslide.config.js");

//$document->addStyleSheet("/highslide/highslide.css");
//$document->addStyleSheet("/highslide/highslide-ie6.css");

// $document->addScript('_ferramentas/Boleto_Recall/spec/support/jquery.js') ;
// $document->addScript('_ferramentas/Boleto_Recall/jquery.editinplace.js');
// $document->addScript('_ferramentas/Boleto_Recall/edit.js');

// if(isset($_GET['mes'])) {
// 	$mes = $_GET['mes'] ;
// } else { 
// 	$mes = date('m');
// }

// if(isset($_GET['ano'])) {
//     $ano = $_GET['ano'] ;
// } else { 
//     $ano = date('Y');
// }
	
  $db =& JFactory::getDBO() ;
  
  // $query = "SELECT count(*) FROM resgate" ;
  // $db->setQuery($query) ;
  // $num_resgate = $db->loadResult();
  
  // $query = "SELECT count(resgate.id) FROM resgate INNER JOIN boletos_bs ON boletos_bs.nosso_numero = resgate.nosso_numero WHERE compensado = 1 AND MONTH(data_compensado)=".$mes." AND YEAR(data_compensado) = ".$db->Quote($ano)." ORDER BY data_compensado;"  ;
  // $db->setQuery($query) ;
  // $resgate_pago = $db->loadResult();
  
  // $resgate_pendente = $num_resgate - $resgate_pago ;
  
  
  // $query = "SELECT resgate.id,idcliente,nome_evo,endereco_remessa,resgate.valor_frete,programas,rodadas,data_operacao,data_compensado,observacao,despacho
		// 	FROM resgate INNER JOIN boletos_bs ON boletos_bs.nosso_numero = resgate.nosso_numero
		// 	WHERE compensado = 1 AND MONTH(data_compensado)=".$mes." AND YEAR(data_compensado) =".$db->Quote($ano)." ORDER BY data_compensado;"         ;
			
  // $db->setQuery($query) ;
  // $table = $db->loadObjectList() ;

$template_main = file_get_contents('_ferramentas/backoffice/pages/main.html') ;
  
  $params = array(
    '{BT_SEARCH}' => 'pesquisar...'
  );
  $page_merged = template_eval($template_main,$params);
  
  echo $page_merged ;
  
?>
