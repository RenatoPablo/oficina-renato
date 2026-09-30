<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function admin(): User
    {
        $user = new User;
        $user->forceFill(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'admin123', 'is_admin' => true])->save();

        return $user;
    }

    public function test_new_non_admin_can_log_in_with_a_long_password(): void
    {
        $this->actingAs($this->admin());
        $password = 'senha-com-mais-de-16-caracteres';
        $this->post(route('admin.user.create.submit'), [
            'name' => 'Cliente', 'email' => 'cliente@example.test',
            'password' => $password, 'verifyPassword' => $password,
            'permissao' => 0, 'ativo' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users'));

        Auth::logout();
        $this->post('/loginSubmit', ['email' => 'cliente@example.test', 'password' => $password])
            ->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'cliente@example.test')->firstOrFail());
        $this->assertFalse((bool) Auth::user()->is_admin);
        $this->assertNotNull(Auth::user()->last_login);
        $this->get(route('admin.users'))->assertRedirect(route('dashboard'));
    }

    public function test_existing_short_password_still_authenticates_but_wrong_password_does_not(): void
    {
        $user = User::create(['name' => 'Cliente', 'email' => 'cliente@example.test', 'password' => '1234']);
        $this->post('/loginSubmit', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHas('loginError');
        $this->assertGuest();
        $this->post('/loginSubmit', ['email' => $user->email, 'password' => '1234'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_editing_without_password_preserves_login(): void
    {
        $this->actingAs($this->admin());
        $user = User::create(['name' => 'Cliente', 'email' => 'cliente@example.test', 'password' => 'original123']);
        $hash = $user->password;
        $this->put(route('admin.users.update'), [
            'id' => $user->id, 'name' => 'Cliente editado', 'email' => $user->email,
            'password' => '', 'verifyPassword' => '', 'permissao' => 0, 'ativo' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users'));
        $this->assertSame($hash, $user->fresh()->password);
        Auth::logout();
        $this->post('/loginSubmit', ['email' => $user->email, 'password' => 'original123'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_mismatched_confirmation_does_not_create_a_user(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('admin.user.create.submit'), [
            'name' => 'Cliente', 'email' => 'cliente@example.test',
            'password' => 'original123', 'verifyPassword' => 'different123',
            'permissao' => 0, 'ativo' => 1,
        ])->assertSessionHasErrors('verifyPassword');
        $this->assertDatabaseMissing('users', ['email' => 'cliente@example.test']);
    }

    public function test_admin_login_still_redirects_to_admin_dashboard(): void
    {
        $admin = $this->admin();
        $this->post('/loginSubmit', ['email' => $admin->email, 'password' => 'admin123'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }
}
