<?php

namespace App\Helpers;

class JsonResponse
{
    static function success($msg,$data, $code = 200)
    {
        return response()->json(['success' => true, 'msg' => $msg,'data' => $data],$code);
    }

    static function error($msg,$code = 400){
        return response()->json(['success' => false, 'msg' => $msg],$code);
    }
}
