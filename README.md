![Laravel](https://img.shields.io/badge/Laravel-11.x-ff2d20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777bb4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-2.x-885630?style=for-the-badge&logo=composer&logoColor=white)
![NPM](https://img.shields.io/badge/NPM-9.x-CB3837?style=for-the-badge&logo=npm&logoColor=white)
![License: MIT](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)
[![Status](https://img.shields.io/badge/status-Em%20Desenvolvimento-yellow.svg)]()

---
# 📊 MEI Manager API

Sistema de gerenciamento para **Microempreendedor Individual (MEI)**.  
Este repositório contém o backend da aplicação, desenvolvido em **Laravel 11**, com foco na organização de notas fiscais, receitas, despesas, controle de pagamento da guia DAS e notificações automatizadas.

---

## 🚀 Tecnologias

- [Laravel 11](https://laravel.com/docs/11.x)
- [PHP 8.2+](https://www.php.net/releases/8.2/)
- [React Native](https://reactnative.dev/)
- [MySQL 8.x](https://dev.mysql.com/doc/)
- [Composer](https://getcomposer.org/)
- [NPM](https://www.npmjs.com/)
- Artisan Commands
- Jobs agendados (via `schedule:run`)
- Estrutura RESTful com Responses padronizadas
- Suporte a autenticação com Sanctum

---
## 📚 Objetivos do Projeto

- Criar back-end da API utilizando laravel.
- Consumir API com aplicativo mobile desenvolvido em **React Native**

---

## 📦 Funcionalidades atuais

### 🔹 Notas Fiscais (`invoices`)
- Cadastro de notas com número, data, valor e link do XML/PDF
- Listagem das notas do usuário autenticado
- Upload/armazenamento opcional do documento

### 🔹 Receitas (`income`)
- Lançamento de valores recebidos por data
- Utilizado para controle de faturamento anual

### 🔹 Despesas (`expense`)
- Lançamento de valores de despesas
- Utilizado para controle e gestão financeira

### 🔹 DAS (`das`)
- Cadastro de guias DAS pagas ou com vencimento
- Armazenamento de valor, competência e status

### 🔹 Notificações (`alerts`)
- Notificações automáticas sobre:
  - Vencimento do DAS (3 dias antes)
  - Limite de faturamento do MEI
- Rota para listar, filtrar por tipo/leitura, e marcar como lido
- Executadas via comando agendado `alerts:generate`

---

## 🛠️ Em desenvolvimento
---
## 🗺️ Roadmap

- ✅ Análise dos requisitos
- ✅ Criação do diagrama do banco de dados
- ✅ Desenvolvimento do CRUD dos endpoints da API
- ✅ Filtro de alertas por leitura e tipo
- ✅ Padronização de mensagens de resposta
- 🔜 Upload de notas fiscais (PDF/XML)
- 🔜 Dashboard com gráficos
- 🔜 Aplicativo React Native
- 🔜 Integração externa com sistemas de emissão NF-e

---

## ⚙️ Instalação

```bash
git clone https://github.com/seu-usuario/mei-manager-api.git
cd mei-manager-api

# Instale as dependências
composer install

# Copie o .env e configure
cp .env.example .env
php artisan key:generate

# Configure banco de dados no .env
php artisan migrate

# Crie usuário de teste, se necessário
php artisan tinker
>>> \App\Models\User::factory()->create()

#Gerar token do usuário utilizando o endpoint
>>> BASE_URL/api/register

#Utilizar o token gerado no postman para demais requisições
>>> BASE_URL/api/login 

# BASE_URL -> configurar URL e portas para localhost
```

---

## 🧑‍💻 Autor

Projeto desenvolvido por **[Felipe Costa de Jesus]** — [LinkedIn](https://www.linkedin.com/in/Felipe-Cjesus)  
Contato: felipecosta.developer@gmail.com

---

**Licença:** Este projeto está licenciado sob a licença MIT.