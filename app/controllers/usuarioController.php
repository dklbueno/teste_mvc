<?php
class Usuario extends Controller{
	
	public function __construct(){
		parent::__construct();
		$this->setTemplate('template_padrao');
		$this->db = new Usuario_Model($this->post);
	}
	
	public function home(){
		$this->view('bemvindo');
	}
	
	public function cadastrar(){		
		if($this->post['Submit'] == 'Cadastrar'){
			$dados = array(
				'nome'=>$this->post['nome'],
				'email'=>$this->post['email'],
				'senha'=>base64_encode($this->post['senha'])
			);
			$this->db->addUsuario($dados);
		}
		if($this->post['Submit'] == 'Alterar'){
			$dados = array(
				'nome'=>$this->post['nome'],
				'email'=>$this->post['email'],
				'senha'=>base64_encode($this->post['senha'])
			);
			$this->db->editUsuario($dados,$this->post['user_id']);
		}
		if($this->getParam('editar')){
			$id = $this->getParam('editar');
			$vars['editar'] = $id;
			$vars['usuario'] = $this->db->getUsuarios($id);
		}
		$this->view('usuario',$vars);
	}
	
	public function lista(){
		header("Access-Control-Allow-origin: *");
		header("Content-Type: application/json");
		header("Cache-Control: no-cache");
		$usuarios = $this->db->getUsuarios();
		foreach($usuarios as $key=>$val){
			$users[$key] = utf8ize($val);
		}
		echo json_encode($users);
		exit;
	}
	
	public function excluir(){
		$this->db->deleteUsuario($this->getParam('id'));
		echo json_encode(array('status'=>'ok'));
		exit;
	}
	
}