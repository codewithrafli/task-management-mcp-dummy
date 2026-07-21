<?php

use App\Models\User;

test('guests are redirected to login', function () {
    $this->get('/')->assertRedirect('/login');
});

test('authenticated users can view the boards', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/')->assertOk();
});
