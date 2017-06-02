<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 8/5/2016
 * Time: 2:35 PM
 */
//authentication
include 'config.php';
if (!isset($_COOKIE['auth']) || !isset($_COOKIE['username'])) {
    die("<script>window.location.replace('/')</script>");
} else if (!hash_equals($_COOKIE['auth'], crypt($_COOKIE['username'], $securityKey))) {
    die("<script>window.location.replace('/')</script>");
} else {
    $SecuredUserName = mysqli_real_escape_string($mysqli, $_COOKIE['username']);
    $checkAllowed = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT `Real Name`, username, isSystemAdministrator FROM master_users WHERE `username` = '$SecuredUserName'"));
    if ($checkAllowed['isSystemAdministrator'] == 0) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 403 FORBIDDEN', true, 500);
        die();
    } else {
        // end authentication
        $userPrivileges = mysqli_query($mysqli, "SELECT concat(`Real Name`, CASE WHEN isSystemAdministrator > 0 THEN ' **' ELSE '' END) as `Real Name`, master_users.username, ifnull(DATE_FORMAT(lastLogin,'%b %d %Y %h:%i %p'), 'Never') as lastLogin, isSystemAdministrator, isDisabled, purchasing_UserData.isAuthorizedForPurchases as purchasingRole, production_UserData.Role as productionRole, quality_UserData.Role as qualityRole, allowedProduction, allowedPurchasing, allowedQuality FROM master_users LEFT JOIN purchasing_UserData ON master_users.username=purchasing_UserData.Username LEFT JOIN quality_UserData ON master_users.username = quality_UserData.UserName LEFT JOIN production_UserData ON master_users.username = production_UserData.UserName");
        $arrayToReturn = array();
        while ($user = mysqli_fetch_assoc($userPrivileges)){
            //convert purchasing
            $user['purchasingRole'] += 1;
            //convert production
            if($user['productionRole'] == 'Production') {
                $user['productionRole'] = 2;
            } else if ($user['productionRole'] == 'ReadOnly'){
                $user['productionRole'] = 1;
            } else {
                $user['productionRole'] = 1;
            }
            //convert quality
            if ($user['qualityRole'] == 'QA') {
                $user['qualityRole'] = 3;
            } elseif ($user['qualityRole'] == 'INS') {
                $user['qualityRole'] = 2;
            } elseif ($user['qualityRole'] == 'Weight') {
                $user['qualityRole'] = 1;
            } else {
                $user['qualityRole'] = 1;
            }
            array_push($arrayToReturn, $user);
        }
        echo json_encode($arrayToReturn);
    }
}
