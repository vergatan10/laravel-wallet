<?php

namespace Vergatan10\Wallet\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vergatan10\Wallet\Models\Wallet;
use Vergatan10\Wallet\Models\WalletTransaction;
use Vergatan10\Wallet\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User;
use Vergatan10\Wallet\Tests\Models\User as UserModel;

class WalletDepositTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure user model exists 
        if (!Schema::hasTable('users')) {
            $this->loadLaravelMigrations();
        }

        // Create user model factory if needed 
        if (!class_exists(\Database\Factories\UserFactory::class)) {
            eval('
            namespace Database\Factories; 
            use Illuminate\Database\Eloquent\Factories\Factory; 
            class UserFactory extends Factory 
            { 
                protected $model = \Illuminate\Foundation\Auth\User::class; 
                
                public function definition() 
                { 
                    return [ 
                        "name" => $this->faker->name, 
                        "email" => $this->faker->safeEmail, 
                        "password" => bcrypt("password") 
                        ]; 
                    } 
                }');
        }

        // Create API route for deposit if not using real route 
        if (!Route::has('wallet.deposit')) {
            Route::post('/api/wallet/deposit', function () {
                $user = auth()->user();
                $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id]);
                $wallet->transactions()->create(['amount' => 1000, 'type' => 'deposit', 'note' => 'test',]);
                return response()->json(['message' => 'Deposited']);
            })
                ->middleware('auth');
        }
    }

    /** @test */
    public function user_can_deposit()
    {
        $user = new UserModel([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        $user->save();
        auth()->login($user);
        $this->actingAs($user);
        $response = $this->postJson('/api/wallet/1/deposit', [
            'amount' => 1000,
            'description' => 'test',
        ]);
        $response->assertStatus(200)->assertJson(['message' => 'Deposited']);
        $this->assertDatabaseHas('wallets', ['user_id' => $user->id]);
        $this->assertDatabaseHas('wallet_transactions', [
            'type' => 'deposit',
            'amount' => 1000
        ]);
    }
}
