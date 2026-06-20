# Convictos ADEL — CONVICTOS UM 2027

Site dinâmico da conferência de jovens **Convictos UM 2027** (Juventude Mais · Assembleia de Deus · Ministério Madureira · Luziânia), construído em **Laravel 13** com painel administrativo **Filament**, banco de dados, **loja de produtos** e **inscrições**.

## Tecnologias

- **Laravel 13** (PHP 8.3+)
- **Filament 5** — painel administrativo (`/admin`)
- **Blade + CSS** — site público (mantém a identidade visual original)
- **SQLite** por padrão (troca simples para MySQL)
- **MercadoPago** — pagamento online da loja (via API, opcional)

## Funcionalidades

### Site público
- Landing page da conferência (`/`)
- Formulário de **inscrição** que grava no banco (`#inscricao`)
- **Loja** com filtro por categoria (`/loja`)
- Página de **produto** com seleção de tamanho e quantidade (`/loja/{slug}`)
- **Carrinho** de compras em sessão (`/carrinho`)
- **Checkout** com criação de pedido + pagamento MercadoPago (`/checkout`)

### Painel administrativo (`/admin`)
- **Inscrições** — gerenciar interessados (status, busca, filtros)
- **Produtos** — CRUD completo com upload de imagem, tamanhos, estoque e destaque
- **Pedidos** — acompanhar vendas, itens, status e pagamento

## Como rodar (desenvolvimento)

```bash
composer install
npm install              # opcional (assets do site usam CSS estático)
cp .env.example .env     # se ainda não existir
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Acesse:
- Site: http://localhost:8000
- Admin: http://localhost:8000/admin

### Acesso ao admin (criado pelo seeder)

- **E-mail:** `admin@convictos.com.br`
- **Senha:** `convictos2027`

> Altere a senha em produção (no painel ou recriando o seeder).

## Banco de dados

Por padrão usa **SQLite** (`database/database.sqlite`), sem configuração extra.

Para usar **MySQL** (ex.: Laragon), edite o `.env`:

```env
# DB_CONNECTION=sqlite
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=convictos_adel
DB_USERNAME=root
DB_PASSWORD=
```

Crie o banco `convictos_adel` e rode `php artisan migrate:fresh --seed`.

## Pagamento (MercadoPago)

Sem credenciais, o checkout registra o pedido como **manual** (a equipe combina o pagamento).
Para ativar o pagamento online, preencha no `.env`:

```env
MERCADOPAGO_ACCESS_TOKEN=seu_access_token
MERCADOPAGO_PUBLIC_KEY=sua_public_key
MERCADOPAGO_SANDBOX=true
```

As credenciais ficam em https://www.mercadopago.com.br/developers/panel/app.
O webhook de confirmação é `POST /webhooks/mercadopago`.

## E-mails

O sistema envia e-mails automáticos em dois momentos:

| Evento | Para o usuário | Para o admin |
| ------ | -------------- | ------------ |
| Nova **inscrição** | Confirmação de inscrição | Alerta de novo inscrito |
| Novo **pedido** | Confirmação do pedido (no pedido manual ou quando o pagamento é aprovado) | Alerta de novo pedido |

Em desenvolvimento, `MAIL_MAILER=log` grava os e-mails em `storage/logs/laravel.log` (não envia de verdade).
Para enviar e-mails reais, configure o SMTP no `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.seuprovedor.com
MAIL_PORT=587
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_FROM_ADDRESS="nao-responda@convictos.com.br"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_ADMIN_ADDRESS="contato@convictos.com.br"   # recebe os avisos de inscrição e pedido
```

> Gmail: use uma **Senha de app** (com verificação em duas etapas), host `smtp.gmail.com`, porta `587`.

## Estrutura principal

```
app/
  Filament/Resources/      # Painel admin (Inscrições, Produtos, Pedidos)
  Http/Controllers/        # Home, Store, Cart, Checkout, Inscricao, Webhook
  Mail/                    # E-mails de inscrição e pedido (cliente + admin)
  Models/                  # Inscricao, Product, Order, OrderItem
  Services/                # Cart (sessão), MercadoPagoService, OrderNotifier
database/
  migrations/ seeders/     # Schema + produtos/admin iniciais
resources/views/           # Blade (layout, home, loja, carrinho, checkout)
public/
  assets/                  # Logos e imagens originais
  css/ js/                 # Estilos e scripts do site
legacy/                    # HTML estático original (referência)
```
