<?php

namespace Geekbrains\App\UnitTests\Blog\Container;

class ClassDependingOnAnother
{
    // Класс с двумя зависимостями
    public function __construct(
        private SomeClassWithoutDependencies $one,
        private SomeClassWithParameter $two,
    ) {
    }
}