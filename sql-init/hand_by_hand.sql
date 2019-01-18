CREATE TABLE IF NOT EXISTS wrgpt.hand_by_hand (
        tournament_id INT NOT NULL,
        table_name VARCHAR(45) NOT NULL,
        hand_num INT NOT NULL,
        player VARCHAR(45) NOT NULL,
        position INT NOT NULL,
        latest_round VARCHAR(45) NOT NULL,
        cards VARCHAR(10),
        put_money_preflop TINYINT NULL,
        raised_preflop TINYINT NULL,
        is_all_in TINYINT NULL,
        was_in_showdown TINYINT NULL,
        is_winner TINYINT NULL,
        PRIMARY KEY (tournament_id, table_name, hand_num, player));
