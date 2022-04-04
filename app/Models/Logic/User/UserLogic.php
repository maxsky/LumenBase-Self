<?php

namespace App\Models\Logic\User;

use App\Models\Entities\User\User;

class UserLogic {

    /**
     * @param int $user_id
     *
     * @return User|null
     */
    public function getById(int $user_id): ?User {
        return User::find($user_id);
    }
}
