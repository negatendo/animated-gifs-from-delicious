animated-gifs-from-delicious
============================

This was something I made that used to scrape animated gifs from the old Del.icio.us website.

I would publish them here: http://negatendo.net/projects/animated-gifs-from-delicious/

It had some chron-edd scripts (found in scripts/) that would check the
http://feeds.delicious.com/rss/tag/system:filetype:gif feed, download the
images, and then determine if they were animated gifs. If they were, they would
be uploaded to Amazon S3, inserted into a database, and presented on a webpage.

Unforunately I had to shut it down because the gif feed no longer exists.

Setup
-------

* Setup your MySQL database with the schema found in db/items.sql
* Configure the database connection in lib/database.inc.php
* Review the files in scripts/ and add your Amazon AWS credentials where needed.

Running It
-------

Run the scripts in scripts/ via the php command-line. Then browse to index.php to see the gifs.
(Assuming the project is in a web-browsable, php-enabled directory on a server.)

* cl_get-images.php - Checks the feed, adds any animated gifs to S3 and the database.
* cl_generate-feed.php - Generates a hard-file RSS feed from the database data.

Misc
-------

Other scripts and their purposes:

* cl_create_legacy_hashes.php - For a while I wasn't tracking md5 file hashes in the db. This fixed legacy data.
* cl_expire-images.php - Hotlinking was costing me $$. This "expired" old images.

There are blank index.php files in the various directories to prevent browsing directory contents.