<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Merchant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_usage_request_requires_valid_fields(): void
    {
        $response = $this->postJson('/api/usage', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'merchant_id',
                'customer_id',
                'units',
                'usage_date',
                'idempotency_key',
            ]);
    }

    public function test_usage_units_must_be_positive(): void
    {
        $merchant = Merchant::create([
            'name' => 'Test Merchant',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/usage', [
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'idempotency_key' => 'validation-test-001',
            'usage_date' => '2026-10-05',
            'units' => 0,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['units']);
    }

    public function test_customer_must_belong_to_the_requested_merchant(): void
    {
        $merchantOne = Merchant::create([
            'name' => 'Merchant One',
        ]);

        $merchantTwo = Merchant::create([
            'name' => 'Merchant Two',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchantTwo->id,
            'name' => 'Customer Two',
            'email' => 'customer-two@example.com',
        ]);

        $response = $this->postJson('/api/usage', [
            'merchant_id' => $merchantOne->id,
            'customer_id' => $customer->id,
            'idempotency_key' => 'tenant-isolation-001',
            'usage_date' => '2026-10-05',
            'units' => 100,
        ]);

        $response->assertNotFound();
    }
}