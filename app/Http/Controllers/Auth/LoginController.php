<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller {

    public function __construct(Request $request) {
        parent::__construct($request);
    }

    /**
     * @param Request $request
     *
     * @return void
     * @throws ValidationException
     */
    public function login(Request $request) {
        $data = $this->validate($request, [
            'param1' => 'required|string'
        ]);
    }
}
