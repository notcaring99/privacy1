# Painel Administrativo Privacy

## Instalação

1. **Configurar Banco de Dados:**
   - Importe o arquivo `config/setup.sql` no seu MySQL
   - Ajuste as configurações de conexão em `config/database.php`

2. **Configurar Permissões:**
   - Certifique-se que a pasta `src/images/` tenha permissão de escrita
   - Configure o servidor web para executar PHP

3. **Acesso Inicial:**
   - URL: `http://seudominio.com/admin/login.php`
   - Usuário: `admin`
   - Senha: `admin123`

## Funcionalidades

### Dashboard Principal
- Estatísticas de pedidos e receita
- Configuração de informações da modelo
- Gerenciamento de preços
- Upload de imagens

### Gerenciamento de Conteúdo
- **Informações da Modelo:** Nome, username, biografia
- **Preços:** Valores mensais e vitalícios (atual e promocional)
- **Imagens:** Upload de todas as imagens do site
- **URLs de Checkout:** Links para os gateways de pagamento

### Sistema de Pedidos
- Captura de dados dos clientes
- Controle de status dos pedidos
- Relatórios de vendas

## Estrutura de Arquivos

```
admin/
├── config/
│   ├── database.php      # Configuração do banco
│   └── setup.sql         # Script de instalação
├── login.php             # Página de login
├── dashboard.php         # Dashboard principal
├── update_config.php     # Atualização de configurações
├── upload_image.php      # Upload de imagens
├── orders.php           # Gerenciamento de pedidos
└── logout.php           # Logout

checkout.php             # Página de checkout
```

## Segurança

- Senhas criptografadas com bcrypt
- Sessões seguras
- Validação de uploads de imagem
- Proteção contra SQL injection

## Personalização

O sistema automaticamente atualiza o arquivo `index.html` quando as configurações são alteradas, mantendo o site sempre sincronizado com as configurações do painel.