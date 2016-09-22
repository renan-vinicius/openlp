<?php

/* Recebe dados da música do formulário */
$titulo = $_POST["titulo"]; // nome da música
$autor = $_POST["autor"]; // nome do autor/compositor
$propriedade = $_POST["propriedade"]; // tipo de verso: estrofe ou refrão
$conteudo = $_POST["conteudo"]; // letra da música

function quebraLinhas($vetor){
	$linhas = explode("\n", $vetor);
	$retorno = '';

	foreach ($linhas as $l){ 
		if($l!=''){
			$ultimo = substr($l,-1);            
			if($ultimo==' '){
				$l = substr($l,0,-1);
			}
		$retorno = $retorno . mb_strtoupper($l,'UTF-8');
		$retorno = $retorno . "<br/>";
		}
	}
	return $retorno;
}

/* Criação do documento XML */

header( "content-type: application/xml; charset=UTF-8" );

// Dados do documento XML
$xml = new DOMDocument( "1.0", "UTF-8" );
$song = $xml->createElement("song");
$song->setAttribute('xmlns', 'http://openlyrics.info/namespace/2009/song');
$song->setAttribute('version', '0.8');
$song->setAttribute('createdIn', 'OpenLP-RV');
$song->setAttribute('modifiedIn', '');
$song->setAttribute('modifiedDate', '2016-03-30T17:18:10.614Z');

// Criar elementos do padrão OpenLyrics
$properties = $xml->createElement( "properties");
$lyrics = $xml->createElement( "lyrics");
$titles = $xml->createElement("titles");
$title = $xml->createElement("title",$titulo);
$authors = $xml->createElement("authors");
$author = $xml->createElement("author",$autor);

/* Hierarquia */
$xml->appendChild($song);
$song->appendChild($properties);
$song->appendChild($lyrics);
$properties->appendChild($titles);
$properties->appendChild($authors);
$titles->appendChild($title);
$authors->appendChild($author);

/* Divisão dos versos */

$i=0;
$qtdeRefrao = 1;
$qtdeVerso = 1;

foreach ($conteudo as $estrofeC){
   $i++;
   $verso = $xml->createElement("verse");

   if($propriedade[$i-0]=="c"){ // contabiliza o ID do refrão
      $valor = $qtdeRefrao;
      $qtdeRefrao++;
  }
  else{
      $valor = $qtdeVerso; // atribui o ID do verso
      $qtdeVerso++;
  }
  
  $verso->setAttribute("name", $propriedade[$i-0].$valor);
  $lyrics->appendChild($verso);
  $lines = $xml->createElement("lines", substr(quebraLinhas($estrofeC), 0, -5));
  $verso->appendChild($lines);
}	

/* Definições do arquivo */
$xml->formatOutput = true;
$xml->preserveWhiteSpace = true;
$xml_string = $xml->saveXML();

$novastring = str_replace("&#13;&lt;br/&gt;", "<br/>", $xml_string); // remove caracteres dos versos
$novastring = str_replace("&#13;", "<br/>", $novastring); // remove caracteres dos versos

/* Nome e caminho do arquivo */
$nomeAjustado = utf8_decode($titulo) . ' - ' . utf8_decode($autor) . '.xml';
$caminho = 'musicas/'.$nomeAjustado;
$nomeDoArquivo = $caminho;
$arquivo = fopen($nomeDoArquivo, "w") or die("Não é possível abrir o arquivo");
$conteudo = $novastring;
fwrite($arquivo,$conteudo);

header('Location: baixar.php?url='.$nomeDoArquivo.'&nome='.$nomeAjustado); //redirecionar e forçar download do arquivo
?>

