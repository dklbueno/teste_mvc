<?php
@session_start();

if(!function_exists('limpa_sqlinjection')){
function limpa_sqlinjection($var)
{
	$var = str_ireplace(" select ","",$var);
	$var = str_ireplace(" delete "," apague ",$var);
	$var = str_ireplace(" union ","",$var);
	$var = str_ireplace(" insert ","",$var);
	$var = str_ireplace(" drop ","",$var);
	$var = str_ireplace(" create table ","",$var);
	$var = str_ireplace(";select ","",$var);
	$var = str_ireplace(";delete ","",$var);
	$var = str_ireplace(";union ","",$var);
	$var = str_ireplace(";insert ","",$var);
	$var = str_ireplace(";drop ","",$var);
	$var = str_ireplace(";create table ","",$var);
	$var = str_ireplace(" or ","",$var);
	$var = str_ireplace(" and ","",$var);
	$var = str_replace(" || ","",$var);
	$var = str_replace("'","",$var);
	$var = str_replace("\'","",$var);
	$var = str_replace("\"","",$var);

	return $var;
}
}

######################################### SEGURANCA ####################################
######### INICIO LIMPA SQL INJECTION
foreach ($_REQUEST as $index=>$valor)
{
	$_REQUEST[$index] = limpa_sqlinjection($valor);
}

foreach ($_GET as $index=>$valor)
{
	$_GET[$index] = limpa_sqlinjection($valor);
}
foreach ($_POST as $index=>$valor)
{
	$_POST[$index] = limpa_sqlinjection($valor);
}

############## FIM LIMPA SQL INJECTION
############################################################################################


function Gera_Boleto($ID_Turma,$ID_Site=NULL,$VlrBoleto,$Categ_ContaReceber,$DiaVenc_Boleto,$ID_Agente=1,$ID_Aluno=NULL,$ID_Pagamento=NULL,$ID_Representante=NULL)
{
	// Categ_ContaReceber = 1 (Mensalidade)
	// Categ_ContaReceber = 2 (Matricula)
	// Categ_ContaReceber = 3 (Iscricao)
	// Categ_ContaReceber = 4 (Certificado)
	// Categ_ContaReceber = 5 (Reposisao de Aula)
	// Categ_ContaReceber = 6 (Acordo)

	///////////////////////////////////////////////////
	//PEGA O ID DO CURSO PARA PEGAR O ID CONFIGURACAO//
	///////////////////////////////////////////////////
	$select_curso = "SELECT ID_Curso FROM tturma WHERE ID_Turma = ".$ID_Turma." ";
	$resultado_curso = mysql_query($select_curso);
	$row_curso = mysql_fetch_array($resultado_curso);
	$ID_Curso = $row_curso['ID_Curso'];	

	$resp = pegaContaBancariaCurso($ID_Curso);
	
	$ID_Configuracao = 1;
	if($resp['ID_Configuracao']){
		$ID_Configuracao = $resp['ID_Configuracao'];
	}
	///////////////////////////////////////////////////

	/*
	$SQLconf = "SELECT Valor_Configuracao FROM tconfiguracao
				WHERE ID_Configuracao = '".$ID_Configuracao."';";
	$resConf = mysql_query($SQLconf);
	$vConf   = mysql_result($resConf,0,0);

	$vConfUpdate = $vConf + '1';

	$SQLupdt = "UPDATE tconfiguracao
				SET Valor_Configuracao = '".$vConfUpdate."'
				WHERE ID_Configuracao = '".$ID_Configuracao."';";
	$resUpdt = mysql_query($SQLupdt);
	*/
	
	$selNB = "SELECT 1 FROM tnumeroboleto WHERE 1=1 ";

	$resNB = mysql_query($selNB);
	if(!mysql_num_rows($resNB)){
		$ID_Configuracao = 1;
		$SQLconf = "SELECT Valor_Configuracao FROM tconfiguracao
					WHERE ID_Configuracao = '".$ID_Configuracao."' ";
		$resConf = mysql_query($SQLconf);
		$Vtemp   = mysql_result($resConf,0,'Valor_Configuracao');
		
		$insertNB = "INSERT INTO tnumeroboleto SET NumeroBoleto = '".($Vtemp+1)."',
												   ID_Turma = '".$ID_Turma."',
												   ID_Aluno = '".$ID_Aluno."',
												   ID_Site = '".$ID_Site."',
												   tabela = 'tcontareceber_1' ";
		mysql_query($insertNB);			
		$vConf = ($Vtemp+1);
		
	}else{
		$insertNB = "INSERT INTO tnumeroboleto SET ID_Turma = '".$ID_Turma."',
												   ID_Aluno = '".$ID_Aluno."',
												   ID_Site = '".$ID_Site."',
												   tabela = 'tcontareceber_1' ";
		mysql_query($insertNB);	
		$vConf = mysql_insert_id();									   										   
	}
	


	# PEGA O PROXIMO ID DO CONTAS A RECEBER DAQUELA TURMA
	$SQLstringBoleto = 	" SELECT IF(ISNULL(MAX(ID_ContaReceber+1)),1,MAX(ID_ContaReceber+1)) AS NID_ContaReceber 
						  FROM tcontareceber_1 WHERE ID_Turma = ".$ID_Turma;
	$rsQueryBoleto = mysql_query($SQLstringBoleto) or die(mysql_error());
	while($rowBoleto=mysql_fetch_array($rsQueryBoleto))
	{
		$NID_ContaReceber = $rowBoleto["NID_ContaReceber"];
	}
	

	# INSERINDO OS DADOS NO TCONTARECEBER
	$SQLstringBoleto2 = " INSERT INTO tcontareceber_1 SET ".
						" ID_Turma 					= ".$ID_Turma.", ".
						" ID_Site					= '".$ID_Site."', ".
						" ID_Aluno					= ".$ID_Aluno.", ".
						" ID_Representante			= '".$ID_Representante."', ".
						" ID_ContaReceber			= ".$NID_ContaReceber.", ".
						" DtVenc_ContaReceber		= '".formataData($DiaVenc_Boleto)."', ".
						" Categ_ContaReceber		= '".$Categ_ContaReceber."', ".
						" Vlr_ContaReceber			= '".$VlrBoleto."', ".
						" TarifaBanco_ContaReceber 	= '1.20', ".
						" Juros_ContaReceber		= NULL, ".
						" Desconto_ContaReceber		= NULL, ".
						" NumBoleto_ContaReceber	= '".$vConf."', ".
						" DataRec_ContaReceber		= NULL, ".
						" ID_Agente					= 1, ".
						" ID_ContaReceberStatus		= 0, ".
						" StatusEnvio_ContaReceber	= 1, ".
						" ID_Pagamento				= '".$ID_Pagamento."', ".
						" AbonoRepasse_ContaReceber = '00' ";
						
	$rsQueryBoleto2   = mysql_query($SQLstringBoleto2);
	
	$select_last_id = "SELECT ID_ContaReceber FROM tcontareceber_1 WHERE ID_Turma = ".$ID_Turma." AND
							  											 ID_Site  = '".$ID_Site."' AND
																		 ID_Aluno = ".$ID_Aluno." ";
	$result_last_id = mysql_query($select_last_id);
	$row = mysql_fetch_array($result_last_id);
	$NID_ContaReceber = $row['ID_ContaReceber'];

	return $NID_ContaReceber;
}

# ---- Função para converter Currency 9.999,99 para Double 9999.99
/**
 * Função para converter Currency 9.999,99 para Double 9999.99
 *
 * @param integer $valor
 *
 * return double
 */
function FormataValor($valor){
	$valor = str_replace(".","",$valor);
	return str_replace(",",".",$valor);
}

# ---- Função para converter a data.
function FD ($DataHora)
	{
		$explode = explode(" ",$DataHora);
		$data = $explode[0];
		//$hora = $explode[1];
		$ndata = explode("/",$data);
		//$nhora = explode(":",$data);
		return $ndata[2]."-".$ndata[1]."-".$ndata[0];
	}


	# ---- Função para converter a data.

	function formataData($data)
		{
			$eData = explode("/",$data);
			$nData = $eData['2'].$eData['1'].$eData['0'];
			return $nData;
		}
	#--------------------------------------------------------






    #----Função p/ Montar o Menu Superior das telas
	function MenuTopo()
	  {
	   require("../MenuSuperior/MenuSuperior.php");
	  }
	#-------------------------------------------------------

	function ConverteMaiuMin($Texto)
	{
		$txt = explode(" ",$Texto);
		for($i=0;$i<count($txt);$i++)
		{
			if($i == 0)
			{
				$txt[0] = strtoupper(substr($txt[0],0,1)).strtolower(substr($txt[0],1));
				$resTxt = $txt[0];
			}
			else
			{
				if(strlen($txt[$i]) > 3)
				{
					$txt[$i] = strtoupper(substr($txt[$i],0,1)).strtolower(substr($txt[$i],1));
				}
				else
				{
					$txt[$i] = strtolower($txt[$i]);
				}
				$resTxt .= " ".$txt[$i];
			}
		}
		return $resTxt;
	}


	#----Função p/ Montar Combo de UF's
	function MontaComboUF($name,$css,$selected_value,$disabled)
	   {
		//require("connect.php");
		require_once("../class/Connect.class.php");
		$con = Conexao::singleton();
		$vHTML = '<select name='.$name.' class='.$css.' '.$disabled.' >';

		$SQLstring = 'SELECT *
		               FROM TUF ';

		$rsQuery = $con->consultar($SQLstring);

			while($row=mysql_fetch_array($rsQuery))
			  {
			   if($row["ID_UF"] == $selected_value)
			    {
		         $vHTML = $vHTML . '<option value='.$row["ID_UF"].' selected>'.$row["Sigla_UF"].'</option>';
				}
			   else
			    {
				 $vHTML = $vHTML . '<option value='.$row["ID_UF"].'>'.$row["Sigla_UF"].'</option>';
				}
			  }

			$vHTML = $vHTML . '</select>';

			echo $vHTML;
		}
	#-------------------------------------------------------

   #----Função p/ Converter Texto p/ Maiúsculo
	function ConverteMaiusculo()
	   {
	    echo "OnBlur='this.value=this.value.toUpperCase()'";
	   }
   #-------------------------------------------------------


   #----Função p/ Definir Atributos no campo
	function AtributoCampo($atrib)
	   {
	    echo " ". $atrib ." ";
	   }
   #-------------------------------------------------------


   #----Função p/ chamar rotina de Mascara de Entrada
   if(!function_exists('Mascara')){
	function Mascara($form,$campo,$mascara)
	  {
	    echo "onkeypress=\"return txtBoxFormat(".$form.", '".$campo."', '".$mascara."', event)\"";
	  }
   }
   #-------------------------------------------------------


   #----Função p/ chamar rotina de pular campo
   	if(!function_exists('PulaCampo')){
	function PulaCampo($form,$campo_atual,$tamanho,$campo_alvo)
	  {
	   echo "onKeyUp=\"JumpField('".$form."','".$campo_atual."',".$tamanho.",'".$campo_alvo."')\"";
	  }
	}
   #-------------------------------------------------------

    #----Função p/ Validar Formulários
	function ValidarForm($form,$campos)
	   {
	    $campos_length = count($campos);

	    $vJS = "<script language=\"Javascript\">".chr(13);
		$vJS = $vJS . " function ValidarForm() ".chr(13);
		$vJS = $vJS . "{".chr(13);
		$vJS = $vJS . " var FormWeb = ". $form .";".chr(13);
		   for($cont=0; $cont < $campos_length;$cont++)
		     {
			   $vCampo = split("#",$campos[$cont]);
   			   if($vCampo[1]=='text')
			   	 {
	 		   	  $vJS = $vJS . "if(FormWeb.".$vCampo[0].".value.length == 0)".chr(13);
				  $vJS = $vJS . "  {".chr(13);
				  $vJS = $vJS . "   alert('".$vCampo[2]."');".chr(13);
				  $vJS = $vJS . "   FormWeb.".$vCampo[0].".focus();".chr(13);
				  $vJS = $vJS . "   return false;".chr(13);
				  $vJS = $vJS . "   }".chr(13);
				 }
				if($vCampo[1]=='text_disabled')
			   	 {
	 		   	  $vJS = $vJS . "if(FormWeb.".$vCampo[0].".value.length == 0)".chr(13);
				  $vJS = $vJS . "  {".chr(13);
				  $vJS = $vJS . "   alert('".$vCampo[2]."');".chr(13);
				  $vJS = $vJS . "   return false;".chr(13);
				  $vJS = $vJS . "   }".chr(13);

				 }
			    if($vCampo[1]=='select')
				 {
	 		   	  $vJS = $vJS . "if(FormWeb.".$vCampo[0].".value == '0')".chr(13);
				  $vJS = $vJS . "  {".chr(13);
				  $vJS = $vJS . "   alert('".$vCampo[2]."');".chr(13);
				  $vJS = $vJS . "   FormWeb.".$vCampo[0].".focus();".chr(13);
				  $vJS = $vJS . "   return false;".chr(13);
				  $vJS = $vJS . "   }".chr(13);
				 }
				if($vCampo[1]=='radio')
				 {
	 		   	  $vJS = $vJS . "var vok=0;".chr(13);
				  $vJS = $vJS . "for(i=0;i < FormWeb.".$vCampo[0].".length;i++)".chr(13);
				  $vJS = $vJS . " {".chr(13);
				  $vJS = $vJS . "  if(FormWeb.".$vCampo[0]."[i].checked)".chr(13);
				  $vJS = $vJS . "   {".chr(13);
				  $vJS = $vJS . "     vok=1;".chr(13);
				  $vJS = $vJS . "   }".chr(13);
				  $vJS = $vJS . " }".chr(13);
				  $vJS = $vJS . "if(!vok)".chr(13);
				  $vJS = $vJS . "  {".chr(13);
				  $vJS = $vJS . "   alert('".$vCampo[2]."');".chr(13);
				  $vJS = $vJS . "   return false;".chr(13);
				  $vJS = $vJS . "   }".chr(13);
				 }
		     }
		 $vJS = $vJS . "return true;".chr(13);
		 $vJS = $vJS . "   }".chr(13);
		 $vJS = $vJS . "</script>".chr(13);
		 echo $vJS;
       }
	#-------------------------------------------------------

    #----Função p/ Chamar Validação Formulários
	 function CallValidarForm()
	   {
	    echo "OnSubmit=\"return ValidarForm()\"";
	   }
	#-------------------------------------------------------

    #----Função exibe mensagem Não foram encontrados registros
	 function NoRecords($table_width)
	   {
	    $vHTML = "<table width=\"".$table_width."\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		$vHTML = $vHTML . "  <tr> ";
		$vHTML = $vHTML . "  	  <td align=\"center\"><font face=\"Verdana\" size=\"2\">Não foram encontrados registros ...</font></td>";
		$vHTML = $vHTML . "  </tr> ";
		$vHTML = $vHTML . "</table> ";
		echo $vHTML;
		exit;
	   }
	#-------------------------------------------------------

	#-------------------------------------------------------
	function f_xor($exp1,$exp2){
		$bin1 = decbin($exp1);
		$bin2 = decbin($exp2);
		if (strlen($bin1)>strlen($bin2)){
			for($i=strlen($bin2);$i<strlen($bin1);$i++){
				$bin2 = "0".$bin2;
			}
		}
		else{
			if (strlen($bin1)<strlen($bin2)){
				for($i=strlen($bin1);$i<strlen($bin2);$i++){
					$bin1 = "0".$bin1;
				}
			}
		}
		$str_temp = "";
		$exp_result = "";
		for($i=0;$i<strlen($bin1);$i++){
			$binchar1 = substr($bin1, $i, 1);
			$binchar2 =    substr($bin2, $i, 1);
			if ($binchar1 == $binchar2){
				$xor_result = 0;
			}
			else{
				$xor_result = 1;
			}
			$str_temp = $str_temp . $xor_result;
		}
		$exp_result = bindec($str_temp);
		return $exp_result;
	}

	 function Crip_Senha($pwd)
	   {
		$senha     = $pwd;
		$Key       = 200;
		$senha_len = strlen($senha);
		  for($i=1;$i <= $senha_len;$i++)
		   {
			$Char  	 = substr($senha,$i,1);
			$CodAsc  = ord($Char);
			$HexvXor = chr(f_xor($CodAsc,$Key));
			$PwdCrip = $PwdCrip . $HexvXor;
		  }
		  return $PwdCrip;
	  }
	#-------------------------------------------------------


	#---- Função p/ Gerar Código Javascript p/ Selecionar opções de janelas externas
	  function SelectOption($local,$form,$field_ID,$field_TEXT,$win_fecha)
	    {
	     $vJS = "<script language=\"Javascript\">".chr(13);
		 $vJS = $vJS . " function SelectOption(ID,Desc) ".chr(13);
		 $vJS = $vJS . "  { ".chr(13);
		 $vJS = $vJS . "   ".$local.".".$form.".".$field_ID.".value=ID; ".chr(13);
		 $vJS = $vJS . "   ".$local.".".$form.".".$field_TEXT.".value=Desc; ".chr(13);
		 $vJS = $vJS . "  ".$win_fecha.".close();".chr(13);
		 $vJS = $vJS . "  } ".chr(13);
		 $vJS = $vJS . " </script> ".chr(13);

		 echo $vJS;
		}
	#-------------------------------------------------------

	#---- Função p/ Gerar Código Javascript p/ Selecionar opções de janelas externas e redirecionar
	  function RedirectSelectOption($local,$url,$params,$win_fecha)
	    {
		 $x   = count($params);
		 for($ct=0;$ct < $x;$ct++)
		    {
			 if($ct == 0)
			   {
			     $vParams  = $params[$ct]."='+".$params[$ct]."+'";
				 $vParamsF = $params[$ct];
			   }
			 else
			   {
			    $vParamsF = $vParamsF.",".$params[$ct];
			    if($ct == ($x-1))
				  {
			       $vParams = $vParams ."&".$params[$ct]."='+".$params[$ct];
				  }
				else
				  {
				   $vParams = $vParams ."&".$params[$ct]."='+".$params[$ct]."+'";
				  }
			   }
			}

	     $vJS = "<script language=\"Javascript\">".chr(13);
		 $vJS = $vJS . " function RedirectSelectOption(".$vParamsF.") ".chr(13);
		 $vJS = $vJS . "  { ".chr(13);
		 $vJS = $vJS . "   ".$local.".location='".$url."?".$vParams.";".chr(13);
		 #$vJS = $vJS . "   alert('".$url."?".$ID_Chave."='+".$ID_Chave."+'&".$ID_Inc."='+".$ID_Inc.");".chr(13);
		 if ($win_fecha)
		 	$vJS = $vJS . "  ".$win_fecha.".close();".chr(13);
		 $vJS = $vJS . "  } ".chr(13);
		 $vJS = $vJS . " </script> ".chr(13);

		 echo $vJS;
		}
	#-------------------------------------------------------

	#---- Função p/ Redirecionar
		function Redirect($url)
		  {
		   echo "<script>".chr(13);
		   echo "window.location='".$url."';".chr(13);
		   echo "</script>".chr(13);
		  }
    #-------------------------------------------------------

	#---- Função p/ Redirecionar
	    function SepData($op,$Data)
		  {
		  if($op == 'y')
		    {
			 $Data = explode("/",$Data);
			 $Ano  = $Data[2];
			 return $Ano;
			}
		  if($op == 'm')
		    {
			 $Data = explode("/",$Data);
			 $Mes  = $Data[1];
			 return $Mes;
			}
		  if($op == 'd')
		    {
			 $Data = explode("/",$Data);
			 $Dia  = $Data[0];
			 return $Dia;
			}
		  if($op == 'y,m,d')
		    {
			 $Data = explode("/",$Data);
			 $Dia  = $Data[2]."-".$Data[1]."-".$Data[0];
			 return $Dia;
			}
		 }
	#-------------------------------------------------------

	#---- Função p/ Abreviar String
		function AbreviaTexto($str_text,$maxlen)
		   {
		     if(strlen(html_entity_decode($str_text,ENT_QUOTES)) > $maxlen)
		  		{
		   			return substr(html_entity_decode($str_text,ENT_QUOTES),0,($maxlen-3))."...";
		  		}
			else
		  		{
		   			return html_entity_decode($str_text,ENT_QUOTES);
		  		}
		   }

	#-------------------------------------------------------

	#function DATEADD
	# By Everton

		function DateAdd($intervalo,$numero,$date)

			{
				$date_array = explode("/",$date);

				$dia = $date_array[0];
				$mes = $date_array[1];
				$ano = $date_array[2];

				switch($intervalo)
					{
					  case 'd':
					  	$dia += $numero;
						break;

					  case 'm':
					  	$mes += $numero;
						break;

					  case 'aaaa':
					  	$ano += $numero;
						break;

					}
				if($mes == 2 && $dia > 28)
				   {
				     $dia = 28;
					 if(checkdate(2,29,$ano))
					   {
					 	 $dia = 29;
					   }
				   }

				if($mes != 2 && $dia == 31 && ($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11))
				   {
				     $dia = 30;
				   }

				$date = mktime(0, 0, 0, $mes, $dia, $ano);
				$date = gmstrftime("%d/%m/%Y",$date);
				return $date;

			}

	 #------------------------------------------------------



	#---- Função p/ Adicionar Hora
		function HourAdd($interval, $number, $date) {

			/*
			yyyy 	=	year
			q 		=	Quarter
			m 		=	Month
			y 		=	Day of year
			d 		=	Day
			w 		=	Weekday
			ww 		=	Week of year
			h 		=	Hour
			n 		=	Minute
			s 		=	Second
			*/

			$date_time_array = getdate($date);
			$hours = $date_time_array['hours'];
			$minutes = $date_time_array['minutes'];
			$seconds = $date_time_array['seconds'];
			$month = $date_time_array['mon'];
			$day = $date_time_array['mday'];
			$year = $date_time_array['year'];

			switch ($interval) {

				case 'yyyy':
					$year+=$number;
					break;
				case 'q':
					$year+=($number*3);
					break;
				case 'm':
					$month+=$number;
					break;
				case 'y':
				case 'd':
				case 'w':
					$day+=$number;
					break;
				case 'ww':
					$day+=($number*7);
					break;
				case 'h':
					$hours+=$number;
					break;
				case 'n':
					$minutes+=$number;
					break;
				case 's':
					$seconds+=$number;
					break;
			}
			   $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
			return $timestamp;
		}
		#-------------------------------------------------------

	function phpAlert($str,$url)
		{
			echo "<script>";
			echo "alert('".$str."');";
			echo "window.location='".$url."';";
			echo "</script>";
		}

$ASCII_SPC_MIN = "àáâãäåæçèéêëìíîïðñòóôõöùúûüýÿžš";
$ASCII_SPC_MAX = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝŸŽŠ";

if(!function_exists('str2upper')){
	function str2upper($text) {
		global $ASCII_SPC_MIN,$ASCII_SPC_MAX;
		return strtr(strtoupper($text),$ASCII_SPC_MIN,$ASCII_SPC_MAX);
	}
}

if(!function_exists('str2lower')){
	function str2lower($text) {
		global $ASCII_SPC_MIN,$ASCII_SPC_MAX;
		return strtr(strtolower($text),$ASCII_SPC_MAX,$ASCII_SPC_MIN);
	}
}

if(!function_exists('ucsmart')){
function ucsmart($text) {
    global $ASCII_SPC_MIN;
    return preg_replace(
        '/([^a-z'.$ASCII_SPC_MIN.']|^)([a-z'.$ASCII_SPC_MIN.'])/e',
        '"$1".str2upper("$2")',
        str2lower($text)
    );
}
}



if(!function_exists('converte')){

function converte($str){
   $oldstr =	ucsmart($str);
   $stringtext = explode(" ",$oldstr);
   $newstr = "";
   for($x=0;$x<count($stringtext);$x++){
    if($x == 0){
	 if(strlen($stringtext[$x]) < 3)
	   $newstr.= strtolower($stringtext[$x]);
	 else
	   $newstr.= $stringtext[$x];
	}else{
 	 if(strlen($stringtext[$x]) < 3)
	   $newstr.= " ".strtolower($stringtext[$x]);
	 else
	   $newstr.= " ".$stringtext[$x];
	}
   }
   return $newstr;
}
}

if(!function_exists('RemoveAcentos')){
function RemoveAcentos($var){
	// Variavel recebendo a string que não será tratada para futura comparação
	$ant = $var;// Variavel recebendo a string já fazendo as substituições
	$var = ereg_replace("[ÁÀÂÃ]","A",$var);
	$var = ereg_replace("[áàâãª]","a",$var);
	$var = ereg_replace("[ÉÈÊ]","E",$var);
	$var = ereg_replace("[éèê]","e",$var);
	$var = ereg_replace("[íÍ]","i",$var);
	$var = ereg_replace("[ÓÒÔÕ]","O",$var);
	$var = ereg_replace("[óòôõº]","o",$var);
	$var = ereg_replace("[ÚÙÛ]","U",$var);
	$var = ereg_replace("[úùû]","u",$var);
	$var = str_replace("Ç","C",$var);
	$var = str_replace("ç","c",$var);// Listando o resultado anterior sem substituição
	return $var;
}
}


function CompletaString($str,$quant,$str_compl){
	$len = strlen($str);
	if($len >= $quant){
		$retorno = substr($str,0,$quant);
	}else{
		$dif = $quant - $len;
		for($i=0; $i<$dif; $i++ ){
			$compl .= (string) $str_compl;
		}
		$retorno = $str.$compl;
	}
	return $retorno;
}

function pegaContaBancariaCurso($ID_Curso){
	////////////////////////////////////////////////////////////////////////////
	////////////////PEGA DADOS CONTA PELO ID CURSO//////////////////////////////
	////////////////////////////////////////////////////////////////////////////
	//require("connect.php");
	require_once("../class/Connect.class.php");
	$con = Conexao::singleton();
	$select_conta = "SELECT a.Agencia_ContaBancaria, a.CC_ContaBancaria, a.CPFCNPJ_ContaBancaria, a.ID_Configuracao, a.Titular_ContaBancaria,
							b.Nome_Banco, b.Numero_Banco
					 FROM  tcontabancaria a JOIN
						   tbanco b ON a.ID_Banco = b.ID_Banco JOIN
						   tcurso c ON a.ID_ContaBancaria = c.ID_ContaBancaria
					 WHERE c.ID_Curso = ".$ID_Curso." ";
	//echo $select_conta;
	$result_conta = $con->consultar($select_conta);
	$row_conta = mysql_fetch_array($result_conta);

	$retorno['Agencia'] 		= $row_conta['Agencia_ContaBancaria'];
	$retorno['Conta'] 			= $row_conta['CC_ContaBancaria'];
	$retorno['CPFCNPJ'] 		= $row_conta['CPFCNPJ_ContaBancaria'];
	$retorno['Nome_Banco'] 		= $row_conta['Nome_Banco'];
	$retorno['Numero_Banco'] 	= $row_conta['Numero_Banco'];
	$retorno['ID_Configuracao'] = $row_conta['ID_Configuracao'];
	$retorno['Titular_ContaBancaria'] = $row_conta['Titular_ContaBancaria'];

	return $retorno;
}

function ConsultaBoletoInscrito($CPF,$ID_Turma){
	//require("connect.php");
	require_once("../class/Connect.class.php");
	$con = Conexao::singleton();
	//CONSULTA SE BOLETO DA INSCRIÇÃO FOI PAGO
	$select = "SELECT a.ID_ContaReceberStatus, a.Vlr_ContaReceber
			   FROM tcontareceber_1 a JOIN tcadastrosite b ON a.ID_Site = b.ID_Site
			   WHERE b.CPF = '".$CPF."' AND
					 a.ID_Turma = ".$ID_Turma." AND
					 a.Categ_ContaReceber = 3  ";
	$result = $con->consultar($select);
	return $result;
}



function GereInscricao(){
	
	if($_POST[valorInscr]){
		
		///////////////////////////DADOS BOLETO//////////////////////////
		$vlIncr = $_POST[valorInscr];
		$dtVenc = explode("/",$_POST[dtVenc]);
		$dt_venc_ts = mktime(0,0,0,$dtVenc[1],$dtVenc[0],$dtVenc[2]);
		$dtVenc = $dtVenc[2]."-".$dtVenc[1]."-".$dtVenc[0];
		/////////////////////////////////////////////////////////////////
		
		//if($_SESSION['teste']){
			
			$hj = mktime(0,0,0,date(m),date(d),date(y));
			
			$sel_dt_public = "SELECT DtPublicacao_Turma FROM tturma WHERE ID_Turma = ".$_POST['ID']." ";
			$res_dt_public = mysql_query($sel_dt_public);
			$row_dt_public = mysql_fetch_array($res_dt_public);
			$dt_p = explode("-",$row_dt_public['DtPublicacao_Turma']);		
		
			///CALCULA A QUANTIDADE DE DIAS ANTES DA DATA DE PUBLICACAO///
			$dt_public = mktime(0,0,0,$dt_p[1],$dt_p[2],$dt_p[0]);
			$dif = ($dt_public - $hj);
			$dias_dif = (($dif / 60) / 60)/24;
			
			if($dias_dif >= 7){
				$hj += (86400 * 3);
				$dtVenc = date('Y-m-d',$hj);
			}else{
				$dtVenc = date('Y-m-d');
			}
			
			//////////////////////////////////////////////////////////////
			//exit;
		//}
		
		$email = htmlspecialchars($_POST['txt_email'],ENT_QUOTES);
					
		if($_POST[recadastro]){
			
			
			//if($_SESSION['teste']){
				$select = "SELECT ID_Aluno,
								  Nome_Aluno,
								  DtNasc_Aluno,
								  Sexo_Aluno,
								  ID_EstadoCivil,
								  Naturalidade_Aluno,
								  Nacionalidade_Aluno,
								  Endereco_Aluno,
								  Complem_Aluno,
								  Bairro_Aluno,
								  Cep_Aluno,
								  ID_UF,
								  ID_Cidade,
								  TelCel_Aluno,
								  TelCom_Aluno,
								  TelRes_Aluno,
								  CPF_Aluno,
								  RG_Aluno
						   FROM taluno 
						   WHERE CPF_Aluno = '".htmlspecialchars($_POST['txt_cpf'],ENT_QUOTES)."' 
						   
						   UNION
						   
						   SELECT '' AS ID_Aluno,
								  Nome AS Nome_Aluno,
								  DtNasc AS DtNasc_Aluno,
								  Sexo AS Sexo_Aluno,
								  EstCivil AS ID_EstadoCivil,
								  Naturalidade AS Naturalidade_Aluno,
								  Nacionalidade AS Nacionalidade_Aluno,
								  Endereco AS Endereco_Aluno,
								  Complemento AS Complem_Aluno,
								  Bairro AS Bairro_Aluno,
								  Cep AS Cep_Aluno,
								  ID_UF,
								  ID_Cidade,
								  TelCel AS TelCel_Aluno,
								  TelCom AS TelCom_Aluno,
								  TelRes AS TelRes_Aluno,
								  CPF AS CPF_Aluno,
								  RG AS RG_Aluno 
						   FROM tcadastrosite 
						   WHERE CPF = '".htmlspecialchars($_POST['txt_cpf'],ENT_QUOTES)."'
						   
						   LIMIT 1
						   
						   ";
						   
				//echo $select."<br><br>";		   
			//}else{
			//	$select = "SELECT * FROM taluno WHERE CPF_Aluno = '".htmlspecialchars($_POST['txt_cpf'],ENT_QUOTES)."' ";
			//}
			
			
			$result = mysql_query($select);
			
			$row = mysql_fetch_array($result);
			
			$id_temp			= $row[ID_Aluno];
			$nome 				= $row[Nome_Aluno];
			$data_nascimento 	= $row[DtNasc_Aluno];
			$sexo				= $row[Sexo_Aluno];
			$estado_civil		= $row[ID_EstadoCivil];
			$naturalidade		= $row[Naturalidade_Aluno];
			$nacionalidade		= $row[Nacionalidade_Aluno];
			$endereco			= $row[Endereco_Aluno];
			$complemento		= $row[Complem_Aluno];
			$bairro				= $row[Bairro_Aluno];
			$cep				= $row[Cep_Aluno];
			$uf					= $row[ID_UF];
			$cidade				= $row[ID_Cidade];
			$telcel				= $row[TelCel_Aluno];
			$telcom				= $row[TelCom_Aluno];
			$telres				= $row[TelRes_Aluno];
			$cpf				= $row[CPF_Aluno];
			$rg					= $row[RG_Aluno];
			
			//ALTERAR EMAIL
			$email_ant			= $row[Email_Aluno];
			if($email_ant != $email){
				$update = "UPDATE taluno SET Email_Aluno = '".$email."' WHERE ID_Aluno = ".$id_temp." ";
				mysql_query($update);
			}
			
		}else{
			$eData = explode("/",$_POST['txt_DtNasc']);
			$eData = $eData['2'].$eData['1'].$eData['0'];
			
			//Cadastro
			$nome 				= htmlspecialchars($_POST['txt_nome'],ENT_QUOTES);
			$data_nascimento 	= htmlspecialchars($eData,ENT_QUOTES);
			$sexo				= htmlspecialchars($_POST['txt_Sexo'],ENT_QUOTES);
			$estado_civil		= htmlspecialchars($_POST['txt_EstadoCivil'],ENT_QUOTES);
			$naturalidade		= htmlspecialchars($_POST['txt_naturalidade'],ENT_QUOTES);
			$nacionalidade		= htmlspecialchars($_POST['txt_nacionalidade'],ENT_QUOTES);
			$endereco			= htmlspecialchars($_POST['txt_endereco'],ENT_QUOTES);
			$complemento		= htmlspecialchars($_POST['txt_complemento'],ENT_QUOTES);
			$bairro				= htmlspecialchars($_POST['txt_bairro'],ENT_QUOTES);
			$cep				= htmlspecialchars($_POST['txt_cep'],ENT_QUOTES);
			$uf					= htmlspecialchars($_POST['ID_UF'],ENT_QUOTES);
			$cidade				= htmlspecialchars($_POST['txt_cidade'],ENT_QUOTES);
			$telcel				= htmlspecialchars($_POST['txt_celular'],ENT_QUOTES);
			$telcom				= htmlspecialchars($_POST['txt_telcom'],ENT_QUOTES);
			$telres				= htmlspecialchars($_POST['txt_telres'],ENT_QUOTES);
			$cpf				= htmlspecialchars($_POST['txt_cpf'],ENT_QUOTES);
			$rg					= htmlspecialchars($_POST['txt_rg'],ENT_QUOTES);
		}
		
		$soube				= htmlspecialchars($_POST['txt_soubecursos'],ENT_QUOTES);
		$nome_representante	= htmlspecialchars($_POST['nome_representante'],ENT_QUOTES);
		$area				= htmlspecialchars($_POST['ID_Area'],ENT_QUOTES);
		$curso 				= htmlspecialchars($_POST['ID_Curso'],ENT_QUOTES);
		$curso_cidade		= htmlspecialchars($_POST['ID_Cidade'],ENT_QUOTES);
		$turma				= htmlspecialchars($_POST['ID'],ENT_QUOTES);
		$pagamento	 		= htmlspecialchars($_POST['opt_pagto'],ENT_QUOTES);
	
		
		$SQLstring_aluno = "INSERT INTO tcadastrosite SET
				data_cadastro = now(),
				ativo = 1,
				Nome = '$nome',
				DtNasc = '$data_nascimento',
				Sexo = '$sexo',
				EstCivil = '$estado_civil',
				Naturalidade = '$naturalidade',
				Nacionalidade ='$nacionalidade',
				Endereco = '$endereco',
				Complemento = '$complemento',
				Bairro = '$bairro',
				Cep = '$cep',
				ID_UF = '$uf',
				ID_Cidade = '$cidade',
				TelCel = '$telcel',
				TelRes = '$telres',
				TelCom = '$telcom',
				Email = '$email',
				CPF = '$cpf',
				RG = '$rg',
				Soube = '$soube',
				nome_representante = '$nome_representante',
				Pagamento = '$pagamento',
				ID_Turma = '$turma',
				ID_Curso = '$curso',
				ID_Area = '$area',
				ID_Curso_Cidade = '$curso_cidade'";
		
		////////////////////////////////////////////////////////////////////////
		////CONFERI SE JÁ EXISTE ESSE CPF E TURMA CADASTRADO/////
		
		$select_jaexist = "SELECT * FROM tcadastrosite WHERE CPF = '".$cpf."' AND ID_Turma = ".$turma." ";
		$result_jaexist = mysql_query($select_jaexist);
		if(mysql_num_rows($result_jaexist)){
			return "<erro>Você já se cadastrou para essa turma!</erro>";
		}
	
		$rsQuery = mysql_query($SQLstring_aluno);
		if($rsQuery){		
			$select_last_id = "SELECT LAST_INSERT_ID() AS id";
			$result_last_id = mysql_query($select_last_id);
			$row = mysql_fetch_array($result_last_id);
			
			$ID_Site = $row[id];
			
			$NID_ContaReceber = Gera_Boleto($turma,$ID_Site,$vlIncr,3,$dtVenc);
		
		}
	
		return array('ID_Site'=>$ID_Site,'ID_ContaReceber'=>$NID_ContaReceber,'ID_Turma'=>$turma,'CPF'=>$cpf);
	}

}


function ImprimirInscricao($CPF,$ID_Turma){
		
	$CPF = $_GET['CPF'];
	$ID_Turma = $_GET['ID_Turma'];
	
	
	$select = "SELECT 	a.Nome, 
						a.DtNasc,
						a.Sexo,
						a.EstCivil,
						a.Naturalidade,
						a.Nacionalidade,
						a.Endereco,
						a.Complemento,
						a.Bairro,
						a.Cep,
						a.ID_UF,
						a.ID_Cidade,
						a.TelCel,
						a.TelCom,
						a.TelRes,
						a.CPF,
						a.RG,
						a.ID_Turma,
						a.ID_Curso,
						a.Pagamento,
						DATE_FORMAT(a.data_cadastro,'%d/%m/%Y') AS data_cadastro,
						b.QtdParc_TurmaPagamento,
						b.VlrParc_TurmaPagamento,
						a.Email,
						'' as nome_representante
	   FROM tcadastrosite a LEFT JOIN
			tturmapagamento b ON a.ID_Turma = b.ID_Turma AND a.Pagamento = b.ID_Pagamento
	   WHERE a.CPF = '".htmlspecialchars($CPF,ENT_QUOTES)."' AND 
			 a.ID_Turma = '".htmlspecialchars($ID_Turma,ENT_QUOTES)."' 
			 
	   UNION
	   
	   SELECT 	a.Nome_Aluno AS Nome, 
				a.DtNasc_Aluno AS DtNasc,
				a.Sexo_Aluno AS Sexo,
				a.ID_EstadoCivil AS EstCivil,
				a.Naturalidade_Aluno AS Naturalidade,
				a.Nacionalidade_Aluno AS Nacionalidade,
				a.Endereco_Aluno AS Endereco,
				a.Complem_Aluno AS Complemento,
				a.Bairro_Aluno AS Bairro,
				a.Cep_Aluno AS Cep,
				a.ID_UF,
				a.ID_Cidade AS ID_Cidade,
				a.TelCel_Aluno AS TelCel,
				a.TelCom_Aluno AS TelCom,
				a.TelRes_Aluno AS TelRes,
				a.CPF_Aluno AS CPF,
				a.RG_Aluno AS RG,
				x.ID_Turma,
				'' AS ID_Curso,
				x.ID_Pagamento AS Pagamento,
				DATE_FORMAT(a.DtCadastro_Aluno,'%d/%m/%Y') AS data_cadastro,
				b.QtdParc_TurmaPagamento,
				b.VlrParc_TurmaPagamento,
				a.Email_Aluno AS Email,
				c.Apelido_Representante as nome_representante
				
		FROM taluno a JOIN 
			 tcontareceber_1 x ON a.ID_Aluno = x.ID_Aluno JOIN
			 tturmapagamento b ON x.ID_Turma = b.ID_Turma AND x.ID_Pagamento = b.ID_Pagamento 
			 LEFT JOIN trepresentante c ON x.ID_Representante = c.ID_Representante
			 
		
		WHERE a.CPF_Aluno = '".htmlspecialchars($CPF,ENT_QUOTES)."' AND 
			  x.ID_Turma = '".htmlspecialchars($ID_Turma,ENT_QUOTES)."'
			 
	   ";
	//echo $select;

	
	$result = mysql_query($select);
	
	$row = mysql_fetch_array($result);
	
	$nome 				= $row[Nome];
	$data_nascimento 	= $row[DtNasc];
	$sexo				= $row[Sexo];
	$estado_civil		= $row[EstCivil];
	$naturalidade		= $row[Naturalidade];
	$nacionalidade		= $row[Nacionalidade];
	$endereco			= $row[Endereco];
	$complemento		= $row[Complemento];
	$bairro				= $row[Bairro];
	$cep				= $row[Cep];
	$uf					= $row[ID_UF];
	$cidade				= $row[ID_Cidade];
	$telcel				= $row[TelCel];
	$telcom				= $row[TelCom];
	$telres				= $row[TelRes];
	$cpf				= $row[CPF];
	$rg					= $row[RG];
	$email				= $row[Email];
	
	$soube				= $row[Soube];
	$nome_representante	= $row[nome_representante];	
	
	
	$area				= $row[ID_Area];
	$curso 				= $row[ID_Curso];
	$curso_cidade		= $row[ID_Curso_Cidade];
	$turma				= $row[ID_Turma];
	
	$data_cadastro 		= $row[data_cadastro];
		
	if($row[QtdParc_TurmaPagamento]){		
		$pagamento	= $row[QtdParc_TurmaPagamento]." x ".number_format($row[VlrParc_TurmaPagamento],2,',','.');
	} else 
	{
		$pagamento	= $row["Pagamento"];
	}
	
	////////////////////////////////////////////////////////////////////////
						
	$SQLstring = "SELECT 	a.Titulo_Curso, 
						a.EAD,
						a.MostraCidadeEad,
						d.Desc_AreaConhece, 
						e.Nome_Cidade, 
						c.DtPublicacao_Turma, 
						a.ID_Banco, 
						c.Codigo_Turma, 
						DATE_FORMAT(c.DtPublicacao_Turma,'%d/%m/%Y') AS Dt_CursoTurma
	
				FROM 	tcurso a JOIN 
						tturma c ON a.ID_Curso = c.ID_Curso JOIN 
						tareaconhece d ON a.ID_AreaConhece = d.ID_AreaConhece JOIN 
						tcidade e ON c.ID_Cidade = e.ID_Cidade
						
				WHERE c.ID_Turma = ".$turma;
	
	
	$rsQuery = mysql_query($SQLstring);
	$nr = mysql_num_rows($rsQuery);
	if($nr > 0)
	{
		$row = mysql_fetch_array($rsQuery);
		
		$vDesc_Cidade 	= 	$row["Nome_Cidade"];
		$vDesc_Area		= 	$row["Desc_AreaConhece"];
		$vDesc_Curso 	= 	$row["Titulo_Curso"];
		$vDATA 			= 	$row["Data_CursoTurma"];
		$CodTurma		= 	$row["Codigo_Turma"];
		$Dt_CursoTurma 	=  	$row['Dt_CursoTurma'];
		$EAD 			=   $row['EAD'];
		$MostraCidadeEad=   $row['MostraCidadeEad'];
		
	}
	
	
	$SQLstring = "SELECT Sigla_UF,Nome_Cidade 
		FROM tcidade
		WHERE ID_Cidade = ".$cidade;
	
	$rsQuery = mysql_query($SQLstring);
	$nr = mysql_num_rows($rsQuery);
	if($nr > 0)
	{
		$linha 		= mysql_fetch_array($rsQuery);
		$cidade 	= 	$linha["Nome_Cidade"];
		$uf			= 	$linha["Sigla_UF"];					
	}
	
	
	if(is_int($cidade)){
		$SQLstring = "SELECT  Nome_Cidade  FROM tcidade WHERE ID_Cidade = " . $cidade;
		$rsQuery = mysql_query($SQLstring,$conn);
		$row = mysql_fetch_array($rsQuery);
		$_cidade = $row["Nome_Cidade"];
	}else{
		$_cidade = $cidade;
	}
	
	
	if(is_int($uf)){
		$SQLstring = "SELECT Sigla_UF FROM tuf WHERE ID_UF = ".$uf;
		$rsQuery = mysql_query($SQLstring,$conn);
		$row = mysql_fetch_array($rsQuery);
		$_uf = $row["Sigla_UF"];
	}else{
		$_uf = $uf;
	}
	
	if(!$EAD || $MostraCidadeEad == 1){
		$htmlCidade = 'Cidade:';		
	} else {
		$htmlCidade = '';
		$vDesc_Cidade = '';		
	}
	
	$html = '
	<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>..:: Central de Cursos - UGF ::..</title>
		<script src="../js/funcao.js"></script>
		<link href="../css/internas.css" rel="stylesheet" type="text/css" />
		<style type="text/css">
		
		html{
			height:100%;
		}
		
		body{
			height:100%;
		}
		
		.type01{
			font-family:Arial, Helvetica, sans-serif;
			font-size:12px;
			font-weight:bold;
		}
		.type02{
			font-family:Arial, Helvetica, sans-serif;
			font-size:12px;
		}
		.type03{
			font-family:Arial, Helvetica, sans-serif;
			font-size:14px;
			font-weight:bold;
			font-style:italic;
		}
		.type04{
			font-family:Arial, Helvetica, sans-serif;
			font-size:10px;
		}
		</style>
		</head>
		<body>
		<h3 class="titulo_gamafilho">Inscrição</h3>
		  
		   <div style="position:absolute; top:843px; left:30px; width: 715px; height: 660px;">
			 <hr noshade="noshade" size="1" color="#bfd0f6" />
					<table cellpadding="4" cellspacing="1" width="100%">
					<tr>
					  <td class="type02"><span class="texto_spadrao"><b>PARA CONCLUIR SUA INSCRI&Ccedil;&Atilde;O: <br />
					  </b></span><span class="link_curso">Deposite a taxa de inscri&ccedil;&atilde;o no<strong> Banco Itaú</strong> - Ag. 0046 - conta corrente 75975-9 ou no <b>Banco Bradesco</b> Ag. 2913-0 / Conta: 1961-5 em nome de <span>Central de Cursos e Eventos</span>.</span>
					  <hr noshade="noshade" size="1" color="#bfd0f6" />
					  <span class="link_curso"><b>Cursos de Extensão:</b></span><br>
				<span>Remeta por fax a ficha de inscrição junto ao comprovante de depósito da taxa de inscrição e os documentos exigidos, (11) 2714-5673.<br>
				<b>Ou v&aacute; pessoalmente em um dos endere&ccedil;os abaixo:</b><br />
				<span>- Central de Cursos  UGF / 
				Rua Treze de Maio, 681 &ndash; Bela Vista - SP / 
				CEP: 01327-000</span><br />
				<b>Documentos Necessários - Extensão</b>:<br>
			
			- Cópia de CPF, RG;<br>
			- Comprovante de dep&oacute;sito da taxa de inscrição.<br>
			- Ficha de inscrição completa.</span><br />
			<br />
			<br />
			<span class="link_curso"><b>Cursos de P&oacute;s:</b></span><br>
				<span>Remeta por SEDEX a ficha de inscrição junto aos documentos exigidos e o comprovante original do depósito bancário:
				<br />
				Central de Cursos - UGF / 
				Rua Treze de Maio, 681 &ndash; Bela Vista - SP / 
				CEP: 01327-000</span><br />
				<b>Ou v&aacute; pessoalmente em um dos endereços abaixo:</b><br>
		
		<span>- Central de Cursos  UGF / 
				Rua Treze de Maio, 681 &ndash; Bela Vista - SP / 
				CEP: 01327-000</span><br>
				- 
		
		<span>Downtown - UGF /
		Av. das Am&eacute;ricas, 500 &ndash; Barra da  Tijuca / 
		Rio de Janeiro - RJ</span><br>
		<b>Documentos Necessários - P&oacute;s:</b><br>
			
			- Cópia de CPF, RG;
			<br>
			- Foto 3x4 (para crachá de identificação pessoal);<br>
			- Ficha de inscrição completa.<br>
			- Cópia autenticada do diploma ou declaração de conclusão do curso superior, traduzido e v&aacute;lido no Brasil**<br>
			- Curriculo Vitae resumido.<br />
			<br />
			<br></span>
			<hr noshade="noshade" size="1" color="#bfd0f6" />
			<span class="texto_spadrao"><b>INFORMAÇÕES GERAIS:</b></span><br>
		   
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">O pagamento da matr&iacute;cula ser&aacute; no 1&ordm; dia de aula;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">Pagamento da 1&ordf; mensalidade ser&aacute; 30 dias ap&oacute;s a matr&iacute;cula;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">N&atilde;o ser&atilde;o aceitas inscri&ccedil;&otilde;es na semana que antecede os cursos;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">Fica reservada &agrave; coordena&ccedil;&atilde;o geral a substitui&ccedil;&atilde;o de professores
				  e locais dos cursos por outros de igual qualifica&ccedil;&atilde;o, caso haja necessidade;</td>
			  </tr>
			  <tr>
				<td width="8" valign="top" class="type02">-</td>
				<td class="type02"> N&atilde;o haver&aacute; reembolso da inscri&ccedil;&atilde;o em caso de desist&ecirc;ncia do aluno;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">Optando por enviar seus documentos via SEDEX, anexar o comprovante de dep&oacute;sito original (guardar c&oacute;pia);</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">Alunos formados pela Universidade Gama Filho, com inscri&ccedil;&atilde;o realizada com at&eacute; 30 dias antes do in&iacute;cio do curso,<br />
		t&ecirc;m desconto de 10% na mensalidade;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">Alunos de institui&ccedil;&otilde;es conveniadas dever&atilde;o impreterivelmente providenciar a inscri&ccedil;&atilde;o at&eacute; 30 dias antes da data de in&iacute;cio<br />
		do curso, sendo que, se o prazo determinado n&atilde;o for cumprido, a institui&ccedil;&atilde;o se desobriga dos descontos;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">N&atilde;o ser&atilde;o aceitas inscri&ccedil;&otilde;es na semana que antecede o curso;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">Fica reservada &agrave; coordena&ccedil;&atilde;o geral a substitui&ccedil;&atilde;o de professores por outros de igual qualifica&ccedil;&atilde;o, caso haja necessidade;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">Os cursos estar&atilde;o sujeitos &agrave; confirma&ccedil;&atilde;o;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">Os cursos t&ecirc;m dura&ccedil;&atilde;o de 18 meses com freq&uuml;&ecirc;ncia de 01 final de
				  semana por m&ecirc;s, exceto Nutri&ccedil;&atilde;o Cl&iacute;nica: Fundamentos Metab&oacute;licos
				  Nutricionais que nos 7 &uacute;ltimos meses ter&aacute; freq&uuml;&ecirc;ncia de 02 finais de
				  semana por m&ecirc;s;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">Todo e qualquer desconto oferecido pela Institui&ccedil;&atilde;o n&atilde;o &eacute; cumulativo;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">As vagas s&atilde;o limitadas;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">Brasileiros ou estrangeiros graduados fora do Brasil devem apresentar
				  c&oacute;pia autenticada do diploma da gradua&ccedil;&atilde;o revalidado no Brasil;</td>
			  </tr>
			  <tr>
				<td valign="top" class="type02">-</td>
				<td class="type02">**Em caso excepcional, poder&aacute; matricular-se o aluno que apresente
				  certificado de conclus&atilde;o ou declara&ccedil;&atilde;o de conclus&atilde;o com data de
				  cola&ccedil;&atilde;o de grau, ficando o mesmo obrigado a apresentar o diploma
				  devidamente registrado antes do t&eacute;rmino do Curso.</td>
			  </tr>
			</table>
		</td>
					</tr>          
				  </table>
		<br />	<hr noshade="noshade" size="1" color="#bfd0f6" />     
		
		<input type="button" value="Imprimir" class="form" onClick="javascript: window.print();"></div>
		
		
		<div style="position:relative; top:178px; left:30px; width: 715px; height: 660px; border:1px solid #000000">
			<table width="355" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td width="679">&nbsp;</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="678" height="20" valign="top" class="type01">DADOS DO CURSO </td>
			  </tr>
			</table>
			<table width="677" border="0" align="center" cellpadding="0" cellspacing="0" style="border-top:1px solid #000000">
			  <tr>
				<td width="677">&nbsp;</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="678" height="20" valign="bottom" class="type01">Curso:</td>
			  </tr>
			  <tr>
				<td height="20" class="type02">'.$vDesc_Curso.'</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="373" height="20" valign="bottom" nowrap class="type01">&Aacute;rea:</td>
				<td width="305" valign="bottom" nowrap class="type01">'.$htmlCidade.'</td>
			  </tr>
			  <tr>
				<td width="373" height="20" nowrap class="type02">'.$vDesc_Area.'</td>
				<td width="305" nowrap class="type02">'.$vDesc_Cidade.'</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td height="20" valign="bottom" class="type01">In&iacute;cio:</td>
				<td height="20" valign="bottom" class="type01">Turma:</td>
			  </tr>
			  <tr>
				<td width="372" height="20" class="type02">'.$Dt_CursoTurma.' - '.$vDATA.'</td>
				<td width="306" class="type02">'.$CodTurma.'</td>
			  </tr>
			</table>
		<table width="355" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td width="679" height="28">&nbsp;</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="678" height="20" valign="top" class="type01">DADOS PESSOAIS </td>
			  </tr>
			</table>
			<table width="677" border="0" align="center" cellpadding="0" cellspacing="0" style="border-top:1px solid #000000">
			  <tr>
				<td width="677">&nbsp;</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="678" height="20" valign="bottom" class="type01">Nome completo: </td>
			  </tr>
			  <tr>
				<td height="20" class="type02">'.$nome.'</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="678" height="20" valign="bottom" class="type01">Endere&ccedil;o:</td>
			  </tr>
			  <tr>
				<td height="20" class="type02">'.$endereco.' '.$complemento.'</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="373" height="20" valign="bottom" nowrap class="type01">Bairro:</td>
				<td width="305" valign="bottom" nowrap class="type01">Cidade:</td>
			  </tr>
			  <tr>
				<td width="373" height="20" nowrap class="type02">'.$bairro.'</td>
				<td width="305" nowrap class="type02">		
				'.$_cidade.'
				</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="373" height="20" valign="bottom" nowrap class="type01">Estado:</td>
				<td width="305" valign="bottom" nowrap class="type01">CEP:</td>
			  </tr>
			  <tr>
				<td width="373" height="20" nowrap class="type02">'.$_uf.'</td>
				<td width="305" nowrap class="type02">'.$cep.'</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="373" height="20" valign="bottom" nowrap class="type01">Telefone residencial: </td>
				<td width="305" valign="bottom" nowrap class="type01">Telefone celular:</td>
			  </tr>
			  <tr>
				<td width="373" height="20" nowrap class="type02">'.$telres.'</td>
				<td width="305" nowrap class="type02">'.$telcel.'</td>
			  </tr>
			</table>   
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="373" height="20" valign="bottom" nowrap class="type01">Telefone comercial: </td>
				<td width="305" valign="bottom" nowrap class="type01">E-mail:</td>
			  </tr>
			  <tr>
				<td width="373" height="20" nowrap class="type02">'.$telcom.'</td>
				<td width="305" nowrap class="type02">'.$email.'</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="373" height="20" valign="bottom" nowrap class="type01">CPF/CIC:</td>
				<td width="305" valign="bottom" nowrap class="type01">RG:</td>
			  </tr>
			  <tr>
				<td width="373" height="20" nowrap class="type02">'.$cpf.'</td>
				<td width="305" nowrap class="type02">'.$rg.'</td>
			  </tr>
			</table>
			<table width="678" border="0" align="center" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="373" height="20" valign="bottom" nowrap class="type01">Como soube dos cursos ? </td>
				<td width="305" valign="bottom" nowrap class="type01">Indique a op&ccedil;&atilde;o de pagamento: </td>
			  </tr>
			  <tr>
				<td width="373" height="20" nowrap class="type02">'.$soube.'</td>
				<td width="305" nowrap class="type02">'.$pagamento.'</td>
			  </tr>
			  
				<tr>
				<td width="373" height="20" valign="bottom" nowrap class="type01">Nome do Divulgador: </td>
				<td width="305" valign="bottom" nowrap class="type01">&nbsp;</td>
			  </tr>
			  <tr>
				<td width="373" height="20" nowrap class="type02">'.$nome_representante.'</td>
				<td width="305" nowrap class="type02">&nbsp;</td>
			  </tr>      
			  
			</table>
			<table width="355" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td width="679">&nbsp;</td>
			  </tr>
			</table>
		</div>
		  <div class="type02" style="position:absolute; top:148px; left:30px; width: 308px;">Sua inscri&ccedil;&atilde;o foi efetuada com base nos dados abaixo: </div>
		  <div style="position:absolute; top:90px; left:30px; width: 691px;" class="type03">FICHA DE INSCRI&Ccedil;&Atilde;O PREENCHIDA COM SUCESSO !!! 
		  </div>
		  <div class="type04" style="position:absolute; top:158px; left:617px; width: 130px;">Data: '.$data_cadastro.' </div>
		  
		  
		</body>
		</html>';
		echo $html;
		
		$mail = new PHPMailer();
		
		$mail->IsSMTP();                                   // send via SMTP
		$mail->Host     = "192.168.0.251"; // SMTP servers
		$mail->SMTPAuth = true;     // turn on SMTP authentication
		$mail->Username = "sistema@posugf.com.br";  // SMTP username
		$mail->Password = "sis2006dev"; // SMTP password
		
		$mail->From     = "sistema@posugf.com.br";
		$mail->FromName = "Universidade Gama Filho";
		$mail->AddAddress("inscricao@posugf.com.br","UNIVERSIDADE GAMA FILHO"); 
		$mail->AddAddress("adreiaduck@posugf.com.br","UNIVERSIDADE GAMA FILHO"); 
		#$mail->AddAddress("joca_junior@terra.com.br");               // optional name
		$mail->AddReplyTo("incricao@posugf.com.br","Inscricao");
		
		
		$mail->WordWrap = 50;                              // set word wrap
		
		$mail->IsHTML(true);                               // send as HTML
		
		$mail->Subject  =  "INSCRIÇÃO PELO SITE - ".$_POST["txt_nome"];
		$mail->Body     =  $html;
		$mail->AltBody  =  "This is the text-only body";
		
		
		if(!$mail->Send())
		{
		   //echo "Email não foi enviado <p>";
		   //echo "Mailer Error: " . $mail->ErrorInfo;
		   //exit;
		}
		
}



function GereInscricao1(){
	
	
	if($_POST[valorInscr]){
		
		$cpf 				= htmlspecialchars($_POST['txt_cpf'],ENT_QUOTES);
		$soube				= htmlspecialchars($_POST['txt_soubecursos'],ENT_QUOTES);
		$nome_representante	= htmlspecialchars($_POST['nome_representante'],ENT_QUOTES);
		
		$email 				= htmlspecialchars($_POST['txt_email'],ENT_QUOTES);
		$turma				= htmlspecialchars($_POST['ID'],ENT_QUOTES);
		$pagamento	 		= htmlspecialchars($_POST['opt_pagto'],ENT_QUOTES);
		
		
		////CONFERI SE JÁ EXISTE ESSE CPF E TURMA CADASTRADO/////		
		$select_jaexist = "SELECT 1 as tipo
						   FROM tcadastrosite a
						   WHERE a.CPF = '".$cpf."' AND 
						   		 a.ID_Turma = ".$turma."
						   
						   UNION 
						   
						   SELECT 2 as tipo
						   FROM tcontareceber_1 a JOIN
						   		taluno b ON a.ID_Aluno = b.ID_Aluno AND b.CPF_Aluno = '".$cpf."'
						   WHERE a.ID_Turma = ".$turma." ";
						   
		$result_jaexist = mysql_query($select_jaexist);
		if(mysql_num_rows($result_jaexist)){
			return "<erro>Você já se cadastrou para essa turma!</erro>";
		}
		
		///////////////////////////DADOS BOLETO//////////////////////////
		$vlIncr = $_POST[valorInscr];
		$dtVenc = explode("/",$_POST[dtVenc]);
		$dt_venc_ts = mktime(0,0,0,$dtVenc[1],$dtVenc[0],$dtVenc[2]);
		$dtVenc = $dtVenc[2]."-".$dtVenc[1]."-".$dtVenc[0];
		/////////////////////////////////////////////////////////////////
		
		$hj = mktime(0,0,0,date(m),date(d),date(y));
		
		$sel_dt_public = "SELECT DtPublicacao_Turma FROM tturma WHERE ID_Turma = ".$_POST['ID']." ";
		$res_dt_public = mysql_query($sel_dt_public);
		$row_dt_public = mysql_fetch_array($res_dt_public);
		$dt_p = explode("-",$row_dt_public['DtPublicacao_Turma']);			
	
		///CALCULA A QUANTIDADE DE DIAS ANTES DA DATA DE PUBLICACAO///
		$dt_public = mktime(0,0,0,$dt_p[1],$dt_p[2],$dt_p[0]);
		$dif = ($dt_public - $hj);
		$dias_dif = (($dif / 60) / 60)/24;
		
		if($dias_dif >= 7){
			$hj += (86400 * 3);
			$dtVenc = date('Y-m-d',$hj);
		}else{
			$dtVenc = date('Y-m-d');
		}
		
		$select_taluno = "SELECT 1 FROM taluno WHERE CPF_Aluno = '".$cpf."' ";
		$res_taluno = mysql_query($select_taluno);		
		
		if($_POST[recadastro] or mysql_num_rows($res_taluno)){
			$select = "SELECT ID_Aluno, Email_Aluno
					   FROM taluno 
					   WHERE CPF_Aluno = '".htmlspecialchars($_POST['txt_cpf'],ENT_QUOTES)."'
					   LIMIT 1					   
					   ";			
			$result = mysql_query($select);			
			$row = mysql_fetch_array($result);			
			$ID_Aluno = $row[ID_Aluno];			

			//ALTERAR EMAIL
			$email_ant = $row[Email_Aluno];
			if($email_ant != $email){
				$update = "UPDATE taluno SET Email_Aluno = '".$email."' WHERE ID_Aluno = ".$ID_Aluno." ";
				mysql_query($update);
			}
		}else{
						
			$eData = explode("/",$_POST['txt_DtNasc']);
			$eData = $eData['2'].$eData['1'].$eData['0'];
			
			if(trim($_POST['txt_nome']) != ""){
				
				//Cadastro
				$nome 				= htmlspecialchars($_POST['txt_nome'],ENT_QUOTES);
				$data_nascimento 	= htmlspecialchars($eData,ENT_QUOTES);
				$sexo				= htmlspecialchars($_POST['txt_Sexo'],ENT_QUOTES);
				$estado_civil		= htmlspecialchars($_POST['txt_EstadoCivil'],ENT_QUOTES);
				$naturalidade		= htmlspecialchars($_POST['txt_naturalidade'],ENT_QUOTES);
				$nacionalidade		= htmlspecialchars($_POST['txt_nacionalidade'],ENT_QUOTES);
				$endereco			= htmlspecialchars($_POST['txt_endereco'],ENT_QUOTES);
				$complemento		= htmlspecialchars($_POST['txt_complemento'],ENT_QUOTES);
				$bairro				= htmlspecialchars($_POST['txt_bairro'],ENT_QUOTES);
				$cep				= htmlspecialchars($_POST['txt_cep'],ENT_QUOTES);
				$uf					= htmlspecialchars($_POST['ID_UF'],ENT_QUOTES);
				$cidade				= htmlspecialchars($_POST['txt_cidade'],ENT_QUOTES);
				$telcel				= htmlspecialchars($_POST['txt_celular'],ENT_QUOTES);
				$telcom				= htmlspecialchars($_POST['txt_telcom'],ENT_QUOTES);
				$telres				= htmlspecialchars($_POST['txt_telres'],ENT_QUOTES);
				$rg					= htmlspecialchars($_POST['txt_rg'],ENT_QUOTES);
				$nome_pai			= htmlspecialchars($_POST['nome_pai'],ENT_QUOTES);
				$nome_mae			= htmlspecialchars($_POST['nome_mae'],ENT_QUOTES);			
			}else{
				
				$select = "SELECT * FROM tcadastrosite WHERE CPF = '".$cpf."' ";
				$res = mysql_query($select);
				$row = mysql_fetch_array($res);
				
				$nome 				= $row['Nome'];
				$data_nascimento 	= $row['DtNasc'];
				$sexo				= $row['Sexo'];
				$estado_civil		= $row['EstCivil'];
				$naturalidade		= $row['Naturalidade'];
				$nacionalidade		= $row['Nacionalidade'];
				$endereco			= $row['Endereco'];
				$complemento		= $row['Complemento'];
				$bairro				= $row['Bairro'];
				$cep				= $row['Cep'];
				$uf					= $row['ID_UF'];
				$cidade				= $row['ID_Cidade'];
				$telcel				= $row['TelCel'];
				$telcom				= $row['TelCom'];
				$telres				= $row['TelRes'];
				$rg					= $row['RG'];
			}
			
			$SQLstring =	" INSERT INTO taluno SET ".
							" ID_Aluno				= NULL ,".
							" DtCadastro_Aluno		= NOW(),".
							" ID_Agente				= 2,".
							" Foto_Aluno			= '',".
							" Nome_Aluno			= '".$nome."',".
							" DtNasc_Aluno			= '".$data_nascimento."',".
							" Sexo_Aluno			= '".$sexo."',".
							" Naturalidade_Aluno	= '".$naturalidade."',".
							" Nacionalidade_Aluno	= '".$nacionalidade."',".
							" ID_EstadoCivil		= '".$estado_civil."',".
							" CPF_Aluno				= '".$cpf."',".
							" RG_Aluno				= '".$rg."',".
							" OrgEmiss_Aluno		= '',".
							" NomePai_Aluno			= '".$nome_pai."',".
							" NomeMae_Aluno			= '".$nome_mae."',".
							" TelRes_Aluno			= '".$telres."',".
							" TelCel_Aluno			= '".$telcel."',".
							" TelCom_Aluno			= '".$telcom."',".
							" TelComRml_Aluno		= '',".
							" TelCont_Aluno			= '',".
							" TelContNome_Aluno		= '',".
							" Email_Aluno			= '".$email."',".
							" Lembrete_Aluno		= 'CPF',".
							" Endereco_Aluno		= '".$endereco."',".
							" Complem_Aluno			= '".$complemento."',".
							" Cep_Aluno				= '".$cep."',".
							" Bairro_Aluno			= '".$bairro."',".
							" ID_UF					= '".$uf."',".
							" ID_Cidade				= '".$cidade."',".
							" ID_Referencia			= '1',".
							" ID_Divulgador			= '',".
							" Ativo_Aluno			= true";
			
			$rsQuery = mysql_query($SQLstring);
			if($rsQuery){		
				$select_last_id = "SELECT LAST_INSERT_ID() AS id";
				$result_last_id = mysql_query($select_last_id);
				$row = mysql_fetch_array($result_last_id);
				
				$ID_Aluno = $row[id];
			}	
		}	
		
	 	 $sqlRepres = "SELECT ID_Representante
	      					  FROM trepresentante 
	      				WHERE Ativo_Representante = '1' AND Apelido_Representante = '".$nome_representante."'
	      				LIMIT 1";
	      
	      $queryRepres = mysql_query($sqlRepres);
	      
	      while ($rowRepres = mysql_fetch_array($queryRepres)){
	      	$ID_Representante = $rowRepres["ID_Representante"];
	      }
		
		$NID_ContaReceber = Gera_Boleto($turma,NULL,$vlIncr,3,$dtVenc,NULL,$ID_Aluno,$pagamento,$ID_Representante);
		
				
		
		return array('ID_Site'=>$ID_Site,
					 'ID_Aluno'=>$ID_Aluno,
					 'ID_ContaReceber'=>$NID_ContaReceber,
					 'ID_Turma'=>$turma,
					 'CPF'=>$cpf,
					 'Email'=>$email);
	}
}

function isEad($ID_Turma){
	$select = "SELECT 1 
			   FROM tturma a JOIN
			   	 	tcurso b ON a.ID_Curso = b.ID_Curso 
			   WHERE a.ID_Turma = ".$ID_Turma." AND
			   		 b.EAD = 1 ";
	$res = mysql_query($select);
	return mysql_num_rows($res);
}


?>