<?php
require_once 'admin/config/database.php';

// Buscar configurações de preço
$stmt = $pdo->query("SELECT * FROM site_config WHERE config_key IN ('monthly_price', 'monthly_old_price', 'lifetime_price', 'lifetime_old_price', 'model_name')");
$configs = [];
while ($row = $stmt->fetch()) {
    $configs[$row['config_key']] = $row['config_value'];
}

$plan = $_GET['plan'] ?? 'monthly';
$price = $plan === 'lifetime' ? $configs['lifetime_price'] : $configs['monthly_price'];
$oldPrice = $plan === 'lifetime' ? $configs['lifetime_old_price'] : $configs['monthly_old_price'];
$planName = $plan === 'lifetime' ? 'Vitalício' : 'Mensal';

if ($_POST) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    if ($name && $email) {
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, plan_type, amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$name, $email, $phone, $planName, $price]);
            
            // Redirecionar para o gateway de pagamento real
            $checkoutUrl = $plan === 'lifetime' ? $configs['checkout_url_lifetime'] : $configs['checkout_url_monthly'];
            header('Location: ' . $checkoutUrl . '?' . http_build_query($_GET));
            exit;
        } catch (Exception $e) {
            $error = 'Erro ao processar pedido. Tente novamente.';
        }
    } else {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?= htmlspecialchars($configs['model_name'] ?? 'Privacy') ?></title>
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
            background: linear-gradient(135deg, #ff6b3d 0%, #ff8f5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .checkout-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
        }
        
        .checkout-header {
            background: linear-gradient(135deg, #ff6b3d 0%, #ff8f5f 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .checkout-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .plan-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .plan-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .price-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .old-price {
            text-decoration: line-through;
            opacity: 0.7;
            font-size: 0.9rem;
        }
        
        .current-price {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .checkout-body {
            padding: 2rem;
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
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ff6b3d;
        }
        
        .required {
            color: #ff6b3d;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #ff6b3d 0%, #ff8f5f 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            margin-top: 1rem;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
        }
        
        .security-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .security-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .security-item i {
            color: #ff6b3d;
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #ff6b3d;
            text-decoration: none;
            margin-bottom: 1rem;
            transition: opacity 0.3s;
        }
        
        .back-link:hover {
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                margin: 0;
                border-radius: 0;
                min-height: 100vh;
            }
            
            .security-info {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-header">
            <h1><i class="fas fa-shield-alt"></i> Checkout Seguro</h1>
            <div class="plan-info">
                <div class="plan-name">Plano <?= $planName ?></div>
                <div class="price-info">
                    <span class="old-price">R$ <?= number_format($oldPrice, 2, ',', '.') ?></span>
                    <span class="current-price">R$ <?= number_format($price, 2, ',', '.') ?></span>
                </div>
            </div>
        </div>
        
        <div class="checkout-body">
            <a href="index.html" class="back-link">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            
            <?php if (isset($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Nome Completo <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Telefone/WhatsApp</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                
                <button type="submit" class="btn-checkout">
                    <i class="fas fa-credit-card"></i> Finalizar Compra
                </button>
            </form>
            
            <div class="security-info">
                <div class="security-item">
                    <i class="fas fa-lock"></i>
                    <span>Pagamento 100% Seguro</span>
                </div>
                <div class="security-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Dados Protegidos</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>