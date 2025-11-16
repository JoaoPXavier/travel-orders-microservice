🚀 Travel Orders Microservice<br>
Microsserviço para gerenciamento de pedidos de viagem corporativa desenvolvido em Laravel com API REST completa.

📋 Sobre o Projeto<br>
Sistema completo para gerenciamento de pedidos de viagem corporativa desenvolvido como parte do teste técnico. Implementa todas as funcionalidades solicitadas com autenticação JWT, regras de negócio robustas, sistema de notificações e testes automatizados.

✅ Funcionalidades Implementadas<br>
🔐 Autenticação & Segurança<br>
Sistema de autenticação JWT

Registro e login de usuários

Proteção de rotas com middleware de autenticação

Cada usuário gerencia apenas seus próprios pedidos

📋 Gestão de Pedidos de Viagem<br>
Criar pedido de viagem - Inclui ID do pedido, nome do solicitante, destino, data de ida, data de volta e status

Consultar pedido - Retorna informações detalhadas baseadas no ID

Listar pedidos - Com opção de filtrar por status, período e destino

Atualizar pedido - Edição de pedidos pelos próprios usuários
<br>
🔄 Workflow de Aprovação<br>
Atualizar status - Para "aprovado" ou "cancelado" (apenas por outros usuários)

Cancelar após aprovação - Lógica de negócio que impede cancelamento de pedidos aprovados

Validações - O usuário que fez o pedido não pode alterar o status do mesmo<br>

🔔 Sistema de Notificações<br>
Notificação automática quando pedido é aprovado ou cancelado

Envio para o usuário que solicitou o pedido

Implementação com Laravel Notifications

Configuração para ambiente de desenvolvimento<br>

🎯 Filtros Avançados<br>
Filtragem por status - solicitado, aprovado, cancelado

Filtragem por período - pedidos com datas dentro de uma faixa específica

Filtragem por destino - busca por destino específico
<br>
🛠️ Tecnologias Utilizadas<br>
Laravel 11 (PHP 8.2) - Framework principal

MySQL 8.0 - Banco de dados relacional

Redis - Cache e sistema de filas

Docker & Docker Compose - Containerização e orquestração

JWT Authentication - Autenticação stateless por tokens

PHPUnit - Testes automatizados

Laravel Notifications - Sistema de notificações
<br>
🚀 Instalação e Configuração<br>
Pré-requisitos<br>
Docker

Docker Compose

Passo a Passo
Clone o repositório

bash
git clone <url-do-repositorio>
cd travel-orders-microservice
Execute com Docker
<br>
bash
docker-compose up -d
Instale as dependências
<br>
bash
docker-compose exec app composer install
Configure o ambiente
<br>
bash
# Copie o arquivo de ambiente
docker-compose exec app cp .env.example .env

# Gere a chave da aplicação
docker-compose exec app php artisan key:generate

# Gere a chave JWT
docker-compose exec app php artisan jwt:secret
Execute as migrações
<br>
bash
docker-compose exec app php artisan migrate
Execute os testes (verificação final)
<br>
bash
docker-compose exec app php artisan test<br>
⚙️ Configuração do Ambiente<br>
Variáveis de Ambiente (.env)
env
APP_NAME="Travel Orders Microservice"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=travel_orders
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

JWT_SECRET=...
MAIL_MAILER=log
Estrutura do Banco
users - Tabela de usuários com autenticação

travel_orders - Tabela de pedidos de viagem

notifications - Tabela de notificações do sistema

migrations - Controle de versão do schema do banco
<br>
🧪 Executando os Testes<br>
Todos os testes<br>
bash
docker-compose exec app php artisan test
<br>

Testes específicos
bash
# Testes de autenticação
docker-compose exec app php artisan test --filter=AuthTest

# Testes de pedidos de viagem
docker-compose exec app php artisan test --filter=TravelOrderTest

# Apenas testes unitários
docker-compose exec app php artisan test --testsuite=Unit

# Apenas testes de feature
docker-compose exec app php artisan test --testsuite=Feature
<br>
✅ Resultados Esperados<br>
Tests: 22 passed (102 assertions)<br>
Duration: ~75 seconds<br>
📡 API Endpoints<br>
🔐 Autenticação<br>
Método	Endpoint	Descrição	Autenticação<br>
POST	/api/register	Registrar usuário	✅ Público<br>
POST	/api/login	Login	✅ Público<br>
<br>
📋 Pedidos de Viagem<br>
Método	Endpoint	Descrição	Autenticação<br>
GET	/api/travel-orders	Listar pedidos 🔒 Token JWT<br>
POST	/api/travel-orders	Criar pedido	🔒 Token JWT<br>
GET	/api/travel-orders/{id}	Ver pedido	🔒 Token JWT<br>
PUT	/api/travel-orders/{id}	Atualizar pedido 🔒 Token JWT<br>
DELETE	/api/travel-orders/{id}	Excluir pedido🔒 Token JWT<br>
PATCH	/api/travel-orders/{id}/status	Atualizar status🔒 Token JWT<br>
🩺 Health Check<br>
Método	Endpoint	Descrição<br>
GET	/api/health	Status do serviço<br>
🔔 Sistema de Notificações<br>
Como Funciona<br>
O sistema de notificações é automaticamente acionado quando:

Um pedido é aprovado por outro usuário

Um pedido é cancelado por outro usuário
<br>
Como Verificar as Notificações<br>
Ambiente de Desenvolvimento (Log)<br>
bash
# Monitorar logs em tempo real
docker-compose exec app tail -f storage/logs/laravel.log
Exemplo de saída nos logs:
<br>
text
[2025-11-16 13:23:00] local.INFO: Travel order status notification sent successfully 
{"travel_order_id":1,"recipient_id":48,"status":"aprovado"}
Ambiente de Testes
As notificações são simuladas e validadas nos testes automatizados:
<br>
bash
docker-compose exec app php artisan test --filter=TravelOrderTest
No Banco de Dados
bash
# Ver notificações salvas<br>
docker-compose exec app php artisan tinker
\Illuminate\Notifications\DatabaseNotification::all();
Configuração de Email<br>
Desenvolvimento: MAIL_MAILER=log (notificações salvas em logs)

Produção: Configure com serviço de email real (SMTP, Mailgun, etc.)

🎯 Exemplos de Uso<br>
Registrar usuário
POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Novo Usuário",
    "email": "novo@empresa.com", 
    "password": "senha123",
    "password_confirmation": "senha123"
  }'
  <br>
Login

POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'
  <br>
Criar pedido de viagem
 POST http://localhost:8000/api/travel-orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <seu_token>" \
  -d '{
    "order_id": "TRV-2025-001",
    "applicant_name": "João Silva", 
    "destination": "São Paulo",
    "departure_date": "2025-02-01",
    "return_date": "2025-02-05",
    "status": "solicitado"
  }'
  <br>
Aprovar pedido (apenas admin/outro usuário)
 PATCH http://localhost:8000/api/travel-orders/1/status \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token_admin>" \
  -d '{
    "status": "aprovado"
  }'
  <br>
🖥️ Usando PowerShell (Windows)
Registrar usuário
powershell
$body = @{
    name = "Novo Usuário"
    email = "novo@empresa.com"
    password = "senha123" 
    password_confirmation = "senha123"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/register" -Method Post -Body $body -ContentType "application/json"
Login
powershell
$body = @{
    email = "user@example.com"
    password = "password"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/login" -Method Post -Body $body -ContentType "application/json"
$token = $response.access_token
Write-Host "Token: $token"
Criar pedido de viagem
powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
}

$body = @{
    order_id = "TRV-2025-001"
    applicant_name = "João Silva"
    destination = "São Paulo"
    departure_date = "2025-02-01"
    return_date = "2025-02-05"
    status = "solicitado"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/travel-orders" -Method Post -Body $body -Headers $headers
Aprovar pedido
powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
}

$body = @{
    status = "aprovado"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/travel-orders/1/status" -Method Patch -Body $body -Headers $headers
<br>
🔒 Regras de Negócio Implementadas<br>
Validações<br>
✅ Data de ida não pode ser anterior a hoje

✅ Data de volta deve ser após data de ida

✅ Order_ID deve ser único no sistema

✅ Status segue workflow: solicitado → aprovado/cancelado
<br>
Segurança<br>
✅ Usuário não pode aprovar/cancelar próprio pedido

✅ Cada usuário gerencia apenas seus próprios pedidos

✅ Autenticação JWT obrigatória para endpoints protegidos

✅ Autorização granular por recurso
<br>
Notificações<br>
✅ Sistema de notificações por email e database

✅ Notificação automática ao alterar status

✅ Filas separadas para melhor performance

✅ Simulação em ambiente de testes
<br>
🐳 Docker<br>
Serviços<br>
Serviço	Porta	Descrição<br>
app	8000	Aplicação Laravel + Nginx
mysql	3306	Banco de dados MySQL
redis	6379	Cache Redis
Comandos Úteis<br>
bash
# Ver status dos containers<br>
docker-compose ps

# Parar aplicação
docker-compose down

# Ver logs da aplicação
docker-compose logs app

# Ver logs do MySQL
docker-compose logs mysql

# Acessar container da aplicação
docker-compose exec app bash

# Reiniciar serviços
docker-compose restart<br>
📚 Documentação Adicional<br>
Postman Collection<br>
Importe o arquivo docs/TravelOrdersAPI.postman_collection.json no Postman para testar todos os endpoints da API com exemplos pré-configurados.
<br>
Estrutura do Projeto
<br>
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   └── TravelOrderController.php
│   └── Requests/
├── Models/
│   ├── User.php
│   └── TravelOrder.php
├── Notifications/
│   └── TravelOrderStatusChanged.php
├── Events/
├── Listeners/
tests/
├── Feature/
│   ├── AuthTest.php
│   └── TravelOrderTest.php
<br>
👥 Usuários de Teste<br>
Para testar a aplicação, você pode criar usuários manualmente ou usar os seguintes exemplos:
<br>
Criar usuários via Tinker <br>
bash
docker-compose exec app php artisan tinker <br>

# Usuário comum
App\Models\User::factory()->create([
    'name' => 'Usuário Teste',
    'email' => 'user@example.com',
    'password' => bcrypt('password')
]);

# Usuário administrador
App\Models\User::factory()->create([
    'name' => 'Admin Teste',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'is_admin' => true
]);
<br>
🧪 Cobertura de Testes
22 testes passando com sucesso
<br>
102 assertions válidas
<br>
100% dos requisitos testados e validados
<br>
Testes cobrem autenticação, CRUD, regras de negócio e notificações
<br>
Resultados dos Testes
text
Tests:    22 passed (102 assertions)
Duration: 75.98s<br>
🚨 Solução de Problemas<br>
Erro de Conexão com Banco
bash
# Recriar containers
docker-compose down
docker-compose up -d --build

# Executar migrações novamente
docker-compose exec app php artisan migrate:fresh
Token Expirado
bash
# Fazer login novamente
POST http://localhost:8000/api/login ...
Erro de Porta em Uso
bash
# Parar serviços e reiniciar
docker-compose down
docker-compose up -d
📞 Suporte<br>
Em caso de dúvidas durante a execução do projeto, consulte:

Este arquivo README.md

A collection do Postman em docs/

Os logs da aplicação via docker-compose logs app
<br>
🧪 Status: 22 testes passando | 102 assertions válidas