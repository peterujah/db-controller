# DBController

DBController is a PHP PDO wrapper that provides a convenient way to interact with a database using the PDO extension.

## Installation

You can install the package via Composer by running the following command:

```bash
composer require peterujah/db-controller
```

# USAGES

To use DBController, follow these easy steps.

1. Create an instance of the DBController class by passing the database configuration as an array or a path to a configuration file that returns array.


```php
use Peterujah\NanoBlock\DBController;
// Pass the configuration as an array
$config = [
    'VERSION' => 'mysql',
    'HOST' => 'localhost',
    'PORT' => 3306,
    'NAME' => 'my_database',
    'USERNAME' => 'root',
    'PASSWORD' => 'password',
];

$handler = new DBController($config);
```

Or extend `\Peterujah\NanoBlock\DBController` to set your connection details like below

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
		parent::__construct($config);
	}
}
```
Initialize your custom class
```php 
$handler = new Conn($_SERVER["HOST_NAME"]=="localhost");
```

Now run query select, insert, update, delete etc.. using prepare statment

```php
$handler->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
$handler->bind(':username', "Peter");
$handler->execute();
$res = $handler->getOne();
$handler->free();
```

Or run query select, insert, update, delete etc.. using query

```php
$handler->query('SELECT * FROM users');
$res = $handler->getAll();
$handler->free();
```

# Customization

Customize the configuration or enable debugging as needed.

```php
// Set a configuration value
$handler->setConfig('VERSION', 'pgsql');

// Enable debugging mode
$handler->setDebug(true);

```

# Error Handling

DBController provides error handling for database operations. You can retrieve the error information using the `error()` or `errorInfo()` methods.

```php
// Get the error information for the last statement execution
$errorInfo = $handler->error();

// Print the error message
if ($errorInfo !== null) {
    echo "Error: " . $errorInfo[2];
}
```

# Debugging

You can enable debugging mode to get more detailed information about the executed statements by calling the `dumpDebug()` method.

```php
// Enable debugging mode
$handler->setDebug(true);

// Dump the debug information for the last statement execution
$handler->dumpDebug();

```

# Methods

Use the various methods provided by the DBController class to interact with the database.

```php
// Prepare a statement
$query = 'SELECT * FROM users WHERE id = :id';
$handler->prepare($query);

// Bind values to parameters
$handler->bind(':id', 1);

//Binds a variable to a parameter.
$handler->param(':id', 1, DBController::_INT)

// Execute the statement
$handler->execute();

// Fetch a single row as an object
$user = $handler->getOne();

// Fetch all rows as an array of objects
$users = $handler->getAll();

// Get the number of rows affected by the last statement execution
$rowCount = $handler->rowCount();

// Get the last inserted ID
$lastInsertId = $handler->getLastInsertId();

// Free up the statement cursor
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


# Configuration format

Connection config array example 

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

# Contributing

Contributions are welcome! If you encounter any issues or have suggestions for improvements, please open an issue or submit a pull request.

# License

```bash
DBController is open-source software licensed under the MIT license.
```


				
