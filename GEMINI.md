# GEMINI.md - Memória Persistente do Projeto (mei-api)

Documento de contexto e memória técnica para continuidade do desenvolvimento entre sessões.

---

## 1. Visão Geral e Stack

- **Propósito**: Backend REST API (`mei-api`) do ecossistema MEI App (integrado com o frontend `mei-app-manager`). Oferece gestão financeira para Microempreendedores Individuais (MEI) e contadores, controlando receitas, despesas, faturamento acumulado, notas fiscais, guias DAS, relatórios mensais e alertas fiscais preventivos.
- **Linguagem & Framework**: PHP 8.2+ / Laravel 12.x
- **Autenticação**: Laravel Sanctum (Tokens Bearer) com rate limiting via `throttle`.
- **Banco de Dados**: MySQL (Ambiente local Laragon / Produção) e SQLite (`:memory:` para execução rápida de testes automatizados).
- **Testes**: PHPUnit 11.5 / `php artisan test`.

---

## 2. Regras e Padrões do Projeto

- **Padronização de Respostas**: Toda resposta de endpoint deve usar o helper `App\Helpers\ApiResponse`:
  - Sucesso: `ApiResponse::success(mixed $data = null, int $code = 200, string $message = '')`
  - Erro: `ApiResponse::error(string $message, int $code = 400, mixed $data = null)`
- **Autorização & Políticas (Policies)**:
  - Operações individuais (`show`, `update`, `delete`) devem validar se o registro pertence ao usuário autenticado (`$this->authorize(...)`).
  - Mapeadas em `app/Policies/` e registradas em `app/Providers/AuthServiceProvider.php`.
- **Isolamento Multiempresas**:
  - Cada registro financeiro pertence a um usuário (`user_id`) e opcionalmente a uma empresa (`enterprise_id`).
  - Ao receber `enterprise_id` em requisições de criação ou edição (`store`/`update`), validar que a empresa pertence ao usuário autenticado:
    ```php
    'enterprise_id' => [
        'nullable',
        Rule::exists('enterprises', 'id')->where(fn ($q) => $q->where('user_id', Auth::id())),
    ]
    ```
  - Em listagens (`index`), aceitar parâmetro de filtro opcional `?enterprise_id=...`, sempre mantendo o escopo base `where('user_id', Auth::id())`.
- **Portabilidade de Banco de Dados**:
  - Toda a API é 100% agnóstica de banco de dados (pronta para MySQL, PostgreSQL e SQLite).
  - O `ReportController` utiliza agregação e agrupamento em memória com Eloquent Collections, eliminando qualquer raw SQL dependente de dialeto (`MONTH()`, `strftime`, etc.).

---

## 3. Estado Atual (Onde Paramos)

### Concluído na sessão:
1. **Multiempresas no `EnterpriseController`**:
   - `EnterpriseController::index()` alterado de `->first()` para `->get()`, retornando array com todas as empresas do usuário.
   - Correção na validação de unicidade de `company_id` no `update` para ignorar o próprio ID da empresa.
2. **Isolamento de Dados Financeiros**:
   - Migration `database/migrations/2025_07_02_000000_add_enterprise_id_to_financial_tables.php`:
     - Coluna `enterprise_id` adicionada às tabelas `incomes`, `expenses`, `invoices` e `das_payments` com chave estrangeira e `onDelete('cascade')`.
   - Controllers atualizados para filtrar por `enterprise_id` e validar propriedade:
     - `IncomeController.php`
     - `ExpenseController.php`
     - `InvoiceController.php`
     - `DasPaymentController.php`
     - `ReportController.php` (relatório mensal agregando dados por empresa).
3. **Isolamento dos Alertas Fiscais e Faturamento MEI**:
   - Migration `database/migrations/2025_07_02_000001_add_enterprise_id_to_alerts_table.php`: adicionada `enterprise_id` à tabela `alerts`.
   - `app/Console/Commands/GenerateAlerts.php`: refatorado para iterar sobre as empresas de cada usuário. O limite anual de R$ 81.000,00 da Receita Federal agora é calculado por **CNPJ (empresa)** e não mais pelo CPF/usuário geral. Os alertas de DAS e teto de receita salvam o `enterprise_id`.
   - `AlertController.php`: agora aceita filtro `?enterprise_id=...`.
4. **Portabilidade Universal para Banco de Dados (PostgreSQL / MySQL / SQLite)**:
   - Refatoração do `ReportController.php` com Agrupamento em Memória via Eloquent Collections. Reduziu de 10 queries com raw SQL para apenas 4 queries Eloquent limpas, tornando a API 100% compatível com PostgreSQL, MySQL e SQLite.
5. **Modelos Atualizados**:
   - Relacionamentos adicionados nos models `Enterprise`, `User`, `Income`, `Expense`, `Invoice`, `DasPayment` e `Alert`.
6. **Cobertura de Testes**:
   - Criada a suíte `tests/Feature/MultiEnterpriseTest.php` com 12 testes e 59 asserções cobrindo cenários multiempresas, filtros, segurança IDOR, cascade delete, migração/rollback e cálculo de alertas por MEI. Todos passando.

---

## 4. Decisões Técnicas

- **Agrupamento de Relatórios em Memória (Eloquent Collections)**: Em vez de disparar 10 queries com dialetos SQL como `MONTH()` ou `EXTRACT()`, o `ReportController` realiza 4 consultas simples trazendo apenas as colunas necessárias e agrupa os 12 meses no PHP. Para a escala de MEIs (centenas de lançamentos/ano), a resposta é quase instantânea e elimina qualquer acoplamento a um SGBD específico.
- **Persistência de `user_id` junto com `enterprise_id`**: Mantivemos a coluna `user_id` nas tabelas financeiras para garantir integridade, autorização rápida e compatibilidade com dados legados sem empresa associada.
- **`enterprise_id` como `nullable`**: Permite transição suave de dados existentes e suporte a usuários que ainda não cadastraram nenhuma empresa MEI no primeiro acesso.
- **Teto Fiscal MEI por CNPJ**: A regra tributária brasileira vincula o limite anual de R$ 81.000,00 ao CNPJ. Contadores ou usuários com mais de uma empresa não têm seus faturamentos somados para fins de alerta de desenquadramento.
- **Deleção em Cascata (`cascade`)**: Ao excluir uma empresa, todos os seus dados vinculados (`incomes`, `expenses`, `invoices`, `das_payments`, `alerts`) são removidos automaticamente via foreign key.

---

## 5. Próximos Passos (TODO)

1. **Validação E2E com o Front-End (`mei-app-manager`)**:
   - Testar o fluxo completo de troca de empresa no frontend garantindo que as chamadas a `/api/incomes`, `/api/expenses`, `/api/invoices`, `/api/das-payments`, `/api/reports/monthly` e `/api/alerts` passem o query param `enterprise_id`.
2. **Seeders para Desenvolvimento**:
   - Criar ou atualizar seeders (`EnterpriseSeeder`, `FinancialDataSeeder`) contendo usuários com múltiplas empresas e lançamentos para facilitar testes locais.
3. **Agendamento do Comando de Alertas**:
   - Configurar o agendamento do comando `alerts:generate` no `routes/console.php` (ex: execução diária `daily()`).
4. **Relatórios Anuais / Exportação**:
   - Avaliar a necessidade de relatórios consolidados por ano (além do `reports/monthly`) com exportação para PDF/Excel se demandado pelo frontend.

