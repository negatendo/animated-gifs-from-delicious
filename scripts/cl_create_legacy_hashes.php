<?php
/**
* cl_create_legacy_hashes.php
*
* This script combs the database for images that don't have an md5 hash for their file, pulls the file
* from amazon S3, fetches the hash, updates it for all records with the same original url, and then
* deletes the file it pulled.
*
* @author Brett O'Connor
*/

define('PROJECT_DIR','/srv/d_ceilingcat/webdata/negatendo.net/projects/animated-gifs-from-delicious/');

//bring in database goodness
require_once(PROJECT_DIR."lib/database.inc.php");

//existing bucket name
define('BUCKET_NAME','');

//access and secret keys for your s3 account
define('S3_ACCESS_KEY','');
define('S3_SECRET_KEY','');

// pear installed goodness
require_once 'Crypt/HMAC.php';
require_once 'HTTP/Request.php';

//other s3 settings
define('S3_URL',"http://s3.amazonaws.com/");

//number of images to process per script execution
define('NUM_IMAGES_TO_PROCESS',5000);

$Db->debug = true;
$already_processed = array();

$query = "SELECT * FROM items where file_hash = '' order by dc_date desc limit ".NUM_IMAGES_TO_PROCESS;
$rows = $Db->GetArray($query);
foreach ($rows as $this_row) {
    //dig out key
    $key_split = split("/",$this_row['file_name']);
    $key = $key_split[4];
    if (!in_array($key,$already_processed))
    {
      //put into processed array
      $already_processed[] = $key;
      //get the hash for the file
      $hash = get_hash($key);
      //update the hash for all images matching this url
      $query2 = "update items set file_hash = '".$hash."' where file_name = '".$this_row['file_name']."'";
      $Db->Execute($query2);
    } else {
      print $key." already processed this session!\n";
    }
} 


//fetches the file to the tmp dir
function get_hash($file_key)
{
  print $file_key;
    //the date and time in rfc 822 (again)
    $rfc_822_datetime = date("r");
    //assemble your s3 signature
    $s3_signature = "GET\n\n\n".$rfc_822_datetime."\n/".BUCKET_NAME."/".$file_key;
 
    $hasher =& new Crypt_HMAC(S3_SECRET_KEY, "sha1");
    $signature = hex2b64($hasher->hash($s3_signature));

    //make the request
    $s3req =& new HTTP_Request(S3_URL.BUCKET_NAME."/".$file_key);
    $s3req->setMethod('GET');
    $s3req->addHeader("Date", $rfc_822_datetime);
    $s3req->addHeader("Authorization", "AWS " . S3_ACCESS_KEY . ":" .
        $signature);
    $s3req->sendRequest();
    //create the temporary file
    $handle = fopen(PROJECT_DIR."imgtmp/needshash.gif","a+");
    fwrite($handle,$s3req->getResponseBody());
    fclose($handle);
    //get the hash, delete the file, and return the hash
    $hash = md5_file(PROJECT_DIR."imgtmp/needshash.gif");
    unlink(PROJECT_DIR."imgtmp/needshash.gif");
    return $hash;
}

function hex2b64($str) {
    $raw = '';
    for ($i=0; $i < strlen($str); $i+=2) {
        $raw .= chr(hexdec(substr($str, $i, 2)));
    }
    return base64_encode($raw);
}
?>
