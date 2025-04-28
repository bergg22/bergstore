CREATE DATABASE IF NOT EXISTS bergstore;
USE bergstore;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    release_year INT,
    developer VARCHAR(100),
    publisher VARCHAR(100),
    category_id INT NOT NULL,
    price DECIMAL(10, 2),
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

DELIMITER //
CREATE TRIGGER IF NOT EXISTS game_log_trigger
AFTER INSERT ON games
FOR EACH ROW
BEGIN
    INSERT INTO game_logs (game_id, action, log_message)
    VALUES (NEW.id, 'INSERT', CONCAT('Novo jogo adicionado: ', NEW.title));
END //
DELIMITER ;

CREATE TABLE IF NOT EXISTS game_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT,
    action VARCHAR(50) NOT NULL,
    log_message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

DELIMITER //
CREATE PROCEDURE IF NOT EXISTS ExportGamesToTXT()
BEGIN
    SELECT CONCAT(g.id, ',', g.title, ',', c.name, ',', g.release_year, ',', g.price) AS game_data
    FROM games g
    JOIN categories c ON g.category_id = c.id
    ORDER BY g.title;
END //
DELIMITER ;

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO users (username, password, email)
VALUES ('admin', '$2y$10$8x5ZWIYaZch7gJq0r5.vseqiXGiOgfC7O5YM1xPz09E0DkMOsU5J2', 'admin@bergstore.com')
ON DUPLICATE KEY UPDATE username = 'admin';

INSERT INTO categories (name, description)
VALUES 
('RPG', 'Jogos de interpretação de papéis com desenvolvimento de personagem'),
('FPS', 'Jogos de tiro em primeira pessoa'),
('Estratégia', 'Jogos de estratégia e planejamento'),
('Aventura', 'Jogos de exploração e aventura'),
('Esportes', 'Jogos baseados em esportes reais'),
('Corrida', 'Jogos de corrida com veículos')
ON DUPLICATE KEY UPDATE name = VALUES(name);