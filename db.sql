DROP TABLE IF EXISTS Users;
CREATE TABLE Users (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    Admin BOOLEAN NOT NULL DEFAULT FALSE
);

DROP TABLE IF EXISTS Posts;
CREATE TABLE Posts (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    `User` INT NOT NULL,
    `Title` VARCHAR(255) NOT NULL,
    `Body` TEXT NOT NULL,
    `Date` DATETIME NOT NULL DEFAULT NOW(),

    FOREIGN KEY (`User`) REFERENCES Users(ID)
);

DROP TABLE IF EXISTS Reports;
CREATE TABLE Reports (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    `Post` INT NOT NULL,
    `User` INT NOT NULL,
    `Body` TEXT NOT NULL
);