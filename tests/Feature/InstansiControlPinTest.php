<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstansiControlPinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['t_user', 'instansi'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('instansi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama');
            $table->string('judul_mobile')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telp')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->unsignedTinyInteger('zoom')->nullable();
            $table->string('logo')->nullable();
            $table->string('logo_mobile')->nullable();
            $table->string('control_pin_hash')->nullable();
            $table->boolean('control_pin_enabled')->default(false);
            $table->timestamp('control_pin_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username');
            $table->string('password');
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->nullable();
        });
    }

    public function test_store_hashes_optional_control_pin(): void
    {
        $this->actingAs($this->superadmin());

        $this->post(route('instansi.store'), [
            'nama' => 'Instansi PIN',
            'alamat' => 'Alamat',
            'control_pin' => '123456',
        ])->assertRedirect(route('instansi.index'));

        $instansi = Instansi::query()->where('nama', 'Instansi PIN')->firstOrFail();

        $this->assertTrue($instansi->control_pin_enabled);
        $this->assertTrue(Hash::check('123456', $instansi->control_pin_hash));
        $this->assertNotSame('123456', $instansi->control_pin_hash);
        $this->assertNotNull($instansi->control_pin_updated_at);
    }

    public function test_update_preserves_or_clears_existing_control_pin(): void
    {
        $this->actingAs($this->superadmin());

        $instansi = Instansi::create([
            'nama' => 'Instansi Lama',
            'control_pin_hash' => Hash::make('654321'),
            'control_pin_enabled' => true,
            'control_pin_updated_at' => now(),
        ]);
        $oldHash = $instansi->control_pin_hash;

        $this->put(route('instansi.update', $instansi), [
            'nama' => 'Instansi Baru',
            'alamat' => 'Alamat',
            'control_pin' => '',
        ])->assertRedirect(route('instansi.index'));

        $instansi->refresh();
        $this->assertSame($oldHash, $instansi->control_pin_hash);
        $this->assertTrue($instansi->control_pin_enabled);

        $this->put(route('instansi.update', $instansi), [
            'nama' => 'Instansi Baru',
            'alamat' => 'Alamat',
            'clear_control_pin' => '1',
        ])->assertRedirect(route('instansi.index'));

        $instansi->refresh();
        $this->assertNull($instansi->control_pin_hash);
        $this->assertFalse($instansi->control_pin_enabled);
        $this->assertNull($instansi->control_pin_updated_at);
    }

    private function superadmin(): t_User
    {
        return t_User::create([
            'nama' => 'Superadmin',
            'username' => 'superadmin',
            'password' => bcrypt('password'),
            'level_user' => 'superadmin',
            'status' => 'aktif',
        ]);
    }
}
