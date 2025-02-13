<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

class RegisterController extends Controller
{
    public function register (Request $req)
    {
        $user = new User();
        $user->name = $req->username;
        $user->email = $req->email;
        $user->password = Hash::make($req->password);
        $user->rfc = $req->rfc;
        $user->contacto = $req->contacto;
        $user->telefono_contacto = $req->telefono_contacto;
        $user->direccion = $req->direccion;
        $user->rol = $req->rol;
        $user->save();

        return 'Ok';
    }
}
