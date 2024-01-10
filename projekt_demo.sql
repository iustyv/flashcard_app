CREATE DATABASE projekt;

DROP TABLE IF EXISTS user_data;
DROP TABLE IF EXISTS decks;
DROP TABLE IF EXISTS flashcards_active;


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
    front varchar(3000) NOT NULL,
    back varchar(3000),
    next_revision date,
    fluency_level tinyint(1) DEFAULT 0,
    completion boolean DEFAULT 0,
    
    deck_id int NOT NULL,
    user_id int NOT NULL,
    FOREIGN KEY (deck_id) REFERENCES decks(deck_id),
    FOREIGN KEY (user_id) REFERENCES user_data(user_id)
);


