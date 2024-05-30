<?php

namespace App\Http\Controllers;

enum PostLoadPeriod: int
{
    case Day = 0;
    case Week = 1;
    case Month = 2;
    case Year = 3;
    case AllTime = 4;
}
