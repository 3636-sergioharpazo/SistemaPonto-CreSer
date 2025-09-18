Sistema Ponto-CrêSer

Sistema de Ponto Digital – Controle de entradas e saídas de colaboradores com validação de IP, geolocalização e interface responsiva.

📋 Visão Geral

O SistemaPonto-CrêSer é uma aplicação web desenvolvida em PHP e JavaScript para registrar e gerenciar o ponto eletrônico de colaboradores.

Funcionalidades principais:

Registro de entrada, saída e intervalos de forma segura

Validação por IP cadastrado

Registro de geolocalização em tempo real

Consulta e edição de pontos por período

Relatórios exportáveis em PDF e Excel

Painel administrativo para gerenciamento de usuários e permissões

🛠 Tecnologias

Backend: PHP 7.4+ (PDO habilitado)

Frontend: HTML5, CSS3, JavaScript (Bootstrap 4+)

Banco de Dados: MySQL/MariaDB

Bibliotecas: SweetAlert, DataTables, Chart.js (opcional)

📦 Instalação
1️⃣ Requisitos

Servidor PHP 7.4+ com PDO habilitado

MySQL/MariaDB

FTP ou gerenciador de arquivos

Acesso ao phpMyAdmin ou equivalente

2️⃣ Download

Baixe o projeto diretamente do GitHub:
📥 Download ZIP

3️⃣ Upload

Extraia o ZIP localmente e envie todo o conteúdo para a pasta raiz do domínio ou subdomínio (public_html).

4️⃣ Configuração do Banco de Dados

Crie um banco de dados MySQL.

Crie um usuário com permissões totais para o banco.

Importe /database/banco.sql via phpMyAdmin.

5️⃣ Configuração do Sistema

Edite o arquivo config.php:

define('DB_HOST', 'localhost');
define('DB_NAME', 'nome_do_banco');
define('DB_USER', 'usuario_do_banco');
define('DB_PASS', 'senha_do_banco');

🌐 API / Webhooks

O sistema fornece endpoints RESTful para integração externa, permitindo cadastrar, consultar, editar e excluir registros de ponto.

URL Base:

https://creseradm.com/


Autenticação:

token (string) – token de segurança definido no sistema.

🔹 Parâmetros Comuns
Parâmetro	Tipo	Obrigatório	Descrição
token	string	Sim	Token de autenticação da API
funcionario	string	Sim (para cadastrar)	Nome do colaborador
tipo_registro	string	Sim (para cadastrar/editar)	Tipo de registro (entrada, saida, intervalo_almoco_entrada, intervalo_almoco_saida, saida_temporaria, retorno_saida_temporaria)
latitude	string	Sim (para cadastrar/editar)	Latitude do registro
longitude	string	Sim (para cadastrar/editar)	Longitude do registro
endereco	string	Sim (para cadastrar/editar)	Endereço do registro
id	int	Sim (para editar/excluir)	ID do ponto no banco
hora	string	Opcional (para editar)	Hora do registro (HH:MM:SS)
data_inicial	string	Opcional (para consulta)	Data inicial do filtro (YYYY-MM-DD)
data_final	string	Opcional (para consulta)	Data final do filtro (YYYY-MM-DD)


1️⃣ Cadastrar Ponto

Endpoint: POST ?acao=cadastrar&token=SEU_TOKEN
Headers: Content-Type: application/json

Payload JSON:

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

Endpoint: GET ?acao=consultar&token=SEU_TOKEN&funcionario=NOME&data_inicial=YYYY-MM-DD&data_final=YYYY-MM-DD

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
Headers: Content-Type: application/json

Payload JSON:

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
Headers: Content-Type: application/json

Payload JSON:

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

⚙️ Boas Práticas e Observações Técnicas

Todos os endpoints retornam JSON estruturado.

Use HTTPS obrigatório para proteger token e dados sensíveis.

O webhook reutiliza a lógica interna do sistema, incluindo cálculo de horas, intervalos e saldo.

Operações de edição e exclusão devem ser realizadas apenas por sistemas autorizados com token válido.

Campos obrigatórios devem ser validados antes do envio para evitar erros de banco de dados.

Tipos de registro válidos: entrada, saida, intervalo_almoco_entrada, intervalo_almoco_saida, saida_temporaria, retorno_saida_temporaria.
