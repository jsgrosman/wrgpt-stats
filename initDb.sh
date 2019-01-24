#! /bin/bash

docker exec -w /app/scripts wrgpt-stats_web_1 php updateDBFromDataDirectory.php
