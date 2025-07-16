<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Buscar configurações atuais
$stmt = $pdo->query("SELECT * FROM site_config");
$configs = [];
while ($row = $stmt->fetch()) {
    $configs[$row['config_key']] = $row['config_value'];
}

// Buscar imagens atuais
$stmt = $pdo->query("SELECT * FROM site_images");
$images = [];
while ($row = $stmt->fetch()) {
    $images[$row['image_key']] = $row;
}

// Buscar pedidos recentes
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
$recent_orders = $stmt->fetchAll();

// Estatísticas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
$total_orders = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as completed FROM orders WHERE status = 'completed'");
$completed_orders = $stmt->fetch()['completed'];

$stmt = $pdo->query("SELECT SUM(amount) as revenue FROM orders WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['revenue'] ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Admin - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #ff6b3d 0%, #ff8f5f 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card .icon {
            font-size: 2rem;
            color: #ff6b3d;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: #ff6b3d;
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .nav-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 1.5rem;
        }
        
        .nav-tab {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .nav-tab.active {
            color: #ff6b3d;
            border-bottom-color: #ff6b3d;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b3d;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff6b3d 0%, #ff8f5f 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 61, 0.3);
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            margin-top: 0.5rem;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th,
        .orders-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 1rem;
            }
            
            .header {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-shield-alt"></i> Privacy Admin</h1>
        <div class="user-info">
            <span>Bem-vindo, <?= htmlspecialchars($_SESSION['admin_user']) ?></span>
            <a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="value"><?= $total_orders ?></div>
                <div class="label">Total de Pedidos</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <div class="value"><?= $completed_orders ?></div>
                <div class="label">Pedidos Concluídos</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="value">R$ <?= number_format($total_revenue, 2, ',', '.') ?></div>
                <div class="label">Receita Total</div>
            </div>
        </div>
        
        <div class="main-grid">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cog"></i> Configurações do Site
                </div>
                <div class="card-body">
                    <div class="nav-tabs">
                        <button class="nav-tab active" onclick="showTab('model-info')">Modelo</button>
                        <button class="nav-tab" onclick="showTab('pricing')">Preços</button>
                        <button class="nav-tab" onclick="showTab('images')">Imagens</button>
                    </div>
                    
                    <div id="model-info" class="tab-content active">
                        <form action="update_config.php" method="POST">
                            <div class="form-group">
                                <label for="model_name">Nome da Modelo</label>
                                <input type="text" id="model_name" name="model_name" value="<?= htmlspecialchars($configs['model_name'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="model_username">Username</label>
                                <input type="text" id="model_username" name="model_username" value="<?= htmlspecialchars($configs['model_username'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="model_bio">Biografia</label>
                                <textarea id="model_bio" name="model_bio"><?= htmlspecialchars($configs['model_bio'] ?? '') ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Informações
                            </button>
                        </form>
                    </div>
                    
                    <div id="pricing" class="tab-content">
                        <form action="update_config.php" method="POST">
                            <div class="form-group">
                                <label for="monthly_price">Preço Mensal (R$)</label>
                                <input type="number" step="0.01" id="monthly_price" name="monthly_price" value="<?= $configs['monthly_price'] ?? '' ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="monthly_old_price">Preço Antigo Mensal (R$)</label>
                                <input type="number" step="0.01" id="monthly_old_price" name="monthly_old_price" value="<?= $configs['monthly_old_price'] ?? '' ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="lifetime_price">Preço Vitalício (R$)</label>
                                <input type="number" step="0.01" id="lifetime_price" name="lifetime_price" value="<?= $configs['lifetime_price'] ?? '' ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="lifetime_old_price">Preço Antigo Vitalício (R$)</label>
                                <input type="number" step="0.01" id="lifetime_old_price" name="lifetime_old_price" value="<?= $configs['lifetime_old_price'] ?? '' ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="checkout_url_monthly">URL Checkout Mensal</label>
                                <input type="url" id="checkout_url_monthly" name="checkout_url_monthly" value="<?= htmlspecialchars($configs['checkout_url_monthly'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="checkout_url_lifetime">URL Checkout Vitalício</label>
                                <input type="url" id="checkout_url_lifetime" name="checkout_url_lifetime" value="<?= htmlspecialchars($configs['checkout_url_lifetime'] ?? '') ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Preços
                            </button>
                        </form>
                    </div>
                    
                    <div id="images" class="tab-content">
                        <form action="upload_image.php" method="POST" enctype="multipart/form-data">
                            <?php foreach ($images as $key => $image): ?>
                                <div class="form-group">
                                    <label for="<?= $key ?>"><?= ucfirst(str_replace('_', ' ', $key)) ?></label>
                                    <input type="file" id="<?= $key ?>" name="<?= $key ?>" accept="image/*">
                                    <?php if ($image['image_path']): ?>
                                        <img src="../<?= htmlspecialchars($image['image_path']) ?>" alt="<?= htmlspecialchars($image['alt_text']) ?>" class="image-preview">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Atualizar Imagens
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-shopping-cart"></i> Pedidos Recentes
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <p>Nenhum pedido encontrado.</p>
                    <?php else: ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Plano</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($order['plan_type']) ?></td>
                                        <td>R$ <?= number_format($order['amount'], 2, ',', '.') ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $order['status'] ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
    </script>
</body>
</html>