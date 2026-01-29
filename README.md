# 💰 Orçamento Familiar

Sistema de gestão de orçamento familiar desenvolvido com Laravel 12 e Filament 5.

## 📋 Sobre o Projeto

Aplicação para controle financeiro familiar que permite:
- ✅ Gerenciamento de múltiplos membros da família
- ✅ Registro de receitas e despesas
- ✅ Divisão de despesas compartilhadas entre membros
- ✅ Categorização de transações
- ✅ Contas bancárias múltiplas
- ✅ Transações recorrentes
- ✅ Orçamentos por categoria
- ✅ Dashboard com estatísticas e gráficos

## 🛠️ Tecnologias

- **Backend**: Laravel 12
- **Admin Panel**: Filament 5
- **Database**: SQLite (desenvolvimento) / PostgreSQL (produção)
- **Frontend**: Livewire, Alpine.js, Tailwind CSS 4
- **PHP**: 8.2+

## 📦 Pré-requisitos

### Desenvolvimento Local (Windows)

- [Laravel Herd](https://herd.laravel.com/) - Ambiente PHP/Laravel completo
- [Node.js](https://nodejs.org/) (v18+) - Para compilar assets
- [Composer](https://getcomposer.org/) - Gerenciador de dependências PHP (já incluído no Herd)

## 🚀 Instalação

### 1️⃣ Clone o Repositório

```bash
git clone <url-do-repositorio>
cd orcamento
```

### 2️⃣ Instale as Dependências

```bash
# Dependências PHP
composer install

# Dependências JavaScript
npm install
```

### 3️⃣ Configure o Ambiente

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Gere a chave da aplicação
php artisan key:generate
```

### 4️⃣ Configure o Banco de Dados

O arquivo `.env` já está configurado para usar SQLite em desenvolvimento:

```env
DB_CONNECTION=sqlite
```

O arquivo `database/database.sqlite` será criado automaticamente.

### 5️⃣ Execute as Migrations e Seeders

```bash
# Cria todas as tabelas do banco de dados
php artisan migrate

# Popula categorias padrão (Alimentação, Moradia, Transporte, etc.)
php artisan db:seed --class=CategorySeeder
```

### 6️⃣ Compile os Assets

```bash
# Desenvolvimento (com hot reload)
npm run dev

# Produção (otimizado)
npm run build
```

### 7️⃣ Acesse a Aplicação

Se estiver usando o **Laravel Herd**:
- A aplicação estará disponível automaticamente em: `https://orcamento.test`

Se estiver usando o servidor embutido do PHP:
```bash
php artisan serve
```
- Acesse: `http://localhost:8000`

## 🔧 Comandos Úteis

### Desenvolvimento

```bash
# Rodar servidor de desenvolvimento com queue, logs e vite
composer dev

# Apenas o servidor Laravel
php artisan serve

# Apenas o Vite (assets)
npm run dev

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Banco de Dados

```bash
# Criar nova migration
php artisan make:migration create_table_name

# Executar migrations
php artisan migrate

# Reverter última migration
php artisan migrate:rollback

# Resetar banco e executar seeders
php artisan migrate:fresh --seed
```

### Filament

```bash
# Criar novo Resource
php artisan make:filament-resource ModelName

# Criar usuário admin
php artisan make:filament-user
```

## 📁 Estrutura do Projeto

```
orcamento/
├── app/
│   ├── Filament/          # Recursos do Filament (Admin Panel)
│   ├── Http/
│   │   └── Controllers/   # Controllers da aplicação
│   ├── Models/            # Models Eloquent
│   └── Providers/
├── database/
│   ├── migrations/        # Migrations do banco
│   ├── seeders/           # Seeders
│   └── database.sqlite    # Banco SQLite (dev)
├── public/                # Assets públicos
├── resources/
│   ├── css/              # Estilos
│   ├── js/               # JavaScript
│   └── views/            # Views Blade
└── routes/               # Rotas da aplicação
```

## 🗄️ Estrutura do Banco de Dados

### Principais Tabelas

- **families** - Famílias/grupos
- **users** - Usuários (membros da família)
- **categories** - Categorias de transações
- **subcategories** - Subcategorias
- **transactions** - Receitas e despesas
- **transaction_splits** - Divisão de despesas compartilhadas
- **accounts** - Contas bancárias
- **budgets** - Orçamentos por categoria
- **recurring_transactions** - Transações recorrentes
- **family_invites** - Convites para família

## 🌐 Deploy (Produção)

### Configuração do Banco de Dados

Edite o `.env` para usar PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=seu-host.com
DB_PORT=5432
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha_segura
```

### Passos para Deploy

```bash
# 1. Instale dependências (sem dev)
composer install --optimize-autoloader --no-dev

# 2. Compile assets para produção
npm run build

# 3. Configure o ambiente
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Execute migrations
php artisan migrate --force

# 5. Popule categorias padrão
php artisan db:seed --class=CategorySeeder --force
```

### Variáveis de Ambiente Importantes

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Configure email para produção
MAIL_MAILER=smtp
MAIL_HOST=seu-smtp.com
MAIL_PORT=587
MAIL_USERNAME=seu-email
MAIL_PASSWORD=sua-senha
MAIL_FROM_ADDRESS=noreply@seu-dominio.com
```

## 👥 Primeiro Acesso

1. Acesse a aplicação
2. Clique em "Registrar"
3. Crie sua conta
4. Crie sua família
5. Convide outros membros (opcional)
6. Comece a registrar suas transações!

## 🐛 Troubleshooting

### Erro: "no such table: families"
```bash
php artisan migrate
php artisan db:seed --class=CategorySeeder
```

### Erro: "Failed to listen on port 8000"
- Se usar Herd, acesse via `https://orcamento.test`
- Ou use outra porta: `php artisan serve --port=8080`

### Assets não carregam
```bash
npm run build
php artisan view:clear
```

### Permissões (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 📝 Licença

Este projeto é privado e proprietário.

## 👨‍💻 Desenvolvedor

Desenvolvido para gestão financeira familiar.
