<?php
    $sql_host = 'localhost';
    $sql_user = 'oaks35_local';
    //$sql_pass = 'Jp9j8LFLs923HtR6';
	$sql_pass = '...';
    $sql_database = 'oaks35_vapelex';
    
    // SQL keres levedese
    function mysql_escape($text){
      global $_mysql;
      
      if(!$_mysql) connect_mysql();
        if(get_magic_quotes_gpc()) $text = stripslashes($text);
        if(!is_numeric($text)) $text = mysql_real_escape_string($text, $_mysql);
        return $text;
    }

    // Connecting to the database
    function connect_mysql(){
      global $_mysql, $sql_host, $sql_user, $sql_pass, $sql_database;
      
      $_mysql = @mysql_connect($sql_host, $sql_user, $sql_pass);
      @mysql_select_db($sql_database, $_mysql) or die(mysql_error());
    }

    // SQL keres vegrehajtasa
    function db_query(){
        global $query_num, $sqltime, $_mysql;
        
        if(!$_mysql) connect_mysql();
        $args  = func_get_args();
        $query = array_shift($args);
        $args  = array_map('mysql_escape', $args);
        array_unshift($args, $query);
        $query = call_user_func_array('sprintf', $args);
        $result = @mysql_query($query, $_mysql) or die(mysql_error());
        
        return $result;
    }
    
    // Add log
    function add_log($uid, $text){
        db_query("INSERT INTO `logs` (`lid`, `uid`, `text`, `time`) VALUES (NULL, %d, '%s', %d)", $uid, $text, time());
        return TRUE;    
    }
?>