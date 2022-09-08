<?php

namespace Geekbrains\App\Http\Auth;

use GeekBrains\App\Blog\User;
use GeekBrains\App\Http\Request;

interface IdentificationInterface
{
    public function user(Request $request): User;
}