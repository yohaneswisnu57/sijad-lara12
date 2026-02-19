<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('authenticated user can logout', function () {
    // 1. Ambil user real
    $user = User::where('userid', '003130771')->first();

    if (!$user) {
        $this->markTestSkipped('User 003130771 tidak ditemukan.');
    }

    // 2. Login
    $this->actingAs($user);
    expect(Auth::check())->toBeTrue();

    // 3. Request Logout
    $response = $this->post(route('logout'));

    // 4. Assertions
    // Default Fortify redirect ke home '/'
    // Jika '/' diproteksi auth, maka pada akhirnya user akan melihat login page (via redirect selanjutnya)
    $response->assertStatus(302);
    
    // Pastikan user sudah tidak terautentikasi
    expect(Auth::check())->toBeFalse();
    
    // Opsional: Cek redirect location
    // $response->assertRedirect('/'); 
});
