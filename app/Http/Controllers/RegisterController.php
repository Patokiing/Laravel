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
    public function update(Request $req, $id)
    {
        $user = User::findOrFail($id);
        $user->name = $req->name;
        $user->email = $req->email;
        if ($req->password) {
            $user->password = Hash::make($req->password);
        }
        $user->rfc = $req->rfc;
        $user->contacto = $req->contacto;
        $user->telefono_contacto = $req->telefono_contacto;
        $user->direccion = $req->direccion;
        $user->rol = $req->rol;
        $user->save();

        return response()->json(['message' => 'User updated successfully']);
    }
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
