-- Criação do banco de dados e tabelas
CREATE DATABASE IF NOT EXISTS privacy_admin;
USE privacy_admin;

-- Tabela de usuários admin
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de configurações do site
CREATE TABLE IF NOT EXISTS site_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de imagens
CREATE TABLE IF NOT EXISTS site_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_key VARCHAR(100) UNIQUE NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de pedidos/checkout
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    customer_phone VARCHAR(50),
    plan_type VARCHAR(50),
    amount DECIMAL(10,2),
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO admin_users (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserir configurações padrão
INSERT INTO site_config (config_key, config_value) VALUES 
('model_name', 'Kamylinha Santos'),
('model_username', '@eukamylinhasantos'),
('model_bio', 'Oi, meus amores! 🔥💦 Sou a Kamylinha Santos, a musa da dancinha do tiktok, e hoje vou revelar um lado meu que vai te deixar sem fôlego… Imagine vídeos gozando com meus ficantes, trisal com amigas safadas e momentos íntimos onde me entrego de corpo e alma. 😏 Cada centímetro do meu corpo é pura tentação e minhas fotos peladas são um convite exclusivo para você explorar seus desejos mais secretos – tudo sem censura! Se você tem coragem de se perder nessa paixão sem limites, vem comigo... Estou te esperando para uma experiência única e irresistível.😈💋'),
('monthly_price', '10.00'),
('monthly_old_price', '47.00'),
('lifetime_price', '27.00'),
('lifetime_old_price', '197.00'),
('checkout_url_monthly', 'https://pay.privacydade.co/privacy1mes/'),
('checkout_url_lifetime', 'https://pay.privacydade.co/privacyvitalicio/');

-- Inserir imagens padrão
INSERT INTO site_images (image_key, image_path, alt_text) VALUES 
('cover_image', 'src/images/capa.png', 'Imagem de capa'),
('profile_image', 'src/images/perfil1.png', 'Foto de perfil'),
('content_image1', 'src/images/imagem1.png', 'Foto 1'),
('content_image2', 'src/images/imagem2.png', 'Foto 2'),
('logo', 'src/images/privacy-logo.svg', 'Privacy Logo'),
('top_bar_logo', 'src/images/Untitled image (25).png', 'Top Bar Logo');