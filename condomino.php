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
      $documento = limpanumero($body['documento']);
      $digitos  = quarteto($numero);
      valida("condomino", $body, $metodo);
      cpfOuCnpj($documento, "condominos");
      $documento = criptografar($documento);
      // if (!cpf($documento)) { header($http." 400"); die(json_encode(array('erro' => 'Documento invalido'))); }
      $sqlCond = $conexao->prepare("SELECT condominios.id,condominios.condominio,condominios.chave FROM condominios WHERE condominios.chave = '".$body["chave"]."' AND erp = ".$registro["id"]."");
      $sqlCond->execute();
      $resultadoC = $sqlCond->fetch();
      $numeroCond = $sqlCond->rowCount();
      if (!$numeroCond) { header($http." 400"); die(json_encode(array('aviso' => 'Erro ao acessar esse condominio.'))); }
      
      $sqlCondomino = $conexao->prepare("SELECT condominos.id,condominos.documento FROM condominos WHERE condominos.documento = '".$documento."' AND erp = ".$registro["id"]."");
      $sqlCondomino->execute();
      $resultadoCondomino = $sqlCond->fetch();
      $numeroCondomino = $sqlCondomino->rowCount();
      if ($numeroCondomino) { header($http." 400"); die(json_encode(array('aviso' => 'Ja existe um cadastro com esse documento'))); }
      
      try {
      $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
      $stmt = $conexao->prepare('INSERT INTO condominos (chave,nome,apartamento,bloco,complemento,documento,telefone,email,erp) VALUES(:chave,:nome,:apartamento,:bloco,:complemento,:documento,:telefone,:email,:erp)');
      $stmt->execute(array(
        ':chave' => $body['chave'],
        ':nome' => $body['nome'],
        ':apartamento' => $body['apartamento'],
        ':bloco' => $body['bloco'],
        ':complemento' => $body['complemento'],
        ':documento' => $documento,
        ':telefone' => criptografar($body['telefone']),
        ':email' => criptografar($body['email']),
        ':erp' => $registro["id"]
      ));
      header($http." 201");
      echo json_encode(array('sucesso' => true, 'mensagem' => 'Condomino cadastrado', 'chave' =>  $body['chave']));
    } catch(PDOException $e) {
      header($http." 500");
      echo json_encode(array('erro' => $e->getMessage()));
    }
    break;
  case "DELETE":
    validametodo("DELETE", $acao);
    if ($acao == "apagar" && $chave) {
      $sql = $conexao->prepare("DELETE FROM condominos WHERE condominos.id = '$chave' AND condominos.erp = ".$registro["id"]."");
      $sql->execute();
      $numero = $sql->rowCount();
      if ($numero) {
        header($http." 200");
        echo json_encode(array('sucesso' => true, 'mensagem' => 'Condomino apagado', 'numero' =>  $numero));
      } else {
        header($http." 400");
        echo json_encode(array('sucesso' => false, 'mensagem' => 'Condomino ja apagado ou nao encontrado', 'numero' =>  $numero));
      }
    } else if ($acao == "apagar" && !$chave)  {
      header($http." 500");
      echo json_encode(array('aviso' => 'Ocorreu um erro interno'));
    } else {
      header($http." 500");
      echo json_encode(array('aviso' => 'Ocorreu um erro interno'));
    }
    break;
  case "GET":
    validametodo("GET", $acao);
    if ($acao == "ver" && $chave) {
      $sql = $conexao->prepare("SELECT id,chave,nome,apartamento,bloco,complemento,documento,telefone,email,erp FROM condominos WHERE condominos.id = '$chave' AND condominos.erp = ".$registro["id"]."");
      $sql->execute();
      $condomino = $sql->fetchAll(PDO::FETCH_ASSOC);
      $numero = $sql->rowCount();
      // descriptografa dos dados do json com os dados abertos em fetchAll()
      $condomino[0]["documento"] = descriptografar($condomino[0]["documento"]);
      $condomino[0]["telefone"] = descriptografar($condomino[0]["telefone"]);
      $condomino[0]["email"] = descriptografar($condomino[0]["email"]);
      if ($numero) {
        header($http." 200");
        $condomino = json_encode($condomino); // a variável $resultado vira o json...
        echo trim($condomino, '[]'); //...para o trim retirar os colchetes
      } else {
        header($http." 400");
        echo json_encode(array('aviso' => 'Erro ao acessar esse condomino'));
      }
      // echo $numero ? json_encode($condomino) : json_encode(array('aviso' => 'Erro ao acessar esse condomino'));
    } else if ($acao == "listar") {
      $sql = $conexao->prepare("SELECT id,chave,nome,apartamento,bloco,complemento,erp FROM condominos WHERE condominos.erp = ".$registro["id"]."");
      $sql->execute();
      $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
      $json = json_encode($resultado);
      $numero = $sql->rowCount();
      if ($numero) {
        header($http." 200");
        echo $json;
      } else {
        header($http." 400");
        echo json_encode(array('aviso' => 'Erro ao acessar esse condomino'));
      }
      // echo $numero ? json_encode($resultado) : json_encode(array('aviso' => 'Erro ao acessar esse condomino'));
    } else if ($acao == "condominio") {
      $sql = $conexao->prepare("SELECT id,nome,apartamento,bloco,chave,erp FROM condominos WHERE condominos.chave = '$chave' AND condominos.erp = ".$registro["id"]."");
      $sql->execute();
      $condominos = $sql->fetchAll(PDO::FETCH_ASSOC);
      $numero = $sql->rowCount();
      if ($numero) {
        header($http." 200");
        $condominos = json_encode($condominos); 
        echo $condominos;
      } else {
        header($http." 400");
        echo json_encode(array('aviso' => 'Erro ao acessar esse condomino'));
      }
    } else if ($acao == "") {
      header($http." 405");
      echo json_encode(array('erro' => 'Metodo nao compativel...'));
    } else {
      header($http." 500");
      echo json_encode(array('aviso' => 'Ocorreu um erro interno'));
    }
    break;
  case "PUT":
    if ($acao == "alterar") {
      try {
        $chave = $body['chave'];
        $idJson = $body['id'];
        $documento = limpanumero($body['documento']);
        //busca ("condominio", $documento, $chave);
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
        $stmt = $conexao->prepare('UPDATE condominos SET nome = :nome, apartamento = :apartamento, bloco = :bloco, complemento = :complemento, telefone = :telefone, email = :email, documento = :documento WHERE chave = :chave AND id = :id');
        $stmt->execute(array(
          ':chave' => $chave,
          ':id' => $idJson,
          ':nome' => $body['nome'],
          ':apartamento' => $body['apartamento'],
          ':bloco' => $body['bloco'],
          ':complemento' => $body['complemento'],
          ':telefone' => $body['telefone'],
          ':email' => $body['email'],
          ':telefone' => $body['telefone'],
          ':documento' => $documento
        ));
        $numero = $stmt->rowCount();
          if ($numero) {
            header($http." 200");
            echo json_encode(array('sucesso' => true, 'mensagem' => 'Condomino Alterado', 'chave' =>  $chave));
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
    echo json_encode(array('erro' => 'Metodo nao compativel...'));
  }