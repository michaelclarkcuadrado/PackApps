<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 7/25/2016
 * Time: 12:30 PM
 */
include '../config_grower.php';
$adminauth = mysqli_query($mysqli, "SELECT isAdmin FROM GrowerData WHERE GrowerCode='" . $_SERVER['PHP_AUTH_USER'] . "'");
$admin = mysqli_fetch_array($adminauth);
if($_POST['operation'] == 'add'){
    $Grower = mysqli_real_escape_string($mysqli, $_POST['grower']);
    $Variety = mysqli_real_escape_string($mysqli, $_POST['variety']);
    $Strain = mysqli_real_escape_string($mysqli, $_POST['strain']);
    $start = date('Y-m-d');
    mysqli_query($mysqli, "INSERT INTO growerCalendar (Grower, Variety, Strain, Start, EndDate) VALUES ('$Grower', '$Variety', '$Strain', '$start', '$start' + INTERVAL 1 DAY)") or die(header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500));
} else if ($_POST['operation'] == 'move') {
    $eventID = mysqli_real_escape_string($mysqli, $_POST['eventID']);
    $deltaDays = mysqli_real_escape_string($mysqli, $_POST['deltaDays']);
    mysqli_query($mysqli, "UPDATE growerCalendar SET `Start` = `Start` + INTERVAL $deltaDays DAY, `EndDate` = `EndDate` + INTERVAL $deltaDays DAY WHERE `ID`=$eventID") or die(header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500));
} else if ($_POST['operation'] == 'resize') {
    $eventID = mysqli_real_escape_string($mysqli, $_POST['eventID']);
    $deltaDays = mysqli_real_escape_string($mysqli, $_POST['deltaDays']);
    mysqli_query($mysqli, "UPDATE growerCalendar SET `EndDate` = `EndDate` + INTERVAL $deltaDays DAY WHERE `ID`=$eventID") or die(header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500));
} else if ($_POST['operation'] == 'delete') {
    $eventID = mysqli_real_escape_string($mysqli, $_POST['eventID']);
    mysqli_query($mysqli, "DELETE FROM `growerCalendar` WHERE `ID`=$eventID") or die(header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500));
}