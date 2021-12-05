<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsuarioController extends Controller
{
    //
    public function index(){
        $usuarios = User::where('id', '!=', '')->paginate(5);

        return view('private.usuarios', ['usuarios' => $usuarios]);
    }

    public function delete($id){
        $user = User::findOrFail($id);
        $user->delete();

        return redirect('admin/usuarios');
    }

    public function view($id){
        $usuario = User::find($id);
        
        return view('private.usuario', ['usuario' => $usuario]);
    }

    public function update(Request $req){
        $usuario = User::find($req->id);
        $usuario->update([
            'cnpj'      => $req->cnpj,
            'razao'     => $req->razao,
            'nome'      => $req->nome,
            'login'     => $req->login,
            'email'     => $req->email,
            'permissao' => $req->permissao,
        ]);

        return redirect('admin/usuarios');
    }
}
