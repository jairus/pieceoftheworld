<?php

require_once 'phpgen_settings.php';
require_once 'components/security/security_info.php';
require_once 'components/security/datasource_security_info.php';
require_once 'components/security/tablebased_auth.php';
require_once 'components/security/user_grants_manager.php';
require_once 'components/security/table_based_user_grants_manager.php';

require_once 'database_engine/mysql_engine.php';

$grants = array('guest' => 
        array()
    ,
    'defaultUser' => 
        array('land' => new DataSourceSecurityInfo(false, false, false, false),
        'land_special' => new DataSourceSecurityInfo(false, false, false, false),
        'land_special.land' => new DataSourceSecurityInfo(false, false, false, false),
        'news' => new DataSourceSecurityInfo(false, false, false, false),
        'user' => new DataSourceSecurityInfo(false, false, false, false),
        'user.land' => new DataSourceSecurityInfo(false, false, false, false),
        'user.land_special' => new DataSourceSecurityInfo(false, false, false, false),
        'settings' => new DataSourceSecurityInfo(false, false, false, false))
    ,
    'john@email.com' => 
        array('land' => new DataSourceSecurityInfo(false, false, false, false),
        'land_special' => new DataSourceSecurityInfo(false, false, false, false),
        'land_special.land' => new DataSourceSecurityInfo(false, false, false, false),
        'news' => new DataSourceSecurityInfo(false, false, false, false),
        'user' => new DataSourceSecurityInfo(false, false, false, false),
        'user.land' => new DataSourceSecurityInfo(false, false, false, false),
        'user.land_special' => new DataSourceSecurityInfo(false, false, false, false),
        'settings' => new DataSourceSecurityInfo(false, false, false, false))
    ,
    'andrew@email.com' => 
        array('land' => new DataSourceSecurityInfo(false, false, false, false),
        'land_special' => new DataSourceSecurityInfo(false, false, false, false),
        'land_special.land' => new DataSourceSecurityInfo(false, false, false, false),
        'news' => new DataSourceSecurityInfo(false, false, false, false),
        'user' => new DataSourceSecurityInfo(false, false, false, false),
        'user.land' => new DataSourceSecurityInfo(false, false, false, false),
        'user.land_special' => new DataSourceSecurityInfo(false, false, false, false),
        'settings' => new DataSourceSecurityInfo(false, false, false, false))
    );

$appGrants = array('guest' => new DataSourceSecurityInfo(false, false, false, false),
    'defaultUser' => new DataSourceSecurityInfo(false, false, false, false),
    'john@email.com' => new AdminDataSourceSecurityInfo(),
    'andrew@email.com' => new DataSourceSecurityInfo(false, false, false, false));

$dataSourceRecordPermissions = array();

$tableCaptions = array('land' => 'land',
'land_special' => 'land_special',
'land_special.land' => 'land_special.land',
'news' => 'news',
'user' => 'user',
'user.land' => 'user.land',
'user.land_special' => 'user.land_special',
'settings' => 'settings');

function CreateTableBasedGrantsManager()
{
    return null;
}

function SetUpUserAuthorization()
{
    global $grants;
    global $appGrants;
    global $dataSourceRecordPermissions;
    $hardCodedGrantsManager = new HardCodedUserGrantsManager($grants, $appGrants);
$tableBasedGrantsManager = CreateTableBasedGrantsManager();
$grantsManager = new CompositeGrantsManager();
$grantsManager->AddGrantsManager($hardCodedGrantsManager);
if (!is_null($tableBasedGrantsManager))
    $grantsManager->AddGrantsManager($tableBasedGrantsManager);
$userAuthorizationStrategy = new TableBasedUserAuthorization(new MyConnectionFactory(), GetGlobalConnectionOptions(), 'user', 'email', 'id', $grantsManager);
    GetApplication()->SetUserAuthorizationStrategy($userAuthorizationStrategy);

GetApplication()->SetDataSourceRecordPermissionRetrieveStrategy(
    new HardCodedDataSourceRecordPermissionRetrieveStrategy($dataSourceRecordPermissions));
}

function GetIdentityCheckStrategy()
{
    return new TableBasedIdentityCheckStrategy(new MyConnectionFactory(), GetGlobalConnectionOptions(), 'user', 'email', 'password', ENCRYPTION_NONE);
}

?>