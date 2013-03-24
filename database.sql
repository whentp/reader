PRAGMA foreign_keys = ON;

CREATE TABLE `users`
(
	--created by @whentp, using sqlite3
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	openidmd5 VARCHAR(50),
	name VARCHAR(50)
);
CREATE INDEX `user_name_idx` ON `users`(name);

CREATE TABLE `outlines`
(
	--created by @whentp, using sqlite3
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	text VARCHAR(50),
	title VARCHAR(50),
	user_id INTEGER,
	order_index INTEGER,
	FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE `feeds`
(
	--created by @whentp, using sqlite3
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	link VARCHAR(50),
	title VARCHAR(50),
	description VARCHAR(50),
	activated INTEGER,
	template INTEGER
);

CREATE TABLE `feed_statuses`
(
	--created by @whentp, using sqlite3
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	outline_id INTEGER,
	feed_id INTEGER,
	user_id INTEGER,
	read INTEGER,
	read_until_id INTEGER,
	order_index INTEGER,
	FOREIGN KEY(user_id) REFERENCES users(id),
	FOREIGN KEY(outline_id) REFERENCES outlines(id),
	FOREIGN KEY(feed_id) REFERENCES feeds(id)
);

CREATE TABLE `items`
(
	--created by @whentp, using sqlite3
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	feed_id INTEGER,
	title VARCHAR(50),
	link VARCHAR(50),
	author VARCHAR(50),
	description TEXT,
	pubDate DATETIME,
	when_fetch DATETIME,
	FOREIGN KEY(feed_id) REFERENCES feeds(id)
);
CREATE INDEX `item_timestamp_idx` ON `items`(pubDate);
CREATE INDEX `item_fetchtime_idx` ON `items`(when_fetch);
CREATE INDEX `item_link_idx` ON `items`(link);
CREATE INDEX `item_feed_id_idx` ON `items`(feed_id);

CREATE TABLE `item_statuses`
(
	--created by @whentp, using sqlite3
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	item_id INTEGER,
	user_id INTEGER,
	read INTEGER,
	starred INTEGER,
	shared INTEGER,
	timestamp DATETIME,
	FOREIGN KEY(user_id) REFERENCES users(id),
	FOREIGN KEY(item_id) REFERENCES items(id)
);
CREATE INDEX `item_read_idx` ON `item_statuses`(read);
CREATE INDEX `item_starred_idx` ON `item_statuses`(starred);
CREATE INDEX `item_user_id_idx` ON `item_statuses`(user_id);
CREATE INDEX `item_item_id_idx` ON `item_statuses`(item_id);
CREATE INDEX `item_item_timestamp_idx` ON `item_statuses`(timestamp);
CREATE INDEX `item_item_shared_idx` ON `item_statuses`(shared);


