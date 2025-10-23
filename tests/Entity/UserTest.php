<?php

namespace App\Tests\Entity;

use App\Tests\BaseTestCase;

class UserTest extends BaseTestCase
{
    public function testRegisterAndLogin(): void
    {
        // Register user
        $this->request('POST', '/users/register', [
            'name' => 'Richard',
            'email' => 'richard@example.com',
            'plainPassword' => 'Test123!',
        ])->toArray(throw: false);
        $this->assertResponseStatusCodeSame(201);

        // login
        $response = $this->request('POST', '/login-check', [
            'email' => 'richard@example.com',
            'password' => 'Test123!',
        ])->toArray();
        $this->assertResponseStatusCodeSame(200);
        $this->assertIsString($response['token']);
        $this->assertStringStartsWith('ey', $response['token']);

        // login with incorrect password and expect status 401
        $this->request('POST', '/login-check', [
            'email' => 'richard@example.com',
            'password' => 'incorrectPassword',
        ]);
        $this->assertResponseStatusCodeSame(401);
    }
}
