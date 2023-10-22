<?php
/**
 * DBController - Php PDO wrapper
 * Provides a PDO wrapper for database operations.
 *
 * @author      Peter Chigozie(NG) peterujah
 * @copyright   Copyright (c), 2021 Peter(NG) peterujah
 * @license     MIT public license
 */
namespace Peterujah\NanoBlock;
use \stdClass;
use \PDO;
use \InvalidArgumentException;
use \PDOException;
/**
 * Class DBController.
 * Parent class for database operations.
 */
class DBController
{
    /**
    * @var int PARAM_INT Integer Parameter
    */
    public const PARAM_INT = PDO::PARAM_INT; 
    
    /**
    * @var bool PARAM_BOOL Boolean Parameter
    */
    public const PARAM_BOOL = PDO::PARAM_BOOL;

    /**
    * @var null PARAM_NULL Null Parameter
    */
    public const PARAM_NULL = PDO::PARAM_NULL;

    /**
    * @var string PARAM_STRING String Parameter
    */
    public const PARAM_STRING = PDO::PARAM_STR;

    /**
    * @var PDO $conn PDO Database connection instance
    */
    protected PDO $conn; 

     /**
    * @var object $stmt pdo statement object
    */
    protected $stmt; 

    /**
    * @var bool $onDebug debug mode flag
    */
    protected $onDebug = false; 

    /**
    * @var array $config  Database configuration
    */
    protected $config = [];

    /**
    * @var string $error Last error message
    */
    public string $error = '';

    /**
    * @var array $keys Required configuration keys
    */
    protected $keys = ['VERSION', 'HOST', 'NAME', 'USERNAME', 'PASSWORD']; 


    /**
     * Constructor.
     *
     * @param array|string|null $config The database configuration.
     *
     * @throws InvalidArgumentException If a required configuration key is missing.
     */
    public function __construct($config = null) {
        if (!empty($config)) {
            if (is_array($config)) {
                $this->config = $config;
            } else if (is_dir($config) && file_exists($config)) {
                $this->config = require($config);
            }

            foreach ($this->keys as $key) {
                if (!array_key_exists($key, $this->config)) {
                    throw new InvalidArgumentException("Missing required configuration key: {$key}");
                }
            }
            $this->onCreate();
        }
    }

    /**
     * Returns the current instance of DBController.
     *
     * @return DBController The current DBController instance.
     */
    public function conn(): self {
        $this->onCreate();
        return $this;
    }

    /**
     * Sets a configuration value.
     *
     * @param string $key   The configuration key.
     * @param mixed  $value The configuration value.
     *
     * @return DBController The current DBController instance.
     */
    public function setConfig(string $key, mixed $value) {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Sets the debug mode.
     *
     * @param bool $debug The debug mode.
     *
     * @return DBController The current DBController instance.
     */
    public function setDebug(bool $debug): self {
        $this->onDebug = $debug;
        return $this;
    }

    /**
     * Initializes the database connection.
     * This method is called internally and should not be called directly.
     */
    protected function onCreate(): void {
        if (!empty($this->conn) or empty($this->config)) {
            return;
        }
        $dsn = "{$this->config["VERSION"]}:host={$this->config["HOST"]};port={$this->config["PORT"]};dbname={$this->config["NAME"]}";
        try {
            $this->conn = new PDO($dsn, $this->config["USERNAME"], $this->config["PASSWORD"], array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ));
        } catch (PDOException $e) {
            if ($this->onDebug) {
                $this->error = $e->getMessage();
                trigger_error($e->getMessage());
            } else {
                print("PDOException: database connection error");
            }
        }
    }

    /**
     * Returns the error information for the last statement execution.
     *
     * @return array|null The error information array.
     */
    public function error(): mixed {
        return $this->stmt->errorInfo();
    }

    /**
     * Returns the error information for the last statement execution.
     *
     * @return array|null The error information array.
     */
    public function errorInfo(): mixed {
        return $this->stmt->errorInfo();
    }

    /**
     * Dumps the debug information for the last statement execution.
     *
     * @return string|null The debug information or null if debug mode is off.
     */
    public function dumpDebug(): mixed {
        return $this->onDebug ? $this->stmt->debugDumpParams() : null;
    }

    /**
     * Prepares a statement for execution.
     *
     * @param string $query The SQL query.
     *
     * @return DBController The current DBController instance.
     */
    public function prepare(string $query): self {
        $this->stmt = $this->conn->prepare($query);
        return $this;
    }

    /**
     * Executes a query.
     *
     * @param string $query The SQL query.
     *
     * @return DBController The current DBController instance.
     */
    public function query(string $query): self {
        $this->stmt = $this->conn->query($query);
        return $this;
    }

    /**
     * Returns the appropriate parameter type based on the value and type.
     *
     * @param mixed       $value The parameter value.
     * @param mixed  $type  The parameter type.
     *
     * @return int The parameter type.
     */
    public function getType(mixed $value, mixed $type) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value): $type = self::PARAM_INT; break;
                case is_bool($value): $type = self::PARAM_BOOL; break;
                case is_null($value): $type = self::PARAM_NULL; break;
                default: $type = self::PARAM_STRING;
            }
        }
        return $type;
    }

    /**
     * Binds a value to a parameter.
     *
     * @param mixed       $param The parameter identifier.
     * @param mixed       $value The parameter value.
     * @param null|int    $type  The parameter type.
     *
     * @return DBController The current DBController instance.
     */
    public function bind(string $param, mixed $value, ?int $type = null): self {
        $this->stmt->bindValue($param, $value, $this->getType($value, $type));
        return $this;
    }

    /**
     * Binds a variable to a parameter.
     *
     * @param mixed       $param The parameter identifier.
     * @param mixed       $value The parameter value.
     * @param null|int    $type  The parameter type.
     *
     * @return DBController The current DBController instance.
     */
    public function param(string $param, mixed $value, ?int $type = null): self {
        $this->stmt->bindParam($param, $value, $this->getType($value, $type));
        return $this;
    }

    /**
     * Executes the prepared statement.
     *
     * @return bool True on success, false on failure.
     */
    public function execute(): bool {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            if ($this->onDebug) {
                $this->error = $e->getMessage();
                trigger_error($e->getMessage());
            } else {
                print("PDOException: database operation error");
            }
            return false;
        }
    }

    /**
     * Returns the number of rows affected by the last statement execution.
     *
     * @return int The number of rows.
     */
    public function rowCount(): int {
        return $this->stmt->rowCount();
    }

    /**
     * Fetches a single row as an object.
     *
     * @return object|bool The result object or false if no row is found.
     */
    public function getOne(): mixed {
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Fetches all rows as an array of objects.
     *
     * @return array The array of result objects.
     */
    public function getAll(): mixed {
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetches all rows as a 2D array of integers.
     *
     * @return array The 2D array of integers.
     */
    public function getInt(): mixed {
        return $this->stmt->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Fetches all rows as a 2D array of integers.
     *
     * @return array The 2D array of integers.
     */
    public function getCount(): int 
    {
        $response = $this->stmt->fetchAll(PDO::FETCH_NUM);
        if (isset($response[0][0])) {
            return (int) $response[0][0];
        }
        return $response;
    }

    /**
     * Fetches all rows as a stdClass object.
     *
     * @return stdClass The stdClass object containing the result rows.
     */
    public function getAllObject(): stdClass {
        $result = new stdClass;
        $count = 0;
        while ($row = $this->stmt->fetchObject()) {
            $count++;
            $result->$count = $row;
        }
        return $result;
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @return string The last insert ID.
     */
    public function getLastInsertId(): string {
        return (string) $this->conn->lastInsertId();
    }

    /**
     * Frees up the statement cursor and sets the statement object to null.
     */
    public function free(): void {
        if ($this->stmt !== null) {
            $this->stmt->closeCursor();
            $this->stmt = null;
        }
    }
     /**
     * Frees up the statement cursor and close database connection
     */
    public function close(): void {
        $this->free();
        $this->conn = null;
    }
}
