<?php
include "../app/variaveis.php";
include "../app/constants.php";
include "../app/conexao.php";
include "../app/function.php";
include "includes/variaveis.php";
include "includes/functions.php";

urlAPI();
acessoAPI();

header('Content-type: application/json');
$jsonBody = file_get_contents('php://input');
$body = json_decode($jsonBody, true);

echo json_encode($body);

switch ($metodo) {
    case "POST":
        //echo "Postando";
        echo $body["consumidor"]["nome"];
    break;
    case "GET":
        //echo "Getout!";
    break;
    default:
    echo json_encode(array('aviso' => 'Sem método.'));
}