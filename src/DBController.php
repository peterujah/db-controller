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

/**
 * Class DBController.
 * Parent class for database operations.
 */
class DBController
{
    protected $conn; // Database connection object
    protected $stmt; // PDO statement object
    protected $onDebug = false; // Debug mode flag
    protected $config = array(); // Database configuration

    public $error; // Last error message
    protected $keys = ['VERSION', 'HOST', 'NAME', 'USERNAME', 'PASSWORD']; // Required configuration keys
    public const _INT = \PDO::PARAM_INT; // Parameter type constant: Integer
    public const _BOOL = \PDO::PARAM_BOOL; // Parameter type constant: Boolean
    public const _NULL = \PDO::PARAM_NULL; // Parameter type constant: Null
    public const _STRING = \PDO::PARAM_STR; // Parameter type constant: String

    /**
     * Constructor.
     *
     * @param array|string|null $config The database configuration.
     *
     * @throws \InvalidArgumentException If a required configuration key is missing.
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
                    throw new \InvalidArgumentException("Missing required configuration key: {$key}");
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
    public function conn() {
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
    public function setConfig($key, $value) {
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
    public function setDebug($debug) {
        $this->onDebug = $debug;
        return $this;
    }

    /**
     * Initializes the database connection.
     * This method is called internally and should not be called directly.
     */
    protected function onCreate() {
        if (!empty($this->conn) or empty($this->config)) {
            return;
        }
        $dsn = "{$this->config["VERSION"]}:host={$this->config["HOST"]};port={$this->config["PORT"]};dbname={$this->config["NAME"]}";
        try {
            $this->conn = new \PDO($dsn, $this->config["USERNAME"], $this->config["PASSWORD"], array(
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ));
        } catch (\PDOException $e) {
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
    public function error() {
        return $this->stmt->errorInfo();
    }

    /**
     * Returns the error information for the last statement execution.
     *
     * @return array|null The error information array.
     */
    public function errorInfo() {
        return $this->stmt->errorInfo();
    }

    /**
     * Dumps the debug information for the last statement execution.
     *
     * @return string|null The debug information or null if debug mode is off.
     */
    public function dumpDebug() {
        return $this->onDebug ? $this->stmt->debugDumpParams() : null;
    }

    /**
     * Prepares a statement for execution.
     *
     * @param string $query The SQL query.
     *
     * @return DBController The current DBController instance.
     */
    public function prepare($query) {
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
    public function query($query) {
        $this->stmt = $this->conn->query($query);
        return $this;
    }

    /**
     * Returns the appropriate parameter type based on the value and type.
     *
     * @param mixed       $value The parameter value.
     * @param null|int    $type  The parameter type.
     *
     * @return int The parameter type.
     */
    public function getType($value, $type) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value): $type = self::_INT; break;
                case is_bool($value): $type = self::_BOOL; break;
                case is_null($value): $type = self::_NULL; break;
                default: $type = self::_STRING;
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
    public function bind($param, $value, $type = null) {
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
    public function param($param, $value, $type = null) {
        $this->stmt->bindParam($param, $value, $this->getType($value, $type));
        return $this;
    }

    /**
     * Executes the prepared statement.
     *
     * @return bool True on success, false on failure.
     */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (\PDOException $e) {
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
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * Fetches a single row as an object.
     *
     * @return object|bool The result object or false if no row is found.
     */
    public function getOne() {
        return $this->stmt->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * Fetches all rows as an array of objects.
     *
     * @return array The array of result objects.
     */
    public function getAll() {
        return $this->stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Fetches all rows as a 2D array of integers.
     *
     * @return array The 2D array of integers.
     */
    public function getInt() {
        return $this->stmt->fetchAll(\PDO::FETCH_NUM);
    }

    /**
     * Fetches all rows as a stdClass object.
     *
     * @return stdClass The stdClass object containing the result rows.
     */
    public function getAllObject() {
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
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }

    /**
     * Frees up the statement cursor and sets the statement object to null.
     */
    public function free() {
        if ($this->stmt !== null) {
            $this->stmt->closeCursor();
            $this->stmt = null;
        }
    }
     /**
     * Frees up the statement cursor and close database connection
     */
    public function close() {
        $this->free();
        $this->conn = null;
    }
}
