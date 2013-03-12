<?php
/*
Uploadify v2.1.4
Release Date: November 8, 2010

Copyright (c) 2010 Ronnie Garcia, Travis Nickels

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

function SureRemoveDir($dir, $DeleteMe=false) {
    if(!$dh = @opendir($dir)) return;
    while (false !== ($obj = readdir($dh))) {
        if($obj=='.' || $obj=='..') continue;
        if (!@unlink($dir.'/'.$obj)) SureRemoveDir($dir.'/'.$obj, true);
    }

    closedir($dh);
    if ($DeleteMe){
        @rmdir($dir);
    }
}
if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	//$targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
	$targetPath = dirname(__FILE__)."/../../../".$_REQUEST['folder'].'/';
	//empty directory
	//SureRemoveDir($targetPath);
	if($_GET['filename']){
		$targetFile =  str_replace('//','/',$targetPath) .$_GET['filename'];
	}
	else{
		$targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
	}
	file_put_contents(getcwd()."/_log.txt", $targetFile);
	
	$targetFilex = $targetFile;
	if(strtolower(substr($targetFilex, -4))=='.php'){
		$targetFilex = substr($targetFilex, 0, strlen($targetFilex)-4);
	}
	if(strtolower(substr($targetFilex, -9))=='.htaccess'){
		$targetFilex = substr($targetFilex, 0, strlen($targetFilex)-9)."htaccess";
	}
	
	move_uploaded_file($tempFile,$targetFilex);
	echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);

}
?>