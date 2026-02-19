<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('login session persistence check', function () {
    // Ambil user real dari database
    $user = User::where('userid', '003130771')->first();

    if (!$user) {
        $this->markTestSkipped('User 003130771 tidak ditemukan di database pegawai. Pastikan koneksi DB benar.');
    }

    // Coba login manual (force login tanpa password)
    // Ini mensimulasikan kondisi setelah verifikasi password berhasil
    $this->actingAs($user);

    // Coba akses halaman dashboard yang diproteksi auth
    $response = $this->get(route('dashboard'));

    // Debug: Lihat ID yang tersimpan di session auth
    // Auth::id() harusnya '003130771' (string string), bukan int 3130771
    $authId = Auth::id();
    echo "\nAuthenticated ID in Test: " . var_export($authId, true);

    // Assertions
    // Jika redirect ke login, berarti session gagal persisten/deserialize
    if ($response->status() === 302) {
        echo "\nRedirect Location: " . $response->headers->get('Location');
    }

    $response->assertStatus(200); // Harusnya OK, bukan Redirect
    $response->assertSee('SIJAD'); 
});
