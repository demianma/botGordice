<?php
//-----------------------------------------------------
//GORDICE SLACK SLASH COMMAND v 1.1
//
//CREATE AN SLASH COMMAND IN SLACK WITH THE FOLLOWING:
//
// Command: /gordice (or whatever you want)
// URL: URL to the folder where index.php, font and seed image are
// Method: GET
// Customize Name: Gordice (or whatever you want)
// Customize Icon: Select topgordiceicon.png
// Autocomplete help text
//     Description: Controle de Gordices da turma
//     Usage hint: [add | del | update] ou help para ajuda.
// Descriptive Label: Controle das gordices da turma
//
// Save it.
//
// run /gordice once to view the options
// run /gordice help whenever you need some help.
//
//-----------------------------------------------------


//HTTP REQUESTS
$prefix = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://";
$url = $prefix . $_SERVER['SERVER_NAME'] . substr($_SERVER['PHP_SELF'], 
	0, strrpos($_SERVER['PHP_SELF'], '/'));
$channel_id = $_GET["channel_id"];
$channel_name = $_GET["channel_name"];
$text = $_GET["text"];

//VARS
$imgfolder = "images/";
$today = date('Y-m-d', time());
$todayh = date('Ymdhms', time());
$params = explode(" ", $text);
$function = $params[0];
$name = $params[1];
$points = floatval(str_replace(',', '.', $params[2]));
$varfile = "vars.json";

//ABORT IF DIRECTMESSAGE
if ($channel_name == "directmessage") {
	echo "Olá! Eu sou o bot Gordice!\n\n";
	echo "Eu sirvo para monitorar os participantes da sua sala\n";
	echo "que mais trazem gordices para a turma comer.\n\n";
	echo "Infelizmente, este recurso só serve para canais (salas)...\n\n";
	echo "Digite /gordice help em um canal para aprender como\n" ;
	echo "usar o meu sistema.";
	exit;
}

//RECOVER RANKING FROM JSON AND SORT IT ---- NÃO FAZENDO....
if (file_exists($varfile)){
	$scores = json_decode(file_get_contents($varfile),true);
	arsort($scores[$channel_id]);
}
else {
	$scores = array();
	upDate();
}

//UPDATE JSON WITH RANKING
function upDate(){
	global $varfile;
	global $scores;
	file_put_contents($varfile, json_encode($scores));
}

//SLACK JSON ANSWER
function geraJSON($url, $f){
	$attachment = array(
	"title" => "Top Gordice 2018",
	"image_url" => $url . '/' . $f,
	"thumb_url" => $url . "topgordiceicon.png",
	"footer" => "Use o comando '/gordice help' para lista de comandos. Imagem temporária.",
	"ts" => 123456789
	);

	$final = array(
	"response_type" => "in_channel",
	"text" => "Vamos dar uma olhada na gordice da salinha " . $channel_name . "...",
	"attachments" => array($attachment)
	);
	
	$myJSON = json_encode($final);

	//JSON ANSWER
	header("Content-type:application/json");
	echo $myJSON;
}

//DOES THIS CHANNEL HAS A RANKING?
function check($scores, $channel_id){
	if (!isset($scores) || !array_key_exists($channel_id, $scores)){
		echo "Olá! Eu sou o bot Gordice!\n\n";
		echo "Eu sirvo para monitorar os participantes da sua sala\n";
		echo "que mais trazem gordices para a turma comer.\n\n";
		echo "Infelizmente, este canal ainda não tem um placar...\n\n";
		echo "Digite /gordice help para aprender como\n" ;
		echo "adicionar, remover ou editar competidores.";
		exit;
	}
}

//ARGUMENT CASES
switch ($function) 
	{
	//USER INPUT WRONG PARAMETERS OR help
	default:
		echo "Bot Gordice! Para controlar a gordice da raça!\n\n";
		echo "Comandos:\n\n";
		echo "Para mostrar o placar\n";
		echo "/gordice\n\n";
		echo "Para adicionar participantes:\n";
		echo "/gordice add [nome] [pontos iniciais]\n\n";
		echo "Para apagar um participante:\n";
		echo "/gordice del [nome]\n\n";
		echo "Para atualizar os pontos:\n";
		echo "/gordice update [nome] [pontos]";
		break;
		
	//ADD NEW COMPETIDOR
	case add:
		//check if exist		
		if (isset($scores) && array_key_exists($name, $scores[$channel_id])) {
			echo "O competidor " . $name . " já existe. Escolha outro nome.";
		}
		else {
			$scores[$channel_id][$name] = $points;
			upDate();
			echo "Adicionado competidor " . $name . " começando com " . $points . 
			($points > 1 ? " pontos." : " ponto.");
		}
		break;
		
	//DELETE COMPETIDOR
	case del:
		//check if exist
		check($scores, $channel_id);
		if (isset($scores) && array_key_exists($name, $scores[$channel_id])) {
			unset($scores[$channel_id][$name]);
			upDate();
			echo "Competidor " . $name . " apagado.";
		}
		else {
			echo "O competidor " . $name . " não existe.";
		}
		break;
		
	//UPDATE COMPETIDOR'S POINTS
	case update:
		//check if exist
		check($scores, $channel_id);
		if (isset($scores) && array_key_exists($name, $scores[$channel_id])) {
			$scores[$channel_id][$name] = $points;
			upDate();
			echo "Pontuação de " . $name . " atualizada para " . $points . 
			($points > 1 ? " pontos." : " ponto.");
		}
		else {
			echo "O competidor " . $name . " não existe.";
		}
		break;
	
	//SHOW IMAGE
	case "":
		check($scores, $channel_id);
		
		//clean older than 7 days images
		foreach(glob($imgfolder . '*.*') as $file) {
			if((time() - filectime($file)) > 604800) {
				@unlink($file);
			}
		}
	
		//build image
		$rImg = ImageCreateFromJPEG( "topgordice.jpg" );
		$color = imagecolorallocate($rImg, 252, 247, 189);
		$colorTitle = imagecolorallocate($rImg, 255, 135, 139);
		$font = './stencil.ttf';
		$line = 220;
		imagettftext($rImg, 20, 0, 60, 180, $colorTitle, $font, "Competidor");
		imagettftext($rImg, 20, 0, 260, 180, $colorTitle, $font, "Pontos");
		foreach($scores[$channel_id] as $name => $points){
			imagettftext($rImg, 20, 0, 60, $line, $color, $font, $name);
			imagettftext($rImg, 20, 0, 300, $line, $color, $font, $points);
			$line = $line + 30;
		}	
		imagejpeg($rImg, $imgfolder . $todayh . ".jpg", 80);
		imagedestroy($rImg);
		
		//JSON ANSWER
		geraJSON($url, $imgfolder . $todayh . ".jpg");
		break;
	}
	
?>
