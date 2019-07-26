<?php 
class dbconn
{
    protected $dbhostname;
    protected $dbusername;
    protected $dbpassword;
    protected $dbname;

    function __construct($dbhostname,$dbusername,$dbpassword,$dbname)
    {	
		$this->conn = mysqli_connect($dbhostname,$dbusername,$dbpassword,$dbname) or mysqli_error();
    }

    function SelectQuery($qry)
    {   
		mysqli_set_charset($this->conn,'utf8');
		$sql = mysqli_query($this->conn,$qry);
        $result = array();
        while($res = mysqli_fetch_array($sql))
            array_push($result,$res);
         return $result;
    }

    function InsertValues($table,$insAry)
    {
        foreach($insAry as $key => $val)
        {
            $keysAry[] = $key;
            $valsAry[] = "'".trim($val)."'";
        }
        $keys = implode("," ,$keysAry);
        $vals = implode("," ,$valsAry);
       
        $qry = "INSERT INTO ".$table." (".$keys.") VALUES (".$vals.")"; 
        return $this->InsertQuery($qry);
    }

    function updateQryWhere($table,$fields,$where)
    {
        $insert_fields='';
        foreach($fields as $key => $value)
            $insert_fields .=  ($insert_fields != '') ? ' , '.$key.'="'.$value.'"' : $key.'="'.$value.'"';
       
        $qry = "UPDATE ".$table." SET ".$insert_fields." WHERE ".$where;
        return $this->executeQuery($qry);
    }

    function delQryWhere($table,$where)
    {
        $qry = "DELETE FROM ".$table." WHERE ".$where;
        return $this->executeQuery($qry);
	}

    function InsertQuery($qry)
    {
		mysqli_set_charset($this->conn,'utf8');
	   	$sql = mysqli_query($this->conn,$qry);
        $insertId = mysqli_insert_id($this->conn);
        return $insertId;
    }

    function executeQuery($qry)
    {
		$sql = mysqli_query($this->conn,$qry);
        return $sql;
    }   

    function close()
    {
        $close = mysqli_close($this->conn);
        return $close;
    }   

	function cleanstr($str)
	{
	$str = trim($str);
	$str = mysqli_real_escape_string($this->conn, $str);
	return $str;
	}

}
?>