<?php

it('does not expose the cache clearing endpoint', function () {
    $this->get('/clean-project')
        ->assertNotFound();
});

it('requires a one-time code for Google OAuth exchange', function () {
    $this->postJson('/api/v1/auth/google/exchange', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});
