<?php
class Controller extends System{
	
	protected $template;
	
	protected function view($nome , $vars = null, $template = true){
		if( is_array($vars) && count($vars) > 0){
			extract($vars, EXTR_PREFIX_ALL, 'view');
		}
		if($this->template && $template){
			$content = VIEWS.$nome.'.phtml';
			return require_once('template/'.$this->template.".phtml");
		}else{
			return require_once(VIEWS.$nome.'.phtml');
		}		

	}
	
	protected function setTemplate($template){
		$this->template = $template;
	}
}