<?php

class DB extends ACore
{
	const TYPE_JOIN_LEFT = "LEFT";
	const TYPE_JOIN_RIGHT = "RIGHT";
	const TYPE_JOIN_INNER = "INNER";

    const COMPARISON_TYPE_EQUAL = "=";
    const COMPARISON_TYPE_NOT_EQUAL = "!=";
    const COMPARISON_TYPE_IN = "IN";

    const INSTRUCTION_TYPE_OR = "OR";
    const INSTRUCTION_TYPE_AND = "AND";

	protected $_connect;
	protected $_prefix;
	protected $_table;
	protected $_join;
	protected $_where;
	protected $_orderBy;
	protected $_limit;
	protected $_lastQuery;
	
	public $error;

	protected function __construct() 
	{        
		$access = Config::load('database');

		$connect = mysqli_connect(
			getFrom("db_host", $access, ""), 
			getFrom("db_user", $access, ""), 
			getFrom("db_pass", $access, ""))			
			or Message::exception("Could not connect ot DB: " . mysqli_error($connect));

		mysqli_select_db($connect, getFrom("db_name", $access, ""))
			or Message::exception("Could not select database: " . mysqli_error($connect));

		mysqli_query($connect, "SET NAMES utf8");

		$this->_connect = $connect;
		$this->_prefix = getFrom("prefix", $access, "");
		$this->error = "";
		$this->_lastQuery = "";

		$this->_resetParams();

    }

    protected function _query($sql, array $val = [])
    {
        if (!isString($sql)) return false;

        foreach ($val as $k => $v)
            $val[$k] = $this->escapeString($v);

        $result = mysqli_query($this->_connect, $sql . " ;");

		$this->_resetParams();
		$this->_lastQuery = $sql;

		if (empty($result)) Message::error(mysqli_error($this->_connect) . " | " . $sql);

		$this->error = mysqli_error($this->_connect);

		return mysqli_fetch_assoc($result);
	}
	
    protected function _resetParams() 
    {
		$this->_join = "";
		$this->_where = "";
		$this->_prefix = "";
		$this->_orderBy = "";
		$this->_limit = "";
	}
	
	public function lastQuery()
	{
		return $this->_lastQuery;
	}
	
	public function table($name)
	{
		if (!isString($name)) return false;
		$this->_table = $name;
		return $this;
	}
    
    public function select(array $columns = [], array $val = [])
    {
		if (empty($this->_table)) return false;
		if (empty($columns) || !isArray($columns)) $columns = [" * "];

		foreach ($columns as $k => $c)
		    $columns[$k] = $this->escapeIdt($c);

		$sql = " SELECT " . implode(' , ', $columns) . " FROM " . $this->_prefix . $this->_table .
			" " . $this->_join . " WHERE " . $this->_where . " " . $this->_orderBy . " " . $this->_limit;

		return $this->_query($sql, $val);
	}
	
	public function selectCount()
	{
		$result = $this->select(" COUNT(*) as count ");
		return getFrom('count', $result, 0);
	}
	
	public function selectRow(array $columns = [], array $val = [])
	{
		$this->limit(1);
		return $this->select($columns, $val);
	}
	
	public function join($jTable, $on, $type = null)
	{
		if (!isString($jTable) || !isString($on)) Message::exception('Wrong incoming data for JOIN.');
		if (
		    empty($type)
            || !in_array($type, [
                DB::TYPE_JOIN_LEFT,
                DB::TYPE_JOIN_RIGHT,
                DB::TYPE_JOIN_INNER,
            ])
        )
		    $type = DB::TYPE_JOIN_LEFT;
		
		$this->_join = $this->_join . " " . $type . " JOIN " . $this->escapeString($jTable) . " ON " . $this->escapeIdt($on) . " ";
		
		return $this;
	}
	
    public function delete(array $columns = [])
    {
        if (empty($columns) || !isArray($columns)) $columns = [];

        foreach ($columns as $k => $c)
            $columns[$k] = $this->escapeIdt($c);

		return $this->_query(" DELETE " . implode(',', $columns) . " FROM " . $this->_prefix . $this->_table .
			" WHERE " . $this->_where . " " . $this->_orderBy . " " . $this->_limit);
	}
	
    public function update(array $columns, array $values)
    {
		if (!isArray($columns) && !isArray($values)) return false;

		$_set = [];
		foreach ($columns as $k => $c)
		    $_set[] = $this->escapeIdt($c) . " " . DB::COMPARISON_TYPE_EQUAL . " " . $this->escapeString($values[$k]);
		if (empty($_set)) return false;

		$sql = " UPDATE " . $this->_prefix . $this->_table . " SET " . implode(',', $_set) .
			" WHERE " . $this->_where . " " . $this->_orderBy . " " . $this->_limit;

		return $this->_query($sql) ? true : false;
	}
	
    public function insert(array $columns, array $values)
    {
        if (!isArray($columns) && !isArray($values)) return false;

        $_set = [];
        $_val = [];

        foreach ($columns as $c)
            $_set[] = $this->escapeIdt($c);
        if (empty($_set)) return false;

        foreach ($values as $v)
        {
            $_valTemp = [];
            if (isArray($v))
            {
                foreach ($v as $vV)
                {
                    $_valTemp[] = $this->escapeString($vV);
                }
            }
            else if (isString($v))
            {
                $_valTemp[] = $this->escapeString($v);
            }
            else
            {
                return false;
            }
            $_val[] = implode(',', $_valTemp);
        }
		
		$sql = " INSERT INTO " . $this->_prefix . $this->_table
            . " ( " . implode(', ', $_set) . " ) VALUES ( " . implode('),(', $_val) . " ) "
            . $this->_limit
        ;

		return $this->_query($sql) ? mysqli_insert_id($this->_connect) : 0;
	}
	
	public function where($column, $value, $comparison, $or = false)
	{
        if (!isString($column) && !isString($value)) return false;
        if (
            !isString($comparison)
            || !in_array($comparison, [
                DB::COMPARISON_TYPE_EQUAL,
                DB::COMPARISON_TYPE_NOT_EQUAL,
                DB::COMPARISON_TYPE_IN,
            ])
        )
            $comparison = DB::COMPARISON_TYPE_EQUAL;

        $_where = $this->escapeIdt($column) . " " . $comparison . " " . $this->escapeString($value);

		if (isString($this->_where))
		{
			$this->_where .= " " . ( ($or === true) ? "OR" : "AND" ) . " (" . $_where . ") ";
		}
		else
		{
			$this->_where = $_where;
		}
		
		return $this;
	}
	
	public function whereOpen()
	{
		$this->_where .= " AND ( ";
		return $this;
	}
	
	public function whereOrOpen()
	{
		$this->_where .= " OR ( ";
		return $this;
	}
	
	public function whereClose()
	{
		$this->_where .= " ) ";
		return $this;
	}
	
	public function whereIn($name, array $val, $or = false)
	{
		return $this->where($name, " ( " . implode(',', $val) . ") ", DB::COMPARISON_TYPE_IN, $or);
	}

	public function whereOr($column, $value, $comparison)
	{
		return $this->where($column, $value, $comparison, true);
	}
	
	public function whereOrIn($name, array $val)
	{
		return $this->whereIn($name, $val, true);
	}
	
	public function limit($rows, $start = 0)
	{
		if (!isNumericPositive($rows)) return false;
		if (!isNumericPositive($start)) $start = 0;
		
		$this->_limit = " LIMIT " . $start . ", " . $rows . " ";
		
		return $this;
	}
	
	public function orderBy($field, $order = " ASC ")
	{
		if (!isString($field)) return false;
		if (!isString($order) || !in_array(strtoupper($order), ["ASC", "DESC"])) $order = " ASC ";
        $field = $this->escapeIdt($field);

		if (empty($this->_orderBy)) {
			$this->_orderBy = " ORDER BY " . $field . " " . $order . " ";
		} else {
			$this->_orderBy .= " , " . $field . " " . $order . " ";
		}
		
		return $this;
	}

    public function escapeIdt($value)
    {
        return "`".str_replace("`","``",$value)."`";
    }

    public function escapeString($value)
    {
        return  "'".mysqli_real_escape_string($this->_connect,$value)."'";
    }
	
}

