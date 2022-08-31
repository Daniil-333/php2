<?php

namespace Geekbrains\App\Http\Actions;

use Geekbrains\App\http\Request;
use Geekbrains\App\http\Response;

interface ActionInterface
{
    public function handle(Request $request): Response;
}