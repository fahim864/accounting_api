<?php

namespace App\Transformers;

use App\Models\UserMapper;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform (UserMapper $user)
    {
        return [
            
            'id'        => (int)$user->id,
            'email'     => $user->email,
            'password'  => $user->password,
            'token'     => $user->token
                
        ];
    }
}