<?php
//-----------------------------------------------------
//GORDICE SLACK SLASH COMMAND
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
// run /gordice once to learn the options
// run /gordice help whenever you need some help.
//
// REMEMBER TO ADJUST THESE PARAMETERS:
//
// Where are the files?
$appfolder = "/apps/slackbot/gordice/";
// 
// Where is the image to be saved? Leave it as is if you want
$imgfolder = "images/";
//
// Is your website https?
$urltype = "http://"; 
//
//-----------------------------------------------------


//GET FROM SLACK AND OTHER STUFF
$today = date('Y-m-d', time());
$todayh = date('Ymdhms', time());
$url = $urltype . $_SERVER['HTTP_HOST'] . $appfolder;
$channel_id = $_GET["channel_name"];
$command = $_GET["command"];
$user_name = $_GET["user_name"];
$text = $_GET["text"];
$params = explode(" ", $text);
$function = $params[0];
$name = $params[1];
$points = floatval(str_replace(',', '.', $params[2]));
$varfile = "vars.json";

//UPDATE VARS
function upDate(){
	global $varfile;
	global $scores;
	file_put_contents($varfile, json_encode($scores));
}

//RECOVER ARRAY scores FROM VAR FILE OR CREATE IT
if (file_exists($varfile)){
	$scores = json_decode(file_get_contents($varfile),true);
	asort($scores);
	$scores = array_reverse($scores);
}
else {
	$scores = array();
	upDate();
}

//SLACK JSON GEN
function geraJSON($url, $f){
	$attachment = array(
	"title" => "Top Gordice 2018",
	"image_url" => $url . $f,
	"thumb_url" => $url . "topgordiceicon.png",
	"footer" => "Use o comando '/gordice help' para lista de comandos. Imagem temporária.",
	"ts" => 123456789
	);

	$attachments = array($attachment);

	$final  = array(
	"response_type" => "in_channel",
	"text" => "Vamos dar uma olhada na gordice da salinha...",
	"attachments" => $attachments);
	

	$myJSON = json_encode($final);

	//resposta JSON
	header("Content-type:application/json");
	echo $myJSON;
}

//CASES
switch ($function) 
	{
	//USER INPUT WRONG PARAMETERS
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
		if (array_key_exists($name, $scores)) {
			echo "O competidor " . $name . " já existe. Escolha outro nome.";
		}
		else {
			$scores[$name] = $points;
			upDate();
			echo "Adicionado competidor " . $name . " começando com " . $points . ($points > 1 ? " pontos." : " ponto.");
		}
		break;
		
	//DELETE COMPETIDOR
	case del:
		//check if exist
		if (array_key_exists($name, $scores)) {
			unset($scores[$name]);
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
		if (array_key_exists($name, $scores)) {
			$scores[$name] = $points;
			upDate();
			echo "Pontuação de " . $name . " atualizada para " . $points . ($points > 1 ? " pontos." : " ponto.");
		}
		else {
			echo "O competidor " . $name . " não existe.";
		}
		break;
	
	//SHOW IMAGE
	case "":
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
		foreach($scores as $name => $points){
			imagettftext($rImg, 20, 0, 60, $line, $color, $font, $name);
			imagettftext($rImg, 20, 0, 300, $line, $color, $font, $points);
			$line = $line + 30;
		}
		imagejpeg($rImg, $imgfolder . $todayh . ".jpg", 80);
		imagedestroy($rImg);
		
		//json response
		geraJSON($url, $imgfolder . $todayh . ".jpg");
		break;
	}
	
?>
