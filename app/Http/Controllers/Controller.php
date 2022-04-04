<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController {

    protected int $user_id = 0;

    public function __construct(Request $request) {
        $user = $request->user();

        if ($user) {
            $this->user_id = $user->id;
        }
    }
}
