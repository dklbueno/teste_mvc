<?php
//Classe Paginação
class Helper_Paginacao
{
	public $pagina;
	public $qtd = 20;
	public $ini = 0;
	public $current_page = 1;
	public $bts;
	
	public function __construct($total,$pagina=null,$gets=null,$current_page=null,$ini=null,$qtd=null)
	{
		$this->total =    $total;
		if($pagina)		  $this->pagina = $pagina;
		if($gets)		  $this->gets = $gets;
		if($current_page) $this->current_page = $current_page;
		if($ini) 		  $this->ini = $ini; 
		if($qtd) 		  $this->qtd = $qtd;	

		$this->paginacao();
	}
	
	//Método Paginação
	public function paginacao()
	{
		$current_page = $this->current_page;
		$pagina = $this->pagina;
		$total = $this->total;
		$qtd = $this->qtd;
		$gets = $this->gets;
		
		$pages = ceil($total/$qtd);
		if($pages > 1){
			$bts .= "<div class='clear'></div>";
			$bts .= "<div id='paginacao'>";
			
			if($this->current_page > 1){
				$bts .= "<div class='seta' onclick=\"location.href='".$pagina."/current_page/1/".$gets."'\"> <<&nbsp;&nbsp; </div>";
				$bts .= "<div class='seta' onclick=\"location.href='".$pagina."/current_page/".($this->current_page-1)."/".$gets."'\"> <&nbsp;&nbsp; </div>";
			}
			
			for($i = 0; $i<$pages; $i++){
				if($i == $this->current_page-1 || ( !$this->current_page && $i == 0 )){
					$acao = "class='bt_pag_ativado' ";
				}else{
					//$acao = "class='bt_pag' onclick=\"location.href='".$pagina."?current_page=".ceil($i+1)."&".$gets."'\"";
					$acao = "class='bt_pag' onclick=\"location.href='".$pagina."/current_page/".ceil($i+1)."/".$gets."'\"";
				}
				$bts .= "<div ".$acao.">".($i+1)."</div>";
			}
			
			if($this->current_page < $pages){
				if(!$this->current_page){
					$current_page = 2;
				}else{
					$current_page = $this->current_page + 1;
				}
				$bts .= "<div class='seta' onclick=\"location.href='".$pagina."/current_page/".$current_page."/".$gets."'\"> &nbsp;&nbsp;> </div>";
				$bts .= "<div class='seta' onclick=\"location.href='".$pagina."/current_page/".$pages."/".$gets."'\"> &nbsp;&nbsp;>> </div>";
			}
			
			$bts .= "</div>";
			//$bts .= "<div class='clear'></div>";
		}
		
		$this->bts = $bts;
	}
	
	public function getPaginacao(){
		return $this->bts;
	}
}