<?php
require_once 'admin/config/database.php';

// Buscar configurações de preço
$stmt = $pdo->query("SELECT * FROM site_config WHERE config_key IN ('monthly_price', 'monthly_old_price', 'lifetime_price', 'lifetime_old_price', 'model_name', 'checkout_url_monthly', 'checkout_url_lifetime')");
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
            $redirectUrl = $checkoutUrl . '?' . http_build_query($_GET);
            header('Location: ' . $redirectUrl);
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
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .checkout-header {
            background: linear-gradient(135deg, #ff6b3d 0%, #ff8f5f 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .checkout-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .checkout-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .plan-info {
            background: rgba(255, 255, 255, 0.15);
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }
        
        .plan-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
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
            font-size: 1.8rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .discount-badge {
            background: rgba(255, 255, 255, 0.9);
            color: #ff6b3d;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 0.5rem;
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
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ff6b3d;
            box-shadow: 0 0 0 3px rgba(255, 107, 61, 0.1);
            transform: translateY(-1px);
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
            transition: all 0.3s ease;
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .btn-checkout::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: 0.5s;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 61, 0.3);
        }
        
        .btn-checkout:hover::before {
            left: 100%;
        }
        
        .btn-checkout:active {
            transform: translateY(0);
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
            background: linear-gradient(135deg, #fee 0%, #fdd 100%);
            color: #c33;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            border: 1px solid #fcc;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #ff6b3d;
            text-decoration: none;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .back-link:hover {
            opacity: 0.8;
            transform: translateX(-3px);
        }
        
        .features-list {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .features-list h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1rem;
            text-align: center;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #555;
        }
        
        .feature-item i {
            color: #ff6b3d;
            font-size: 0.8rem;
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
            
            .checkout-header {
                padding: 1.5rem;
            }
            
            .checkout-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-header">
            <h1><i class="fas fa-shield-alt"></i> Checkout Seguro</h1>
            <div class="plan-info">
                <div class="plan-name">
                    <i class="fas fa-crown"></i>
                    Plano <?= $planName ?>
                    <?php if ($plan === 'lifetime'): ?>
                        <span class="discount-badge">MELHOR OFERTA</span>
                    <?php endif; ?>
                </div>
                <div class="price-info">
                    <span class="old-price">R$ <?= number_format($oldPrice, 2, ',', '.') ?></span>
                    <span class="current-price">R$ <?= number_format($price, 2, ',', '.') ?></span>
                </div>
            </div>
        </div>
        
        <div class="checkout-body">
            <a href="index.html" class="back-link">
                <i class="fas fa-arrow-left"></i> Voltar ao site
            </a>
            
            <div class="features-list">
                <h3><i class="fas fa-star"></i> O que você vai receber:</h3>
                <div class="feature-item">
                    <i class="fas fa-check"></i>
                    <span>Acesso a todos conteúdos exclusivos</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check"></i>
                    <span>Chat ao vivo com a <?= htmlspecialchars($configs['model_name'] ?? 'modelo') ?></span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check"></i>
                    <span>Vídeo chamada exclusiva</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check"></i>
                    <span>Conteúdo sem censura</span>
                </div>
                <?php if ($plan === 'lifetime'): ?>
                <div class="feature-item">
                    <i class="fas fa-check"></i>
                    <span><strong>Acesso vitalício - pague uma vez, use para sempre!</strong></span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Nome Completo <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Digite seu nome completo">
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="seu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="phone">Telefone/WhatsApp</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="(11) 99999-9999">
                </div>
                
                <button type="submit" class="btn-checkout">
                    <i class="fas fa-credit-card"></i> Finalizar Compra - R$ <?= number_format($price, 2, ',', '.') ?>
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
                <div class="security-item">
                    <i class="fas fa-undo"></i>
                    <span>Garantia 30 dias</span>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Adicionar máscara para telefone
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = value;
        });
        
        // Animação de loading no botão
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.querySelector('.btn-checkout');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            btn.disabled = true;
        });
    </script>
</body>
</html>