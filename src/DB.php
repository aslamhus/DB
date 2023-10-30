<?php
namespace Database;

use Database\DBAllowList;

class DB
{
    private $conn;
    private $status;
    private $lastSql;
    private $validColumns;
    private $validTables;
    private $searchResult;

    public function __construct()
    {
        $this->validColumns = DBAllowList::getValidColumns();
        $this->validTables = DBAllowList::getValidTables();
        $this->connect();
    }

    public function getConn()
    {
        return $this->conn;
    }

    private function connect()
    {
        if ($_ENV['DEV'] === 'DEV') {
            $this->conn = new \mysqli(
                $_ENV['DB_HOST'],
                $_ENV['DB_LUSER'],
                $_ENV['DB_LNAME'],
                $_ENV['DB_NAME']
            );
        } elseif ($_ENV['DEV'] === 'PRODUCTION') {
            $this->conn = new \mysqli(
                $_ENV['DB_HOST'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $_ENV['DB_NAME']
            );
        }

        if ($this->conn->connect_error) {
            die('Connection failed: ' . $this->conn->connect_error);
        }
    }

    private function check_connection()
    {
        if (!isset($this->conn)) {
            $this->connect();
        }

        return true;
    }

    public function last_insert_id()
    {
        $result = $this->conn->query('SELECT LAST_INSERT_ID()');
        $id = $result->fetch_assoc();
        if ($id) {
            return $id['LAST_INSERT_ID()'];
        }

        return false;
    }

    public function insert(...$parameters): bool
    {
        $table = $parameters[0];
        $params = $parameters[1];
        $duplicate = false;
        if (isset($parameters[2])) {
            $duplicate = $parameters[2];
        }
        $this->check_connection();
        $this->isTableValid($table);
        $sql = "INSERT INTO `$table` (";
        $this->lastSql = $parameters[1];
        $paramObj = $this->parseParams($params);

        if (!$paramObj) {
            $this->status[] = 'failed to parse parameters - insert';

            return false;
        };
        // add all variable keys to sql statement
        if (!empty($paramObj->keys['var'])) {
            foreach ($paramObj->keys['var'] as $value) {
                $sql .= '`' . $value . '`'; // key name, ie. "user_login
                $sql .= ', ';
            }
        }
        // add all constant keys to sql statement
        if (!empty($paramObj->keys['constant'])) {
            foreach ($paramObj->keys['constant'] as $value) {
                $sql .= '`' . $value . '`'; // key name, ie. "user_login
                $sql .= ', ';
            }
        }
        $sql = rtrim($sql, ', ');
        $sql .= ') VALUES (';
        $pTotal = count($paramObj->vars);
        $pCount = 0;
        while ($pCount < $pTotal) {
            //  Values
            $sql .= '?, ';
            $pCount++;
        }
        if (count($paramObj->constants) > 0) {
            foreach ($paramObj->constants as $value) {
                $sql .= $value . ', ';
            }
        }
        $sql = rtrim($sql, ', ');
        $sql .= ')';
        if ($duplicate) {
            $sql .= ' on duplicate key UPDATE ';
            $duplicateObj = $this->parseParams($params);
            if (!empty($duplicateObj->keys['var'])) {
                foreach ($duplicateObj->keys['var'] as $key) {
                    $sql .= ' `' . $key . '` = ?, ';
                }
            }
            $sql = rtrim($sql, ', ');
            if (count($duplicateObj->constants) > 0) {
                foreach ($duplicateObj->constants as $key => $value) {
                    $sql .= ' `' . $key . '` = ' . $value . ', ';
                }
            }

            $sql = rtrim($sql, ', ');
            $paramObj->types .= $duplicateObj->types;
            $paramObj->vars = array_merge($paramObj->vars, $duplicateObj->vars);
        }

        $sql .= ';';
        $this->lastSql = $sql;

        if (!$query = $this->conn->prepare($sql)) {
            $this->status[] = 'Prepare failed - insert Error: ' . $this->conn->error;
            echo $sql;
            echo json_encode($this->status);

            return false;
        };
        if (!$query->bind_param($paramObj->types, ...$paramObj->vars)) {
            $this->status[] = 'Bind param failed - insert Error: ' . $query->error;

            echo json_encode($this->status);

            return false;
        }
        if (!$query->execute()) {
            $this->status[] = 'Execute failed - insert. Error: ' . $query->error;

            $this->status['sql'] = $sql;

            echo json_encode($this->status);
            exit;

            return false;
        }
        $query->close();

        return true;
    }

    public function update($table, $params, $condition)
    {
        $this->check_connection();

        $this->isTableValid($table);

        $sql = "UPDATE `$table` set ";
        $paramObj = $this->parseParams($params);
        if (!empty($paramObj->keys['var'])) {
            foreach ($paramObj->keys['var'] as $key) {
                $sql .= ' `' . $key . '` = ?, ';
            }
        }
        $sql = rtrim($sql, ', ');
        if (count($paramObj->constants) > 0) {
            if (!empty($paramObj->keys['var'])) {
                $sql .= ', ';
            }
            foreach ($paramObj->constants as $key => $value) {
                $sql .= ' `' . $key . '` = ' . $value . ', ';
            }
        }
        $sql = rtrim($sql, ', ');
        $conditionObj = $this->parseParams($condition);
        $sql .= ' WHERE ';
        $sql .= $this->getConditions($conditionObj);
        $this->lastSql = $sql;
        $types = $paramObj->types . $conditionObj->types;
        $vars = array_merge($paramObj->vars, $conditionObj->vars);
        if (!$query = $this->conn->prepare($sql)) {
            $this->status[] = 'Prepare failed. Update. Error :' . $this->conn->error;

            return false;
        }
        if (!$query->bind_param($types, ...$vars)) {
            $this->status[] = 'Bind param failed. Error: ' . $query->error;
            // return false;
        }
        if (!$query->execute()) {
            $this->status[] = 'failed to execute. Error: ' . $query->error;
            // return false;
        }
        $query->close();

        $this->status[] = 'Update successful';

        return true;
    }

    public function isAssoc(array $arr)
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * selectAll
     *
     * Select all is equivalent to a normal select,
     * except that it will always return results in
     * a sequential array.
     * Compare with a regular select, which will return
     * a single result as an associative array or an
     * empty result set as 0 (false)
     * Therefore a foreach/while loop is necessary
     * to reach the results of selectAll every time.
     *
     * @return array
     */
    public function selectAll(): array
    {
        $args = func_get_args();
        $result = $this->select(...$args);
        if (empty($result)) {
            return [];
        }
        $isAssociativeArray = $this->isAssoc($result);

        if ($isAssociativeArray) {
            return [$result];
        }

        return $result;
    }

    public function select()
    {
        $args = func_num_args();
        switch ($args) {
            case 2:
                $columns = func_get_arg(0);
                $table = func_get_arg(1);
                $condition = null;
                $order = null;

                break;
            case 3:
                $columns = func_get_arg(0);
                $table = func_get_arg(1);
                $condition = func_get_arg(2);
                $order = null;

                break;
            case 4:
                $columns = func_get_arg(0);
                $table = func_get_arg(1);
                $condition = func_get_arg(2);
                $order = func_get_arg(3);

                break;
            default:
                throw new \Exception('Invalid arguments');
        }
        $this->check_connection();
        $this->isTableValid($table);
        if (!$this->isColumnValid($columns)) {
            return false;
        }

        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }
        $sql = "SELECT $columns FROM `$table` ";
        if ($condition) {
            $sql .= 'WHERE';
            $conditionObj = $this->parseParams($condition);
            if ($conditionObj->keys['var']) {
                $sql .= $this->getConditions($conditionObj, 'var');
            }
            if (!empty($conditionObj->constants)) {
                foreach ($conditionObj->constants as $key => $value) {
                    $sql .= str_replace('?', $value, $key);
                }
            }
        }
        if ($order) {
            $sql .= $order;
        }
        $sql .= ';';
        $this->lastSql = $sql;
        if (!$query = $this->conn->prepare($sql)) {
            $this->status[] = 'Select prepare failed. Error: ' . $this->conn->error;

            return false;
        }
        if ($condition) {
            if (!$query->bind_param($conditionObj->types, ...$conditionObj->vars)) {
                $this->status[] = 'Bind param failed. Error: ' . $query->error;

                return false;
            }
        }
        if (!$query->execute()) {
            $this->status[] = 'Select execute failed. Error: ' . $query->error;

            return false;
        }
        if (!$result = $query->get_result()) {
            $this->status[] = 'Select get result failed. Error: ' . $query->error;

            return false;
        }
        $rows = mysqli_num_rows($result);
        if ($rows <= 0) {
            // return 0 as a result
            $this->status[] = 'Select. No rows found.';

            return 0;
        }
        if ($rows == 1) {
            // return single result
            if (!$select = $result->fetch_assoc()) {
                $this->status[] = 'Select. Failed to fetch assoc. Error: ' . $this->conn->error;

                return false;
            }

            return $select;
        }
        $returnArray = [];
        while ($row = $result->fetch_assoc()) {
            // return array result
            $returnArray[] = $row;
        }
        $query->close();

        return $returnArray;
    }

    public function delete($table, $condition)
    {
        $this->isTableValid($table);
        $sql = "DELETE FROM $table WHERE ";
        $conditionObj = $this->parseParams($condition);
        $sql .= $this->getConditions($conditionObj);
        $sql .= ';';
        if (!$query = $this->conn->prepare($sql)) {
            $this->status[] = 'Delete prepare failed. Error: ' . $this->conn->error;
            $this->status[] = $sql;
            // echo $sql;
            return false;
        }
        if (!$query->bind_param($conditionObj->types, ...$conditionObj->vars)) {
            $this->status[] = 'Delete bind param failed. Error: ' . $this->conn->error;

            return false;
        }
        if (!$query->execute()) {
            $this->status[] = 'Delete execute  failed. Error: ' . $this->conn->error;

            return false;
        }
        $this->status[] = 'Delete successful';

        return true;
        //echo $sql;
    }

    private function typeToString($value, $key)
    {
        $type = substr(gettype($value), 0, 1);
        if (
            $type == 's' ||
            $type == 'd' ||
            $type == 'i'
        ) {
            return $type;
        } else {
            $this->status[] = "\n Unallowed fieldtype for column: '" . $key . "' - '" . $type . "' - Value: '" . $value . "'";

            return false;
        }
    }

    private function parseParams($params)
    {
        // check order of constants vs vars!!
        if (!is_array($params) && !is_object($params)) {
            $this->status[] = 'Parse params failed. Invalid data, requires object or array but found s' . getType($params);

            return false;
        }
        $keys = [];
        $paramVars = [];
        $types = '';
        $constants = [];
        foreach ($params as $key => $value) {
            //  Keys
            if (!is_array($value)) {
                ${"param$key"} = $value; // creates new unique variable for paramVars array
                $paramVars[] = &${"param$key"}; // &variable for bind_param
                $types .= $this->typeToString($value, $key); // gets first character of type (i, s, d)
                $keys['var'][] = $key;
            } elseif (!empty($value[1]) && $value[1] != 'constant') {
                ${"param$key"} = $value[0];
                $paramVars[] = &${"param$key"};
                $types .= $value[1];
                $keys['var'][] = $key;
            } elseif (!isset($value[1]) || empty($value[1])) {
                ${"param$key"} = $value[0];
                $paramVars[] = &${"param$key"};
                $types .= $this->typeToString($value[0], $key);
                $keys['var'][] = $key;
            } elseif ($value[1] == 'constant') {
                $constants[$key] = mysqli_real_escape_string($this->conn, $value[0]); // does not require type
                $keys['constant'][] = $key;
            } else {
                $this->status[] = 'failed to parse parameters';

                return false;
            }
        }

        $parsedParams = (object) [
            'keys' => $keys,
            'vars' => $paramVars,
            'types' => $types,
            'constants' => $constants
        ];

        return $parsedParams;
    }

    private function getConditions($conditionObj, $keyType = 'var')
    {
        $sql = '';
        foreach ($conditionObj->keys[$keyType] as $key) {
            $sql .= ' ' . $key;
        }

        return $sql;
    }

    private function isTableValid($table)
    {
        if (empty($this->validTables)) {
            return true;
        }
        if (!$this->isValid($table, $this->validTables)) {
            $this->status[] = 'Table invalid';

            throw new \Exception("DB: Invalid table '$table'");
        }

        return true;
    }

    private function isColumnValid($columns)
    {
        if (empty($this->validColumns)) {
            return true;
        }
        if (!is_array($columns)) {
            // remove white space
            $columns = preg_replace('/\s+/', '', $columns);
            //implode string into array
            $columns = explode(',', $columns);
        }
        foreach ($columns as $column) {
            if (!$this->isValid($column, $this->validColumns)) {
                $this->status[] = "Column invalid, $column";

                throw new \Exception("DB: Invalid column '$column'");
            }
        }

        return true;
    }

    private function isValid($array, $validArr)
    {
        if (!is_array($array)) {
            $array = [$array];
        }
        foreach ($array as $value) {
            if (!in_array($value, $validArr)) {
                return false;
            }
        }

        return true;
    }

    public function search($sqlToPrepare, $types, $vars)
    {
        //if(!$this->isSearchValid($sqlToPrepare)){ return false; }
        if (!$query = $this->conn->prepare($sqlToPrepare)) {
            $this->status[] = 'Search failed. Prepare Error: ' . $this->conn->error;
            print_r($this->getErrorReport());

            return false;
        }
        if (!$query->bind_param($types, ...$vars)) {
            $this->status[] = 'Search failed. Bind Error: ' . $query->error;

            return false;
        }
        if (!$query->execute()) {
            $this->status[] = 'Search failed. Execute Error: ' . $query->error;

            return false;
        }
        $result = $query->get_result();
        if (!$result) {
            $this->status[] = 'Search failed. Result error: ' . $query->error;

            return false;
        }

        $searchArray = [];
        while ($row = $result->fetch_assoc()) {
            $searchArray[] = $row;
        }
        $this->searchResult = $searchArray;

        return $searchArray;
    }

    public function getErrorReport()
    {
        return $this->status;
    }

    public function getLastSql()
    {
        return $this->lastSql;
    }

    public function close() : bool
    {
        return $this->conn->close();
    }

    public function __debugInfo()
    {
        $properties = [];

        return $properties;
    }
}
