<?php

namespace Tests\Unit;

use App\Helpers\AuthHelper;
use Tests\TestCase;

class AuthHelperTest extends TestCase
{
    public function test_verify_legacy_password_without_bcrypt_exception(): void
    {
        $password = 'Dxdzcg888';
        $salt = 'abc1234567';
        $hash = AuthHelper::encryptLegacyPassword($password, $salt);

        $this->assertTrue(AuthHelper::verifyPassword($password, $hash, $salt));
        $this->assertFalse(AuthHelper::verifyPassword('wrong', $hash, $salt));
    }

    public function test_verify_modern_password(): void
    {
        $password = 'secret123';
        $hash = AuthHelper::encryptPassword($password);

        $this->assertTrue(AuthHelper::isModernPasswordHash($hash));
        $this->assertTrue(AuthHelper::verifyPassword($password, $hash));
    }
}
