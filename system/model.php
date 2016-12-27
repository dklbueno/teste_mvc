<?php
class Model extends System{
	protected $db;
	public $_tabela;
	public $db_name;
	public function __construct($db_name=null){	
		parent::__construct();
		try {
			$this->db = new PDO("mysql:host=".DB_HOST.";dbname=".($db_name?$db_name:DB_DBNAME)."","".DB_USER."","".DB_PASS.""); //;charset=ISO-8859-1
			$this->db->exec('SET NAMES ISO-8859-1'); 
		} catch (PDOException $e) {
			echo 'Erro: '.$e->getMessage().PHP_EOL;
			exit(1);
		} 
	}
	
	public function insert( Array $dados , $tabela = null, $exibir_sql = null ){
		//echo "<pre>"; echo print_r($dados); echo "</pre>"; exit;
		$tabela = ($tabela == null ? $this->_tabela : $tabela);
		$campos = implode(",",array_keys($dados));
		$vals = array_values($dados);
		foreach($vals as $val){
			$values[] = str_replace(array("\\","'"),array("","\'"),$val);
		}
		$valores = implode("','",$values);
		$sql = " INSERT INTO {$tabela} ({$campos}) VALUES ('".$valores."') ";
		if($exibir_sql){
			echo "<pre>".$sql."</pre><br>";
		}
		$this->db->query( $sql ) or die (print_r($this->db->errorInfo(),true));
		return $this->db->lastInsertId();
	}	
	
	public function update( Array $dados, $where, $tabela = null, $exibir_sql = null ){
		$tabela = ($tabela == null ? $this->_tabela : $tabela);
		foreach($dados as $key=>$val){
			//$val = utf8_encode($val);
			$campos[] = " {$key} = '".str_replace(array("\\","'"),array("","\'"),$val)."' ";
		}
		$campos = implode(",",$campos);
		//$where_cliente = ( CLIENTE ? "id_cliente = ".CLIENTE : "1 = 1" );
		$sql = " UPDATE {$tabela} SET ".$campos." WHERE {$where} ";
		if($exibir_sql){
			echo "<pre>".$sql."</pre><br>";
			//exit;
		}
		return $this->db->query( $sql ) or die (print_r($this->db->errorInfo(),true));
	}
	
	public function delete( $where, $tabela = null ){
		$tabela = ($tabela == null ? $this->_tabela : $tabela);
		$sql = " DELETE FROM {$tabela} WHERE {$where} ";
		//echo $sql."<br>"; exit;
		return $this->db->query( $sql );
	}
	
	public function select( $campos = "*", $where = null, $tabela = null, $order = null, $limit = null ){
		$tabela = ($tabela == null ? $this->_tabela : $tabela);
		//$where_cliente = ( CLIENTE ? "id_cliente = ".CLIENTE : "1 = 1" );
		$order = ($order != null ? $order = " ORDER BY ".$order." " : $order = null);
		$limit = ($limit != null ? $limit = " LIMIT ".$limit." " : $limit = null);
		$sql = " SELECT {$campos} FROM {$tabela} ".($where ? "WHERE ".$where : "")." {$order} {$limit} ";
		//echo $sql."<br><br>";
		$q = $this->db->query( $sql );
		$q->setFetchMode(PDO::FETCH_ASSOC);
		return $q->fetchAll();
	}
	
	public function select_livre( $sql, $exibir_sql = false ){
		//var_dump(debug_backtrace());
		if($exibir_sql){
			echo "<pre>".$sql."</pre><br>";
			//exit;
		}
		if($this->getParam('log')) cria_log($sql);
		$q = $this->db->query( $sql );
		$q->setFetchMode(PDO::FETCH_ASSOC);
		return $q->fetchAll();
	}
}