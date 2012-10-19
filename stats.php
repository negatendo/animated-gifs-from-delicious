<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Animated Gifs from del.icio.us - Fun Statistics!</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Brett O'Connor">
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://negatendo.net/projects/animated-gifs-from-delicious/xml/rss.xml" />
	<!-- Date: 2007-05-23 -->
</head>
<body>
<div style="text-align: center; font-size: smaller">
    Here's some real-time statistics for <a href="index.php">Animated Gifs from del.icio.us</a>.  This data is all as-of <strong>June 6, 2007</strong>.
</div>

<?php
//bring in database goodness
require_once('lib/database.inc.php');
?>

<h2>Top 10 most prolific posters of Animated Gifs to del.icio.us</h2>
<table border="1" cellpadding="1">
<thead style="font-weight: bold">
    <tr>
        <td>Rank</td>
        <td>del.icio.us Username</td>
        <td>Number of Posts</td>
    </tr>
</thead>
<tbody>
<?php
$rows = $Db->GetArray("SELECT dc_creator, COUNT(dc_creator) AS num_posts FROM items GROUP BY dc_creator ORDER BY num_posts DESC LIMIT 10");
$row_int = 1;
foreach ($rows as $this_row) {
    echo "<tr><td>$row_int</td><td><a href=\"http://del.icio.us/".$this_row['dc_creator']."\">".$this_row['dc_creator']."</a></td><td>".$this_row['num_posts']."</tr>";
    $row_int++;
}
?>
</tbody>
</table>

<h2>Top 50 most popular Animated Gifs posted to del.icio.us</h2>
<table border="1" cellpadding="1">
<thead style="font-weight: bold">
    <tr>
        <td>Rank</td>
        <td>URL</td>
        <td>Occurrences</td>
    </tr>
</thead>
<tbody>
<?php
$rows = $Db->GetArray("SELECT link, COUNT(link) AS Occurrences FROM items GROUP BY link ORDER BY Occurrences DESC LIMIT 50");

$row_int = 1;
foreach ($rows as $this_row) {
    echo "<tr><td>$row_int</td><td><a href=\"".$this_row['link']."\">".$this_row['link']."</a></td><td>".$this_row['Occurrences']."</tr>";
    $row_int++;
}
?>
</tbody>
</table>

</body>
</html>
