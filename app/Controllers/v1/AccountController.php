<?php

namespace App\Controllers\v1;

use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;
use App\Models\UserMapper;
use App\Models\Account;
use RR\Shunt\Parser;
use RR\Shunt\Context;
use vermotr\Math\Matrix;
use DateTime;
use RecursiveIteratorIterator;
use RecursiveArrayIterator;

class AccountController
{

    protected $auth;
    protected $account;
    protected $user;

    public function __construct(ContainerInterface $container, Account $accountModel, UserMapper $userModel)
    {
        $this->auth = $container->get('auth');
        $this->account = $accountModel;
        $this->user = $userModel;
    }

    public function index(Request $request)
    {
    }

    public function tokenCheckValidity(Request $req, Response $res)
    {
        $requestUser = $this->auth->requestUser($req);
        $userID = $this->user->get_User_Id();
        if (!$requestUser) {
            $data = [
                "error" => true,
                "Message"   => "Expired token"
            ];
            return $res->withJson($data, 401);
        }
        if ($requestUser['id'] !== $userID) {
            return $res->withJson([], 401);
        }

        $res_data = array(
            "error" => false,
            "msg"   => "token Not expired"
        );
        return $res->withJson($res_data);
    }

    //Customer_buyerListr info
    public function customer_buyer_list(Request $req, Response $res)
    {
        $requestUser = $this->auth->requestUser($req);
        $admin_id = $this->user->get_User_Id();
        if (is_null($requestUser)) {
            return $res->withJson([], 401);
        }
        if ($requestUser['id'] !== $admin_id) {
            return $res->withJson([], 401);
        }
        $data = $req->getParsedBody();

        $res_data = $this->account->customerList($admin_id, $data);

        return $res->withJson($res_data);
    }
}
