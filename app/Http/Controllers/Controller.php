<?php

namespace App\Http\Controllers;

use App\Models\t_User;

abstract class Controller
{
    protected function currentUser(): ?t_User
    {
        $user = auth()->user();
        return $user instanceof t_User ? $user : null;
    }
}
