<?php

namespace App\Http\Controllers;

enum PostSortType: int
{
    case Latest = 0;
    case Popular = 1;
}
