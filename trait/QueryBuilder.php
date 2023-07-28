<?php
namespace Core\Builder;

trait QueryBuilder
{
    private static $class; // tương ứng với class ben database
    protected static $tableName = '';
    protected static $where = '';
    protected static $whereExit = '';
    protected static $select = '*';
    protected static $orderBy = '';
    protected static $operator = '';
    protected static $join = '';
    protected static $on = '';
    protected static $page = '';
    protected static $limit = '';

    public static function table($tableName)
    {
        self::$tableName = $tableName;
        return self::$class;
    }

    public static function where($field, $compare = '', $value = '')
    {
        if (is_callable($field)) {
            $field(self::$class);
            return self::$class;
        }
        if (empty(self::$where)) {
            self::$operator = " WHERE ";
        } else {
            self::$operator = " AND ";
        }
        $operator = self::$operator;
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator} {$field} {$compare} {$value}";
        return self::$class;
    }

    public static function orWhere($field, $compare, $value)
    {
        if (is_callable($field)) {
            $field(self::$class);
            return self::$class;
        }
        if (empty(self::$where)) {
            self::$operator = " WHERE ";
        } else {
            self::$operator = " OR ";
        }
        $operator = self::$operator;
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator} {$field} {$compare} {$value}";
        return self::$class;
    }

    public static function whereLike($field, $value)
    {
        if (is_callable($field)) {
            $field(self::$class);
            return self::$class;
        }
        if (empty(self::$where)) {
            self::$operator = " WHERE ";
        } else {
            self::$operator = " AND ";
        }
        $operator = self::$operator;
        $value = (is_numeric($value) ? $value:"'".$value."'");
        self::$where .= "{$operator} {$field} like {$value}";
        return self::$class;
    }

    public static function select($field){
        $field = (is_array($field)) ? implode(", ", $field):$field;
        self::$select = $field;
        return self::$class;
    }

    public static function orderBy($field, $orderBy = 'ASC'){
        self::$orderBy = " ORDER BY {$field} {$orderBy} ";
        return self::$class;
    }

    public static function join($table, $function = ''){
        self::$join = " INNER JOIN {$table}";
        if (is_callable($function)) {
            $function(self::$class);
        }
        return self::$class;
    }

    public static function on($field1, $compare, $field2, $operator = ''){
        if(!empty(self::$on)){
            $operator = empty($operator) ? "AND":$operator;
            self::$operator = " {$operator} ";
        }else{
            self::$operator = " ON ";
        }
        $operator = self::$operator;
        self::$join .= "{$operator} {$field1} {$compare} {$field2}";
        return self::$class;
    }

    public static function page($page){
        self::$page = $page;
        return self::$class;
    }

    public static function limit($limit){
        self::$limit = $limit;
        return self::$class;
    }


    public static function get(){
        if(static::$query){
            $query = static::$query->fetchAll(\PDO::FETCH_OBJ);
            static::$query = '';
            return $query;
        }
        $sql = self::sqlQuery();
        $query = self::$class->query($sql);
        if (!empty($query)) {
            return $query->fetchAll(\PDO::FETCH_OBJ);
        }
        return false;
    }

    public static function first(){
        $sql = self::sqlQuery();
        $query = self::$class->query($sql);
        if (!empty($query)) {
            return $query->fetch(\PDO::FETCH_OBJ);
        }
        return false;
    }

    public static function getArray(){
        if(static::$query){
            $query = static::$query->fetchAll(\PDO::FETCH_ASSOC);
            static::$query = '';
            return $query;
        }
        $sql = self::sqlQuery();
        $query = self::$class->query($sql);
        if (!empty($query)) {
            return $query->fetchAll(\PDO::FETCH_ASSOC);
        }
        return false;
    }

    public static function firstArray(){
        $sql = self::sqlQuery();
        $query = self::$class->query($sql);
        if (!empty($query)) {
            return $query->fetch(\PDO::FETCH_ASSOC);
        }
        return false;
    }

    public static function sqlQuery(){
        $select = self::$select;
        $tableName = self::$tableName ? self::$tableName:static::$tableName; // ko có sẽ lấy bên model
        $join = self::$join;
        $where = self::$where;
        $whereExit = self::$whereExit;
        $orderBy = self::$orderBy;
        $fieldTable = static::$field ?? '';
        $offset = !empty(self::$page) && !empty(self::$limit) ? ' OFFSET '.self::$page * self::$limit:'';
        $limit = !empty(self::$limit) ? "LIMIT ".self::$limit:'';

        if (empty($select)) {
            if (empty($fieldTable)) {
                $fieldTable = '*';
            }else{
                $fieldTable = implode(',', $fieldTable);
            }
            $select = $fieldTable;
        }

        $sql = "SELECT {$select} FROM {$tableName}{$join}
        {$where}{$whereExit}{$orderBy}{$limit}{$offset}";
        $sql = trim($sql);
        // reset
        self::$where = '';
        self::$select = '*';
        self::$orderBy = '';
        self::$operator = '';
        self::$join = '';
        self::$on = '';
        self::$whereExit = '';
        self::$page = '';
        self::$limit = '';
        return $sql;
    }
}