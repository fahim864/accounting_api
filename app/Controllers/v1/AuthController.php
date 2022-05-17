<?php

namespace App\Controllers\v1;

use App\Models\UserMapper;
use App\Transformers\UserTransformer;
use Interop\Container\ContainerInterface;
use League\Fractal\Resource\Item;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AuthController
{
    protected $db;
    private $auth;
    protected $fractal;



    public function __construct(ContainerInterface $container)
    {
        $this->auth = $container->get('auth');
        $this->db = $container->get('dbh');
        $this->fractal = $container->get('fractal');
    }

    /**
     * Return token after successful login
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     *
     * @return \Slim\Http\Response
     */
    public function login(Request $request, Response $response, $args)
    {
        $userParams = $request->getParsedBody();
        if (!isset($userParams["identity"])) {
            $user = $this->auth->attempt($userParams['email'], $userParams['password']);

            if ($user === 1) {
                return $response->withJson(
                    ['error' => false, "message" => "Invaild Cred"],
                    401
                );
            } elseif ($user === 2) {
                $user = $this->auth->createUserAdmin($userParams['email'], $userParams['password'], $userParams['name']);

                $user->token = $this->auth->generateToken($user);
                $user->password = '';
                return $response->withJson(['error' => false, 'message' => "User validate successfully",  'data' => $user]);
            } else {
                if (!$user->token = $this->auth->generateToken($user)) {
                }
                $user->password = '';
                return $response->withJson(['error' => false, 'message' => "User validate successfully",  'data' => $user]);
            }
        } elseif (isset($userParams["identity"]) && !empty($userParams["identity"])  &&  $userParams["identity"] === "student_login_send") {
            if ($user = $this->auth->attempt_for_student($userParams['email'], $userParams['name'], $userParams['password'])) {

                $user->token = $this->auth->generateToken($user);
                $user->password = '';
                return $response->withJson(['error' => false, 'message' => "User validate successfully",  'data' => $user]);
            } else {
                return $response->withJson(['error' => true], 401);
            }
        } elseif (isset($userParams["identity"]) && !empty($userParams["identity"])  &&  $userParams["identity"] === "teacher_login_send") {
            if ($user = $this->auth->attempt_for_teacher($userParams['email'], $userParams['name'], $userParams['password'])) {

                $user->token = $this->auth->generateToken($user);
                $user->password = '';
                return $response->withJson(['error' => false, 'message' => "User validate successfully",  'data' => $user]);
            } else {
                return $response->withJson(['error' => true], 401);
            }
            // echo "teacher_Login";
        } else {
            return $response->withJson(['error' => true], 401);
        }
    }
}
