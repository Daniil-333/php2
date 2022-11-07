<?php

namespace Geekbrains\App\Http\Auth;

use GeekBrains\App\Blog\User;
use GeekBrains\App\Http\Request;

interface AuthenticationInterface
{
    public function user(Request $request): User;
}