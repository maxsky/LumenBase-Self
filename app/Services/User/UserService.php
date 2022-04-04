<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 4/4/2022
 * Time: 4:48 PM
 */

namespace App\Services\User;

use App\Models\Entities\User\User;
use App\Models\Logic\User\UserLogic;

class UserService {

    private UserLogic $userLogic;

    public function __construct(UserLogic $userLogic) {
        $this->userLogic = $userLogic;
    }

    /**
     * @param int $user_id
     *
     * @return User|null
     */
    public function getUserById(int $user_id): ?User {
        return $this->userLogic->getById($user_id);
    }
}
