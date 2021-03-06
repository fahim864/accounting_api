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

$app->group('/v1', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
    $app->get('/settings', AccountController::class . ':settingsReturn')->add($jwtMiddleware)->setName('account.SettingsReturn');
    $app->post('/settings', AccountController::class . ':settingsControl')->add($jwtMiddleware)->setName('account.Settings');
});

$app->group('/v1/products', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
    $app->get('/list', AccountController::class . ':products_list')->add($jwtMiddleware)->setName('account.products_list');
    $app->post('/add', AccountController::class . ':products_add')->add($jwtMiddleware)->setName('account.products_add');
    $app->put('/edit', AccountController::class . ':products_edit')->add($jwtMiddleware)->setName('account.products_edit');
    $app->post('/import', AccountController::class . ':products_import')->add($jwtMiddleware)->setName('account.products_import');
});

$app->group('/v1/realisation', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
    $app->post('/search', AccountController::class . ':realisation_search')->add($jwtMiddleware)->setName('account.realisation_search');
});

$app->group('/v1/payment_to_supplier', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
    $app->post('/search', AccountController::class . ':payment_to_supplier_search')->add($jwtMiddleware)->setName('account.payment_to_supplier_search');
});

$app->group('/v1/sales', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
    $app->get('/user_search', AccountController::class . ':sales_user_search')->add($jwtMiddleware)->setName('account.sales_user_search');
    $app->post('/add', AccountController::class . ':sales_add')->add($jwtMiddleware)->setName('account.sales_add');
    $app->put('/edit', AccountController::class . ':sales_edit')->add($jwtMiddleware)->setName('account.sales_edit');
    $app->post('/import', AccountController::class . ':sales_import')->add($jwtMiddleware)->setName('account.sales_import');
});

$app->group('/v1/purchase', function () use ($app) {
    $jwtMiddleware = $this->getContainer()->get('jwt');
    $app->post('/search_supplier', AccountController::class . ':purchase_search_supplier')->add($jwtMiddleware)->setName('account.purchase_search_supplier');
});
