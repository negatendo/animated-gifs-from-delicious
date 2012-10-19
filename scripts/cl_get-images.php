<?php
/**
* cl_get-images.php
*
* This command-line script searches the del.icio.us gif filetype feed for
* animated gifs, downloads the image to the img directory, and puts a reference
* to it in the database.
*
* @author Brett O'Connor
*/

//base tag feed url
define('BASE_IMAGE_FEED_URL','http://feeds.delicious.com/rss/tag/system:filetype:gif');

define('PROJECT_DIR','/srv/d_ceilingcat/webdata/negatendo.net/projects/animated-gifs-from-delicious/');

//bring in database goodness
require_once(PROJECT_DIR."lib/database.inc.php");
//bring in magpie goodness
require_once(PROJECT_DIR."lib/rss_fetch.inc");

//set cache directory
define('MAGPIE_CACHE_DIR', PROJECT_DIR."cache");

//set image directory
define('IMAGE_TMP_DIR', PROJECT_DIR."imgtmp");

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
define('ACL_SETTING','public-read');

//get rid of error Notices because magpie produces some
error_reporting(E_ERROR | E_WARNING | E_PARSE);

//for those really HUGE images :P
ini_set("memory_limit","32M");

#echo "Starting cl_get-images.php at ".date('l dS \of F Y h:i:s A')."... ";

//grab the feed
$rss = fetch_rss(BASE_IMAGE_FEED_URL);
#echo "<pre>"; print_r($rss); echo "</pre>"; die();
//setup our animated items array
$animated_gifs = array();
//download each image to the temp dir
foreach ($rss->items as $rss_item) {
    //modify the $rss_item arry to add our own file name
    $rss_item['file_name'] = md5($rss_item['link']).".gif";
    
    //skip this item if it already exists in the database posted by this user
    $row = $Db->GetOne("SELECT id FROM items 
        WHERE 
        link = '".mysql_real_escape_string($rss_item['link'])."'
        AND
        dc_creator = '".mysql_real_escape_string($rss_item['dc']['creator'])."'");
    
    if ($row == '') {
        //download the image
        $ch = curl_init($rss_item['link']);
        $fp = fopen(IMAGE_TMP_DIR."/".$rss_item['file_name'], "w");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        //if the download was ok, then evaluate the gif
        if ($response)
        {
          //ignore if the file already exists or is not animated
          if (is_ani(IMAGE_TMP_DIR."/".$rss_item['file_name'])) {
              $animated_gifs[] = $rss_item;
          } else {
              unlink(IMAGE_TMP_DIR."/".$rss_item['file_name']);
          }
        } else {
          echo "Download failed for ".$rss_item['link']." response was ".$response."";
        }
        fclose($fp);
    }
}


//insert new gifs into the amazon s3 bucket and the database
foreach ($animated_gifs as $this_gif) {
    //get the file hash
    $file_hash = md5_file(IMAGE_TMP_DIR."/".$this_gif['file_name']);
    //upload the image to s3 and return the s3 file name
    $s3_file_name = put_to_s3(IMAGE_TMP_DIR."/".$this_gif['file_name']);
    //remove the image from the image temp directory
    unlink(IMAGE_TMP_DIR."/".$this_gif['file_name']);
    //insert the data into the db
    $Db->Execute("INSERT INTO items
        (title, link, description, dc_creator, dc_date, dc_subject, file_name, file_hash)
        VALUES
        (
        '".mysql_real_escape_string($this_gif['title'])."',
        '".mysql_real_escape_string($this_gif['link'])."',
        '".mysql_real_escape_string($this_gif['description'])."',
        '".mysql_real_escape_string($this_gif['dc']['creator'])."',
        now(),
        '".mysql_real_escape_string($this_gif['dc']['subject'])."',
        '".mysql_real_escape_string($s3_file_name)."',
        '".mysql_real_escape_string($file_hash)."'
        )");
}

#echo "done. ".sizeof($animated_gifs)." new items inserted.\n";

//checks for multi frame gif images via hex codes
function is_ani($filename)
{
        $filecontents=file_get_contents($filename);
        $str_loc=0;
        $count=0;
        while ($count < 2) # There is no point in continuing after we find a 2nd frame
        {

                $where1=strpos($filecontents,"\x00\x21\xF9\x04",$str_loc);
                if ($where1 === FALSE)
                {
                        break;
                }
                else
                {
                        $str_loc=$where1+1;
                        $where2=strpos($filecontents,"\x00\x2C",$str_loc);
                        if ($where2 === FALSE)
                        {
                                break;
                        }
                        else
                        {
                                if ($where1+8 == $where2)
                                {
                                        $count++;
                                }
                                $str_loc=$where2+1;
                        }
                }
        }

        if ($count > 1)
        {
                return(true);

        }
        else
        {
                return(false);
        }
}

//puts the image up on Amazon s3
function put_to_s3($temp_file_loc)
{
    //the date and time in rfc 822
    $rfc_822_datetime = date("r");
    //file key is an md5 hash of the file path and name
    $file_key = md5($temp_file_loc);
    //assemble your s3 signature
    $s3_signature = 
        "PUT\n\nimage/gif\n".$rfc_822_datetime
        ."\nx-amz-acl:".ACL_SETTING."\n/".BUCKET_NAME."/".$file_key;
    $hasher =& new Crypt_HMAC(S3_SECRET_KEY, "sha1");
    $signature = hex2b64($hasher->hash($s3_signature));

    //make the request to create the file in the bucket
    $s3req =& new HTTP_Request(S3_URL.BUCKET_NAME."/".$file_key);
    $s3req->setMethod('PUT');
    $s3req->addHeader("content-type", 'image/gif');
    $s3req->addHeader("Date", $rfc_822_datetime);
    $s3req->addHeader("x-amz-acl", ACL_SETTING);
    $s3req->addHeader("Authorization", "AWS " . S3_ACCESS_KEY . ":" .
        $signature);
    $s3req->setBody(file_get_contents($temp_file_loc));
    $s3req->sendRequest();
    
    if ($s3req->getResponseCode() != 200) {
        echo $s3req->getResponseBody();
        die("Problem creating file for ".$temp_file_loc." (".$file_key.") - Status was ".$s3req->getResponseCode()."\n");
    } else {
        return S3_URL.BUCKET_NAME."/".$file_key;
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
