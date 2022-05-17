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
    $app->put('/edit', AccountController::class . ':customer_buyer_edit')->add($jwtMiddleware)->setName('account.Customer_Edit');
    $app->delete('/delete', AccountController::class . ':customer_buyer_delete')->add($jwtMiddleware)->setName('account.Customer_Delete');
});
$app->group('/v1/user', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
    $app->get('/list', AccountController::class . ':user_list')->add($jwtMiddleware)->setName('account.User_list');
    $app->post('/add', AccountController::class . ':user_add')->add($jwtMiddleware)->setName('account.User_Add');
    $app->put('/edit', AccountController::class . ':user_edit')->add($jwtMiddleware)->setName('account.User_Edit');
    $app->delete('/delete', AccountController::class . ':user_delete')->add($jwtMiddleware)->setName('account.User_Delete');
});

$app->group('/v2/course', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
});
