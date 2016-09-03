<?php

namespace Phphub\Creators;

use Phphub\Listeners\UserCreatorListener;
use App\Models\User;

/**
* This class can call the following methods on the observer object:
*
* userValidationError($errors)
* userCreated($user)
*/
class UserCreator
{
    protected $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel  = $userModel;
    }

    public function create(UserCreatorListener $observer, $data)
    {
        //return $data;
        //{"image_url":"http:\/\/q.qlogo.cn\/qqapp\/101267475\/26149077B50C6D5B2A6D9B43794EE569\/100","name":"66777","email":"777777@777.com","register_source":"qq"}
        $user = User::create($data);
        if (! $user) {
            return $observer->userValidationError($user->getErrors());
        }
        $user->cacheAvatar();
        return $observer->userCreated($user);
    }
}
