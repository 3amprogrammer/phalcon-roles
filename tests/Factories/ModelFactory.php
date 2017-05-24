<?php

use League\FactoryMuffin\Faker\Facade as Faker;
use PhalconRoles\Models\Permissions;
use PhalconRoles\Models\Roles;
use PhalconRoles\Tests\Stubs\Users;

/** @var \League\FactoryMuffin\FactoryMuffin $fm */
$fm->define(Users::class)->setDefinitions([
    'email' => Faker::email(),
    'password' => Faker::password()
]);

$fm->define(Roles::class)->setDefinitions([
    'name' => Faker::username(),
    'slug' => Faker::slug(),
    'description' => Faker::text()
]);

$fm->define(Permissions::class)->setDefinitions([
    'name' => Faker::username(),
    'slug' => Faker::slug(),
    'description' => Faker::text()
]);