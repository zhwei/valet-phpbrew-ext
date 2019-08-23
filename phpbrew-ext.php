<?php

\Illuminate\Container\Container::getInstance()
    ->make(\Zhwei\ValetPhpBrewExt\CommandRegister::class)
    ->register($app);
