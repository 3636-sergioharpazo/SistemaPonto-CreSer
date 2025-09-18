SistemaPonto-CrêSer

Sistema de Ponto Digital
Registro de entradas e saídas de colaboradores com controle por IP, geolocalização e interface responsiva.

📋 Sobre o Sistema

O SistemaPonto-CrêSer é uma aplicação web desenvolvida em PHP e JavaScript para registrar e gerenciar o ponto eletrônico de colaboradores.
Funcionalidades:

Registro de entrada e saída com validação por IP cadastrado

Armazenamento seguro no banco de dados

Consulta de registros por período

Painel administrativo para gerenciamento de usuários

Relatórios exportáveis em PDF e Excel

🛠 Tecnologias Utilizadas

Backend: PHP 7.4+ (compatível com hospedagens compartilhadas)

Frontend: HTML5, CSS3, JavaScript (com Bootstrap)

Banco de Dados: MySQL/MariaDB

Bibliotecas Extras: SweetAlert, DataTables, Chart.js (opcional)

📦 Instalação em Hospedagem Compartilhada
1️⃣ Requisitos

PHP 7.4 ou superior com PDO habilitado

MySQL/MariaDB

Acesso ao phpMyAdmin ou similar

FTP ou gerenciador de arquivos

2️⃣ Download e Upload

Baixe o projeto do GitHub:
📥 Download ZIP

Extraia o ZIP localmente.

Envie todo o conteúdo para public_html ou pasta raiz do domínio/subdomínio.

3️⃣ Configuração do Banco de Dados

Crie um banco MySQL via cPanel ou painel da hospedagem.

Crie um usuário e associe ao banco com todas as permissões.

Importe o arquivo /database/banco.sql pelo phpMyAdmin.

4️⃣ Configuração do Sistema

Abra o arquivo config.php.

Configure os parâmetros do banco de dados:

define('DB_HOST', 'localhost');
define('DB_NAME', 'nome_do_banco');
define('DB_USER', 'usuario_do_banco');
define('DB_PASS', 'senha_do_banco');


Verifique se PDO está habilitado no servidor PHP.

🌐 Webhooks da API do SistemaPonto-CrêSer

O sistema disponibiliza endpoints RESTful para integração externa via webhooks, permitindo cadastrar, consultar, editar e excluir registros de ponto.

🔒 Autenticação obrigatória: todos os endpoints exigem um token seguro definido no sistema.

URL Base:

https://creseradm.com/webhook_ponto.php


Parâmetro de autenticação:

token: token de segurança (ex: seuTokenSeguro123)

1️⃣ Cadastrar Ponto

Endpoint: POST ?acao=cadastrar&token=SEU_TOKEN
Headers: Content-Type: application/json
Corpo JSON:

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


Resposta JSON:

{
  "status": "sucesso",
  "msg": "Ponto registrado com sucesso"
}

2️⃣ Consultar Ponto

Endpoint: GET ?acao=consultar&token=SEU_TOKEN&funcionario=NOME&data_inicial=YYYY-MM-DD&data_final=YYYY-MM-DD

Exemplo cURL:

curl -X GET "https://creseradm.com/webhook_ponto.php?acao=consultar&token=seuTokenSeguro123&funcionario=João Silva&data_inicial=2025-09-01&data_final=2025-09-18"


Resposta JSON:

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
Headers: Content-Type: application/json
Corpo JSON:

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


Resposta JSON:

{
  "status": "sucesso",
  "msg": "Registro atualizado com sucesso"
}

4️⃣ Excluir Ponto

Endpoint: DELETE ?acao=excluir&token=SEU_TOKEN
Headers: Content-Type: application/json
Corpo JSON:

{
  "id": 123
}


Exemplo cURL:

curl -X DELETE "https://creseradm.com/webhook_ponto.php?acao=excluir&token=seuTokenSeguro123" \
-H "Content-Type: application/json" \
-d '{"id":123}'


Resposta JSON:

{
  "status": "sucesso",
  "msg": "Registro excluído com sucesso"
}

⚙️ Observações Técnicas

Todos os endpoints retornam JSON estruturado.

O webhook reutiliza toda a lógica interna de registro, incluindo cálculo de horas, intervalos e saldo.

HTTPS obrigatório para proteger credenciais e dados de geolocalização.

Edição e exclusão devem ser feitas apenas por sistemas autorizados com token válido.

Campos obrigatórios: funcionario, tipo_registro, latitude, longitude, endereco.

Tipos de registro válidos: entrada, saida, intervalo_almoco_entrada, intervalo_almoco_saida, saida_temporaria, retorno_saida_temporaria.
