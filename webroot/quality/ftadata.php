<?php
/**
 * Created by PhpStorm.
 * User: MAC
 * Date: 7/20/2015
 * Time: 11:21 AM
 */
//This file parses an XLS file generated by the fruit test suite FTA and inserts its results into AppleSamples
include_once("Classes/excel_reader2.php");
include '../config.php';

//get real name for logging accountability
//authentication
if (!isset($_COOKIE['auth']) || !isset($_COOKIE['username'])) {
    die("<script>window.location.replace('/')</script>");
} else if (!hash_equals($_COOKIE['auth'], crypt($_COOKIE['username'], $securityKey))) {
    die("<script>window.location.replace('/')</script>");
} else {
    $SecuredUserName = mysqli_real_escape_string($mysqli, $_COOKIE['username']);
    $checkAllowed = mysqli_fetch_array(mysqli_query($mysqli, "SELECT `Real Name`, Role, isSectionManager as isAdmin, allowedQuality FROM master_users JOIN quality_UserData ON master_users.username=quality_UserData.UserName WHERE master_users.username = '$SecuredUserName'"));
    if (!$checkAllowed['allowedQuality'] > 0) {
        die ("<script>window.location.replace('/')</script>");
    } else {
        $RealName = $checkAllowed;
        $Role = $checkAllowed['Role'];
    }
}
// end authentication
if ($RealName[1] !== "QA") {
    die("UNAUTHORIZED");
};

$xlsdata = new Spreadsheet_Excel_Reader($_FILES['xlsupload']['tmp_name'], false);


//13 + samples*2 = number of rows
//samples*2 + 8 = post of RT#
$NumSamples = ($xlsdata->rowcount()-13)/2;
//RT check
if($xlsdata->val($NumSamples*2+8,'B') != $_POST['RT'])
{
    die ("WRONG FTA FILE. EXPECTED: " . $_POST['RT'] . ". GOT: " . $xlsdata->val($NumSamples*2+8,'B') . " <br> <a href='' onclick='window.history.back();'> Go Back</a>");
}

$rt= mysqli_real_escape_string($mysqli,$_POST['RT']);

$updatestmt = mysqli_prepare($mysqli, "UPDATE AppleSamples SET Weight=?, Pressure1=?, Pressure2=?, `FinalTestedBy`=? WHERE `RT#`= ? AND SampleNum= ?");
mysqli_stmt_bind_param($updatestmt, "ssssii", $Weight, $Press1, $Press2, $RealName[0], $rt, $SampleNum);
//execute statements
for($i = 1; $i <= $NumSamples; $i++)
{
    $SampleNum = $i;
    $Weight = $xlsdata->val($i*2, 'C');
    $Press1 = $xlsdata->val($i*2, 'B');
    $Press2 = $xlsdata->val($i*2+1, 'B');
    mysqli_stmt_execute($updatestmt);
}

mysqli_query($mysqli, "UPDATE InspectedRTs SET FTAup ='1' WHERE RTNum='" . $rt . "'");
echo "<script>location.replace('QA.php?FTAsel=" . $rt . "#QA')</script>";
