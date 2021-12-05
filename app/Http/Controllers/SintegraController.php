<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Nota;
use App\Models\User;

use Illuminate\Http\Request;
use stdClass;

class SintegraController extends Controller
{
    //
    public function index(Request $req){
        $user       = User::find(Auth::user()->id);
        $notas      = Nota::whereBetween('data', [date('Y-'.$req->mes.'-01'), date('Y-'.$req->mes.'-t')])->get();

        $txt = SintegraController::linha10($req, $user);

        $txt = SintegraController::linha11($txt, $user);

        foreach($notas as $nota){
            $txt = SintegraController::linha50($txt, $nota);
        }

        foreach($notas as $nota){
            $aux = explode('¬', $nota->produtos);
            $i = 0;
            foreach($aux as $a){
                $auxCodigo  = explode('¬', $nota->codigo);
                $auxQtd     = explode('¬', $nota->quantidade);
                $auxValor   = explode('¬', $nota->valor);

                $produto = new \stdClass();
                $produto->codigo        = str_pad($auxCodigo[$i] , 14 , 0 , STR_PAD_LEFT);
                $produto->quantidade    = intval($auxQtd[$i]);
                $produto->valor         = floatval($auxValor[$i]);
                $produto->numero        = str_pad(strval($i + 1) , 3 , 0 , STR_PAD_LEFT);

                $txt = SintegraController::linha54($txt, $produto, $nota);

                $i++;
            }
        }

        SintegraController::createFile($txt);

        return view('private.sintegra', ['txt' => $txt]);
    }

    public static function createFile($txt){
        $myfile = fopen(public_patH('sintegra/') . "sintegra.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $txt);
        fclose($myfile);
    }

    private static function linha10($req, $user){
        $inscricao =  str_pad(strval($user->inscricao) , 14 , ' ' , STR_PAD_RIGHT);

        $razao =  str_pad(strval($user->razao) , 35 , ' ' , STR_PAD_RIGHT);

        $cidade =  str_pad(strval($user->cidade) , 30 , ' ' , STR_PAD_RIGHT);

        $fax = "          ";

        $txt = "10".$user->cnpj.$inscricao.$razao.$cidade.$user->estado.$fax.
        date('01'.$req->mes.'Y').date('t'.$req->mes.'Y'). "111" . "\n";

        return $txt;
    }

    private static function linha11($txt, $user){
        $rua =  str_pad(strval($user->rua) , 34 , ' ' , STR_PAD_RIGHT);

        $numero =  str_pad(strval($user->numero) , 5 , 0 , STR_PAD_LEFT);

        $complemento =  str_pad(strval($user->complemento) , 22 , ' ' , STR_PAD_RIGHT);

        $bairro =  str_pad(strval($user->bairro) , 15 , ' ' , STR_PAD_RIGHT);

        $nome =  str_pad(strval($user->nomeContato) , 28 , ' ' , STR_PAD_RIGHT);

        $telefone =  str_pad(strval($user->telefoneContato) , 12 , 0 , STR_PAD_LEFT);

        $txt .= "11" . $rua . $numero . $complemento . $bairro . $user->cep .$nome . $telefone . "\n";

        return $txt;
    }

    private static function linha50($txt, $nota){
        $inscricao  =  str_pad(strval($nota->inscricao) , 14 , ' ' , STR_PAD_RIGHT);

        $serie      =  str_pad(strval($nota->serie) , 3 , ' ' , STR_PAD_RIGHT);

        $numero      =  str_pad(strval(substr($nota->numero, 0, 6)) , 6 , 0 , STR_PAD_LEFT);

        if($nota->entrada > 0){
            $valor      =  str_pad(strval(str_replace('.','',$nota->entrada)) , 13 , 0 , STR_PAD_LEFT);
        } else {
            $valor      =  str_pad(strval(str_replace('.','',$nota->saida)) , 13 , 0 , STR_PAD_LEFT);
        }

        $cfop = str_pad(strval($nota->cfop) , 4 , 0 , STR_PAD_LEFT);

        $base = str_pad(strval(str_replace('.','',$nota->baseIcms)) , 13 , 0 , STR_PAD_LEFT);

        $icms = str_pad(strval(str_replace('.','',$nota->icms)) , 13 , 0 , STR_PAD_LEFT);

        $tributado = str_pad(strval(str_replace('.','',$nota->valorTributado)) , 13 , 0 , STR_PAD_LEFT);
        

        $txt .= "50" . $nota->cnpj . $inscricao . date('Ymd', strtotime($nota->data)) . $nota->estado .
        $nota->modelo . $serie . $numero . $cfop . $nota->emitente . $valor . $base . $icms .
        $tributado . $nota->outras . $nota->aliquota . $nota->situacao . "\n";    

        return $txt;
    }

    private static function linha54($txt, $produto, $nota){
        $serie          =  str_pad(strval($nota->serie) , 3 , ' ' , STR_PAD_RIGHT);

        $numero         =  str_pad(strval(substr($nota->numero, 0, 6)) , 6 , 0 , STR_PAD_LEFT);

        $cfop           = str_pad(strval($nota->cfop) , 4 , 0 , STR_PAD_LEFT);

        $total          = str_pad(str_replace('.','',$produto->quantidade * $produto->valor), 12 , 0 , STR_PAD_LEFT);

        $quantidade     = str_pad(strval($produto->quantidade) , 11 , 0 , STR_PAD_LEFT);

        $desconto       =  str_pad(strval(str_replace('.','',$nota->desconto)) , 12 , 0 , STR_PAD_LEFT);

        $base           = str_pad(strval(str_replace('.','',$nota->baseIcms)) , 12 , 0 , STR_PAD_LEFT);

        $baseSt         = str_pad(strval(str_replace('.','',$nota->baseIcmsSt)) , 12 , 0 , STR_PAD_LEFT);

        $ipi            = str_pad(strval(str_replace('.','',$nota->ipi)) , 12 , 0 , STR_PAD_LEFT);

        $txt .= "54" .  $nota->cnpj . $nota->modelo . $serie . $numero . $cfop . "200" . $produto->numero . $produto->codigo . $quantidade . $total . $desconto . $base . $baseSt . $ipi . $nota->aliquota . "\n";

        return $txt;
    }
}
