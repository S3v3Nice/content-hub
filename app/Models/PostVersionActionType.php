<?php

namespace App\Models;

enum PostVersionActionType: int
{
    case Submit = 0;
    case RequestChanges = 1;
    case Accept = 2;
    case Reject = 3;
    case AssignModerator = 4;
}
