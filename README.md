# What is WRGPT?

WRGPT stands for the World Rec.Gambling.Poker Tournament, a long running email poker tournament. 
There are no entry fees, and no prizes.

# What is this?

This stats program can parse a WRGPT hand summary, put the data in a database, and can analyze how a player has played.

# What data is included?

I don't have every hand summary. I'm starting this in Tournament 28 in 2018-19, so I will have every hand starting from then. 
Before that, I only have hand histories of hands that I've played in since Tournament 18, in 2008-9. 

As of January 16, 2019

* Number of players: 801
* Number of hands: 6392
* Number of actions: 107627 

I've checked in all the hand histories I have available to `./data`.
I will continue to add new hand histories as the tournament continues.

# Installation

This app uses docker containers. You will need to install docker on your local machine.

`docker-compose up`

This command will read the configuration from `docker-compose.yml`, create two containers, one for PHP and one for mysql.
It will set up the environment, create the db schema, and download php and javascript libraries


# Data Load

`docker exec -w /app/scripts wrgpt-stats_web_1 php updateDBFromDataDirectory.php`

It will not be necessary to connect to the wrgpt site in this initial load.

Once the data is loaded, the site can be accessed at:

http://localhost:8080/player.html

# Contributing Hand Histories

If you have hand histories that I don't currently possess, I would be grateful.

## Data Directory Structure

* `./data`
  * `t28`
    * `b`
      * `b1_1.txt`
      * `b1_2.txt`
      * ...
    * `c`
      * ...
    * ...  

I ignore the `a` round, because that's just for the practice round. 

# Contributing Code

This is mostly a fun project for me to improve my game and play around with the latest in modern web dev.


