<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 6/5/17
 * Time: 2:00 PM
 */
include 'scripts/APR1_MD5.php';
use WhiteHat101\Crypt\APR1_MD5;

/**
 * Creates a new user in packapps, by default with lowest privilege
 *
 * @param $realName
 * @param $userName
 * @param $newPassword
 * @param $isSystemAdministrator
 */
function createNewPackappsUser($mysqli, $realName, $userName, $newPassword, $isSystemAdministrator){
        $realName = mysqli_real_escape_string($mysqli, $realName);
        $userName = mysqli_real_escape_string($mysqli, $userName);
        $newPassword = APR1_MD5::hash(mysqli_real_escape_string($mysqli, $newPassword));
        mysqli_query($mysqli, "INSERT INTO master_users (username, `Real Name`, `Password`, isSystemAdministrator) VALUES ('$userName', '$realName', '$newPassword', '$isSystemAdministrator')");
        mysqli_query($mysqli, "INSERT INTO quality_UserData (username, DateCreated) VALUES ('$userName', NOW())");
        mysqli_query($mysqli, "INSERT INTO production_UserData (username) VALUES ('$userName')");
        mysqli_query($mysqli, "INSERT INTO purchasing_UserData (username) VALUES ('$userName')");
        if(mysqli_errno($mysqli)){
            $passwdChangeErrorMsg = "Could not set info for new user.";
        }
}

function changePassword($mysqli, $userName, $oldPassword, $newPassword, $confirmNewPassword){
    $SecuredUserName = mysqli_real_escape_string($mysqli, $userName);
    $newPassword = mysqli_real_escape_string($mysqli, $newPassword);
    $confirmNewPassword = mysqli_real_escape_string($mysqli, $confirmNewPassword);
    $hash = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT `Password` FROM master_users WHERE username = '" . $SecuredUserName . "'"))['Password'];
    if (APR1_MD5::check($oldPassword, $hash) && $newPassword == $oldPassword) {
        $newHash = APR1_MD5::hash($newPassword);
        mysqli_query($mysqli, "UPDATE master_users SET Password = '$newHash' WHERE username = '$SecuredUserName'");
        $passwdChangeErrorMsg = "Password changed to <mark>" . substr($newPassword, 0, 1) . str_repeat("*", strlen($newPassword) - 2) . substr($newPassword, -1) . "</mark>. This will take effect the next time you log in.";
    } else {
        $passwdChangeErrorMsg = "Either your current password is incorrect or your new passwords did not match. Try again.";
    }
    return $passwdChangeErrorMsg;
}

/**
 * Reset a user's password to their own username, for forgotten passwords
 *
 * @param $userName
 */
function resetPassword($userName){
    $newPassword = mysqli_real_escape_string($mysqli, APR1_MD5::hash($_GET['passwordReset']));
    $user = mysqli_real_escape_string($mysqli, $_GET['passwordReset']);
    mysqli_query($mysqli, "UPDATE master_users SET Password='$newPassword' WHERE username='$user'") or die(header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500));
    die();
}