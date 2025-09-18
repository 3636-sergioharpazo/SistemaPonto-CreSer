# SistemaPonto-CrêSer  
**Sistema de Ponto Digital**  
Registro de entradas e saídas de colaboradores com controle por IP, geolocalização e interface responsiva.

## 📋 Sobre o Sistema
O **SistemaPonto-CrêSer** é uma aplicação web desenvolvida em **PHP** e **JavaScript** para registrar e gerenciar o ponto eletrônico de colaboradores.  
Permite:
- Registro de entrada e saída com validação por IP cadastrado
- Armazenamento seguro no banco de dados
- Consulta de registros por período
- Painel administrativo para gerenciamento de usuários
- Relatórios exportáveis em PDF e Excel

## 🛠 Tecnologias Utilizadas
- **Backend:** PHP 7.4+ (compatível com hospedagens compartilhadas)
- **Frontend:** HTML5, CSS3, JavaScript (com Bootstrap)
- **Banco de Dados:** MySQL/MariaDB
- **Bibliotecas Extras:** SweetAlert, DataTables, Chart.js (opcional para gráficos)

## 📦 Instalação em Hospedagem Compartilhada

### 1️⃣ Requisitos
- PHP **7.4** ou superior
- MySQL/MariaDB
- Acesso ao **phpMyAdmin** ou similar
- FTP ou gerenciador de arquivos da hospedagem

### 2️⃣ Download e Upload dos Arquivos
1. Baixe o projeto diretamente do GitHub:  
   [📥 Download ZIP](https://github.com/3636-sergioharpazo/SistemaPonto-CreSer/archive/refs/heads/main.zip)
2. Extraia o arquivo `.zip` no seu computador.
3. Acesse seu **FTP** ou gerenciador de arquivos e envie todo o conteúdo para a pasta **public_html** (ou pasta raiz do domínio/subdomínio).

### 3️⃣ Configuração do Banco de Dados
1. No **cPanel** ou painel da sua hospedagem, crie um novo banco de dados MySQL.
2. Crie um usuário e associe ao banco com **todas as permissões**.
3. Acesse o **phpMyAdmin** e importe o arquivo `banco.sql` que está no diretório `/database` do projeto.

### 4️⃣ Configuração do Sistema
1. Localize o arquivo:
4️⃣ Configuração do Sistema

Localize o arquivo config.php e edite os dados do banco de dados:

define('DB_HOST', 'localhost');
define('DB_NAME', 'nome_do_banco');
define('DB_USER', 'usuario_do_banco');
define('DB_PASS', 'senha_do_banco');


Verifique se o seu servidor possui PDO habilitado para PHP.

🌐 Webhooks da API do SistemaPonto-CrêSer

O sistema disponibiliza endpoints para integração externa via webhooks, permitindo cadastrar, consultar, editar e excluir registros de ponto.

🔒 Todos os webhooks devem ser chamados com um token de autenticação para segurança.

🔹 URL Base
https://creseradm.com/webhook_ponto.php

🔹 Parâmetros de Autenticação

token: token de segurança definido no sistema (ex: seuTokenSeguro123)

1️⃣ Cadastrar Ponto

Endpoint: POST ?acao=cadastrar&token=SEU_TOKEN

Exemplo JSON:

{
  "funcionario": "João Silva",
  "tipo_registro": "entrada",
  "latitude": "-23.5505",
  "longitude": "-46.6333",
  "endereco": "Av. Paulista, São Paulo"
}


Exemplo cURL:

curl -X POST "https://creseradm.com/webhook_ponto.php?acao=cadastrar&token=seuTokenSeguro123" \
-H "Content-Type: application/json" \
-d '{
  "funcionario": "João Silva",
  "tipo_registro": "entrada",
  "latitude": "-23.5505",
  "longitude": "-46.6333",
  "endereco": "Av. Paulista, São Paulo"
}'


Resposta:

{
  "status": "sucesso",
  "msg": "Ponto registrado com sucesso"
}

2️⃣ Consultar Ponto

Endpoint: GET ?acao=consultar&token=SEU_TOKEN&funcionario=João Silva&data_inicial=2025-09-01&data_final=2025-09-18

Exemplo cURL:

curl -X GET "https://creseradm.com/webhook_ponto.php?acao=consultar&token=seuTokenSeguro123&funcionario=João Silva&data_inicial=2025-09-01&data_final=2025-09-18"


Resposta:

[
  {
    "id": 123,
    "nome": "João Silva",
    "tipo_registro": "entrada",
    "data_atual": "2025-09-18",
    "hora_atual": "08:00:00",
    "latitude": "-23.5505",
    "longitude": "-46.6333",
    "endereco": "Av. Paulista, São Paulo"
  }
]

3️⃣ Editar Ponto

Endpoint: PUT ?acao=editar&token=SEU_TOKEN

Exemplo JSON:

{
  "id": 123,
  "tipo_registro": "entrada",
  "hora": "08:30:00",
  "latitude": "-23.5510",
  "longitude": "-46.6320",
  "endereco": "Av. Paulista, São Paulo"
}


Exemplo cURL:

curl -X PUT "https://creseradm.com/webhook_ponto.php?acao=editar&token=seuTokenSeguro123" \
-H "Content-Type: application/json" \
-d '{
  "id": 123,
  "tipo_registro": "entrada",
  "hora": "08:30:00",
  "latitude": "-23.5510",
  "longitude": "-46.6320",
  "endereco": "Av. Paulista, São Paulo"
}'


Resposta:

{
  "status": "sucesso",
  "msg": "Registro atualizado com sucesso"
}

4️⃣ Excluir Ponto

Endpoint: DELETE ?acao=excluir&token=SEU_TOKEN

Exemplo JSON:

{
  "id": 123
}


Exemplo cURL:

curl -X DELETE "https://creseradm.com/webhook_ponto.php?acao=excluir&token=seuTokenSeguro123" \
-H "Content-Type: application/json" \
-d '{"id":123}'


Resposta:

{
  "status": "sucesso",
  "msg": "Registro excluído com sucesso"
}

🔹 Observações

Todos os webhooks retornam JSON.

Recomenda-se usar HTTPS para segurança.

O webhook reutiliza toda a lógica interna de registro de ponto, garantindo que cálculos de horas e saldo sejam atualizados automaticamente.

As operações de edição e exclusão devem ser feitas apenas por sistemas autorizados.
