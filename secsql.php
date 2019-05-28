<?php
namespace SECSQL;
class Query{
	private $query,$prepData = array(),$where,$whereData= array(),$limit,$dsn,$queryType,$whereSet = false,$userPass,$error,$order;
	
	//Default for mysql utf8 connection
	function __construct($hostname,$port,$username,$password,$database,$driver = 'mysql',$charset = "utf8"){
		$this->dsn = "$driver:host=$hostname;dbname=$database;charset=$charset";
		$this->userPass = ["username"=>$username,"password"=>$password];
	}
	
	public function Select($table,$columns = null){
		$this->queryType = "select";
		$this->query = "SELECT ";
		if($columns != null){
			if(gettype($columns) == gettype(array())){
				foreach($columns as $column){
					$this->query .= "`".$column."`,";
				}
				$this->query = rtrim($this->query,",");
			}else if(gettype($columns) == gettype("")){
				$this->query .= "`".$columns."`";
			}
		}else{
			$this->query .= "*";
		}
		$this->query .= " FROM ".$table;
		return $this;
	}
	
	public function Insert($table,$columnValues){
		$this->queryType = "insert";
		$this->query = "INSERT INTO ".$table;
		$columns = "(";
		$values = "(";
		$prePrepped = array();
		foreach($columnValues as $column => $value){
			$columns .= "`".$column."`,";
			$tempClause = $column;
			$i = 0;
			while(array_key_exists($tempClause,$this->whereData) || array_key_exists($tempClause,$this->prepData)){
				$tempClause = $column.$i;
				$i++;
			}
			$values .= ":".$tempClause.",";
			$prePrepped[$tempClause] = $value;
		}
		$columns = rtrim($columns,",").")";
		$values = rtrim($values,",").")";
		$this->prepData = $prePrepped;
		$this->query .= " ".$columns." VALUES ".$values;
		return $this;
	}
	
	public function Update($table,$columnValues){
		$this->queryType = "update";
		$this->query = "UPDATE ".$table." SET ";
		$prePrepped = array();
		foreach($columnValues as $column => $value){
			$tempClause = $column;
			$i = 0;
			while(array_key_exists($tempClause,$this->whereData) || array_key_exists($tempClause,$this->prepData)){
				$tempClause = $column.$i;
				$i++;
			}
			$this->query .= "`".$column."`=:".$tempClause.",";
			$prePrepped[$tempClause] = $value;
		}
		$this->query = rtrim($this->query,",");
		$this->prepData = $prePrepped;
		return $this;
	}
	
	public function Delete($table){
		$this->queryType = "delete";
		$this->query = "DELETE FROM ".$table;
		return $this;
	}
	
	public function Where($clauses){
		if(gettype($clauses) != gettype(array())){
			$this->error = "Invalid where clauses variable, requires an array ['column','clause','value'] or [['column','clause','value'],['column','clause','value']]";
			return false;
		}
		$this->whereData = array();
		if(count($clauses) == 3 && gettype($clauses[0]) != gettype(array())){
				
				$tempClause = $clauses[0];
				$i = 0;
				while(array_key_exists($tempClause,$this->whereData) || array_key_exists($tempClause,$this->prepData)){
					$tempClause = $clauses[0].$i;
					$i++;
				}
				$this->where = "`".$clauses[0]."` ".$clauses[1]." :".$tempClause;
				$this->whereData[$tempClause] = $clauses[2];
		}else{
			$this->where = "";
			foreach($clauses as $clause){
				$tempClause = $clause[0];
				$i = 0;
				while(array_key_exists($tempClause,$this->whereData) || array_key_exists($tempClause,$this->prepData)){
					$tempClause = $clause[0].$i;
					$i++;
				}
				if(count($clause) == 3){
					$this->where .= "`".$clause[0]."` ".$clause[1]." :".$tempClause." AND ";
				}else if(count($clause == 4)){
					$this->where .= "`".$clause[0]."` ".$clause[1]." :".$tempClause." ".$clause[4]." ";
				}
				$this->whereData[$tempClause] = $clause[2];
			}
			$this->where = rtrim($this->where," AND ");
			$this->where = rtrim($this->where," OR ");
			$this->where = rtrim($this->where," AND ");
		}
		return $this;
	}
	
	public function OrderBy($columnsOrder){
		$this->order = "ORDER BY ";
		if(count($columnsOrder) == 2){
			$this->order .= $columnsOrder[0]." ".$columnsOrder[1];
		}else{
			foreach($columnsOrder as $set){
				$this->order .= $set[0]." ".$set[1].",";
			}
			$this->order = rtrim($this->order,",");
		}
		return $this;
	}
	
	public function Limit($number,$startAfter = 0){
		$this->limit = "LIMIT ".$startAfter.",".$number;
		return $this;
	}
	
	public function Execute(){
		if($this->queryType == "delete" && $this->where == null){
			$this->error = "Unsafe to call a delete statement without a where clause";
			return false;
		}
		try {
			$options = [
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
				\PDO::ATTR_EMULATE_PREPARES   => false,
			];
			 $pdo = new \PDO($this->dsn, $this->userPass['username'], $this->userPass['password'], $options);
		} catch (\PDOException $e) {
			 throw new \PDOException($e->getMessage(), (int)$e->getCode());
		}
		if($this->where != null){
			$this->query .= " WHERE ".$this->where;
		}if($this->order  != null){
			$this->query .= " ".$this->order;
		}if($this->limit  != null){
			$this->query .= " ".$this->limit;
		}
		
		$statement = $pdo->prepare($this->query);
		if(!empty($this->whereData) && !empty($this->prepData)){
			$execData = array_merge($this->prepData,$this->whereData);
		}else if(empty($this->prepData) && !empty($this->whereData)){
			$execData = $this->whereData;
		}else if(!empty($this->prepData)){
			$execData = $this->prepData;
		}else{
			$execData = array();
		}
		$statement->execute($execData);
		if($this->queryType == "insert" || $this->queryType == "update" || $this->queryType == "delete"){
			return true;
		}
		return $statement->fetchAll();
	}
	
	public function Errors(){
		if(!isset($this->error)){
			return false;
		}
		return $this->error;
	}
}