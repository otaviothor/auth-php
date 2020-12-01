CREATE DATABASE auth;
USE auth;

CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT,
  first_name VARCHAR(255) NOT NULL,
  last_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  passwd VARCHAR(255) NOT NULL,
  facebook_id VARCHAR(30),
  google_id VARCHAR(30),
  photo VARCHAR(255),
  forget VARCHAR(255),
  created_at TIMESTAMP DEFAULT current_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (id)
);