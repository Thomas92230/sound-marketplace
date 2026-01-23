<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:role {email} {role}', function (string $email, string $role) {
    $role = strtolower(trim($role));
    $allowed = ['user', 'artist', 'admin'];

    if (! in_array($role, $allowed, true)) {
        $this->error("Role invalide: {$role}. Roles possibles: user|artist|admin");
        return 1;
    }

    /** @var \App\Models\User|null $user */
    $user = \App\Models\User::where('email', $email)->first();

    if (! $user) {
        $this->error("Utilisateur introuvable pour email: {$email}");
        return 1;
    }

    $user->role = $role;
    $user->save();

    $savedRole = $user->role;
    if ($savedRole instanceof \App\Enums\UserRole) {
        $savedRole = $savedRole->value;
    }

    $this->info("OK: {$user->email} => role={$savedRole}");
    return 0;
})->purpose('Assigne un rôle (user|artist|admin) à un utilisateur via son email');

Artisan::command('user:list', function () {
    $users = \App\Models\User::query()
        ->select(['id', 'name', 'email', 'role', 'created_at'])
        ->orderBy('id')
        ->get();

    if ($users->isEmpty()) {
        $this->warn('Aucun utilisateur en base (table users vide).');
        return 0;
    }

    $this->table(
        ['id', 'name', 'email', 'role', 'created_at'],
        $users->map(function ($u) {
            $role = $u->role;

            // Si c'est une enum, on prend la valeur, sinon on affiche tel quel
            if ($role instanceof \App\Enums\UserRole) {
                $role = $role->value;
            }

            return [$u->id, $u->name, $u->email, $role, (string) $u->created_at];
        })->all()
    );

    return 0;
})->purpose('Liste les utilisateurs (id/email/role) en base');
