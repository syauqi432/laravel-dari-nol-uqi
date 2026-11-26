<?php

namespace Tests\Feature;

use App\Models\Saham;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_portfolio_or_market_api(): void
    {
        $this->get(route('market.overview'))->assertRedirect(route('login'));

        $this->getJson('/api/harga-saham/BBCA')->assertUnauthorized();
        $this->getJson('/api/market/index')->assertUnauthorized();
        $this->getJson('/api/market/news')->assertUnauthorized();
    }

    public function test_first_owner_setup_claims_legacy_stocks_and_closes_setup(): void
    {
        $legacyStock = Saham::create([
            'nama_pemilik' => 'Uqi',
            'kode_emiten' => 'BBCA',
            'nama_perusahaan' => 'Bank Central Asia',
            'sektor' => 'Keuangan',
            'jumlah_lot' => 10,
            'harga_beli' => 10000,
            'tanggal_beli' => '2026-01-01',
        ]);

        $response = $this->post(route('owner.setup.store'), [
            'name' => 'Pemilik',
            'email' => 'owner@example.test',
            'password' => 'OwnerPassword2026!',
            'password_confirmation' => 'OwnerPassword2026!',
        ]);

        $owner = User::where('email', 'owner@example.test')->firstOrFail();
        $response->assertRedirect(route('market.overview'));
        $this->assertAuthenticatedAs($owner);
        $this->assertSame($owner->id, DB::table('sahams')->where('id', $legacyStock->id)->value('user_id'));
        $this->get(route('owner.setup'))->assertRedirect();
    }

    public function test_owner_setup_is_not_available_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->get(route('owner.setup'))->assertNotFound();
    }

    public function test_guest_can_open_public_registration_page(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Buat akun investasi')
            ->assertSee(route('login'));
    }

    public function test_registration_creates_a_private_portfolio_account(): void
    {
        $existingUser = User::factory()->create();
        $this->actingAs($existingUser);
        $existingStock = Saham::create([
            'nama_pemilik' => $existingUser->name,
            'kode_emiten' => 'OLD',
            'nama_perusahaan' => 'Portofolio privat akun lama',
            'sektor' => 'Keuangan',
            'jumlah_lot' => 2,
            'harga_beli' => 1000,
            'tanggal_beli' => '2026-01-01',
        ]);
        $this->post(route('logout'))->assertRedirect(route('login'));

        $response = $this->post(route('register.store'), [
            'name' => 'Investor Baru',
            'email' => 'NEW@example.test',
            'password' => 'NewInvestor2026!',
            'password_confirmation' => 'NewInvestor2026!',
        ]);

        $newUser = User::query()->where('email', 'new@example.test')->firstOrFail();
        $response->assertRedirect(route('market.overview'));
        $this->assertAuthenticatedAs($newUser);
        $this->assertTrue(Hash::check('NewInvestor2026!', $newUser->password));

        $newStock = Saham::create([
            'nama_pemilik' => $newUser->name,
            'kode_emiten' => 'NEW',
            'nama_perusahaan' => 'Portofolio privat akun baru',
            'sektor' => 'Teknologi',
            'jumlah_lot' => 1,
            'harga_beli' => 2000,
            'tanggal_beli' => '2026-01-01',
        ]);

        $this->assertDatabaseHas('sahams', [
            'id' => $newStock->id,
            'user_id' => $newUser->id,
        ]);
        $this->get(route('saham.index'))
            ->assertOk()
            ->assertSee('Portofolio privat akun baru')
            ->assertDontSee('Portofolio privat akun lama');
        $this->get(route('issuers.show', $existingStock))->assertNotFound();
    }

    public function test_registration_rejects_an_existing_email_regardless_of_case(): void
    {
        User::factory()->create(['email' => 'investor@example.test']);

        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Investor Duplikat',
            'email' => 'INVESTOR@EXAMPLE.TEST',
            'password' => 'StrongPassword2026!',
            'password_confirmation' => 'StrongPassword2026!',
        ]);

        $response->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_rejects_a_weak_password(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Investor Baru',
            'email' => 'new@example.test',
            'password' => 'Weak1',
            'password_confirmation' => 'Weak1',
        ]);

        $response->assertRedirect(route('register'))
            ->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_a_password_confirmation_mismatch(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Investor Baru',
            'email' => 'new@example.test',
            'password' => 'StrongPassword2026!',
            'password_confirmation' => 'DifferentPassword2026!',
        ]);

        $response->assertRedirect(route('register'))
            ->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_login_accepts_a_differently_cased_email(): void
    {
        User::factory()->create([
            'email' => 'investor@example.test',
            'password' => 'StrongPassword2026!',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'INVESTOR@EXAMPLE.TEST',
            'password' => 'StrongPassword2026!',
        ]);

        $response->assertRedirect(route('market.overview'));
        $this->assertAuthenticated();
    }

    public function test_another_account_cannot_read_or_trade_an_owned_stock(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $stock = Saham::create([
            'nama_pemilik' => $owner->name,
            'kode_emiten' => 'BBCA',
            'nama_perusahaan' => 'Bank Central Asia',
            'sektor' => 'Keuangan',
            'jumlah_lot' => 10,
            'harga_beli' => 10000,
            'tanggal_beli' => '2026-01-01',
        ]);

        $this->actingAs(User::factory()->create());
        $this->get(route('issuers.show', $stock))->assertNotFound();

        $this->from(route('transactions.index'))
            ->post(route('paper-orders.store'), [
                'saham_id' => $stock->id,
                'jenis' => 'jual',
                'jumlah_lot' => 1,
                'harga' => 10000,
            ])
            ->assertSessionHasErrors('saham_id');

        $this->assertDatabaseCount('paper_orders', 0);
    }
}
