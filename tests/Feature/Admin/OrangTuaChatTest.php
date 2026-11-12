<?php

namespace Tests\Feature\Admin;

use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\OrangTuaChat;
use App\Models\OrangTuaChatMessage;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrangTuaChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * @return array{sekolah: Sekolah, sekolah2: Sekolah, admin: User, ortu: User, chat: OrangTuaChat}
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'email' => 'admin-ortu-chat@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $ortu = User::factory()->create([
            'email' => 'ortu-admin-view@test.com',
            'sekolah_id' => $sekolah->id,
            'name' => 'Siti Ortu',
        ]);
        $ortu->assignRole('Orang Tua');

        $pengajar = Pengajar::create([
            'user_id' => $admin->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru',
        ]);

        $kelas = Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas A',
            'wali_kelas_id' => $pengajar->id,
        ]);

        Anak::create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'kelas_id' => $kelas->id,
            'name' => 'Anak Admin View',
            'status' => 'approved',
        ]);

        $chat = OrangTuaChat::create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'cleared_at' => now()->subDay(),
        ]);

        OrangTuaChatMessage::create([
            'orangtua_chat_id' => $chat->id,
            'role' => OrangTuaChatMessage::ROLE_USER,
            'content' => 'Pesan sebelum dihapus ortu',
            'created_at' => now()->subDays(2),
        ]);

        OrangTuaChatMessage::create([
            'orangtua_chat_id' => $chat->id,
            'role' => OrangTuaChatMessage::ROLE_ASSISTANT,
            'content' => 'Jawaban AI lama',
            'created_at' => now()->subDays(2),
        ]);

        $sekolah2 = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Sekolah B',
            'address' => 'Jl. B',
            'phone' => '021-11111111',
        ]);

        $ortuOther = User::factory()->create([
            'email' => 'ortu-sekolah-b@test.com',
            'sekolah_id' => $sekolah2->id,
        ]);
        $ortuOther->assignRole('Orang Tua');

        $otherChat = OrangTuaChat::create([
            'user_id' => $ortuOther->id,
            'sekolah_id' => $sekolah2->id,
        ]);

        OrangTuaChatMessage::create([
            'orangtua_chat_id' => $otherChat->id,
            'role' => OrangTuaChatMessage::ROLE_USER,
            'content' => 'Chat sekolah lain',
        ]);

        return compact('sekolah', 'sekolah2', 'admin', 'ortu', 'chat', 'otherChat');
    }

    public function test_admin_sekolah_can_view_chat_list(): void
    {
        $f = $this->createFixtures();

        $this->actingAs($f['admin'])
            ->get(route('admin.orangtua-chat.index'))
            ->assertOk()
            ->assertSee('Chat Orang Tua')
            ->assertSee('Siti Ortu')
            ->assertSee('Anak Admin View')
            ->assertDontSee('Chat sekolah lain');
    }

    public function test_admin_sekolah_can_view_full_chat_history_including_cleared(): void
    {
        $f = $this->createFixtures();

        $this->actingAs($f['admin'])
            ->get(route('admin.orangtua-chat.show', $f['chat']))
            ->assertOk()
            ->assertSee('Pesan sebelum dihapus ortu')
            ->assertSee('Jawaban AI lama')
            ->assertSee('menghapus riwayat');
    }

    public function test_admin_sekolah_cannot_view_chat_from_other_school(): void
    {
        $f = $this->createFixtures();

        $this->actingAs($f['admin'])
            ->get(route('admin.orangtua-chat.show', $f['otherChat']))
            ->assertNotFound();
    }
}
