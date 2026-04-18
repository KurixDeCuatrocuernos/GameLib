-- We create the dabase
CREATE DATABASE IF NOT EXISTS gamelib;
-- We use the database
USE gamelib;

-- Table for roles 
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);
-- Table for Themes
CREATE TABLE IF NOT EXISTS themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);
-- Table for languages
CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);
-- Table for gemes collected from IGDB API
CREATE TABLE IF NOT EXISTS games (
    -- Internal ID
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    -- IGDB API ID
    igdb_id INT NOT NULL,
    -- JSON to creates the Image URL
    cover VARCHAR(255) NOT NULL,
    -- Title
    name VARCHAR(255) NOT NULL,
    release_date DATE NOT NULL,
    -- Unique combination of name and release_date
    UNIQUE KEY unique_name_release (name, release_date)
);
-- Critical Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role INT DEFAULT(1),
    password VARCHAR(250) NOT NULL,
    created_at TIMESTAMP DEFAULT(CURRENT_TIMESTAMP),

    FOREIGN KEY (role) REFERENCES roles(id)
);
-- It's a link Table, that's why only has Foreign Keys 
CREATE TABLE IF NOT EXISTS users_data (
    id INT PRIMARY KEY,
    theme INT,
    language INT,
    
    FOREIGN KEY (id) REFERENCES users(id),
    FOREIGN KEY (theme) REFERENCES themes(id),
    FOREIGN KEY (language) REFERENCES languages(id)
);
-- Table for different providers (steam, Epic, GOG...)
CREATE TABLE IF NOT EXISTS users_providers(
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(50) NOT NULL,
    -- We don't know their extension, so we use TEXT type 
    steam_id VARCHAR(100), -- Este es exclusivo de Steam
    access_token TEXT,
    refresh_token TEXT,
    expires_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_provider (user_id, provider)
);
-- Table for categories
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    -- Could be null, depends on if is a user's tag or page's tag
    created_by_user INT, 

    FOREIGN KEY (created_by_user) REFERENCES users(id) ON DELETE SET NULL
);
-- Table which links tags and games in order to sort and filter games
CREATE TABLE IF NOT EXISTS game_tags (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT NOT NULL,
    tag_id INT,

    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
-- Link table and the importantest
CREATE TABLE IF NOT EXISTS users_games (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    game_id BIGINT NOT NULL,
    user_id INT NOT NULL,
    user_provider_id BIGINT,

    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_provider_id) REFERENCES users_provider(id) ON DELETE SET NULL
);

-- Como no admiten duplicados no se repetirá la inserción aunque lo ejecutemos por segunda vez
INSERT INTO roles(name) VALUE("user");
INSERT INTO roles(name) VALUE("admin");

INSERT INTO themes(name) VALUE("light");
INSERT INTO themes(name) VALUE("dark");

INSERT INTO languages(name) VALUE("english");
INSERT INTO languages(name) VALUE("spanish");




