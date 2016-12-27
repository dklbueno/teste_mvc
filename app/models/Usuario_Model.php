<?php
class Usuario_Model extends Model{
	
	public $_tabela = "usuario";
	public $post;

	function __construct($post){
		parent::__construct();
		$this->post = $post;
	}
	
	public function getUsuarios($id){
		return $this->select( "*", ($id?"user_id=$id":null) );
	}
	
	public function deleteUsuario($id){
		return $this->delete("user_id=$id");
	}
	
	public function addUsuario($data){
		return $this->insert($data);
	}
	
	public function editUsuario($data,$id){
		return $this->update($data,"user_id=$id");
	}	
	
}