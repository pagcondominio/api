<?php
include "../app/variaveis.php";
include "../app/constants.php";
include "../app/conexao.php";
include "../app/function.php";
include "includes/variaveis.php";
include "includes/functions.php";

header('Content-type: application/json');
acessoAPI();
header($http." 500");
echo json_encode(array('aviso' => 'Erro Interno'));