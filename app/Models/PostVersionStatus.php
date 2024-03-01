<?php

namespace App\Models;

enum PostVersionStatus: int
{
    case Draft = 0;
    case Pending = 1;
    case Accepted = 2;
    case Rejected = 3;
}
