<?php

namespace App\Models;

enum PostVersionActionType: int
{
    case Submit = 0;
    case ChangeRequest = 1;
    case Accept = 2;
    case Reject = 3;
    case ModeratorAssignment = 4;
}
