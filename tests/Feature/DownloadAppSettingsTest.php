<?php

namespace Tests\Feature;

use App\Models\t_User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadAppSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    private function superadmin(): t_User
    {
        return new t_User(['level_user' => 'superadmin']);
    }

    public function test_admin_can_upload_apk_and_it_drives_download_page(): void
    {
        $apk = UploadedFile::fake()->create('mini_stesy.apk', 512); // 512 KB

        $response = $this->actingAs($this->superadmin())->post(route('settings.download.update'), [
            'download_android_mode' => 'apk',
            'download_android_version' => '1.2.0',
            'download_android_apk' => $apk,
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        // File tersimpan & metadata tercatat di app_settings.json
        $settings = json_decode(Storage::disk('local')->get('app_settings.json'), true);
        $this->assertSame('apk', $settings['download_android_mode']);
        $this->assertNotEmpty($settings['download_android_apk_path']);
        $this->assertSame('mini_stesy.apk', $settings['download_android_apk_name']);
        $this->assertTrue(Storage::disk('local')->exists($settings['download_android_apk_path']));

        // Halaman download menampilkan tombol Android (tersedia) mengarah ke route APK
        $page = $this->actingAs($this->superadmin())->get(route('download.index'));
        $page->assertOk();
        $page->assertSee(route('download.android.apk'));

        // Route APK men-stream file sebagai unduhan
        $download = $this->actingAs($this->superadmin())->get(route('download.android.apk'));
        $download->assertOk();
        $download->assertDownload('mini_stesy.apk');
    }

    public function test_uploading_new_apk_replaces_the_old_file(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->post(route('settings.download.update'), [
            'download_android_mode' => 'apk',
            'download_android_apk' => UploadedFile::fake()->create('old.apk', 100),
        ]);
        $oldPath = json_decode(Storage::disk('local')->get('app_settings.json'), true)['download_android_apk_path'];

        $this->actingAs($admin)->post(route('settings.download.update'), [
            'download_android_mode' => 'apk',
            'download_android_apk' => UploadedFile::fake()->create('new.apk', 100),
        ]);
        $newPath = json_decode(Storage::disk('local')->get('app_settings.json'), true)['download_android_apk_path'];

        $this->assertNotSame($oldPath, $newPath);
        $this->assertFalse(Storage::disk('local')->exists($oldPath));
        $this->assertTrue(Storage::disk('local')->exists($newPath));
    }

    public function test_playstore_mode_uses_link_on_download_page(): void
    {
        $url = 'https://play.google.com/store/apps/details?id=com.ministesy.app';

        $this->actingAs($this->superadmin())->post(route('settings.download.update'), [
            'download_android_mode' => 'playstore',
            'download_android_playstore_url' => $url,
            'download_android_version' => '1.2.0',
        ])->assertRedirect(route('settings.index'));

        $page = $this->actingAs($this->superadmin())->get(route('download.index'));
        $page->assertOk();
        $page->assertSee($url, false);
    }

    public function test_ios_link_is_saved_and_shown(): void
    {
        $url = 'https://apps.apple.com/id/app/mini_stesy/id6480156441';

        $this->actingAs($this->superadmin())->post(route('settings.download.update'), [
            'download_android_mode' => 'apk',
            'download_ios_url' => $url,
            'download_ios_version' => '1.3.6',
        ])->assertRedirect(route('settings.index'));

        $page = $this->actingAs($this->superadmin())->get(route('download.index'));
        $page->assertSee($url, false);
    }

    public function test_apk_route_returns_404_when_no_apk_uploaded(): void
    {
        $this->actingAs($this->superadmin())->get(route('download.android.apk'))->assertNotFound();
    }

    public function test_non_admin_cannot_update_download_settings(): void
    {
        $pegawai = new t_User(['level_user' => 'pegawai']);
        // Tanpa migrasi tabel roles; setel relasi null agar hasPermission tidak query DB.
        $pegawai->setRelation('role', null);

        $this->actingAs($pegawai)->post(route('settings.download.update'), [
            'download_android_mode' => 'apk',
        ])->assertForbidden();
    }

    public function test_invalid_mode_is_rejected(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('settings.download.update'), ['download_android_mode' => 'bogus'])
            ->assertSessionHasErrors('download_android_mode');
    }
}
