<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Animated Gifs from del.icio.us</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Brett O'Connor">
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://negatendo.net/projects/animated-gifs-from-delicious/xml/rss.xml" />
	<!-- Date: 2007-05-23 -->
</head>
<body>
<div style="text-align: center; font-size: smaller">
    This is a live stream of all animated gif images posted to <a href="http://del.icio.us/">del.icio.us</a>. - Best when served <a href="http://negatendo.net/projects/animated-gifs-from-delicious/xml/rss.xml">RSS</a>! - Feed is rebuilt every hour. - Please don't hotlink. - <a href="stats.php">Fun Statistics!</a> - <a href="http://negatendo.net/~brett/">Contact Me</a>
</div>
<div style="text-align: center; font-size: larger; color: red; padding-top: 5px; font-weight: bold">
    WARNING: Some images may not be safe for work. View at your own risk!
</div>
<p style="font-size: larger; text-align: center; padding: 3em">This page and the .gif feed is shut down until further notice.  Sorry. :( - Brett</p>
<?php
/**
//bring in database goodness
require_once('lib/database.inc.php');

//number of items to be on page 
define('NUM_PAGE_ITEMS', 30);

$rows = $Db->GetArray("SELECT * FROM items 
    ORDER BY dc_date DESC LIMIT ".NUM_PAGE_ITEMS."");

foreach ($rows as $this_row) {
    echo "<div><h2>".$this_row['title']."</h2><img src=\"".$this_row['file_name']."\"><br /><a href=\"".$this_row['link']."\">".$this_row['title']."</a> - posted by <a href=\"http://del.icio.us/".$this_row['dc_creator']."\">".$this_row['dc_creator']."</a> - tags: ".$this_row['dc_subject']."</div>";
}
*/
?>
</body>
</html>
