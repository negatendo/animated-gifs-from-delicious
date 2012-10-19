<?php
/**
* Quote Handle - When included will ensure magic_quotes is disabled and
* supplies mysql_real_escape_array() and stripslashes_array()
*
* @author Brett O'Connor
*/

//Make sure when reading file data, PHP doesn't "magically" mangle
//backslashes!
set_magic_quotes_runtime(FALSE);

//this application will not run if magic_quotes_gpc is enabled
if (get_magic_quotes_gpc())
    trigger_error("You must disable magic quotes GPC.",E_USER_ERROR);

/**
* Does mysql_real_escape_string() on every value in an array
*
* @param array The array to be escaped
*/
function mysql_real_escape_array($data) {
    if (is_array($data)){
        foreach ($data as $key => $value){
            $data[$key] = mysql_real_escape_array($value);
        }
        return $data;
    }else{
        //escape if not numeric
        if (!is_numeric($data))
            return mysql_real_escape_string($data);
        else
            return $data;
    }
}

/**
* Does stripslashes() on every value in an array
*
* @param array The array to be stripped
*/
function stripslashes_array($data) {
    if (is_array($data)){
        foreach ($data as $key => $value){
            $data[$key] = stripslashes_array($value);
        }
        return $data;
    }else{
        return stripslashes($data);
    }
}


/**
* Does addslashes() on every value in an array (NOTICE: Using 
* mysql_real_escape_string() or mysql_escape_array() is MUCH BETTER
* - especially for any data to be used in MySQL This function just exists for
* rare circumstances.)
*
* @param array The array to be slashed
*/
function addslashes_array($data) {
    if (is_array($data)){
        foreach ($data as $key => $value){
            $data[$key] = addslashes_array($value);
        }
        return $data;
    }else{
        if (!is_numeric($data))
            return addslashes($data);
        else
            return $data;
    }
}

?>