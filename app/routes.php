<?php

use App\Controllers\v1\AuthController;
use App\Controllers\v1\AccountController;

$app->group('/v1/user', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
    //Group of data {
    $app->post('/login', AuthController::class . ':login')->setName('auth.login');
    $app->post('/createaccount', AccountController::class . ':CreateAccount')->add($jwtMiddleware)->setName('account.createaccount');
    // Token Check
    $app->get('/tokencheck', AccountController::class . ':tokenCheckValidity')->add($jwtMiddleware)->setName('account.tokenCheck');
});

$app->group('/v2/staff', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
});

$app->group('/v2/course', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
});
