<?php

namespace App\Helpers;

class FormHelper
{
    public static function preencherCampoSeTiver($request, $campo, $objeto)
    {
        if ($request->filled($campo)) {
            $objeto->$campo = $request->$campo;
        }
    }
}
