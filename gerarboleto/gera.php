<?php
include("../../assets/mpdf60/mpdf.php");

$doc = new DOMDocument();
$doc->loadHTMLFile("https://api.pagcondominio.com/gerarboleto/boleto.php?a=7");

$html = $doc->saveHTML();
$html = utf8_encode($html);

$mpdf=new mPDF(); 
$mpdf->SetDisplayMode('fullpage');
$css = file_get_contents("../../assets/mpdf60/css/estilo.css");
$mpdf->WriteHTML($css,1);
$mpdf->WriteHTML($html);
$mpdf->Output();
exit;