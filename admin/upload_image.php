<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_POST && $_FILES) {
    try {
        $uploadDir = '../src/images/';
        
        foreach ($_FILES as $key => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = $key . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $filename;
                
                // Verificar se é uma imagem válida
                $imageInfo = getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    continue;
                }
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    // Atualizar no banco de dados
                    $relativePath = 'src/images/' . $filename;
                    
                    // Para profile_image_main, usar URL completa se necessário
                    if ($key === 'profile_image_main') {
                        $relativePath = $uploadPath; // ou construir URL completa se necessário
                    }
                    
                    $stmt = $pdo->prepare("UPDATE site_images SET image_path = ? WHERE image_key = ?");
                    $stmt->execute([$relativePath, $key]);
                }
            }
        }
        
        // Regenerar o arquivo index.html
        include 'update_config.php';
        generateIndexFile();
        
        header('Location: dashboard.php?success=1');
    } catch (Exception $e) {
        header('Location: dashboard.php?error=1');
    }
}
?>