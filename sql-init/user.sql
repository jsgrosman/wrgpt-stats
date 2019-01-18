CREATE TABLE IF NOT EXISTS wrgpt.player
(
  player_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  player_name VARCHAR(45) NOT NULL,
  player_url VARCHAR(255),
  player_vpip FLOAT,
  player_pfr FLOAT,
  player_aliases VARCHAR(255)
);
