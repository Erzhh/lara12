<?php

use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

it('can create user and dispatch email', function () {
    $factory = UserFactory::new()->definition();
    $data    = [
        'name'     => 'Test User',
        'email'    => $factory['email'],
        'password' => 'password',
    ];

    $response = $this->post('/api/users', $data);

    $response->assertStatus(201);
    $response->assertJsonFragment([
        'name'  => 'Test User',
        'email' => $factory['email'],
    ]);

    $this->assertDatabaseHas('users', [
        'email' => $factory['email'],
    ]);
});


it('fails to create user without required fields', function () {
    $response = $this->postJson('/api/users', []);

    $response->assertStatus(422); // Unprocessable Entity (validation error)

    $response->assertJsonValidationErrors(['name', 'email', 'password']);
});


it('can list users', function () {
    \App\Models\User::factory()->count(3)->create();

    $response = $this->get('/api/users');

    $response->assertStatus(200);
});

it('can show user', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->get("/api/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'id'    => $user->id,
            'email' => $user->email,
        ]);
});

it('validates user create request')
    ->postJson('/api/users', [])
    ->assertStatus(422)
    ->assertJsonValidationErrors(['name', 'email', 'password']);

it('can update user', function () {
    $user   = \App\Models\User::factory()->create();
    $update = [
        'name'  => 'Updated Name',
        'email' => "update_" . $user['email'],
    ];

    $response = $this->putJson("/api/users/{$user->id}", $update);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'name'  => 'Updated Name',
            'email' => $update['email'],
        ]);

    $this->assertDatabaseHas('users', [
        'id'    => $user->id,
        'name'  => 'Updated Name',
        'email' => $update['email'],
    ]);
});

it('can delete user', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

it('fails on invalid user data',
    function (array $data) {

        $this->postJson('/api/users', $data)->assertStatus(422);

    })
    ->with([
        [['email' => 'bademail']], // нет name, нет password
        [['name' => '', 'email' => 'test@test.com']], // пустое name
    ]);
