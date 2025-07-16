<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_POST) {
    try {
        $pdo->beginTransaction();
        
        foreach ($_POST as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO site_config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        
        $pdo->commit();
        
        // Regenerar o arquivo index.html com as novas configurações
        generateIndexFile();
        
        header('Location: dashboard.php?success=1');
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: dashboard.php?error=1');
    }
}

function generateIndexFile() {
    global $pdo;
    
    // Buscar todas as configurações
    $stmt = $pdo->query("SELECT * FROM site_config");
    $configs = [];
    while ($row = $stmt->fetch()) {
        $configs[$row['config_key']] = $row['config_value'];
    }
    
    // Buscar todas as imagens
    $stmt = $pdo->query("SELECT * FROM site_images");
    $images = [];
    while ($row = $stmt->fetch()) {
        $images[$row['image_key']] = $row['image_path'];
    }
    
    // Ler o template do index.html
    $template = file_get_contents('../index.html');
    
    // Substituir os valores no template
    $template = str_replace('Kamylinha Santos', $configs['model_name'] ?? 'Kamylinha Santos', $template);
    $template = str_replace('@eukamylinhasantos', $configs['model_username'] ?? '@eukamylinhasantos', $template);
    
    // Substituir biografia
    if (isset($configs['model_bio'])) {
        $pattern = '/<p class="bio-text" id="bioText">(.*?)<\/p>/s';
        $replacement = '<p class="bio-text" id="bioText">' . htmlspecialchars($configs['model_bio']) . '</p>';
        $template = preg_replace($pattern, $replacement, $template);
    }
    
    // Substituir preços
    $template = str_replace('R$ 10,00', 'R$ ' . number_format($configs['monthly_price'] ?? 10, 2, ',', '.'), $template);
    $template = str_replace('R$ 47,00', 'R$ ' . number_format($configs['monthly_old_price'] ?? 47, 2, ',', '.'), $template);
    $template = str_replace('R$ 27,00', 'R$ ' . number_format($configs['lifetime_price'] ?? 27, 2, ',', '.'), $template);
    $template = str_replace('R$ 197,00', 'R$ ' . number_format($configs['lifetime_old_price'] ?? 197, 2, ',', '.'), $template);
    
    // Substituir URLs de checkout
    $template = str_replace('https://pay.privacydade.co/privacy1mes/', $configs['checkout_url_monthly'] ?? 'https://pay.privacydade.co/privacy1mes/', $template);
    $template = str_replace('https://pay.privacydade.co/privacyvitalicio/', $configs['checkout_url_lifetime'] ?? 'https://pay.privacydade.co/privacyvitalicio/', $template);
    
    // Substituir caminhos das imagens
    foreach ($images as $key => $path) {
        switch ($key) {
            case 'cover_image':
                $template = str_replace('src/images/capa.png', $path, $template);
                break;
            case 'profile_image':
                $template = str_replace('src/images/perfil1.png', $path, $template);
                break;
            case 'content_image1':
                $template = str_replace('src/images/imagem1.png', $path, $template);
                break;
            case 'content_image2':
                $template = str_replace('src/images/imagem2.png', $path, $template);
                break;
            case 'logo':
                $template = str_replace('src/images/privacy-logo.svg', $path, $template);
                break;
            case 'top_bar_logo':
                $template = str_replace('src/images/Untitled image (25).png', $path, $template);
                break;
        }
    }
    
    // Salvar o arquivo atualizado
    file_put_contents('../index.html', $template);
}
?>