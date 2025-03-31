<?php

namespace Modules\ExtraSecurity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ExtraSecurityController extends Controller
{
    public function getIp()
    {
        return view('extrasecurity::get_ip', [
            'ip' => \Helper::getClientIp(),
        ]);
    }
}
