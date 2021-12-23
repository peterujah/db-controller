# db-controller
Php PDO wrapper

## Installation

Installation is super-easy via Composer:
```md
composer require peterujah/db-controller
```

# USAGES

```php
$handler = new \Peterujah\NanoBlock\DBController($configArray);
```

Or extend `\Peterujah\NanoBlock\DBController` to set your connection details

```php
class Conn extends \Peterujah\NanoBlock\DBController{ 
	public function __construct(bool $development = false){
		$config = array(
			"PORT" => 3306,
			"HOST" => "localhost",
			"VERSION" => "mysql",
		);
		if($development){
			$config["USERNAME"] = "root";
			$config["PASSWORD"] = "";
			$config["NAME"] = "dbname";
		}else{
			$config["USERNAME"] = "dbusername";
			$config["PASSWORD"] = "dbpass";
			$config["NAME"] = "dbname";
		} 
		$this->onDebug = $development;
		$this->config = $config;
		/* Initalize db create */
		$this->onCreate();
	}
}
```
Initialize custom class
```php 
$handler = new Conn($_SERVER["HOST_NAME"]=="localhost");
```

```php
$handler->prepare('SELECT * FROM users WHERE username = :username');
$handler->bind(':username', "Perer");
$handler->execute();
$res = $handler->getAll();
$handler->free();
```

```php
$handler->query('SELECT * FROM users');
$res = $handler->getAll();
$handler->free();
```


| Options         | Description                                                                         |
|-----------------|-------------------------------------------------------------------------------------|
| prepare(string)            | Call "prepare" with sql query string to prepare query execution                                                   |
| query(string)            | Call "query" width sql query without "bind" and "param"                                                  |
| bind(param, value, type)          | Call "bind" to bind value to the pdo prepare method                                  |
| param(param, value, type)           | Call "param" to bind parameter to the pdo statment                                    |
| execute()           | Execute prepare statment                                       |
| rowCount()           | Get result row count                                      |
| getOne()           | Get one resault row, this is useful when you set LIMIT 1                                       |
| getAll()           | Retrieve all result                                      |
| getInt()           | Gets integer useful when you select COUNT()                                      |
| getAllObject()          | Gets result object                                       |
| getLastInsertedId()           | Gets list inserted id from table                                      |
| free()           | Free database connection                                       |
| dumpDebug()           | Dump debug sql query parameter                                      |
| errorInfo()           | Print PDO prepare statment error when debug is enabled                                     |
| error()           | Print connection or execution error when debug is enabled                                     |
| setDebug(bool)           | Sets debug status                                       |
| setConfig(array)           | Sets connection config array                                       |
| conn()           | Retrieve DBController Instance useful when you call "setConfig(config)"                                    |



```php 
[
     PORT => 3306,
     HOST => "localhost",
     VERSION => "mysql",
     NAME => "dbname",
     USERNAME => "root",
     PASSWORD => ""
]
```


				
