<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request){
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password]))
        {
            $user = Auth ::user();
            $token = $user->createToken('app')->plainTextToken;
            $arr =array('acceso'=>'OK',
                        'error' => "",
                         'token'=>$token,
                        'idUsuario'=>$user ->id,
                        'nombreUsuario'=> $user->name);
            return json_encode($arr);
        }
        else{
            $arr =array('acceso'=> "",'token' => "",
                           'error' => "No exite el usuario o la contraseña",
                           'idUsuario' =>0,
                           'nombreUsuario' => '');
            return json_encode($arr);
        }
    }
}