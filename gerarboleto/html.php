<?php
$doc = new DOMDocument();
$doc->loadHTMLFile("html5.html");
echo $doc->saveHTML();
?>