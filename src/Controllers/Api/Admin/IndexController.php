<?php

namespace Jxgame\Aibisai\Controllers\Api\Admin;

use Jxgame\Aibisai\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class IndexController extends Controller
{
    public function __construct()
    {
     //return Carbon::now()->toDateTimeString();
    }

	/** init*/
    public function init(Request $request)
    {
		return $this->ok("This is Init");
	}
}