<?php
class System{

	private $_url;
	private $_explode;
	public $_controller;
	public $_action;
	public $_params;
	public $post;
	public $get;
	public $session;

	public function __construct(){
		$this->setUrl();
		$this->setExplode();
		$this->setController();
		$this->setAction();
		$this->setParams();
		$this->setPost($_POST);
		$this->setGet($_GET);
		$this->setSession($_SESSION);
	}

	private function setPost($post = null){
		if($post){
			$this->post = self::cleanVariable($post);
		}
	}

	private function setGet($get = null){
		if($get){
			$this->get = self::cleanVariable($get);
		}
	}

	private function setSession($session = null){
		if($session){
			$this->session = $session;
		}
	}

	static function cleanVariable( $mx_params , $bo_addslashes = true , $bo_striptags = true , $v_allowed_tags = null )
	{
		if( is_array( $mx_params ) )
		foreach( $mx_params AS $key => $value )
		$mx_params[$key] = self::cleanVariable( $value , $bo_addslashes , $bo_striptags , $v_allowed_tags );
		else
		{
			if($bo_addslashes == true)
			$mx_params = addslashes($mx_params);
			if($bo_striptags ==  true)
			$mx_params = strip_tags($mx_params,$v_allowed_tags);
		}
		return $mx_params;
	}

	private function setUrl(){

		$_GET['url'] = (isset($_GET['url']) ? $_GET['url'] : 'usuario/home');

		$this->_url = $_GET['url'];
	}

	private function setExplode(){
		$this->_explode = explode('/', $this->_url);
	}

	private function setController(){
		$this->_controller = $this->_explode[0];
	}

	private function setAction(){
		$ac = (!isset($this->_explode[1]) || $this->_explode[1] == null || $this->_explode[1] == 'index' ? 'index_action' : $this->_explode[1]);
		$this->_action = $ac;
	}

	private function setParams(){
		unset( $this->_explode[0], $this->_explode[1]);
		if( end($this->_explode) == null)
		array_pop( $this->_explode );
		$i = 0;
		if(!empty($this->_explode)){
			foreach($this->_explode as $val){
				if($i % 2 == 0){
					$key[] = $val;
				}else{
					$value[] = $val;
				}
				$i++;
			}
		}else{
			$key = array();
			$value = array();
		}
		if(count($key) == count($value) && !empty($key) && !empty($value))
		$this->_params = array_combine($key, $value);
		else
		$this->_params = array();
		return $this->_params;
	}

	public function getParam( $name = null ){
		if( $name != null )
		return $this->_params[ $name ];
		else
		return $this->_params;
	}

	public function run(){

		//$this->_controller = "usuario";
		//$this->_action = "home";

		$controller_path = CONTROLLERS . $this->_controller . "Controller.php";
		if(!file_exists($controller_path))
		die('Houve um erro. O Controller não existe!');
		require_once($controller_path);

		$app = new $this->_controller();
		if(!method_exists($app, $this->_action))
		die('Houve um erro. A Action não existe!');

		$action = $this->_action;
			
		$app->$action();
	}
}