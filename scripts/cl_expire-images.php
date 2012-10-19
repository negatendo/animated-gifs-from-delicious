<?php
/**
* cl_expire-images.php
*
* This command line script combs the database for images older than MAX_DAYS_READ and then
* removes permissions on Amazon AWS for them. I had to make this script because
* image hotlinkers were making me have to pay too much in bandwidth. Also sets is_expired
* to 1 to note this expiration happened (and minimize the database call this thing has to
* make all the time)
*
* TODO:  With cl_get-images.php, when downloading an image that already exists, will check
* to see if it's expired and if so, un-expire it and re-enable READ permissions, updating
* the date?
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

//max num days for an image to be READ (live)
define('MAX_DAYS_READ',1);

$query = "SELECT * FROM items WHERE dc_date < subdate(curdate(),interval ".MAX_DAYS_READ." day) and is_expired = 0 order by dc_date desc";
$rows = $Db->GetArray($query);
foreach ($rows as $this_row) {
    //dig out key
    $key_split = split("/",$this_row['file_name']);
    $key = $key_split[4];
    //remove acl permissions for all users (minus owner)
    remove_acl($key);
    //update db record to set is_expired to true
    $Db->Execute("UPDATE items SET is_expired = 1 WHERE id = ".$this_row['id']);
    //messages
    echo "Expired ".S3_URL.BUCKET_NAME."/".$key."\n";
} 


//removes public access from a file
function remove_acl($file_key)
{
    //first get the current acl policy (to grab owner info)
    //the date and time in rfc 822 (again)
    $rfc_822_datetime = date("r");
    //assemble your s3 signature
    $s3_signature = "GET\n\n\n".$rfc_822_datetime."\n/".BUCKET_NAME."/".$file_key."?acl";
 
    $hasher =& new Crypt_HMAC(S3_SECRET_KEY, "sha1");
    $signature = hex2b64($hasher->hash($s3_signature));

    //make the request to get current acl
    $s3req =& new HTTP_Request(S3_URL.BUCKET_NAME."/".$file_key."?acl");
    $s3req->setMethod('GET');
    $s3req->addHeader("Date", $rfc_822_datetime);
    $s3req->addHeader("Authorization", "AWS " . S3_ACCESS_KEY . ":" .
        $signature);
    $s3req->sendRequest();
    $current_acl = $s3req->getResponseBody();
    //seperate out the "group" policy
    $split_policy = split('<Grant><Grantee xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="Group">',$current_acl);
    //create the new policy with just owner info (preserved part of original policy)
    $new_policy = $split_policy[0]."</AccessControlList></AccessControlPolicy>";

    //ok, now construct another request to set the new policy

    //the date and time in rfc 822 (again)
    $rfc_822_datetime = date("r");
    //assemble your s3 signature
    $s3_signature = "PUT\n\napplication/x-www-form-urlencoded\n".$rfc_822_datetime."\n/".BUCKET_NAME."/".$file_key."?acl";
    
    $hasher =& new Crypt_HMAC(S3_SECRET_KEY, "sha1");
    $signature = hex2b64($hasher->hash($s3_signature));

    //make the request to change the acl
    $s3req =& new HTTP_Request(S3_URL.BUCKET_NAME."/".$file_key."?acl");
    $s3req->setMethod('PUT');
    $s3req->addHeader("Date", $rfc_822_datetime);
    $s3req->addHeader("Authorization", "AWS " . S3_ACCESS_KEY . ":" .
        $signature);
    //new xml acl
    $s3req->setBody($new_policy);
    $s3req->sendRequest();
    
    if ($s3req->getResponseCode() != 200) {
        echo "Problem updating acl for ".$file_key." - Status was ".$s3req->getResponseCode()."\n";
    }
}

function hex2b64($str) {
    $raw = '';
    for ($i=0; $i < strlen($str); $i+=2) {
        $raw .= chr(hexdec(substr($str, $i, 2)));
    }
    return base64_encode($raw);
}
?>
