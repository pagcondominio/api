<?php
include "../app/variaveis.php";
include "../app/constants.php";
include "../app/conexao.php";
include "../app/function.php";
include "includes/variaveis.php";
include "includes/functions.php";

header('Content-type: application/json');

acessoAPI();
urlAPI();

$jsonBody = file_get_contents('php://input');
$body = json_decode($jsonBody, true);

switch ($metodo) {
  case "POST":
    validametodo("POST", $acao);
    $cep = limpanumero($body['cep']);
    $documento = limpanumero($body['documento']);
    // valida("condominio", $body, $metodo);
    busca ("condominio", $documento);
    cpfOuCnpj($documento, "condominios");
    documentoverifica($documento, "condominios");
    $documento = criptografar($documento);
    // if (!cnpj($documento)) { header($http." 400"); die(json_encode(array('erro' => 'Documento invalido'))); }

    $sqlCond = $conexao->prepare("SELECT condominios.id,condominios.condominio,condominios.documento FROM condominios WHERE condominios.documento = '".$documento."' AND erp = ".$registro["id"]."");
    $sqlCond->execute();
    $resultadoC = $sqlCond->fetch();
    $numeroCond = $sqlCond->rowCount();
    if ($numeroCond) { header($http." 400"); die(json_encode(array('aviso' => 'Ja existe um condominio com esse documento.'))); }
    // die($documento.$numeroCond);
    try {
    $chave = chave();
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
    $stmt = $conexao->prepare('INSERT INTO condominios (chave,cep,logradouro,numero,bairro,cidade,estado,condominio,telefone,documento,erp,banco,agencia,conta) VALUES(:chave,:cep,:logradouro,:numero,:bairro,:cidade,:estado,:condominio,:telefone,:documento,:erp,:banco,:agencia,:conta)');
    $stmt->execute(array(
      ':chave' => $chave,
      ':cep' => limpanumero($body['cep']),
      ':logradouro' => $body['logradouro'],
      ':numero' => $body['numero'],
      ':bairro' => $body['bairro'],
      ':cidade' => $body['cidade'],
      ':estado' => $body['estado'],
      ':condominio' => $body['condominio'],
      ':telefone' => $body['telefone'],
      ':documento' => $documento,
      ':banco' => $body['banco'],
      ':agencia' => $body['agencia'],
      ':conta' => $body['conta'],
      ':erp' => $registro["id"]
    ));
      header($http." 201");
      echo json_encode(array('sucesso' => true, 'mensagem' => 'Condominio cadastrado', 'chave' =>  $chave));
    } catch(PDOException $e) {
      header($http." 500");
      echo json_encode(array('erro' => $e->getMessage()));
    }
    break;
  case "DELETE":
    validametodo("DELETE", $acao);
    if ($acao == "apagar" && $chave) {
      $sql = $conexao->prepare("DELETE FROM condominios WHERE condominios.chave = '$chave' AND condominios.erp = ".$registro["id"]."");
      $sql->execute();
      $numero = $sql->rowCount();
      if ($numero) {
        header($http." 200");
        echo json_encode(array('sucesso' => true, 'mensagem' => 'Condominio apagado', 'registros' =>  $numero));
      } else {
        header($http." 400");
        echo json_encode(array('sucesso' => false, 'mensagem' => 'Condominio ja apagado ou nao encontrado', 'registros' =>  $numero));
      }
    } else{
      header($http." 500");
      echo json_encode(array('aviso' => 'Ocorreu um erro interno'));
    }
    break;
  case "GET":
    validametodo("GET", $acao);
    if ($acao == "ver" && $chave) {
      $sql = $conexao->prepare("SELECT id,condominio,cep,logradouro,numero,bairro,cidade,estado,chave FROM condominios WHERE condominios.chave = '$chave' AND erp = ".$registro["id"]."");
      $sql->execute();
      $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
      $numero = $sql->rowCount();
      if ($numero) {
        header($http." 200");
        $resultado = json_encode($resultado); // a variável $resultado vira o json...
        echo trim($resultado, '[]'); //...para o trim retirar os colchetes
      } else {
        header($http." 404");
        echo json_encode(array('aviso' => 'Erro ao acessar esse condominio'));
      }
      // echo $numero ? json_encode($resultado) : json_encode(array('aviso' => 'Erro ao acessar esse condominio'));
    } else if ($acao == "listar") {
      $sql = $conexao->prepare("SELECT id,condominio,cep,logradouro,numero,bairro,cidade,estado,chave FROM condominios WHERE erp = ".$registro["id"]."");
      $sql->execute();
      $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
      $json = json_encode($resultado);
      $numero = $sql->rowCount();
      if ($numero) {
        header($http." 200");
        echo $json;
      } else {
        header($http." 400");
        echo json_encode(array('aviso' => 'Sem condominios registrados'));
      }
      echo $numero ? $json : json_encode(array('aviso' => 'Sem condominios registrados'));
    } else if ($acao == "") {
      header($http." 405");
      echo ($verbo == "POST") ? "" : die(json_encode(array('erro' => 'Metodo nao compativel. Use POST.')));
    } else {
      header($http." 500");
      echo json_encode(array('aviso' => 'Ocorreu um erro interno'));
    }
    break;
  case "PUT":
    if ($acao == "alterar") {
      validametodo("PUT", $acao);
      $cep = limpanumero($body['cep']);
      $documento = limpanumero($body['documento']);
      valida("condominio", $body, $metodo);
      if (!cnpj($documento)) { header($http." 400"); die(json_encode(array('erro' => 'Documento invalido'))); }
      try {
      $chave = $body['chave'];
      //busca ("condominio", $documento, $chave);
      $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
      $stmt = $conexao->prepare('UPDATE condominios SET condominio = :condominio, cep = :cep, logradouro = :logradouro, numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, telefone = :telefone, documento = :documento, banco = :banco, agencia = :agencia, conta = :conta WHERE chave = :chave');
      $stmt->execute(array(
        ':chave' => $chave,
        ':condominio' => $body['condominio'],
        ':cep' => limpanumero($body['cep']),
        ':logradouro' => $body['logradouro'],
        ':numero' => $body['numero'],
        ':bairro' => $body['bairro'],
        ':cidade' => $body['cidade'],
        ':estado' => $body['estado'],
        ':telefone' => $body['telefone'],
        ':documento' => $documento,
        ':banco' => $body['banco'],
        ':agencia' => $body['agencia'],
        ':conta' => $body['conta']
      ));
      $numero = $stmt->rowCount();
        if ($numero) {
          header($http." 200");
          echo json_encode(array('sucesso' => true, 'mensagem' => 'Condominio Alterado', 'chave' =>  $chave));
        } else {
          header($http." 400");
          echo json_encode(array('sucesso' => false, 'mensagem' => 'Ocorreu um erro ou nao há o que alterar em sua requisicao'));
        }
      } catch(PDOException $e) {
        header($http." 500");
        echo json_encode(array('erro' => $e->getMessage()));
      }
    }
    break;
  default:
    header($http." 405");
    echo ($verbo == "POST") ? "" : die(json_encode(array('erro' => 'Metodo nao compativel.')));
}