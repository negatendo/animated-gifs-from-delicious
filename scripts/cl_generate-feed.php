<?php
/**
* cl_generate-feed.php
*
* This command-line script searches grabs the latest so many items from the
* items database and then makes an RSS feed out of them. Yay!
*
* @author Brett O'Connor
*/

define('PROJECT_DIR','/srv/d_ceilingcat/webdata/negatendo.net/projects/animated-gifs-from-delicious/');

//bring in database goodness
require_once(PROJECT_DIR."lib/database.inc.php");

//set xml directory and filename
define('XML_FILE', PROJECT_DIR."xml/rss.xml");

//number of items to be in feed
define('NUM_FEED_ITEMS', 30);

//rcd822 formatted date (php 4 constant not working?)
define('RFC822','D, d M y H:i:s O');

#echo "Starting cl_generate-feed.php at ".date('l dS \of F Y h:i:s A')."... ";
    
$xml = "<?xml version=\"1.0\"?>
<rss version=\"2.0\">
<channel>
<title>Animated Gifs from Del.icio.us</title>
<link>http://negatendo.net/projects/animated-gifs-from-delicious/</link>
<description>All animated .gif images posted to del.icio.us. By negatendo.</description>
<language>en-us</language>
<pubDate>".date(RFC822)."</pubDate>
<lastBuildDate>".date(RFC822)."</lastBuildDate>";

$rows = $Db->GetArray("SELECT * FROM items 
    ORDER BY dc_date DESC LIMIT ".NUM_FEED_ITEMS."");

foreach ($rows as $this_row) {
$xml .= "
<item>
<title>".xmlentities($this_row['title'])."</title>
<link>".xmlentities($this_row['link'])."</link>
<description><![CDATA[
<img src=\"".xmlentities($this_row['file_name'])."\"><br />
<a href=\"".xmlentities($this_row['link'])."\">".xmlentities($this_row['title'])."</a> - posted by <a href=\"http://del.icio.us/".xmlentities($this_row['dc_creator'])."\">".xmlentities($this_row['dc_creator'])."</a> - tags: ".xmlentities($this_row['dc_subject'])."
]]></description>
<pubDate>".date(RFC822,strtotime($this_row['dc_date']))."</pubDate>
<guid>".xmlentities($this_row['link'])."</guid>
</item>";

}

$xml .= "
</channel>
</rss>";

$handle = fopen(XML_FILE,'w');
fwrite($handle,$xml);
fclose($handle);

#echo "done.\n";

// XML Entity Mandatory Escape Characters
function xmlentities($string) {
   return str_replace ( array ( '&', '"', "'", '<', '>', '�' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string );
}
?>
