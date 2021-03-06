<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 6/5/17
 * Time: 2:00 PM
 */

include_once 'scripts/APR1_MD5.php';

use WhiteHat101\Crypt\APR1_MD5;

/**
 * Authorizes a user to be logged into packapps, or a certain packapp if specified
 * @param null $packapp
 * @return array userinfo ['username', 'Real Name', 'lastLogin', 'isSystemAdministrator', [UserData Columns if packapp specified]]
 */
function packapps_authenticate_user($packapp = null) {
    if (isset($_COOKIE['grower'])) {
        die(header('Location: /grower/portal/'));
    }
    require 'config.php';
    if (!isset($_COOKIE['auth']) || !isset($_COOKIE['username'])) {
        die("<script>window.location.replace('/')</script>");
    } else if (!hash_equals($_COOKIE['auth'], crypt($_COOKIE['username'], $securityKey))) {
        die("<script>window.location.replace('/')</script>");
    } else {
        $SecuredUserName = mysqli_real_escape_string($mysqli, $_COOKIE['username']);
        $userInfo = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT username, `Real Name`, lastLogin, isSystemAdministrator FROM packapps_master_users WHERE username = '$SecuredUserName'"));
        if ($packapp != null) {
            //check if specific Packapp allowed
            $check = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT allowed" . ucfirst($packapp) . " FROM packapps_master_users WHERE username = '$SecuredUserName'"));
            if ($check['allowed' . ucfirst($packapp)] == 0) {
                die("<script>window.location.replace('/')</script>");
            } else {
                $roleArray = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT * FROM " . $packapp . "_UserData JOIN packapps_app_permissions ON packapp='$packapp' AND Role=permissionLevel WHERE username='$SecuredUserName'"));
                $userInfo = array_merge($userInfo, $roleArray);
            }
        }
        return $userInfo;
    }
}

function packapps_authenticate_grower($skip_onramp_check = false) {
    require 'config.php';
    if (isset($_COOKIE['grower']) && isset($_COOKIE['auth']) && isset($_COOKIE['username'])) {
        if (!hash_equals($_COOKIE['auth'], crypt($_COOKIE['username'], $growerSecurityKey))) {
            die("<script>window.location.replace('/')</script>");
        } else {
            $SecuredUserName = mysqli_real_escape_string($mysqli, $_COOKIE['username']);
            $userInfo = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT * FROM grower_GrowerLogins WHERE GrowerCode='$SecuredUserName'"));
            if (!$skip_onramp_check && $userInfo['email_confirmed'] == 0) {
                die(header('Location: /grower/portal/onramp/'));
            }
            return $userInfo;
        }
    } else {
        die("<script>window.location.replace('/')</script>");
    }
}

/**
 * Uploads a file to an S3 bucket
 * @param $bucketName - bucket to upload to
 * @param $fileToUpload - path to file
 * @param $fileNewName - new file name, including directory
 * @param $acl - optional, set to 'private' for non-public uploads
 * @return string url of file
 */
function packapps_uploadToS3($bucketName, $fileToUpload, $fileNewName, $acl = 'public-read') {
    require 'config.php';
    if (!in_array($bucketName, $availableBuckets)) {
        die("Bucket not found!");
    }
    require_once 'scripts/aws/aws-autoloader.php';
    $s3client = new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => 'us-east-2'
    ]);
    try {
        $status = $s3client->putObject(array(
            'Bucket' => $bucketName,
            'Key' => $companyShortName . '-' . $fileNewName,
            'SourceFile' => $fileToUpload,
            'ACL' => $acl
        ));
        return $status;
    } catch (S3Exception $e) {
        echo $e->getMessage() . "\n Failed!";
        die();
    }
}

/**
 * Delete a stored file from S3.
 *
 *
 * @param $bucketName
 * @param $filename
 */
function packapps_deleteFromS3($bucketName, $filename) {
    require 'config.php';
    if (!in_array($bucketName, $availableBuckets)) {
        die("Bucket not found!");
    }
    require_once 'scripts/aws/aws-autoloader.php';
    $s3client = new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => 'us-east-2'
    ]);
    try {
        $status = $s3client->deleteObject([
            'Bucket' => $bucketName,
            'Key' => $companyShortName . '-' . $filename
        ]);
    } catch (S3Exception $e) {
        echo $e->getMessage() . "\n Failed!";
        die();
    }
}

/**
 * Download a stored file from S3.
 *
 * Example usage:
 * $result = packapps_downloadFromS3('packapps-quality-uploadedimages', 'test.jpg');
 * header("Content-Type: ".$result['ContentType']);
 * echo $result['Body'];
 *
 * @param $bucketName
 * @param $filename
 * @return \Aws\Result Useful attribs: ['ContentType'], ['Body']. Null if no key
 */
function packapps_downloadFromS3($bucketName, $filename) {
    require 'config.php';
    if (!in_array($bucketName, $availableBuckets)) {
        die("Bucket not found!");
    }
    require_once 'scripts/aws/aws-autoloader.php';
    $s3client = new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => 'us-east-2'
    ]);
    try {
        $status = $s3client->getObject([
            'Bucket' => $bucketName,
            'Key' => $companyShortName . '-' . $filename
        ]);
        return $status;
    } catch (NoSuchKeyException $e) {
        return null;
    } catch (S3Exception $e) {
        echo $e->getMessage() . "\n Failed!";
        die();
    }
}

/**
 * Only runs once, initializes system_info row
 * @param $mysqli
 * @param $companyShortName
 */
function initialize_packapps($mysqli, $companyShortName) {
    //convert plain textgrower passwords to APR1 MD5 hashes
    $grower_passwords = mysqli_query($mysqli, "SELECT growerCode, Password FROM grower_GrowerLogins");
    while ($grower = mysqli_fetch_assoc($grower_passwords)) {
        if ($grower['Password'] !== null) {
            $newHash = APR1_MD5::hash($grower['Password']);
            mysqli_query($mysqli, "UPDATE grower_GrowerLogins SET Password = '$newHash' WHERE GrowerCode = '" . $grower['growerCode'] . "'");
        }
    }

    mysqli_query($mysqli, "UPDATE packapps_system_info SET systemInstalled=1, dateInstalled=CURRENT_TIMESTAMP(), company_slug='$companyShortName'");
    if (mysqli_errno($mysqli)) {
        APIFail(mysqli_error($mysqli));
    }
}

/**
 * Creates a new user in packapps, by default with lowest privilege
 *
 * @param $realName
 * @param $userName
 * @param $newPassword
 * @param $isSystemAdministrator
 * @return string
 */
function createNewPackappsUser($mysqli, $realName, $userName, $newPassword, $isSystemAdministrator) {
    $realName = trim(mysqli_real_escape_string($mysqli, $realName));
    $userName = trim(mysqli_real_escape_string($mysqli, $userName));
    $newPassword = APR1_MD5::hash(mysqli_real_escape_string($mysqli, $newPassword));
    mysqli_query($mysqli, "INSERT INTO packapps_master_users (username, `Real Name`, `Password`, isSystemAdministrator) VALUES ('$userName', '$realName', '$newPassword', '$isSystemAdministrator')");
    //enumerate packapps
    $packapps_query = mysqli_query($mysqli, "SELECT short_app_name, long_app_name FROM packapps_appProperties WHERE isEnabled = 1");
    while ($packapp = mysqli_fetch_assoc($packapps_query)) {
        mysqli_query($mysqli, "INSERT INTO " . $packapp['short_app_name'] . "_UserData (username) VALUES ('$userName')");
    }
    if (mysqli_errno($mysqli)) {
        die("Could not set info for new user.");
    }
}

/**
 * changes a user's password
 *
 * @param $mysqli
 * @param $userName
 * @param $oldPassword
 * @param $newPassword
 * @param $confirmNewPassword - should be same as newPassword
 * @return string - Returns a user-displayable html status message
 */
function changePassword($mysqli, $userName, $oldPassword, $newPassword, $confirmNewPassword) {
    $SecuredUserName = mysqli_real_escape_string($mysqli, $userName);
    $newPassword = mysqli_real_escape_string($mysqli, $newPassword);
    $confirmNewPassword = mysqli_real_escape_string($mysqli, $confirmNewPassword);
    $hash = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT `Password` FROM packapps_master_users WHERE username = '" . $SecuredUserName . "'"))['Password'];
    if (APR1_MD5::check($oldPassword, $hash) && $newPassword == $confirmNewPassword) {
        $newHash = APR1_MD5::hash($newPassword);
        mysqli_query($mysqli, "UPDATE packapps_master_users SET Password = '$newHash' WHERE username = '$SecuredUserName'");
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
 * @return string
 */
function resetPassword($mysqli, $userName) {
    $newPassword = mysqli_real_escape_string($mysqli, APR1_MD5::hash($userName));
    $user = mysqli_real_escape_string($mysqli, $userName);
    mysqli_query($mysqli, "UPDATE packapps_master_users SET Password='$newPassword' WHERE username='$user'") or die(header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500));
    return "Password reset.";
}

/**
 * Kills a connection and gives an error message
 * @param $errorMsg - string, failure reason given
 */
function APIFail($errorMsg = 'Internal Server Error'){
    header($_SERVER['SERVER_PROTOCOL'] . '500 ' . $errorMsg, true, 500);
    error_log($errorMsg);
    echo $errorMsg;
    die();
}

/*
 * Grower Portal has an entry for each block each year in the database.
 * On a new year, rollover the data and insert new data for the new year.
 * Inserts a recommended estimate, and pads older entries with every year until current year.
 * */
function incrementGrowerPortalEstimatesYear(MYSQLI $mysqli){
    $blocksAndYears = mysqli_query($mysqli,"SELECT
  PK,
  IFNULL(SUM(bushelsInBin), 0) AS bushels
FROM `grower_crop-estimates`
  LEFT JOIN storage_grower_receipts ON `grower_crop-estimates`.PK = storage_grower_receipts.grower_block
  LEFT JOIN storage_grower_fruit_bins sgfb ON storage_grower_receipts.id = sgfb.grower_receipt_id
WHERE YEAR(storage_grower_receipts.date) = (YEAR(CURDATE()) - 1) OR id IS NULL
GROUP BY PK;");
    $act = 'act';
    $est = 'est';
    $curYear = date('Y');
    $curYearMinusOne = $curYear - 1;
    $insert_stmt = mysqli_prepare($mysqli, "
        INSERT IGNORE INTO grower_block_bushel_history (block_PK, year, value_type, bushel_value)
        VALUES (?, ?, ?, ?);
    ");
    mysqli_stmt_bind_param($insert_stmt, 'iisi', $PK, $year, $type, $bushels);
    while($block = mysqli_fetch_assoc($blocksAndYears)){
        $PK = $block['PK'];
        $bushels = $block['bushels'];
        $year = $curYearMinusOne;
        $type = $act;
        mysqli_stmt_execute($insert_stmt);
        $year = $curYear;
        $type = $est;
        mysqli_stmt_execute($insert_stmt);
    }
    mysqli_stmt_close($insert_stmt);
    mysqli_query($mysqli, "UPDATE `grower_crop-estimates` SET isSameAsLastYear = 0, isFinished = 0");
    mysqli_query($mysqli, "UPDATE packapps_system_info SET growerPortalLastInitializedYear = $curYear");
}