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
        
        // Lista de imagens que podem ser atualizadas
        $imageKeys = [
            'cover_image',
            'profile_image_main', 
            'profile_image',
            'content_image1',
            'content_image2',
            'logo',
            'top_bar_logo'
        ];
        
        foreach ($imageKeys as $key) {
            if (isset($_POST[$key]) && !empty($_POST[$key])) {
                $imageUrl = filter_var($_POST[$key], FILTER_VALIDATE_URL);
                
                if ($imageUrl) {
                    // Verificar se a URL é uma imagem válida
                    $headers = @get_headers($imageUrl, 1);
                    $contentType = isset($headers['Content-Type']) ? $headers['Content-Type'] : '';
                    
                    if (is_array($contentType)) {
                        $contentType = $contentType[0];
                    }
                    
                    // Verificar se é uma imagem
                    if (strpos($contentType, 'image/') === 0 || 
                        preg_match('/\.(jpg|jpeg|png|gif|svg|webp)$/i', $imageUrl)) {
                        
                        // Atualizar no banco de dados
                        $stmt = $pdo->prepare("
                            INSERT INTO site_images (image_key, image_path, alt_text) 
                            VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            image_path = VALUES(image_path), 
                            updated_at = CURRENT_TIMESTAMP
                        ");
                        
                        $altText = ucfirst(str_replace('_', ' ', $key));
                        $stmt->execute([$key, $imageUrl, $altText]);
                    }
                }
            }
        }
        
        $pdo->commit();
        
        // Regenerar o arquivo index.html com as novas imagens
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
    
    // Substituir preços nos botões
    if (isset($configs['monthly_price']) && isset($configs['monthly_old_price'])) {
        $template = preg_replace(
            '/De <span class="old-price">R\$ [\d,]+<\/span> por R\$ [\d,]+<\/span>/',
            'De <span class="old-price">R$ ' . number_format($configs['monthly_old_price'], 2, ',', '.') . '</span> por R$ ' . number_format($configs['monthly_price'], 2, ',', '.') . '</span>',
            $template,
            1
        );
    }
    
    if (isset($configs['lifetime_price']) && isset($configs['lifetime_old_price'])) {
        $template = preg_replace(
            '/De <span class="old-price">R\$ [\d,]+<\/span> por R\$ [\d,]+<\/span>/',
            'De <span class="old-price">R$ ' . number_format($configs['lifetime_old_price'], 2, ',', '.') . '</span> por R$ ' . number_format($configs['lifetime_price'], 2, ',', '.') . '</span>',
            $template,
            1
        );
    }
    
    // Substituir URLs de checkout
    $template = str_replace('https://pay.privacydade.co/privacy1mes/', 'checkout.php?plan=monthly', $template);
    $template = str_replace('https://pay.privacydade.co/privacyvitalicio/', 'checkout.php?plan=lifetime', $template);
    
    // Substituir caminhos das imagens com as URLs
    foreach ($images as $key => $url) {
        switch ($key) {
            case 'cover_image':
                $template = str_replace('src/images/capa.png', $url, $template);
                break;
            case 'profile_image':
                $template = str_replace('src/images/perfil1.png', $url, $template);
                break;
            case 'profile_image_main':
                $template = str_replace('https://i.pinimg.com/736x/54/07/97/540797a2f501dafa106499a8ef3208ed.jpg', $url, $template);
                break;
            case 'content_image1':
                $template = str_replace('src/images/imagem1.png', $url, $template);
                break;
            case 'content_image2':
                $template = str_replace('src/images/imagem2.png', $url, $template);
                break;
            case 'logo':
                $template = str_replace('src/images/privacy-logo.svg', $url, $template);
                break;
            case 'top_bar_logo':
                $template = str_replace('src/images/Untitled%20image%20(25).png', $url, $template);
                $template = str_replace('src/images/Untitled image (25).png', $url, $template);
                break;
        }
    }
    
    // Salvar o arquivo atualizado
    file_put_contents('../index.html', $template);
}
?>