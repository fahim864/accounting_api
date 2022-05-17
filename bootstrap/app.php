<?php

use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;

require_once __DIR__ . '/../vendor/autoload.php';


//Create Slim APP
$app = new \Slim\App([
    'debug' => true,
    'determineRouteBeforeAppMiddleware' => true, //VERY IMPORTANT for route logging
    'settings'  => [

        'displayErrorDetails'   => true,

        //App Settings
        'app'                    => [
            'name' => 'Accounting API APP v1',
            'url'  => 'http://localhost:8080/public',
            'env'  => 'local',
        ],
        //Render settings
        'renderer'  => [
            'template_path' => __DIR__ . '/../templates/',
            'cache_path'         => __DIR__ . '/../cache/',
        ],

        //Monolog Settings
        'logger'    => [
            'name'  => 'Slim-app',
            'path'  => __DIR__ . '/../logs/' . date('Y-m-d') . '.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'db'    => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'tutorapps_v1',
            'username' => 'root',
            'password' => '',
            'charset'   => 'latin1',
            'collation' => 'latin1_swedish_ci',
            'prefix'    => '',
        ],
        'pdo' => [
            'engine' => 'mysql',
            'host' => 'localhost',
            'database' => 'accounting_api_v2',
            'username' => 'root',
            'password' => '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',

            'options' => [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => true,
            ],
        ],

        // jwt settings
        'jwt'  => [
            'secret' => "disseminarteconsultingpvtbdltddevelopersatiqfahim",
            'secure' => false,
            "header" => "Authorization",
            "regexp" => "/Bearer\s+(.*)$/i",
            'passthrough' => ['OPTIONS'],
            "error" => function ($request, $response, $arguments) {
                $data["error"] = true;
                $data["status"] = "error";
                $data["msg"] = $arguments["message"];
                return $response
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            }
        ],

        // api rate limiter settings
        'api_rate_limiter' => [
            'requests' => '200',
            'inmins' => '180',
        ],
    ]

]);


//Fetch DI Container
$container = $app->getContainer();

// Monolog
$container['logger'] = function (\Slim\Container $c) {
    $settings = $c->get('settings')['logger'];

    $logger = new Monolog\Logger($settings['name']);

    $handler = new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG);

    $handler->setFormatter(new Monolog\Formatter\LineFormatter(
        "[%datetime%] %level_name% > %message% - %context% - %extra%\n"
    ));

    $logger->pushHandler($handler);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushProcessor(new Monolog\Processor\WebProcessor);

    return $logger;
};

$container->register(new \App\Services\Database\PdoServiceProvider());
$container->register(new \App\Services\Auth\AuthServiceProvider());


//Register Twig View Helper
$container['view'] = function ($c) {
    $settings = $c->get('settings')['renderer'];

    $view =  new Slim\Views\Twig($settings['template_path'], [
        'cache' => $settings['cache_path'],
    ]);

    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));

    //$c->router;
    //$c->request->getUri();

    $view->addExtension(new Slim\Views\TwigExtension($router, $uri));

    return $view;
};

$container[App\Models\UserMapper::class] = function ($c) {
    return new App\Models\UserMapper($c->get('logger'), $c->get('dbh'));
};

$container[App\Models\Account::class] = function ($c) {
    return new App\Models\Account($c->get('logger'), $c->get('dbh'));
};


//--------------------------------------------------------------
//    Controller Factories
//--------------------------------------------------------------

$container[App\Controllers\HomeController::class] = function ($c) {
    $logger = $c->get('logger');
    $mapper = $c->get(App\Models\UserMapper::class);

    return new App\Controllers\HomeController($logger, $mapper);
};

$container[App\Controllers\v1\AuthController::class] = function ($c) {

    return new App\Controllers\v1\AuthController($c);
};
$container[App\Controllers\v1\AccountController::class] = function ($c) {

    return new App\Controllers\v1\AccountController($c, $c->get(App\Models\Account::class), $c->get(App\Models\UserMapper::class));
};

// Fractal
$container['fractal'] = function ($c) {
    $manager = new Manager();
    $manager->setSerializer(new ArraySerializer());

    return $manager;
};
// Jwt Middleware
$container['jwt'] = function ($c) {

    $jws_settings = $c->get('settings')['jwt'];

    return new \Slim\Middleware\JwtAuthentication($jws_settings);
};

require_once __DIR__ . '/../app/middleware.php';

require_once __DIR__ . '/../app/routes.php';
