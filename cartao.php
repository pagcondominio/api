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

$cartao = "anser";
$jsonBody = file_get_contents('php://input');
$body = json_decode($jsonBody, true);

switch ($metodo) {
  case "POST":
    validametodo("POST", $acao);
    if (empty($acao)) {
      $erp = $registro["id"];
      $numero = limpanumero($body['numero']);
      $documento = limpanumero($body['documento']);
      $digitos  = quarteto($numero);
      valida("cartao", $body, $metodo);
      $documento = criptografar($documento);

					/*Verifica se o documento está cadastradpo no nosso banco de dados*/
					$buscacond = $conexao->prepare("SELECT id,erp,chave FROM condominios WHERE chave = '".$body['chave']."'");
					$buscacond->execute();
          $resultadobuscacond = $buscacond->fetch();
					$numerobuscacond = $buscacond->rowCount();
					echo $numerobuscacond ? "" : die(json_encode(array('aviso' => 'Nao existe um condomino cadastrado com essa chave')));
            /*Verifica se o documento está cadastradpo no nosso banco de dados*/
            $doccond = $conexao->prepare("SELECT id,nome,documento FROM condominos WHERE documento = '$documento'");
            $doccond->execute();
            $resultadocond = $doccond->fetch();
            $numerocond = $doccond->rowCount();
            echo $numerocond ? "" : die(json_encode(array('aviso' => 'Nao existe um condomino cadastrado com esse documento')));
            if ($body['idcondomino'] <> $resultadocond["id"]) {
              die(json_encode(array('aviso' => 'Id diferente do documento')));
            }
            /*Verifica se o documento está cadastradpo no nosso banco de dados*/
            /*Verifica se tem 2 cartoes no banco de dados*/
            $simples = $conexao->prepare("SELECT id,idcondomino,erp,documento,estado FROM cartao WHERE documento = '$documento' AND (erp = '$erp' AND estado = '1')");
            $simples->execute();
            $numero = $simples->rowCount();
            // die($numero);
            if ($numero >= 2) {
              header($http." 200");
              die(json_encode(array('aviso' => 'Limite de 2 cartoes atingido')));
            }
            /*Verifica se tem 2 cartoes no banco de dados*/
          try {
          $token = chave();
          $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
          $stmt = $conexao->prepare('INSERT INTO cartao (token,chave,idcondomino,documento,numero,digitos,nome,bandeira,cvv,validade,erp) VALUES(:token,:chave,:idcondomino,:documento,:numero,:digitos,:nome,:bandeira,:cvv,:validade,:erp)');
          $stmt->execute(array(
            ':token' => $token,
            ':chave' => $body['chave'],
            ':idcondomino' => $body['idcondomino'],
            ':documento' => $documento,
            ':numero' => criptografar($numero),
            ':digitos' => $digitos,
            ':nome' => $body['nome'],
            ':bandeira' => $body['bandeira'],
            ':cvv' => criptografar($body['cvv']),
            ':validade' => $body['validade'],
            ':erp' => $registro["id"]
          ));
          header($http." 201");
          echo json_encode(array('sucesso' => true, 'mensagem' => 'Cartao cadastrado', 'token' =>  $token));
        } catch(PDOException $e) {
          header($http." 500");
          echo json_encode(array('erro' => 'Ocorreu um erro ao inserir: '.$e->getMessage()));
        }
    } else if ($acao == "pagamento") {
      $status = include "includes/curlcartaostatus.php";
      echo $resposta ? "" : json_encode(array('aviso' => 'Sistema de pagamento fora do ar'));
      include "includes/curlcartaocadastro.php";
      include "includes/curlcartao.php";
    } else {
      header($http." 500");
      echo json_encode(array('aviso' => 'Ocorreu um erro interno'));
    }
    break;
  case "DELETE":
    validametodo("DELETE", $acao);
    if ($acao == "apagar" && $token) {
      $sql = $conexao->prepare("DELETE FROM cartao WHERE cartao.token = '$token' AND cartao.erp = ".$registro["id"]."");
      // $sql = $conexao->prepare("DELETE FROM cartao WHERE cartao.token = '$token'");
      $sql->execute();
      $numero = $sql->rowCount();
      if ($numero) {
        header($http." 200");
        echo json_encode(array('sucesso' => true, 'mensagem' => 'Cartao apagado'));
      } else {
        header($http." 400");
        echo json_encode(array('sucesso' => false, 'mensagem' => 'Cartao ja apagado ou nao encontrado', 'numero' =>  $numero));
      }
      // echo $numero ? json_encode(array('sucesso' => true, 'mensagem' => 'Cartao apagado', 'numero' =>  $numero)) : json_encode(array('sucesso' => false, 'mensagem' => 'Cartao ja apagado ou nao encontrado', 'numero' =>  $numero));
    } else {
      header($http." 500");
      echo json_encode(array('aviso' => 'Ocorreu um erro interno'));
    }
    break;
  case "GET":
    validametodo("GET", $acao);
    if ($acao == "ver" && $token) {
      $sql = $conexao->prepare("SELECT cartao.token,cartao.chave,cartao.idcondomino,cartao.bandeira,cartao.digitos,cartao.nome,cartao.validade FROM cartao WHERE cartao.token = '$token' AND cartao.erp = ".$registro["id"]."");
      $sql->execute();
      $cartao = $sql->fetchAll(PDO::FETCH_ASSOC);
      $numero = $sql->rowCount();
      if ($numero) {
        header($http." 200");
        // echo json_encode($cartao);
        $cartao = json_encode($cartao); // a variável $resultado vira o json...
        echo trim($cartao, '[]'); //...para o trim retirar os colchetes
      } else {
        header($http." 400");
        echo json_encode(array('aviso' => 'Erro ao acessar esse cartao'));
      }
      // echo $numero ? json_encode($cartao) : json_encode(array('aviso' => 'Erro ao acessar esse cartao'));
    } else if ($acao == "listar") {
      $sql = $conexao->prepare("SELECT cartao.documento,cartao.id,cartao.token,cartao.chave,cartao.idcondomino,cartao.bandeira,cartao.digitos,cartao.nome,cartao.validade FROM cartao WHERE cartao.erp = ".$registro["id"]."");
      $sql->execute();
      $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
      $numero = $sql->rowCount();
      if ($numero) {
        header($http." 200");
        echo json_encode($resultado);
      } else {
        header($http." 400");
        echo json_encode(array('aviso' => 'Erro ao acessar esse cartao'));
      }
      // echo $numero ? json_encode($resultado) : json_encode(array('aviso' => 'Erro ao acessar esse cartao'));
    } else if ($acao == "consulta") {
      // include "includes/curlcartaoconsulta.php";
      header($http." 200");
      echo json_encode(array('aviso' => 'Consulta de pagamento de cartao'));
    } else if ($acao == "") {
      header($http." 405");
      echo json_encode(array('erro' => 'Metodo nao compativel...'));
    } else {
      header($http." 500");
      echo json_encode(array('aviso' => 'Ocorreu um erro interno'));
    }
    break;
  default:
    header($http." 405");
    echo json_encode(array('erro' => 'Metodo nao compativel.'));
}