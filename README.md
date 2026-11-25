# 📊 MEI Manager API

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12" />
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8" />
  <img src="https://img.shields.io/badge/Sanctum-Auth-F05340?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Sanctum" />
  <img src="https://img.shields.io/badge/Tests-Passing-4c1?style=for-the-badge&logo=githubactions&logoColor=white" alt="Tests Passing" />
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge" alt="MIT License" />
</p>

---

## 📖 Sobre o Projeto

A **MEI Manager API** é o backend RESTful do ecossistema MEI App, projetado para simplificar e automatizar a gestão operacional, financeira e tributária de **Microempreendedores Individuais (MEI)**. 

Além de atender o empreendedor individual, a API conta com arquitetura **Multiempresas**, permitindo que contadores ou empreendedores com múltiplos cadastros gerenciem vários CNPJs MEI de forma totalmente isolada em uma única conta.

---

## 🚀 Principais Funcionalidades

### 🏢 Suporte Multiempresas
- Cadastro e gerenciamento de múltiplos CNPJs por usuário/contador.
- Isolamento estrito de todos os lançamentos financeiros, faturas, guias e alertas por empresa (`enterprise_id`).
- Proteção contra acessos indevidos (anti-IDOR) validando a titularidade da empresa em cada requisição.

### 💰 Gestão Financeira (Receitas & Despesas)
- **Receitas (`/api/incomes`)**: Controle de valores recebidos e pendentes, categorização por data, empresa e controle do faturamento anual.
- **Despesas (`/api/expenses`)**: Registro de despesas manuais ou vinculadas a documentos, com upload e armazenamento de comprovantes (PDF/XML/imagens).

### 📄 Notas Fiscais (`/api/invoices`)
- Registro de notas fiscais emitidas com número, competência, valor e descrição.
- Upload seguro de arquivos anexos (PDF e XML da NF-e) com geração de link público.

### 📑 Controle de Guias DAS (`/api/das-payments`)
- Gestão do Documento de Arrecadação do Simples Nacional (DAS).
- Acompanhamento de competências, datas de vencimento, pagamentos e status (`pending`, `paid`, `overdue`, `exempt`).

### 🔔 Motor de Alertas Fiscais Automatizado (`/api/alerts`)
- Comando Artisan inteligente (`alerts:generate`) com regras de negócio adaptadas para multiempresas:
  - **Aviso de Vencimento de DAS**: Alerta com 3 dias de antecedência do vencimento.
  - **Aviso de DAS Vencida**: Notificação e atualização automática do status da guia para `overdue`.
  - **Monitoramento do Limite Anual MEI (R$ 81.000,00)**: Cálculo de faturamento individual por **CNPJ (empresa)**, disparando alertas de 80%, 100% e limite ultrapassado sem gerar falsos positivos para contadores com várias empresas.
- Leitura e marcação de alertas como visualizados (`/api/alerts/{id}/read`).

### 📊 Relatórios Consolidados (`/api/reports/monthly`)
- Fechamento mensal com agregação de:
  - Faturamento bruto e notas fiscais emitidas
  - Receitas recebidas e pendentes
  - Despesas realizadas
  - Guias DAS pagas
  - **Balanço líquido mensal** (`Receitas - Despesas - DAS`)
  - Suporte a filtro por empresa (`enterprise_id`), ano e mês.

---

## 🛠️ Tecnologias e Padrões

- **Framework**: [Laravel 12](https://laravel.com/)
- **Linguagem**: [PHP 8.2+](https://www.php.net/)
- **Autenticação**: [Laravel Sanctum](https://laravel.com/docs/sanctum) (Tokens Bearer)
- **Persistência**: [MySQL 8.x](https://www.mysql.com/) / [SQLite](https://www.sqlite.org/) (em memória nos testes)
- **Segurança**:
  - Respostas padronizadas via helper centralizado `App\Helpers\ApiResponse`.
  - Políticas de Autorização (Eloquent Policies) para todos os recursos.
  - Proteção contra IDOR via regras de validação `Rule::exists('enterprises', 'id')->where('user_id', Auth::id())`.
  - Rate Limiting (`throttle:10,1` no login, `throttle:5,1` no registro e `throttle:100,1` nas rotas autenticadas).

---

## 📋 Endpoints da API

Todas as rotas privadas exigem o cabeçalho `Authorization: Bearer <seu_token>`.

### 🔑 Autenticação
| Método | Endpoint | Descrição |
|---|---|---|
| `POST` | `/api/register` | Cadastro de novo usuário |
| `POST` | `/api/login` | Autenticação e geração de token Sanctum |
| `POST` | `/api/logout` | Revogação do token atual |

### 🏢 Empresas (`/api/enterprises`)
| Método | Endpoint | Parâmetros / Filtros | Descrição |
|---|---|---|---|
| `GET` | `/api/enterprises` | - | Lista todas as empresas do usuário |
| `POST` | `/api/enterprises` | `company_name`, `company_id`, etc. | Cadastra uma nova empresa MEI |
| `GET` | `/api/enterprises/{id}` | - | Exibe detalhes de uma empresa |
| `PUT` | `/api/enterprises/{id}` | Dados parciais | Atualiza os dados da empresa |
| `DELETE` | `/api/enterprises/{id}` | - | Remove a empresa e dados vinculados (cascade) |

### 💰 Receitas (`/api/incomes`)
| Método | Endpoint | Filtros Query | Descrição |
|---|---|---|---|
| `GET` | `/api/incomes` | `enterprise_id`, `year`, `date`, `description`, `page` | Lista receitas com paginação e filtros |
| `POST` | `/api/incomes` | `enterprise_id`, `description`, `amount`, `date`, `received` | Registra nova receita |
| `GET` | `/api/incomes/{id}` | - | Exibe dados de uma receita |
| `PUT` | `/api/incomes/{id}` | Dados parciais | Atualiza uma receita |
| `DELETE` | `/api/incomes/{id}` | - | Exclui uma receita |

### 💸 Despesas (`/api/expenses`)
| Método | Endpoint | Filtros Query / Payload | Descrição |
|---|---|---|---|
| `GET` | `/api/expenses` | `enterprise_id`, `year`, `date`, `description`, `page` | Lista despesas paginadas |
| `POST` | `/api/expenses` | `enterprise_id`, `description`, `amount`, `date`, `type`, `file` | Registra despesa com comprovante opcional |
| `GET` | `/api/expenses/{id}` | - | Exibe dados da despesa |
| `PUT` | `/api/expenses/{id}` | Dados parciais | Atualiza a despesa |
| `DELETE` | `/api/expenses/{id}` | - | Exclui a despesa |

### 📄 Notas Fiscais (`/api/invoices`)
| Método | Endpoint | Filtros Query / Payload | Descrição |
|---|---|---|---|
| `GET` | `/api/invoices` | `enterprise_id`, `year`, `page` | Lista faturas emitidas |
| `POST` | `/api/invoices` | `enterprise_id`, `number`, `issue_date`, `value`, `description`, `file` | Cadastra nota com anexo PDF/XML |
| `GET` | `/api/invoices/{id}` | - | Exibe dados da nota |
| `PUT` | `/api/invoices/{id}` | Dados parciais | Atualiza nota fiscal |
| `DELETE` | `/api/invoices/{id}` | - | Exclui nota fiscal |

### 📑 Guias DAS (`/api/das-payments`)
| Método | Endpoint | Filtros Query | Descrição |
|---|---|---|---|
| `GET` | `/api/das-payments` | `enterprise_id`, `year`, `status`, `reference`, `page` | Lista guias DAS cadastradas |
| `POST` | `/api/das-payments` | `enterprise_id`, `reference`, `due_date`, `amount`, `status` | Cria guia DAS |
| `GET` | `/api/das-payments/{id}` | - | Exibe guia específica |
| `PUT` | `/api/das-payments/{id}` | Dados parciais (ex: `payment_date`, `status: paid`) | Atualiza status da guia |
| `DELETE` | `/api/das-payments/{id}` | - | Remove registro de DAS |

### 🔔 Notificações e Alertas (`/api/alerts`)
| Método | Endpoint | Filtros Query | Descrição |
|---|---|---|---|
| `GET` | `/api/alerts` | `enterprise_id`, `read`, `type` | Lista alertas da empresa/usuário |
| `PUT` | `/api/alerts/{id}/read` | - | Marca o alerta como lido |

### 📈 Relatórios (`/api/reports`)
| Método | Endpoint | Filtros Query | Descrição |
|---|---|---|---|
| `GET` | `/api/reports/monthly` | `enterprise_id`, `year`, `month` | Relatório consolidado e balanço mensal |

---

## ⚙️ Instalação e Configuração

### Pré-requisitos
- **PHP** >= 8.2 com extensões `pdo`, `pdo_mysql`, `sqlite3`, `fileinfo` e `mbstring` habilitadas.
- **Composer** 2.x
- **MySQL** 8.x (ou MariaDB compatível)

### Passo a Passo

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/Felipe-Cjesus/mei-api.git
   cd mei-api
   ```

2. **Instale as dependências via Composer:**
   ```bash
   composer install
   ```

3. **Configure as variáveis de ambiente:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Ajuste as credenciais do banco no arquivo `.env`:**
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mei
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Execute as migrações do banco de dados:**
   ```bash
   php artisan migrate
   ```

6. **Crie o link simbólico para o armazenamento público:**
   ```bash
   php artisan storage:link
   ```

7. **Inicie o servidor de desenvolvimento:**
   ```bash
   php artisan serve
   ```
   A API estará disponível em: `http://127.0.0.1:8000`

---

## 🧪 Testes Automatizados

A aplicação conta com suíte de testes automatizados com cobertura completa para as regras de negócio multiempresas, filtros, isolamento e segurança IDOR:

```bash
# Executar todos os testes
php artisan test

# Executar especificamente os testes de Multiempresas
php artisan test --filter=MultiEnterpriseTest
```

---

## ⏰ Tarefas Agendadas (Cron & Artisan)

Para processar as notificações preventivas de DAS e monitoramento fiscal de faturamento, execute o comando:

```bash
php artisan alerts:generate
```

Em ambiente de produção, configure o Cron do servidor para rodar o agendador do Laravel a cada minuto:
```bash
* * * * * cd /caminho-do-projeto && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📄 Licença

Este projeto está licenciado sob os termos da licença [MIT](LICENSE).

---

## 🧑‍💻 Autor

Desenvolvido por **[Felipe Costa de Jesus]**  
- **LinkedIn**: [linkedin.com/in/Felipe-Cjesus](https://www.linkedin.com/in/Felipe-Cjesus)  
- **E-mail**: felipecosta.developer@gmail.com
