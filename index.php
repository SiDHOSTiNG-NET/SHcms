<?php 
// DEBUG Function Samaj ni aa rahi VS Test
$LoadingKey=strtoupper(bin2hex(random_bytes("8")));	// Random Code
function sh_debug($array){
	global $con;
	global $sh_debug;
	$debug_backtrace=debug_backtrace();

	if(!isset($array["debug_backtrace"])){ 
		$array["debug_backtrace"]="";
		if(isset($debug_backtrace["1"]["function"])){ $array["Function"]=$debug_backtrace["1"]["function"]; }
	} else {
		$fun_debug_backtrace=$array["debug_backtrace"];
		$array["Function"]=$fun_debug_backtrace["0"]["function"];
	}
	if(!isset($array["File"])){ $array["File"]=$debug_backtrace["0"]["file"]; }
	if(!isset($array["Function"])){ $array["Function"]="SHcms"; }
	if(!isset($array["Line"])){ $array["Line"]=$debug_backtrace["0"]["line"]; }
	if(!isset($array["Msg"])){ $array["Msg"]=""; }

	$sh_debug_["debug_backtrace"]=$array["debug_backtrace"];
		$sh_debug_["debug_backtrace"]=json_encode($sh_debug_["debug_backtrace"]);
	$sh_debug_["Function"]=$array["Function"];
	$sh_debug_["File"]=$array["File"];
		$removePath=str_replace("private_html", "public_html", $_SERVER['DOCUMENT_ROOT'])."/";
		$sh_debug_["File"] = str_replace($removePath, '', $sh_debug_["File"]);
	$sh_debug_["Line"]=$array["Line"];
	$sh_debug_["microtime"]=microtime(TRUE); // Automatic
	$sh_debug_["Msg"]=$array["Msg"];
	
	$sh_debug[]=$sh_debug_;
	if(isset($con)){ 
		
	}
}
sh_debug(array("Msg"=>"Start","File"=>__FILE__,"Line"=>__Line__));







// Error Enable
if(is_file("include/config/php/display_error.php")){ 
	sh_debug(array("Msg"=>"is_file TRUE include/config/php/display_error.php ","File"=>__FILE__,"Line"=>__LINE__)); 
	include("include/config/php/display_error.php"); 
}


if(is_file("include/config/MaintenanceModeOn.php")){
	sh_debug(array("Msg"=>"is_file TRUE include/config/MaintenanceModeOn.php ","File"=>__FILE__,"Line"=>__LINE__)); 
	$SourceIPs=file_get_contents($_SERVER['DOCUMENT_ROOT']."/include/config/MaintenanceModeOn.php"); // Loading Default Setting
    $IPs=explode('|', $SourceIPs);
	if(in_array($_SERVER["REMOTE_ADDR"], $IPs)){
		//sh_debug(array("Msg"=>$_SERVER["REMOTE_ADDR"]." Allowed under MaintenanceMode","File"=>__FILE__,"Line"=__LINE__)); 
	} else {
		sh_debug(array("Msg"=>"Maintenance Mode Page ","File"=>__FILE__,"Line"=>__LINE__)); 
		header("HTTP/1.1 503 Service Temporarily Unavailable");
		header("Status: 503 Service Temporarily Unavailable");
		include("include/Pages/Maintenance.php");
		exit();
	}
	
}


if(is_file("page/index.php")){
	include("page/index.php");
} else {
	include("include/Pages/index.php");
} 








sh_debug(array("Msg"=>"End","File"=>__FILE__,"Line"=>__Line__));

if(isset($con)){ 
	
	$VisiterID=VisiterID();
	$VisiterIP=VisiterIP();
	foreach ($sh_debug as $sh_debug_row) {
		$sh_debug_row["Function"]=mysqli_real_escape_string($con, $sh_debug_row["Function"]);
		$con->query("INSERT INTO `sh_debug` (`debug_backtrace`, `File`, `Line`, `Function`, `Msg`, `microtime`, `VisiterID`, `VisiterIP`, `LoadingKey`) 
		VALUES ('$sh_debug_row[debug_backtrace]', '$sh_debug_row[File]', '$sh_debug_row[Line]', '$sh_debug_row[Function]', '$sh_debug_row[Msg]', '$sh_debug_row[microtime]', '$VisiterID', '$VisiterIP', '$LoadingKey')");
	} 
	$delete_microtime=microtime(TRUE)-300; // 900 sec = 15 min
	$con->query("DELETE FROM `sh_debug` WHERE microtime < $delete_microtime");
}
?>
