<?php
/**
 * Created by PhpStorm.
 * User: jgrosman
 * Date: 1/10/19
 * Time: 10:19 PM
 */

namespace wrgpt\util;

use PDO;

class DBConn
{
    private static $conn;

    private $pdo;

    public static function getConnection()
    {
        if (empty(self::$conn))
        {
            self::$conn = new DBConn();
        }

        return self::$conn;
    }

    public function __construct()
    {
        $dsn = "mysql:host=db;dbname=wrgpt;charset=utf8mb4";
        $this->pdo = new PDO($dsn, 'wrgpt', 'wrgpt', $options = []);
    }

    /**
     * bindAndQuery - bind parameters and execute the query.
     *
     * if a colon is present in the first key of parameters this function assumes named parameters were used
     *
     * @param string $sql
     * @param array  $parameters - can either be an array of namedParam => paramValue values or array of paramValues
     *
     * @throws \Exception
     *
     * @return \PDOStatement
     */
    private function bindAndQuery($sql, $parameters)
    {
        $statement = $this->pdo->prepare($sql);

        // One day we should add this as a debug - useful for fingers_crossed
        // error_log("SQL: $sql, params:".print_r($parameters, true));

        // we need to determine if we've been passed named parameters
        $namedParamsUsed = false;
        $paramKeys = array_keys($parameters);
        if (isset($paramKeys[0]) && is_string($paramKeys[0])) {
            $namedParamsUsed = strpos($paramKeys[0], ':') !== false ? true : false;
        }

        $p = '';
        foreach ($parameters as $paramKey => $paramValue) {
            $p .= '['.$paramKey.'] = '.$paramValue.', ';
            if (!$namedParamsUsed && is_numeric($paramKey)) {
                // bindValue expects 1 based array, PHP's assoc arrarys are 0 based
                ++$paramKey;
            }

            if (is_int($paramValue)) { // we need to do this for LIMIT & offset
                $statement->bindValue($paramKey, $paramValue, \PDO::PARAM_INT);
            } elseif (is_null($paramValue)) {
                $statement->bindValue($paramKey, null, \PDO::PARAM_NULL);
            } else { // MySQL doesn't mind if everything is passed as string and will do the Right Thing
                $statement->bindValue($paramKey, $paramValue);
            }
        }

        if ($statement->execute() === false) {
            $info = $statement->errorInfo();
            ob_start();
            $statement->debugDumpParams(); //header("Content-Type: image/png"); in here
            $stmtParams = ob_get_contents();
            ob_end_clean();
            throw new \Exception('Error executing database statement: '.$statement->errorCode().' / '.$info[2].' ( '.$stmtParams.' )');
        }

        return $statement;
    }

    public function queryFetchAll($sql, $parameters = array())
    {

        try {
            $statement = $this->bindAndQuery($sql, $parameters);
            $arr = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $statement->closeCursor();
            return ($arr === false) ? array() : $arr;
        } catch (\Exception $e) {
            error_log($e);
        }

        return [];
    }

    /**
     * execute a DML query.
     *
     * For use with INSERT, UPDATE and DELETE
     * Returns the number of rows affected
     *
     * @param string $sql
     * @param array  $parameters
     *                           can either be an array of namedParam => paramValue values or array of paramValues
     *
     * @return int
     */
    public function execute($sql, $parameters = array())
    {

        try {
            $statement = $this->bindAndQuery($sql, $parameters);
            $result = $statement->rowCount();
            return $result;
        } catch (\Exception $e) {
            error_log($e);
        }

        return 0;
    }

}
