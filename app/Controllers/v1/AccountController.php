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

    //customer_buyer_add info
    public function customer_buyer_add(Request $req, Response $res)
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

        $res_data = $this->account->customerAdd($admin_id, $data);

        return $res->withJson($res_data);
    }

    //customer_buyer_edit info
    public function customer_buyer_edit(Request $req, Response $res)
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

        $res_data = $this->account->customerEdit($admin_id, $data);

        return $res->withJson($res_data);
    }

    //customer_buyer_dalate info
    public function customer_buyer_delete(Request $req, Response $res)
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
        $res_data = $this->account->customerDelete($admin_id, $data);

        return $res->withJson($res_data);
    }
    //user_list info
    public function user_list(Request $req, Response $res)
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

        $res_data = $this->account->userList($admin_id, $data);

        return $res->withJson($res_data);
    }

    //user_add info
    public function user_add(Request $req, Response $res)
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

        $res_data = $this->account->userAdd($admin_id, $data);

        return $res->withJson($res_data);
    }

    //user_edit info
    public function user_edit(Request $req, Response $res)
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

        $res_data = $this->account->userEdit($admin_id, $data);

        return $res->withJson($res_data);
    }

    //user_delete info
    public function user_delete(Request $req, Response $res)
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
        $res_data = $this->account->userDelete($admin_id, $data);

        return $res->withJson($res_data);
    }

    //settingsControl info
    public function settingsControl(Request $req, Response $res)
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
        $res_data = $this->account->settingsModel($admin_id, $data);

        return $res->withJson($res_data);
    }

    //products_list info
    public function products_list(Request $req, Response $res)
    {
        $requestUser = $this->auth->requestUser($req);
        $admin_id = $this->user->get_User_Id();
        if (is_null($requestUser)) {
            return $res->withJson([], 401);
        }
        if ($requestUser['id'] !== $admin_id) {
            return $res->withJson([], 401);
        }
        $res_data = $this->account->productslist($admin_id);

        return $res->withJson($res_data);
    }

    //products_add info
    public function products_add(Request $req, Response $res)
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
        $res_data = $this->account->productsAdd($admin_id, $data);

        return $res->withJson($res_data);
    }

    //products_edit info
    public function products_edit(Request $req, Response $res)
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
        $res_data = $this->account->productsEdit($admin_id, $data);

        return $res->withJson($res_data);
    }

    //products_import info
    public function products_import(Request $req, Response $res)
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
        foreach ($data['importData'] as $key) {
            $res_data = $this->account->productsAdd($admin_id, $key);
        }
        return $res->withJson($res_data);
    }
}
