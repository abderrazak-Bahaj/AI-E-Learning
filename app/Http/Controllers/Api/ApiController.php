<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Traits\HasPagination;

abstract class ApiController extends Controller
{
    use ApiResponse;
    use HasPagination;
}
