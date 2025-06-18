![Laravel](https://img.shields.io/badge/Laravel-11.x-ff2d20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777bb4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-2.x-885630?style=for-the-badge&logo=composer&logoColor=white)
![NPM](https://img.shields.io/badge/NPM-9.x-CB3837?style=for-the-badge&logo=npm&logoColor=white)
![License: MIT](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)
[![Status](https://img.shields.io/badge/status-Em%20Desenvolvimento-yellow.svg)]()

---
# ?? MEI Manager API

Sistema de gerenciamento para **Microempreendedor Individual (MEI)**.  
Este reposit�rio cont�m o backend da aplica��o, desenvolvido em **Laravel 11**, com foco na organiza��o de notas fiscais, receitas, despesas, controle de pagamento da guia DAS e notifica��es automatizadas.

---

## ?? Tecnologias utilizadas

- ? [Laravel 11](https://laravel.com/docs/11.x)
- ? [PHP 8.2+](https://www.php.net/releases/8.2/)
- ? [React Native](https://reactnative.dev/)
- ? [MySQL 8.x](https://dev.mysql.com/doc/)
- ? [Composer](https://getcomposer.org/)
- ? [NPM](https://www.npmjs.com/)
- ? Artisan Commands
- ? Jobs agendados (via `schedule:run`)
- ? Estrutura RESTful com Responses padronizadas
- ? Suporte a autentica��o com Sanctum

---
## ?? Objetivos do Projeto

- Criar back-end da API utilizando laravel.
- Consumir API com aplicativo mobile desenvolvido em **React Native**

---

## ?? Funcionalidades atuais

### ?? Notas Fiscais (`invoices`)
- Cadastro de notas com n�mero, data, valor e link do XML/PDF
- Listagem das notas do usu�rio autenticado
- Upload/armazenamento opcional do documento

### ?? Receitas (`income`)
- Lan�amento de valores recebidos por data
- Utilizado para controle de faturamento anual

### ?? Despesas (`expense`)
- Lan�amento de valores de despesas
- Utilizado para controle e gest�o financeira

### ?? DAS (`das`)
- Cadastro de guias DAS pagas ou com vencimento
- Armazenamento de valor, compet�ncia e status

### ?? Notifica��es (`alerts`)
- Notifica��es autom�ticas sobre:
  - Vencimento do DAS (3 dias antes)
  - Limite de faturamento do MEI
- Rota para listar, filtrar por tipo/leitura, e marcar como lido
- Executadas via comando agendado `alerts:generate`

---

## ??? Em desenvolvimento
---
### ??? Roadmap

- [x]? An�lise dos requisitos
- [x]? Cria��o do diagrama do banco de dados
- [x]? Desenvolvimento do CRUD dos endpoints da API
- [x]? Filtro de alertas por leitura e tipo
- [x]? Padroniza��o de mensagens de resposta
- [ ]?? Upload de notas fiscais (PDF/XML)
- [ ]?? Dashboard com gr�ficos
- [ ]?? Aplicativo React Native
- [ ]?? Integra��o externa com sistemas de emiss�o NF-e

---

## ?? Instala��o

```bash
git clone https://github.com/seu-usuario/mei-manager-api.git
cd mei-manager-api

# Instale as depend�ncias
composer install

# Copie o .env e configure
cp .env.example .env
php artisan key:generate

# Configure banco de dados no .env
php artisan migrate

# Crie usu�rio de teste, se necess�rio
php artisan tinker
>>> \App\Models\User::factory()->create()

#Gerar token do usu�rio utilizando o endpoint
>>> /register

#Utilizar o token gerado no postman para demais requisi��es
>>> /login 
```

---

## ????? Autor

Projeto desenvolvido por **[Felipe Costa de Jesus]** � [LinkedIn](https://www.linkedin.com/in/Felipe-Cjesus)  
Contato: felipecosta.developer@gmail.com

---

**Licen�a:** Este projeto est� licenciado sob a licen�a MIT.