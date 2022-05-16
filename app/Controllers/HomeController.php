<?php

namespace App\Controllers;

use Slim\Views\Twig as View;
use Monolog\Logger;
use App\Models\UserMapper;

class HomeController extends BaseController
{
    /**
    *  protected $view;
    *    public function __construct(View $view) {
     *       $this->view = $view;
      *  }
     * *
     * @param type $request
     * @param type $response
     * @return type
     */

    
    // public function index($request, $response){
        
    //     //var_dump($request->getParam('name'));
    //     //return "Home Controller";
    //    //$users =  $this->eloquent->table('users')->find(1);
    //    //var_dump($users);
    //     // die();
    //     $this->logger->info("Display users");
    //     $checkList = $this->UserMapper->fetchAll();
    //     print_r($checkList);
    //     die();
    //         return $this->view->render($response, 'home.twig');
    // }
        
    protected  $logger;
    protected $mapper;
    
    public function __construct(Logger $logger, UserMapper $mapper) {
      // parent::__construct();
        $this->logger = $logger;
        $this->mapper = $mapper;
                
        }

    public function __invoke($request, $response, $next) {
        $this->logger->info("Display All users");
        $checkList = $this->mapper->fetchAll();
        var_dump($checkList);
    }
    
    
}