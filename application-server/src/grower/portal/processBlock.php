<!DOCTYPE HTML>
<html>
<head>
    <?php
    include '../../config.php';
    $userinfo = packapps_authenticate_grower();
    echo "<title>Managing Block...</title>";
    if ($_GET['PK']) {
        //delete or undelete block
        mysqli_query($mysqli, "UPDATE `grower_crop-estimates` SET `isDeleted` = NOT `isDeleted` WHERE Grower='" . $userinfo['GrowerCode'] . "' AND PK='" . $_GET['PK'] . "'");
    } elseif ($_GET['Done']) {
        //finish or unfinish block
        mysqli_query($mysqli, "UPDATE `grower_crop-estimates` SET `isFinished`= NOT `isFinished` WHERE Grower='" . $userinfo['GrowerCode'] . "' AND PK='" . $_GET['Done'] . "'");
    } elseif ($_GET['sameEst']) {
        //check same as last year or uncheck
        mysqli_query($mysqli, "UPDATE `grower_crop-estimates` SET isSameAsLastYear = NOT `isSameAsLastYear` WHERE Grower='" . $userinfo['GrowerCode'] . "' AND PK='" . $_GET['sameEst'] . "'");
    }
    ?>
    <script>location.replace("index.php#estimatestable")</script>
</head>
</html>