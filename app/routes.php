<?php

use App\Controllers\v1\AuthController;
use App\Controllers\v1\AccountController;

$app->group('/v1/user', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
    //Group of data {
    $app->post('/login', AuthController::class . ':login')->setName('auth.login');
    // Token Check
    $app->get('/tokencheck', AccountController::class . ':tokenCheckValidity')->add($jwtMiddleware)->setName('account.tokenCheck');
});

$app->group('/v1/customer', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
    $app->get('/list', AccountController::class . ':customer_buyer_list')->add($jwtMiddleware)->setName('account.Customer_list');
    $app->post('/add', AccountController::class . ':customer_buyer_add')->add($jwtMiddleware)->setName('account.Customer_Add');
});

$app->group('/v2/course', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
});
