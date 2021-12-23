# db-controller
Php PDO wrapper


```php
class Conn extends \Peterujah\NanoBlock\DBController{ 
	public function __construct($host = null){
		$config = array(
			"PORT" => 3306,
			"HOST" => "localhost",
			"VERSION" => "mysql",
		);
		if($_S == 'localhost'){
      $this->onDebug = true;
			$config["USERNAME"] = "root";
			$config["PASSWORD"] = "";
			$config["NAME"] = "dbname";
		}else{
      $this->onDebug = false;
			$config["USERNAME"] = "dbusername";
			$config["PASSWORD"] = "dbpass";
			$config["NAME"] = "dbname";
		} 
		$this->config = $config;
    /* Initalize db create */
		$this->onCreate();
	}
}
```
