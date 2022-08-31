<?php

namespace Geekbrains\App\Http\Actions;

use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\Response;

interface ActionInterface
{
    public function handle(Request $request): Response;
}