CREATE DATABASE projekt;

DROP TABLE IF EXISTS user_data;
DROP TABLE IF EXISTS decks;
DROP TABLE IF EXISTS flashcards;


CREATE TABLE user_data(
    user_id int AUTO_INCREMENT PRIMARY KEY,
    username varbinary(40) UNIQUE NOT NULL,
    password varbinary(40) NOT NULL
);

CREATE TABLE decks(
    deck_id int AUTO_INCREMENT PRIMARY KEY,
    deck_name varchar(50) NOT NULL,
    flashcard_count int DEFAULT 0,
    user_id int NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user_data(user_id)
);

CREATE TABLE flashcards_active(
    flashcard_id int AUTO_INCREMENT PRIMARY KEY,
    front varchar(1000) NOT NULL,
    back varchar(1000),
    
    deck_id int NOT NULL,
    user_id int NOT NULL,
    FOREIGN KEY (deck_id) REFERENCES decks(deck_id),
    FOREIGN KEY (user_id) REFERENCES user_data(user_id)
);

CREATE TABLE flashcards_archive LIKE flashcards_active;

