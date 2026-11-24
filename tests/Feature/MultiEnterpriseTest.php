<?php

namespace Tests\Feature;

use App\Models\DasPayment;
use App\Models\Enterprise;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MultiEnterpriseTest extends TestCase
{
    use RefreshDatabase;

    public function test_enterprise_index_returns_array_of_all_user_enterprises(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $enterprise1 = Enterprise::create([
            'user_id'      => $user1->id,
            'company_name' => 'Empresa 1 User 1',
            'company_id'   => '11111111000101',
        ]);

        $enterprise2 = Enterprise::create([
            'user_id'      => $user1->id,
            'company_name' => 'Empresa 2 User 1',
            'company_id'   => '22222222000102',
        ]);

        $enterprise3 = Enterprise::create([
            'user_id'      => $user2->id,
            'company_name' => 'Empresa User 2',
            'company_id'   => '33333333000103',
        ]);

        Sanctum::actingAs($user1);

        $response = $this->getJson('/api/enterprises');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertEquals($enterprise1->id, $data[0]['id']);
        $this->assertEquals($enterprise2->id, $data[1]['id']);
    }

    public function test_cannot_associate_financial_record_with_another_users_enterprise(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user2Enterprise = Enterprise::create([
            'user_id'      => $user2->id,
            'company_name' => 'Empresa User 2',
            'company_id'   => '44444444000104',
        ]);

        Sanctum::actingAs($user1);

        // Income with User 2's enterprise
        $responseIncome = $this->postJson('/api/incomes', [
            'enterprise_id' => $user2Enterprise->id,
            'description'   => 'Venda 1',
            'amount'        => 500.00,
            'date'          => '2026-05-10',
            'received'      => true,
        ]);
        $responseIncome->assertStatus(422);

        // Expense with User 2's enterprise
        $responseExpense = $this->postJson('/api/expenses', [
            'enterprise_id' => $user2Enterprise->id,
            'description'   => 'Despesa 1',
            'amount'        => 100.00,
            'date'          => '2026-05-10',
            'type'          => 'manual',
        ]);
        $responseExpense->assertStatus(422);

        // Invoice with User 2's enterprise
        $responseInvoice = $this->postJson('/api/invoices', [
            'enterprise_id' => $user2Enterprise->id,
            'number'        => 'NF-123',
            'issue_date'    => '2026-05-10',
            'value'         => 1500.00,
            'description'   => 'Serviço prestado',
        ]);
        $responseInvoice->assertStatus(422);

        // DAS with User 2's enterprise
        $responseDas = $this->postJson('/api/das-payments', [
            'enterprise_id' => $user2Enterprise->id,
            'reference'     => '05/2026',
            'due_date'      => '2026-06-20',
            'amount'        => 75.00,
            'status'        => 'pending',
        ]);
        $responseDas->assertStatus(422);
    }

    public function test_financial_records_can_be_filtered_by_enterprise_id(): void
    {
        $user = User::factory()->create();

        $enterpriseA = Enterprise::create([
            'user_id'      => $user->id,
            'company_name' => 'Empresa A',
            'company_id'   => '55555555000105',
        ]);

        $enterpriseB = Enterprise::create([
            'user_id'      => $user->id,
            'company_name' => 'Empresa B',
            'company_id'   => '66666666000106',
        ]);

        Sanctum::actingAs($user);

        // Create records for Enterprise A
        Income::create([
            'user_id'       => $user->id,
            'enterprise_id' => $enterpriseA->id,
            'description'   => 'Receita A',
            'amount'        => 1000.00,
            'date'          => '2026-03-01',
            'received'      => true,
        ]);

        Expense::create([
            'user_id'       => $user->id,
            'enterprise_id' => $enterpriseA->id,
            'description'   => 'Despesa A',
            'amount'        => 200.00,
            'date'          => '2026-03-02',
            'type'          => 'manual',
        ]);

        Invoice::create([
            'user_id'       => $user->id,
            'enterprise_id' => $enterpriseA->id,
            'number'        => 'NFA-1',
            'issue_date'    => '2026-03-03',
            'value'         => 1000.00,
            'description'   => 'NF Empresa A',
        ]);

        DasPayment::create([
            'user_id'       => $user->id,
            'enterprise_id' => $enterpriseA->id,
            'reference'     => '03/2026',
            'due_date'      => '2026-03-20',
            'amount'        => 75.00,
            'status'        => 'paid',
        ]);

        // Create records for Enterprise B
        Income::create([
            'user_id'       => $user->id,
            'enterprise_id' => $enterpriseB->id,
            'description'   => 'Receita B',
            'amount'        => 2000.00,
            'date'          => '2026-03-01',
            'received'      => true,
        ]);

        Expense::create([
            'user_id'       => $user->id,
            'enterprise_id' => $enterpriseB->id,
            'description'   => 'Despesa B',
            'amount'        => 400.00,
            'date'          => '2026-03-02',
            'type'          => 'manual',
        ]);

        Invoice::create([
            'user_id'       => $user->id,
            'enterprise_id' => $enterpriseB->id,
            'number'        => 'NFB-1',
            'issue_date'    => '2026-03-03',
            'value'         => 2000.00,
            'description'   => 'NF Empresa B',
        ]);

        DasPayment::create([
            'user_id'       => $user->id,
            'enterprise_id' => $enterpriseB->id,
            'reference'     => '03/2026',
            'due_date'      => '2026-03-20',
            'amount'        => 75.00,
            'status'        => 'paid',
        ]);

        // Test filtering Incomes
        $resIncomesA = $this->getJson("/api/incomes?enterprise_id={$enterpriseA->id}");
        $resIncomesA->assertStatus(200);
        $this->assertCount(1, $resIncomesA->json('data.data'));
        $this->assertEquals('Receita A', $resIncomesA->json('data.data.0.description'));

        // Test filtering Expenses
        $resExpensesA = $this->getJson("/api/expenses?enterprise_id={$enterpriseA->id}");
        $resExpensesA->assertStatus(200);
        $this->assertCount(1, $resExpensesA->json('data.data'));
        $this->assertEquals('Despesa A', $resExpensesA->json('data.data.0.description'));

        // Test filtering Invoices
        $resInvoicesA = $this->getJson("/api/invoices?enterprise_id={$enterpriseA->id}");
        $resInvoicesA->assertStatus(200);
        $this->assertCount(1, $resInvoicesA->json('data.data'));
        $this->assertEquals('NFA-1', $resInvoicesA->json('data.data.0.number'));

        // Test filtering Das Payments
        $resDasA = $this->getJson("/api/das-payments?enterprise_id={$enterpriseA->id}");
        $resDasA->assertStatus(200);
        $this->assertCount(1, $resDasA->json('data.data'));
        $this->assertEquals($enterpriseA->id, $resDasA->json('data.data.0.enterprise_id'));

        // Test filtering Monthly Report for Enterprise A
        $resReportA = $this->getJson("/api/reports/monthly?year=2026&month=3&enterprise_id={$enterpriseA->id}");
        $resReportA->assertStatus(200);
        $reportDataA = $resReportA->json('data.total');
        $this->assertEquals(1000.00, $reportDataA['income_total']);
        $this->assertEquals(200.00, $reportDataA['expense_total']);
        $this->assertEquals(1000.00, $reportDataA['invoice_total']);
        $this->assertEquals(75.00, $reportDataA['daspayment_total']);
        $this->assertEquals(725.00, $reportDataA['balance']); // 1000 - 200 - 75

        // Test filtering Monthly Report for Enterprise B
        $resReportB = $this->getJson("/api/reports/monthly?year=2026&month=3&enterprise_id={$enterpriseB->id}");
        $resReportB->assertStatus(200);
        $reportDataB = $resReportB->json('data.total');
        $this->assertEquals(2000.00, $reportDataB['income_total']);
        $this->assertEquals(400.00, $reportDataB['expense_total']);
        $this->assertEquals(2000.00, $reportDataB['invoice_total']);
        $this->assertEquals(75.00, $reportDataB['daspayment_total']);
        $this->assertEquals(1525.00, $reportDataB['balance']); // 2000 - 400 - 75
    }

    public function test_can_create_and_update_with_enterprise_id(): void
    {
        $user = User::factory()->create();

        $enterprise1 = Enterprise::create([
            'user_id'      => $user->id,
            'company_name' => 'Empresa 1',
            'company_id'   => '77777777000107',
        ]);

        $enterprise2 = Enterprise::create([
            'user_id'      => $user->id,
            'company_name' => 'Empresa 2',
            'company_id'   => '88888888000108',
        ]);

        Sanctum::actingAs($user);

        // Store income with enterprise1
        $postRes = $this->postJson('/api/incomes', [
            'enterprise_id' => $enterprise1->id,
            'description'   => 'Venda de Consultoria',
            'amount'        => 350.00,
            'date'          => '2026-04-10',
            'received'      => true,
        ]);
        $postRes->assertStatus(201);
        $incomeId = $postRes->json('data.id');
        $this->assertEquals($enterprise1->id, $postRes->json('data.enterprise_id'));

        // Update income switching to enterprise2
        $putRes = $this->putJson("/api/incomes/{$incomeId}", [
            'enterprise_id' => $enterprise2->id,
        ]);
        $putRes->assertStatus(200);
        $this->assertEquals($enterprise2->id, $putRes->json('data.enterprise_id'));
    }

    public function test_can_update_enterprise_retaining_same_company_id(): void
    {
        $user = User::factory()->create();

        $enterprise = Enterprise::create([
            'user_id'      => $user->id,
            'company_name' => 'Empresa Original',
            'company_id'   => '99999999000109',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/enterprises/{$enterprise->id}", [
            'company_name' => 'Empresa Atualizada',
            'company_id'   => '99999999000109',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Empresa Atualizada', $response->json('data.company_name'));
    }

    public function test_nullable_enterprise_id_allows_records_without_enterprise(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $resIncome = $this->postJson('/api/incomes', [
            'description' => 'Receita Geral',
            'amount'      => 100.00,
            'date'        => '2026-01-01',
            'received'    => true,
        ]);
        $resIncome->assertStatus(201);
        $this->assertNull($resIncome->json('data.enterprise_id'));
    }

    public function test_enterprise_cascade_deletes_associated_records(): void
    {
        $user = User::factory()->create();

        $enterprise = Enterprise::create([
            'user_id'      => $user->id,
            'company_name' => 'Empresa Deletavel',
            'company_id'   => '10101010000110',
        ]);

        $income = Income::create([
            'user_id'       => $user->id,
            'enterprise_id' => $enterprise->id,
            'description'   => 'Receita que sumira',
            'amount'        => 50.00,
            'date'          => '2026-01-01',
            'received'      => true,
        ]);

        $this->assertDatabaseHas('incomes', ['id' => $income->id]);

        $enterprise->delete();

        $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
    }

    public function test_migration_rollback_and_run_succeeds(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 1])->assertSuccessful();
        $this->artisan('migrate')->assertSuccessful();
    }
}
