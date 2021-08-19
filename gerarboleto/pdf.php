<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
*/

include("../../assets/mpdf60/mpdf.php");

 $doc = new DOMDocument();
 $doc->loadHTMLFile("boleto.php?a=4");
 //echo $doc->saveHTML();
//exit;

 $html = $doc->saveHTML();
 //$html = "<b>Brasil</b>eiros";

 $mpdf=new mPDF(); 
 $mpdf->SetDisplayMode('fullpage');
 $css = file_get_contents("../../assets/mpdf60/css/estilo.css");
 $mpdf->WriteHTML($css,1);
 $mpdf->WriteHTML($html);
 $mpdf->Output();

 exit;