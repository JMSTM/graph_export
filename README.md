# INSTALL
## SQL
- Create a new database
- Launch queries from install.sql in this database

##CONFIG
Update config.php file with SQL credentials

##CRON
Launch php cron.php once

# Graph precision
##config.php
###$dataPrecision;
- 1 : minute precision : shown data will be max(datas) group by minute
- 2 : seconde precision : no group by