<?php

namespace App\Http\Controllers;

enum UserRole: int
{
    case User = 0;
    case Moder = 1;
    case Admin = 2;
}
