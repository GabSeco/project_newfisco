<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    //
    public function create(request $request){
        User::create([
            'cnpj'      => str_replace(['.', '/', '-'],'',$request->cnpj),
            'razao'     => $request->razao,
            'nome'      => $request->nome,
            'email'     => $request->email,
            'login'     => $request->login,
            'password'  => Hash::make($request->senha),
            'permissao' => 1,
        ]);

        return redirect('login');
    }

    public function logar(request $request){
        $bool = Auth::attempt([
            'login'     => $request->login,
            'password'  => $request->password,
        ]);

        if($bool){
            return redirect('admin');
        }

        return redirect('login');
    }

    public function logout(){
        Auth::logout();

        return redirect('login');
    }

    
}
