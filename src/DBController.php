<?php 
/**
 * DBController - Php PDO wrapper
 * @author      Peter Chigozie(NG) peterujah
 * @copyright   Copyright (c), 2021 Peter(NG) peterujah
 * @license     MIT public license
 */
namespace Peterujah\NanoBlock;

/**
 * Class DBController.
 */
class DBController{ 
    public const PORT = "PORT";
    public const HOST = "HOST";
    public const VERSION = "VERSION";
    public const NAME = "NAME";
    public const USERNAME = "USERNAME";
    public const PASSWORD = "PASSWORD";

	protected $conn; 
	protected $stmt; 
    protected $onDebug = false;
    /**
     * self::PORT => 3306,
     * self::HOST => "localhost",
     * self::VERSION => "mysql",
     * self::NAME => "dbname",
     * self::USERNAME => "root",
     * self::PASSWORD => ""
     */
    protected $config = array();

    public $error;
    public const _INT = \PDO::PARAM_INT;
    public const _BOOL = \PDO::PARAM_BOOL;
    public const _NULL = \PDO::PARAM_NULL;
    public const _STRING = \PDO::PARAM_STR;

	public function __construct($config = null){
		if(!empty($config)){
            if(is_array($config)){
                $this->config = $config;
            }else if(is_dir($config) && file_exists($config)){
                $this->config = include_once($config);
            }
            $this->onCreate();
        }
	}

    public function conn(){
        $this->onCreate();
        return $this; 
    } 

    public function setConfig($key, $value){
        $this->config[$key] = $value;
        return $this;
    } 

    public function setDebug($debug){
        $this->onDebug = $debug;
        return $this;
    } 

    protected function onCreate(){
        if(!empty($this->conn) or empty($this->config)){
            return;
        }
        $dsn = "{$this->config["VERSION"]}:host={$this->config["HOST"]};port={$this->config["PORT"]};dbname={$this->config["NAME"]}"; 
		try{ 
            //$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			$this->conn = new \PDO($dsn, $this->config["USERNAME"], $this->config["PASSWORD"], array( 
				\PDO::ATTR_PERSISTENT => true, 
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
			)); 
		} catch(\PDOException $e){ 
            if($this->onDebug){
                $this->error = $e->getMessage();
			    trigger_error($e->getMessage()); 
            }else{
                print("PDOException: database operation connection error"); 
            }
		} 
    }

	public function error(){
        return $this->stmt->errorInfo(); 
    } 

	public function errorInfo(){
        return $this->stmt->errorInfo(); 
    } 

	public function dumpDebug(){
		return $this->onDebug ? $this->stmt->debugDumpParams() : null;
	}

	public function prepare($query){
        $this->stmt = $this->conn->prepare($query); 
    } 

    public function query($query){
        return $this->conn->query($query); 
    }

	public function getType($value, $type){
		if(is_null($type)){
			switch(true){ 
				case is_int($value): $type = self::_INT; break; 
				case is_bool($value): $type = self::_BOOL; break;
				case is_null($value): $type = self::_NULL; break; 
				default: $type = self::_STRING; 
			} 
		} 
		return $type; 
	}

	public function bind($param, $value, $type = null){
		$this->stmt->bindValue($param, $value, $this->getType($value, $type)); 
        return $this;
	}

	public function param($param, $value, $type = null){
		$this->stmt->bindParam($param, $value, $this->getType($value, $type)); 
        return $this;
	}

	public function execute(){
        return $this->stmt->execute(); 
    } 

	public function rowCount(){
        return $this->stmt->rowCount(); 
    } 

	public function getOne(){
        return $this->stmt->fetch(\PDO::FETCH_OBJ); 
    } 

	public function getAll(){
        return $this->stmt->fetchAll(\PDO::FETCH_OBJ); 
    } 

	public function getInt(){
        return $this->stmt->fetchAll(\PDO::FETCH_NUM); 
    } 

	public function getAllObject(){
		$result = new stdClass; 
		$count = 0; 
		while($row = $this->stmt->fetchObject()){ 
			$count++; 
			$result->$count = $row; 
		} 
		return $result; 
	} 

	public function getLastInsertId(){
        return $this->conn->lastInsertId(); 
    } 

	public function free(){ 
        $this->stmt = null; 
    } 
}
