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
    
    public function __construct(UserMapper $db, Collection $appConfig) {
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
        $future = new DateTime("now +2 hours");

        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $future->getTimeStamp(),
            "jti" => base64_encode(random_bytes(16)),
            'iss' => $this->appConfig['app']['url'],  // Issuer
            "sub" => $user->id
        ];

        $secret = $this->appConfig['jwt']['secret'];
        $token = JWT::encode($payload, $secret, "HS256");

        return $token;
    }

    /**
     * Attempt to find the user based on email and verify password
     *
     * @param $email
     * @param $password
     *
     * @return bool|\Conduit\Models\User
     */
    public function attempt($email, $password)
    {
        
        if ( ! $user = $this->userMapper->findByEmail($email)) {
            return false;
        }
        
        
        if(md5($password) == $user->password){
            return $user;
        }
        /**
        if (password_verify($password, $user->hash)) {
            return $user;
        }
         * 
         */

        return false;
    }

    /**
     * Retrieve a user by the JWT token from the request
     *
     * @param \Slim\Http\Request $request
     *
     * @return User|null
     */
    public function requestUser(Request $request)
    {
        // Should add more validation to the present and validity of the token?
        
        if ($token = $request->getAttribute('token')) {
            //return User::where(static::SUBJECT_IDENTIFIER, '=', $token->sub)->first();
            $userid = $this->userMapper->loadById($token->sub);
            return $userid;
            //return $token;
        }
    }
}