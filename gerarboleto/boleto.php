<?php
// Projeto BoletoPhp: www.boletophp.com.br

// ------------------------- DADOS DINAMICOS DO SEU CLIENTE PARA A GERACAO DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formulario c/ POST, GET ou de BD (MySql,Postgre,etc)	//

//Pega os dados no sistema do Pag Condominio
include "../../app/variaveis.php";
include "../../app/constants.php";
include "../../app/conexao.php";
include "../../app/function.php";
include "../includes/variaveis.php";
include "../includes/functions.php";

$pdf = explode("/", $url);
validaboleto($pdf[3]);

switch ($pdf[4]) {
    case "pdf":
        include("../../assets/mpdf60/mpdf.php");

        $doc = new DOMDocument();
        $doc->loadHTMLFile("https://api.pagcondominio.com/gerarboleto/boleto/".$pdf[3]."");
        
        $html = $doc->saveHTML();
        $html = utf8_encode($html);
        
        $mpdf=new mPDF(); 
        $mpdf->SetDisplayMode('fullpage');
        $css = file_get_contents("../../assets/mpdf60/css/estilo.css");
        $mpdf->WriteHTML($css,1);
        $mpdf->WriteHTML($html);
        $mpdf->Output();
        exit;
        break;
    case "download":
        echo "download";
        //die();
        break;
    case "teste":
        //echo "Teste";
        //$sql = $conexao->prepare("SELECT boleto.id,boleto.interno,boleto.body FROM boleto WHERE boleto.id = '$a'");
        break;
    default:
        //echo "Na tela!";
        $sql = $conexao->prepare("SELECT boleto.id,boleto.interno,boleto.body FROM boleto WHERE boleto.interno = '$pdf[3]'");
}

//$sql = $conexao->prepare("SELECT * FROM boleto WHERE boleto.id = '$a'");
$sql->execute();
$boleto = $sql->fetch();
$numero = $sql->rowCount();
echo $numero ? "" : die("<h1>Boleto não encontrado</h1>");
//echo $boleto["body"];
$body = json_decode($boleto["body"], true);
//echo $body["consumidor"]["nome"];
//echo $body["pagamento"]["descricao"];
//echo '<hr>';

$numero_documento = str_pad($boleto["id"], 12, 0, STR_PAD_LEFT);

//Soma os valores e multiplica a quantidade informada no boleto
foreach ($body['produtos'] as $valores){
    $valor += $valores["valor"] * $valores["quantidade"];
}

//Lista as mensagens
foreach ($body['pagamento']['mensagens'] as $mensagem){
    $mensagens = $mensagem. "\n";
}

// DADOS DO BOLETO PARA O SEU CLIENTE
$dias_de_prazo_para_pagamento = 5;
$taxa_boleto = $splitvalor;
// $data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
$data_venc = $body["pagamento"]["vencimento"]; 
$valor_cobrado = $valor + $taxa_boleto; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(",", ".",$valor_cobrado);
$valor_boleto = number_format($valor_cobrado, 2, ',', '');

include "include/curl.php";

$dadosboleto["nosso_numero"] = $bodycurl["ResponseDetail"]["IdTransaction"];  // Nosso numero sem o DV - REGRA: Maximo de 7 caracteres!
$dadosboleto["numero_documento"] = $numero_documento;	// Num do pedido ou nosso numero
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissao do Boleto
$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com virgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"]     = $body["consumidor"]["nome"];
$dadosboleto["condominio"] = $body["condominionome"];
$dadosboleto["endereco1"]  = $body["consumidor"]["endereco"]["logradouro"].", ".$body["consumidor"]["endereco"]["numero"]." ".$body["consumidor"]["endereco"]["bairro"]." - ".$body["consumidor"]["endereco"]["estado"]." - ".$body["consumidor"]["endereco"]["cep"];
// $dadosboleto["endereco2"]  = "";

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = $body["pagamento"]["descricao"];
$dadosboleto["demonstrativo2"] = $body['pagamento']['mensagens'];
//$dadosboleto["demonstrativo2"] = "Mensalidade referente a nonon nonooon nononon<br>Taxa bancária - R$ ".number_format($taxa_boleto, 2, ',', '');
//$dadosboleto["demonstrativo3"] = "BoletoPhp - http://www.boletophp.com.br";
$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: contato@pagcondominio.com";
$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Pag Condominio";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "";
$dadosboleto["valor_unitario"] = "";
$dadosboleto["aceite"] = "";		
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "";

// ---------------------- DADOS FIXOS DE CONFIGURACAO DO SEU BOLETO --------------- //

// DADOS PERSONALIZADOS - SANTANDER BANESPA
$dadosboleto["codigo_cliente"] = "47423382"; // Codigo do Cliente (PSK) (Somente 7 digitos)
$dadosboleto["ponto_venda"] = "0001"; // Ponto de Venda = Agencia
$dadosboleto["carteira"] = "102";  // Cobranca Simples - SEM Registro
$dadosboleto["carteira_descricao"] = "COBRANÇA SIMPLES - CSR";  // Descricao da Carteira

// SEUS DADOS
$dadosboleto["identificacao"] = "PAG CONDOMINIO - www.pagcondominio.com";
$dadosboleto["cpf_cnpj"] = "30194829000133";
$dadosboleto["endereco"] = "Rua Capote Valente, 671504";
$dadosboleto["cidade_uf"] = "Pinheiros/SP";
$dadosboleto["cedente"] = "PAG CONDOMINIO SOLUCOES LTDA";

// NAO ALTERAR!
include("include/funcoes_santander_banespa.php"); 
include("include/layout_santander_banespa.php");
?>
