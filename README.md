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
