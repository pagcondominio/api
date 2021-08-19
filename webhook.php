<?php
// ini_set('display_errors',1);
// ini_set('display_startup_erros',1);
// error_reporting(E_ALL);
include "../app/variaveis.php";
include "../app/constants.php";
include "../app/conexao.php";
include "../app/function.php";
include "includes/variaveis.php";
include "includes/functions.php";

header('Content-type: application/json');
date_default_timezone_set('America/Sao_Paulo');

acessoAPI();
urlAPI();

$pagamento    = $urlapi[3];
$pagamentoac  = array("boleto", "cartao", "pix", "debito");
$token        = $urlapi[4];


if ($metodo == "GET") {
    header("Content-Type:application/json");

    include "includes/curlconsulta.php"; //Conversa com a API do Boleto

    // echo in_array($pagamento, $pagamentoac) ? "" : die(json_encode(array('aviso' => 'Meio de pagamento nao aceito')));
    if (!in_array($pagamento, $pagamentoac)) {header($http." 400");die(json_encode(array('aviso' => 'Meio de pagamento nao aceito')));}
    if (!$token) { die(json_encode(array('aviso' => 'Sem Token complica...'))); }

    switch ($pagamento) {
        case "boleto":
            webhook ($pagamento, $token);
            break;
        case "cartao":
            webhook ($pagamento, $token);
            break;
        case "pix":
            die(json_encode(array('aviso' => 'PIX em breve')));
            break;
        case "debito":
            die(json_encode(array('aviso' => 'Debito em breve')));
            break;
    }

} else {
    header($http." 405");
    die( json_encode(array('aviso' => 'Metodo nao compativel.')) );
}


function webhook ($pagamento, $token) {
    global $http;
    $status     = "";
    $buscatoken = "231323123213213231244312";
    $numero     = "0";

    header($http." 200");
    
        if ($numero) {
            $response['status'] = true;
            $response['pagamento'] = $pagamento;
            $response['token'] = $token;
            $response['message'] = "Pagamento efetuado";
        } else {
            $response['status'] = false;
            $response['pagamento'] = $pagamento;
            $response['token'] = $token;
            $response['message'] = "Pagamento pendente";
        }

    $json = json_encode($response);
    echo $json;
}