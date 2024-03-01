<?php

namespace App\Models;

enum UserRole: int
{
    case User = 0;
    case Moderator = 1;
    case Admin = 2;
}
