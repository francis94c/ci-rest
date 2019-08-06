#
# TABLE: rest_api_rate_limit
#
CREATE TABLE IF NOT EXISTS rest_api_rate_limit (client VARCHAR(255) NOT NULL DEFAULT "",
_group VARCHAR(255) NOT NULL DEFAULT "_ip_address", start TIMESTAMP NOT NULL DEFAULT
CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, count INT(11) NOT NULL DEFAULT 1,
PRIMARY KEY (client, _group)) ENGINE=InnoDB;

#@@@

#
# TABLE: users
#
CREATE TABLE IF NOT EXISTS users (id INT(7) AUTO_INCREMENT PRIMARY KEY, username VARCHAR(20)
NOT NULL, password TEXT NOT NULL, email VARCHAR(30) NOT NULL) Engine=InnoDB;

#@@@

#
# TABLE: api_keys
#
CREATE TABLE IF NOT EXISTS api_keys (id INT(7) AUTO_INCREMENT PRIMARY KEY, api_key TEXT,
api_secret TEXT, _limit TINYINT DEFAULT 1, user_id INT(9) NOT NULL, app_id TEXT,
FOREIGN KEY (user_id) REFERENCES users(id)) Engine=InnoDB;

#@@@

# Inserts

INSERT INTO users (id, username, password, email) VALUES (1, 'francis94c',
  '$2y$10$eSvuRvrZe./d.a/g4EuokepXntP.rwAf.ibpNZ/CDKaOqYW2mWHf.', 'francis@email.com');

#@@@

INSERT INTO api_keys (id, api_key, api_secret, _limit, user_id, app_id) VALUES (
  1, 'ABCDE', 'abcdefghijklmnopqrstuvwxyz', 1, 1, '01234567890');
