<?php

//  define('SHOW_VARIABLES', 1);
//  define('DEBUG_LEVEL', 1);

//  error_reporting(E_ALL ^ E_NOTICE);
//  ini_set('display_errors', 'On');

set_include_path('.' . PATH_SEPARATOR . get_include_path());


require_once 'components/utils/system_utils.php';

//  SystemUtils::DisableMagicQuotesRuntime();

SystemUtils::SetTimeZoneIfNeed('Asia/Yekaterinburg');

function GetGlobalConnectionOptions()
{
	/*
	return array(
	'server' => 'localhost',
	'port' => '3306',
	'username' => 's15331327_admin',
	'password' => '8VnNhDQw',
	'database' => 's15331327_gmm'
	);
	*/
	return array(
	'server' => 'localhost',
	'port' => '3306',
	'username' => 'pieceoft_db2',
	'password' => '1litervand',
	'database' => 'pieceoft_db2'
	);
}

function HasAdminPage()
{
    return false;
}

function GetPageInfos()
{
    $result = array();
    $result[] = array('caption' => 'Land', 'short_caption' => 'Land', 'filename' => 'land.php', 'name' => 'land');
    $result[] = array('caption' => 'Land Special', 'short_caption' => 'Land Special', 'filename' => 'land_special.php', 'name' => 'land_special');
    $result[] = array('caption' => 'News', 'short_caption' => 'News', 'filename' => 'news.php', 'name' => 'news');
    $result[] = array('caption' => 'User', 'short_caption' => 'User', 'filename' => 'user.php', 'name' => 'user');
    $result[] = array('caption' => 'Settings', 'short_caption' => 'Settings', 'filename' => 'settings.php', 'name' => 'settings');
    return $result;
}

function GetPagesHeader()
{
    return
		'<script language="javascript" type="text/javascript">'.
		'$.getScript("../js/admin_google_maps.js", function() { /*alert("Script loaded and executed.");*/ });'.
		'</script>';
}

function GetPagesFooter()
{
    return
        '<!-- Footer -->'; 
    }

function ApplyCommonPageSettings($page, $grid)
{
    $page->SetShowUserAuthBar(true);
    $grid->BeforeUpdateRecord->AddListener('Global_BeforeUpdateHandler');
    $grid->BeforeDeleteRecord->AddListener('Global_BeforeDeleteHandler');
    $grid->BeforeInsertRecord->AddListener('Global_BeforeInsertHandler');
}

/*
  Default code page: 1252
*/
function GetAnsiEncoding() { return 'windows-1252'; }

function Global_BeforeUpdateHandler($page, $rowData, &$cancel, &$message, $tableName)
{

}

function Global_BeforeDeleteHandler($page, $rowData, &$cancel, &$message, $tableName)
{

}

function Global_BeforeInsertHandler($page, $rowData, &$cancel, &$message, $tableName)
{

}

function GetDefaultDateFormat()
{
    return 'Y-m-d';
}
?>