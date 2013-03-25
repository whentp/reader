how to set up a reader environment

1. run update.database.php to genereate or upgrade database file (please ALWAYS do this when source code files are changed).
2. set a cron job for cron.fetchfeed.php.
3. set permission of cache to 777.
4. set config.php properly, especially the HOSTNAME.
5. login with your google openid.
6. browse and see an ugly interface. add some feeds and run cron.fetchfeed.php then the ui will be fine.

IMPORTANT: Please run cron.fetchfeed.php first, either manually or setup a cron job !!!


how to subscribe a feed

create an OPML file then copy & paste to import box,
or copy & paste existing OPML files to import box,
or click ``subscribe'' on the top.


how to complain this simple reader

1. blablabla > /dev/null .
2. complain @whentp is lazy.
3. contribute your codes.
