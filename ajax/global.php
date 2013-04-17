<?php
function GetMasterUser()
{
	return "masteruser@gmail.com";
}
function GetMasterUserId()
{
	return 3;
}
function GetTmpUser()
{
	return "tmpuser@pieceoftheworld.co";
}
function GetTmpUserId()
{
	return 5;
}
function GetGlobalConnectionOptions()
{
	/*
	return array(
	'server' => 'localhost',
	'port' => '3306',
	'username' => 's15331327_admin',
	'password' => '8VnNhDQw',
	'database' => 's15331327_gmm'
	*/
	return array(
	'server' => 'localhost',
	'port' => '3306',
	'username' => 'pieceoft_db3',
	'password' => '1litervand',
	'database' => 'pieceoft_db3'
);
}

//get a connection
$_dblink = dbQuery("", "", true);

function dbQuery($query, $link="", $connectonly=false){
	if($_SERVER['HTTP_HOST']=='localhost'){
		$DATABASE_HOST = "localhost";
		$DATABASE_USER = "root";
		$DATABASE_PASSWORD = "";
		$DATABASE = "pieceoft_db3";
	}
	else{
		$DATABASE_HOST = "localhost";
		$DATABASE_USER = "pieceoft_db3";
		$DATABASE_PASSWORD = "1litervand";
		$DATABASE = "pieceoft_db3";
	}
    $returnArr = array();
	
	

	if(!$link){
		/* Connecting, selecting database */
		$link = mysql_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASSWORD)
			or die("Could not connect : " . mysql_error());
		if($connectonly){
			return $link;
		}
	}
	mysql_select_db($DATABASE) or die("Could not select database");

	//detect if query is select
	if(strpos(strtolower(trim($query)), "select")===0){
		mysql_query('SET CHARACTER SET utf8;');
	}
	
    /* Performing SQL query */
    $result = mysql_query($query) or die("Query failed : " . mysql_error() . "<br>Query: <b>$query</b>");

   
    //if query is select
    if(@mysql_num_rows($result))
    {
        while ($row = mysql_fetch_assoc($result))
        {
            array_push($returnArr, $row);
        }      
    }
    //if query is insert
    else if(@mysql_insert_id())
    {
        $returnArr["mysql_insert_id"] = @mysql_insert_id();
    }
       

    /* Free resultset */
    @mysql_free_result($result);

    /* Closing connection */
    //mysql_close($link);
   
    //return array
    return $returnArr;
}


?>