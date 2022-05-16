<?php

namespace App\Services\Auth;

use App\Models\UserMapper;
use DateTime;
use Firebase\JWT\JWT;
use Slim\Collection;
use Slim\Http\Request;

class AuthService
{
    const SUBJECT_IDENTIFIER = 'username';

    private $userMapper;

    private $appConfig;



    /**
     * AuthService Constructor
     */

    public function __construct(UserMapper $db, Collection $appConfig)
    {
        $this->userMapper = $db;
        $this->appConfig = $appConfig;
    }

    /**
     * Generate a new JWT token
     *
     * @param \Conduit\Models\User $user
     *
     * @return string
     * @internal param string $subjectIdentifier The username of the subject user.
     *
     */
    public function generateToken($user)
    {
        $now = new DateTime();
        $future = new DateTime("now +1 hours");


        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $future->getTimeStamp(),
            "jti" => base64_encode(random_bytes(16)),
            'iss' => $this->appConfig['app']['url'],  // Issuer
            "sub" => $user->id
        ];
        if ($this->setToken($user->id, $payload['jti'])) {
            $secret = $this->appConfig['jwt']['secret'];
            $token = JWT::encode($payload, $secret, "HS256");
            return $token;
        }
        return false;
    }

    private function setToken($u_id, $jti)
    {
        if ($this->userMapper->setToken_by_id($u_id, $jti)) {
            return true;
        }

        return false;
    }

    private function getToken($u_id)
    {
        if ($ret = $this->userMapper->getToken_by_id($u_id)) {
            return $ret;
        }

        return false;
    }


    public function attempt($email, $password)
    {

        if (!$user = $this->userMapper->findByEmail($email)) {
            return false;
        }


        if (md5($password) == $user->password) {
            return $user;
        }

        return false;
    }

    public function attempt_for_student($academic_id, $student_id, $dob)
    {

        if (!$user = $this->userMapper->findByStudent_id($academic_id, $student_id, $dob)) {
            return false;
        } else {
            return $user;
        }

        return false;
    }

    public function attempt_for_teacher($teacher_id, $teacher_name, $teacher_num)
    {
        if (!$user = $this->userMapper->findByteacher_id($teacher_id, $teacher_name, $teacher_num)) {
            return false;
        } else {
            return $user;
        }

        return false;
    }

    public function createUserAdmin($email, $password, $name)
    {

        if (!$this->userMapper->createAdminmain($email, $password, $name)) {
            return false;
        } else {
            $user = $this->userMapper->findByEmail($email);
        }


        if (md5($password) == $user->password) {
            return $user;
        }

        return false;
    }

    public function requestUser(Request $request)
    {
        // Should add more validation to the present and validity of the token?
        if ($token = $request->getAttribute('token')) {

            if ($this->getToken($token->sub)['token'] === $token->jti) {
                $userid = $this->userMapper->loadById($token->sub);
                $this->userMapper->set_User_Id($userid['id']);

                return $userid;
            }
        }
        return false;
    }
}
