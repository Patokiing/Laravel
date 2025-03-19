<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientesController extends Controller
{
    public function UsuariosC()
    {
        $clientes = User::where ('rol','C')->get();
        return $clientes;
    }

    public function UsuariosT()
    {
        $clientes = User::where ('rol','T')->get();
        return $clientes;
    }

    public function client($id)
    {
        $cliente = Clientes::find ( $id );
        return $cliente;
    }

    public function destroy($id)
    {
        $cliente = Clientes::find($id);
        $cliente->delete();

        return "OK";
    }

    public function store(Request $request)
    {
        if ($request->id != 0)
        {
            $clientes = Clientes::find($request->id);
        }
        else
        {
            $cliente = new Clientes();
        }


        $cliente->nombre = $request->nombre;
        $cliente->email = $request->email;
        $cliente->password =Hash::make ($request->password);
        $cliente->rfc = $request->rfc;
        $cliente->contacto = $request->contacto;
        $cliente->telefono_contacto = $request->telefono_contacto;
        $cliente->direccion = $request->direccion;
        $cliente->rol = 'C';

        $cliente->save();

        return "OK";
    }
}
