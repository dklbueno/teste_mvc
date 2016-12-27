<?php
/**
  * @desc Converte Datas para BR ou US
  * @param date $data Data a ser cobvertida
  * @param string $para Destino BR ou US
  * @return date Convertida
  */
  function ConverteDatas($data_full,$para = "US") {
	if($data_full) {
		list($data,$hora) = explode(" ",$data_full);
		if($para == "BR") {
			$data = explode("-",$data);
			$data_conv = $data[2]."/".$data[1]."/".$data[0];
		} else {
			$data = explode("/",$data);
			$data_conv = $data[2]."-".$data[1]."-".$data[0];
		}
		if($hora) $data_conv .= " $hora";
	} else {
        $data_conv = "";
	}
	return $data_conv;
  }
  
  /**
   * @desc Decobre idade pela data de nascimento
   * @param $data
   * @return $idade 
   **/
  function MostraIdade($data){
  	// Declara a data! :P
    $data = ConverteDatas($data,'BR');
    
    // Separa em dia, mês e ano
    list($dia, $mes, $ano) = explode('/', $data);
    
    // Descobre que dia é hoje e retorna a unix timestamp
    $hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    // Descobre a unix timestamp da data de nascimento do fulano
    $nascimento = mktime( 0, 0, 0, $mes, $dia, $ano);
    
    // Depois apenas fazemos o cálculo já citado :)
    $idade = floor((((($hoje - $nascimento) / 60) / 60) / 24) / 365.25);
 
    return $idade;
  }
  
function hex2rgb($hex) {
	$hex = str_replace("#", "", $hex);

	if(strlen($hex) == 3) {
		$r = hexdec(substr($hex,0,1).substr($hex,0,1));
		$g = hexdec(substr($hex,1,1).substr($hex,1,1));
		$b = hexdec(substr($hex,2,1).substr($hex,2,1));
	} else {
		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2));
	}
	$rgb = array($r, $g, $b);
   //return implode(",", $rgb); // returns the rgb values separated by commas
	return $rgb; // returns an array with the rgb values
}

function FormataImpressaoMatricial($label,$texto,$size,$to="."){
	$texto_size = strlen($label.$texto);
	$novo_texto = $label;
	$sobra = $size-$texto_size;
	$sobra = ( $sobra < 0 ? $sobra*(-1) : $sobra );
	for($i = 0; $i<$sobra; $i++){
		$novo_texto.=$to;
	}
	$novo_texto.= $texto;
	return $novo_texto;
}

function cria_log($content,$tipo=null){
	$content = date('d/m/Y H:i:s')." -".($tipo?" ".$tipo." ":"")."-------------------------------------\n\n".$content;
	$fp = fopen("log.txt", "a+");
	$escreve = fwrite($fp, $content."\n\n");	 
	fclose($fp);
}

function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}