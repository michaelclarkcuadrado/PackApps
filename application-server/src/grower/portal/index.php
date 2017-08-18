<!DOCTYPE HTML>
<html>
<head>
    <?php
    require_once '../../scripts-common/Mobile_Detect.php';
    require_once 'incrementYearInDB.php';
    include '../../config.php';
    $userinfo = packapps_authenticate_grower();
    $detect = new Mobile_Detect;
    $year = new Year();
    if (!$year->isCurrent($mysqli)) {
        error_log("First access since new year, incrementing year.");
	$year->increment($mysqli);
    }

    $blockCompletionData = mysqli_query($mysqli, "select `grower_crop-estimates`.`PK` AS `PK`,
    `grower_crop-estimates`.`Comm Desc` AS `Comm Desc`,
    `grower_crop-estimates`.`VarDesc` AS `VarDesc`,
    `grower_crop-estimates`.`Str Desc` AS `Str Desc`,
    `grower_crop-estimates`.`FarmDesc` AS `FarmDesc`,
    `grower_crop-estimates`.`BlockDesc` AS `BlockDesc`,
    ifnull(sum(`BULKRTCSV`.`Bu`), 0) AS `Total`,
    `" . date('Y') . "est` AS Est,
    ifnull(round(((sum(`BULKRTCSV`.`Bu`) / `grower_crop-estimates`.`" . date('Y') . "est`) * 100), 2), 0) AS `Percent`,
    `grower_crop-estimates`.`isFinished` AS `isFinished` 
  from (`grower_crop-estimates` 
    join `BULKRTCSV` 
      on(((trim(`BULKRTCSV`.`Comm Desc`) = trim(`grower_crop-estimates`.`Comm Desc`)) 
          and (trim(`BULKRTCSV`.`VarDesc`) = trim(`grower_crop-estimates`.`VarDesc`)) 
          and (trim(`BULKRTCSV`.`StrDesc`) = trim(`grower_crop-estimates`.`Str Desc`)) 
          and (trim(`BULKRTCSV`.`BlockDesc`) = trim(`grower_crop-estimates`.`BlockDesc`)) 
          and (trim(`BULKRTCSV`.`FarmDesc`) = trim(`grower_crop-estimates`.`FarmDesc`)) 
          and (trim(`BULKRTCSV`.`Grower`) = trim(`grower_crop-estimates`.`Grower`))))) 
  where ((`crop-estimates`.`isDeleted` = '0') 
         and `crop-estimates`.Grower = '" . $userinfo['GrowerCode'] . "'
         and `Crop Year`= substr(YEAR(CURDATE()), 4,1))
  group by `crop-estimates`.`PK`
        ORDER BY isFinished, Percent ASC") or error_log(mysqli_error($mysqli));

    $estimates = mysqli_query($mysqli, "SELECT PK,`Comm Desc`,VarDesc,FarmDesc,BlockDesc,`Str Desc`,isDeleted,isSameAsLastYear," . (date('Y') - 3) . "act," . (date('Y') - 2) . "act," . (date('Y') - 1) . "est," . (date('Y') - 1) . "act," . (date('Y')) . "est from `crop-estimates` where Grower='" . $userinfo['GrowerCode'] . "' Order by isDeleted, `Comm Desc`, VarDesc, FarmDesc, BlockDesc, `Str Desc` ASC;");

    $numPreHarvest = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM (SELECT PK FROM `grower_Preharvest_Samples` WHERE Grower= '" . $userinfo['GrowerCode'] . "' AND `Date` >= (NOW() - INTERVAL 7 DAY) GROUP BY `Date`, `PK`) t1"))['count'];

    $receiptdata = mysqli_query($mysqli, "SELECT `RT#` AS RTNum, `Comm Desc`,`VarDesc`,`StrDesc`,`FarmDesc`,`BlockDesc`,`Date`,`Qty`,`Bu` FROM `BULKRTCSV` WHERE Grower='" . $userinfo['GrowerCode'] . "' AND Class='1' AND Bu <> 0 AND `Crop Year` = '".substr(date('y'), -1)."' ORDER BY DATE DESC LIMIT 75");

    echo "<title>" . $companyName . " Portal: " . $userinfo['GrowerName'] . "</title>";
    ?>

    <link rel="stylesheet" href="css/select2.min.css">
    <!--[if lte IE 8]>
    <script src="css/ie/html5shiv.js"></script><![endif]-->
    <script src="js/jquery.min.js"></script>
    <script src="js/notify.min.js"></script>
    <script src="js/jquery.scrolly.min.js"></script>
    <script src="js/jquery.scrollzer.min.js"></script>
    <script src="js/skel.min.js"></script>
    <script src="js/skel-layers.min.js"></script>
    <script src="js/init.js"></script>
    <script src="js/select2.min.js"></script>
    <script src="js/jquery.tablesorter.min.js"></script>
    <noscript>
        <link rel="stylesheet" href="css/skel.css"/>
        <link rel="stylesheet" href="css/style.css"/>
        <link rel="stylesheet" href="css/style-wide.css"/>
    </noscript>
    <!--[if lte IE 9]>
    <link rel="stylesheet" href="css/ie/v9.css"/><![endif]-->
    <!--[if lte IE 8]>
    <link rel="stylesheet" href="css/ie/v8.css"/><![endif]-->
</head>
<script>
    $(document).ready(function () {
        $("#hider").click(function () {
            $("#longtab").slideToggle();
        });
        $("#hider2").click(function () {
            $("#longtab2").slideToggle();
        });
        $("#hider3").click(function () {
            $("#addblockpanel").slideToggle();
        });
        $("#optiontoggle").click(function () {
            $("#options").slideToggle();
            $("#optionstab").toggleClass("fa-chevron-down fa-minus");
            _paq.push(['trackEvent', 'Options', 'MenuExpanded']);

        });
        $("#autovar").select2({
            placeholder: "Variety",
            data: varieties

        });
        $("#autostr").select2({
            placeholder: "Strain",
            data: strains
        });

        $('#tableEstimatesSubmitter').tablesorter({
            textExtraction: {
                3: function (node, table, cellIndex) {
                    return $(node).find("input").val();
                }
            },
            headers: {
                0: {sorter: false},
                9: {sorter: false}
            }
        });
    });
    //attach event listeners to estimates table
    $(document).on("change", ".estimatesubmitter, .blocknamer", function () {
        $.post("processEstimates.php<?if (isset($_GET['alt_acc'])) {
            echo '?alt_acc=' . $_GET['alt_acc'];
        }
            ?>", $(this).serialize(), function () {
            $.notify("Information Received", "success");
            _paq.push(['trackEvent', 'EstimatesandBlockName', 'Submitted']);
        });
        return false;
    });

    function logout() {
        document.cookie = "auth=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
        document.cookie = "username=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
        document.cookie = "grower=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
        window.location.replace('/');
    }
</script>
<body>

<!-- Header -->
<div id="header" class="skel-layers-fixed">

    <div class="top">

        <!-- Logo -->
        <div id="logo">
            <span class="image"><img src="images/avatar.png" alt=""/></span>
            <h1 id="title"><? echo $userinfo['GrowerName'] ?></h1>
            <p><? echo $companyName ?><br></p>
        </div>

        <!-- Nav -->
        <nav id="nav">
            <ul>
                <li><a href="#top" id="top-link" class="skel-layers-ignoreHref"><span
                            class="icon fa-home">Home</span></a></li>
                <li><a href="#receiving" id="receiving-link" class="skel-layers-ignoreHref"><span class="icon fa-truck">Received Shipments</span></a>
                </li>
                <li><a href="#blockdata" id="blockdata-link" class="skel-layers-ignoreHref"><span class="icon fa-th">Picking Status</span></a>
                </li>
                <li><a href="#estimates" id="estimates-link" class="skel-layers-ignoreHref"><span
                            class="icon fa-sliders">Blocks & Estimates</span></a></li>
                <li><hr style='width: 75%; margin-top: 0; margin-bottom:0; border-top: solid 1px rgba(255,255,255,0.5)'></li>
                <li <?php echo ( $detect->isMobile() ? "style='display: none'" : '')?>><a href="growerCalendar.php" id="top-link" class="skel-layers-ignoreHref"><span class="icon fa-calendar">Picking Calendar</span></a></li>
                <li><a href="QAview.php"><span class="icon fa-area-chart">Block-by-Block QA</span></a>
                </li>
                <li><a href="preharvest.php"><span
                            class="icon fa-stethoscope <?if($numPreHarvest > 0){echo 'phbadge';}?>" data-badge="<?echo $numPreHarvest?>">Pre-Harvest Checkup</span></a></li>
                <li><a href="growerfileshare/"><span
                            class="icon fa-cloud-download">Shared Files</span></a></li>
                <? if ($detect->isMobile()) {
                    echo " <div id='options' style='background: rgba(0,0,0,0.15);'>
                                <li><a href='changepw.php'><span  class=\"icon fa-key\">Change Password</span></a></li>
                                <li><a href=\"#\" onclick=\"logout();\" id=\"logout-link\"><span class=\"icon fa-sign-out\">Log Out</span></a></li>
                                </div>";
                } else {
                    echo "<li id='optiontoggle'><a href='#' ><span id='optionstab' class=\"icon fa-chevron-down\">Options</span></a></li>
                                <div id='options' style='display: none; background: rgba(0,0,0,0.15);'>
                                <li><a href='changepw.php'><span  class=\"icon fa-key\">Change Password</span></a></li>
                                <li><a href='csvExport.php' id='export1' class='skel-layers-ignoreHref'><span class='icon fa-download'>Download My Blocks</a></span></li>
                                <li><a href=# onclick=\"logout();\" id=\"logout-link\"><span class=\"icon fa-sign-out\">Logout / Switch User</span></a></li>
                                </div>";
                }
                ?>
            </ul>
        </nav>

    </div>
</div>

<!-- Main -->
<div id="main">
    <!-- Intro -->
    <section id="top" class="one dark cover">
        <div class="container">

            <header>
                <h2 class="alt"><strong><?php echo $userinfo['GrowerName'] ?></strong> Grower Control Panel<br/>
                    at <strong><? echo $companyName ?></strong></h2>
                <p>Access grower tools and information, review your shipments, and track your estimates.</p>
            </header>
            <footer>
                <a href="#estimates" class="button scrolly"><span
                        class="icon fa-pencil"> Let's do my Estimates</a>
            </footer>

        </div>
    </section>
    <!--Received Shipments -->
    <section id="receiving" class="receiving three">
        <div class="container">
            <header>
                <h2 class="alt">Your Shipments to <? echo $companyName ?></h2>
                <h2><span class="icon fa-truck"></span></h2>
            </header>
            <p>The last 75 trucks you sent to us.</p>
            <p>
                <button id="hider"
                        class="button"><span
                        class="icon fa-eye-slash"> View/Hide Receiving Data <? echo "(" . mysqli_num_rows($receiptdata) . " receipts)" ?></span>
                </button>
            </p>
            <div id="longtab" style="display: none;">
                <table id="receivedTrucksTable" border='1px'>
                    <thead>
                    <tr>
                        <th></th>
                        <th><b>Date Received</th>
                        <th><b>Farm</th>
                        <th><b>Block</th>
                        <th><b>Variety</th>
                        <th><b>Strain</th>
                        <th><b>Bins</th>
                        <th><b>Bushels</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($ReceivingArray = mysqli_fetch_array($receiptdata)) {
                        echo "<tr><td><abbr title='View Delivery #" . $ReceivingArray['RTNum'] . "'><a href='img.php?q=../" . $ReceivingArray['RTNum'] . ".jpg' class='icon fa-camera'></a></td></abbr></td><td>" . date('D, jS M Y', strtotime($ReceivingArray['Date'])) . "</td><td>" . $ReceivingArray['FarmDesc'] . "</td><td>" . $ReceivingArray['BlockDesc'] . "</td><td><abbr title=" . rtrim($ReceivingArray['Comm Desc']) . "><img src='images/" . rtrim($ReceivingArray['Comm Desc']) . ".png' height='25px' width='25px'/></abbr>" . $ReceivingArray['VarDesc'] . "</td><td>" . $ReceivingArray['StrDesc'] . "</td><td>" . $ReceivingArray['Qty'] . "</td><td>" . $ReceivingArray['Bu'] . "</td><tr>";
                    } ?></tbody>
                </table>
            </div>
        </div>
    </section>
    <!--Blockdata -->
    <section id="blockdata" class="blockdata two">
        <div class="container">
            <header>
                <h2 class="alt">Picking Status</h2>
                <h2><span class="icon fa-th"></span></h2>
            </header>
            <p>As you pick and deliver a block's fruit, the block progress will fill up until it reads 100%. Use this
                tool to see how much of a block you've picked according to your estimate, and to refine your estimate
                during picking.</p>
            <p>
                <button id="hider2" class="button" style="text-align: left"><span
                        class="icon fa-eye-slash"> View/Hide Picking Stats</span></button>
            </p>
            <div id="longtab2" style="display: none">
                <?
                if (mysqli_num_rows($blockCompletionData) > 0) {
                    if ($detect->isMobile()) {
                        echo "
                            <table border='1 px'>
                                <tr>
                                    <td><b>Farm</td>
                                    <td><b>Block</td>
                                    <td><b>Variety</td>
                                    <td><b>Total Bushels</td>
                                    <td><b>Block Progress</b></td>
                                    <td><b>Mark as Done Picking</b></td></tr>";
                        while ($blockCompletionTempArray = mysqli_fetch_assoc($blockCompletionData)) {
                            if ($blockCompletionTempArray['isFinished'] == 0) {
                                echo "<tr id='" . $blockCompletionTempArray['PK'] . "'><td>" . $blockCompletionTempArray['FarmDesc'] . "</td><td>" . $blockCompletionTempArray['BlockDesc'] . "</td><td><abbr title='" . rtrim($blockCompletionTempArray['Comm Desc']) . "'><img src='images'" . rtrim($blockCompletionTempArray['Comm Desc']) . ".png' height='25px' width='25px'/></abbr> " . $blockCompletionTempArray['VarDesc'] . "</td><td>" . $blockCompletionTempArray['Total'] . "</td><td><div class='noload'><div class='load' style='width: " . ($blockCompletionTempArray['Percent'] > 100 ? 100 : $blockCompletionTempArray['Percent']) . "%'></div></div></td><td><a href='javascript:void(0)' onclick=\"$.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : $userinfo['GrowerCode']) . "', { Done: " . $blockCompletionTempArray['PK'] . "});_paq.push(['trackEvent', 'Blocks', 'Marked Done']);$('#" . $blockCompletionTempArray['PK'] . "').slideUp();$.notify('Thanks! We won\'t expect any more from that block.', 'success');\"  class='icon fa-unlock-alt' ></a></td><tr>";
                            } else {
                                echo "<tr id='" . $blockCompletionTempArray['PK'] . "' style='color: #5897fb'><td>" . $blockCompletionTempArray['FarmDesc'] . "</td><td>" . $blockCompletionTempArray['BlockDesc'] . "</td><td><abbr title='" . rtrim($blockCompletionTempArray['Comm Desc']) . "'><img src='images/" . rtrim($blockCompletionTempArray['Comm Desc']) . ".png' height='25px' width='25px'/></abbr> " . $blockCompletionTempArray['VarDesc'] . "</td><td>" . $blockCompletionTempArray['Total'] . "</td><td><div class='noload'><div class='load' style='width: " . ($blockCompletionTempArray['Percent'] > 100 ? 100 : $blockCompletionTempArray['Percent']) . "%'></div></div></td><td><a href='javascript:void(0)' onclick=\"$.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : $userinfo['GrowerCode']) . "', { Done: " . $blockCompletionTempArray['PK'] . "});_paq.push(['trackEvent', 'Blocks', 'Marked unDone']);$('#" . $blockCompletionTempArray['PK'] . "').slideUp();$.notify('Thanks! We\'ll open that one again...', 'success');\"  class='icon fa-lock' ></a></td><tr>";
                            }
                        }
                    } else {
                        echo "
                            <table border='1 px'>
                                <tr>
                                <td><b>ID</td>
                                    <td><b>Farm</td>
                                    <td><b>Block</td>
                                    <td><b>Variety</td>
                                    <td><b>Strain</td>
                                    <td><b>Received Bushels</td>
                                    <td><b>Estimated Bushels</td>
                                    <td><b><abbr title='(of the estimate for this block)'>% Done</abbr></td>
                                    <td><b>Block Progress</b></td>
                                    <td><b>Mark as <br>Done Picking</b></td></tr>";
                        while ($blockCompletionTempArray = mysqli_fetch_array($blockCompletionData)) {
                            if ($blockCompletionTempArray['isFinished'] == 0) {
                                echo "<tr id='" . $blockCompletionTempArray['PK'] . "'><td>" . $blockCompletionTempArray['PK'] . "</td><td>" . $blockCompletionTempArray['FarmDesc'] . "</td><td>" . $blockCompletionTempArray['BlockDesc'] . "</td><td><abbr title='" . rtrim($blockCompletionTempArray['Comm Desc']) . "'><img src='images/" . rtrim($blockCompletionTempArray['Comm Desc']) . ".png' height='25px' width='25px'/></abbr> " . $blockCompletionTempArray['VarDesc'] . "</td><td>" . $blockCompletionTempArray['Str Desc'] . "</td><td>" . $blockCompletionTempArray['Total'] . "</td><td>" . $blockCompletionTempArray['Est'] . "</td><td>" . $blockCompletionTempArray['Percent'] . "%</td><td><div class='noload'><div class='load' style='width: " . ($blockCompletionTempArray['Percent'] > 100 ? 100 : $blockCompletionTempArray['Percent']) . "%'></div></div></td><td><a href='javascript:void(0)' onclick=\"$.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : '') . "', {Done: " . $blockCompletionTempArray['PK'] . "});_paq.push(['trackEvent', 'Blocks', 'Marked Done']);$('#" . $blockCompletionTempArray['PK'] . "').slideUp();$.notify('Thanks! We won\'t expect any more from that block.', 'success');\"  class='icon fa-unlock-alt' ></a></td><tr>";
                            } else {
                                echo "<tr id='" . $blockCompletionTempArray['PK'] . "' style='color: #5897fb'><td>" . $blockCompletionTempArray['PK'] . "</td><td>" . $blockCompletionTempArray['FarmDesc'] . "</td><td>" . $blockCompletionTempArray['BlockDesc'] . "</td><td><abbr title='" . rtrim($blockCompletionTempArray['Comm Desc']) . "'><img src='images/" . rtrim($blockCompletionTempArray['Comm Desc']) . ".png' height='25px' width='25px'/></abbr> " . $blockCompletionTempArray['VarDesc'] . "</td><td>" . $blockCompletionTempArray['Str Desc'] . "</td><td>" . $blockCompletionTempArray['Total'] . "</td><td>" . $blockCompletionTempArray['Est'] . "</td><td>" . $blockCompletionTempArray['Percent'] . "%</td><td><div class='noload'><div class='load' style='width: " . ($blockCompletionTempArray['Percent'] > 100 ? 100 : $blockCompletionTempArray['Percent']) . "%'></div></div></td><td><a href='javascript:void(0)' onclick=\"$.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : '') . "', {Done:" . $blockCompletionTempArray['PK'] . "});_paq.push(['trackEvent', 'Blocks', 'Marked unDone']);$('#" . $blockCompletionTempArray['PK'] . "').slideUp();$.notify('Thanks! We\'ll open that one again...', 'success');\"  class='icon fa-lock' ></a></td><tr>";
                            }
                        }

                    }
                } else {
                    echo "<p style='padding: 10px; border: gray 1px solid '>Blocks will begin to appear here once you start delivering fruit for them.</p>";
                }
                ?>
                </table>
            </div>
        </div>
    </section>
    <!-- Crop Estimates -->
    <section id="estimates" class="two">
        <div style="vertical-align: middle;">

            <header>
                <h2>Blocks and Estimates: Season <? echo date('Y') ?></h2>
                <h2><span class="icon fa-sliders"></span></h2>
            </header>
            <p><strong>Every block needs an estimate</strong>, and you may change your estimates as many times as you
                like.<br>If you are retiring a block, or if the block is there in error, hit the trash bin to delete its
                records.<br> Deleted blocks may be restored if needed.</p>
            <div id="estimatestable">

                <button id="hider3" class="button" style="text-align: left"><span
                        class="icon fa-plus"> New Block</span></button>
                <div id="addblockpanel" style="display: none">
                    <form name="newBlock" action="addBlock.php" method="post">
                        <table border='1px'>
                            <thead>
                            <tr>
                                <th><b>Fruit Type</th>
                                <th><b>Variety</th>
                                <th><b>Farm</th>
                                <th><b>Block</th>
                                <th><b>Strain</th>
                                <th><b>'<? echo date('y') ?> Estimate</th>
                            </tr>
                            </thead>
                            <tr>
                                <td><input type="radio" name='CommDesc' value="Apple" required> Apple
                                    <input type="radio" name="CommDesc" value="Peach">Peach<br>
                                    <input type="radio" name="CommDesc" value="Nectarine">Nectarine
                                    <input type="radio" name="CommDesc" value="Pear">Pear
                                    <input type="radio" name="CommDesc" value="Pluot">Pluot
                                </td>
                                <td><select style="width: 100%" name="VarDesc" id="autovar" required></select></td>
                                <td><input type="text" name="Farm" style='width:150px;'></td>
                                <td><input type="text" name="Block" style='width:150px;'></td>
                                <td><select style="width: 100%" name="Strain" id="autostr" required></select></td>
                                <td><input type="number" name="newEst" style='width:110px;' placeholder="Bushels"
                                           required
                                </td>
                            </tr>
                        </table>
                        <input type=submit value="Submit New Block">
                    </form>
                </div>
                <br>
                <hr width="85%">
                <br>
                <form id="PK" name="Estimates" action="processEstimates.php" method="post">
                    <span class="icon fa-th"></span> Your Current Blocks
                    <br><br>
                    <div style="display: inline-block; border:gray solid 1px">
                        <span class="icon fa-info-circle"></span> Color key<br>
                        <p style="background-color:#9ef939; padding:6px; border-top:gray solid 1px; border-right:gray solid 1px; float:left; margin: auto">
                            We've got your numbers</p>
                        <p style="padding:6px; border-top:gray solid 1px; float:left; margin: auto">We still need an
                            estimate</p>
                        <p style="background-color:#FF9990; padding:6px; border-left:gray solid 1px; border-top:gray solid 1px; float:left; margin: auto">
                            Block Deleted</p>
                    </div>
                    <br><br>

                    <?php
                    //prepare the estimates table - mobile has a different view, and rows that are deleted get different styling
                    //show table headers
                    if ($detect->isMobile()) {
                        echo "
                                    <table id='tableEstimatesSubmitter'  border='1px'>
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th><b>Variety</th>
                                            <th><b>Farm</th>
                                            <th><b>Block</th>
                                            <th><b>Strain</th>
                                            <th><b>'" . (date('y') - 2) . "</th>
                                            <th><b>'" . (date('y') - 1) . "</th>
                                            <th><b>'" . date('y') . " Estimate</th>
                                        </tr>
                                        </thead>
                                        <tbody>";
                    } else {
                        echo "
                                    <table id='tableEstimatesSubmitter'  border='1px'>
                                        <thead style='cursor: pointer'>
                                        <tr>
                                            <th></th>
                                            <th><b>Variety</th>
                                            <th><b>Farm</th>
                                            <th><b>Block</th>
                                            <th><b>Strain</th>
                                            <th><b>'" . (date('y') - 3) . " Actual</th>
                                            <th><b>'" . (date('y') - 2) . " Actual</th>
                                            <th><b>'" . (date('y') - 1) . " Estimate</th>
                                            <th><b>'" . (date('y') - 1) . " Actual</th>
                                            <th><b>'" . date('y') . " Estimate</th>
                                        </tr>
                                        </thead>
                                        <tbody>";
                    }
                    while ($growerdata = mysqli_fetch_assoc($estimates)) {
                        if ($detect->isMobile()) {
                            if ($growerdata['isDeleted'] == "0") {
                                echo "<tr id=\"est" . $growerdata['PK'] . "\" " . (($growerdata[date('Y') . 'est'] <> $growerdata[(date('Y') - 1) . 'act'] || $growerdata['isSameAsLastYear']) ? "bgcolor='#9ef939'" : "") . ">
                                <td><a href='javascript:void(0)'  onclick=\"$.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : '') . "', { PK: " . $growerdata['PK'] . "}, function() {_paq.push(['trackEvent', 'Estimates', 'DeleteBlock']);$.notify('Block Deleted. Refresh to see changes.', 'error');$('#est" . $growerdata['PK'] . "').slideUp();});\"  class=\"icon fa-trash-o\"></a></td>
                                <td>" . (($growerdata['Comm Desc'] != "Apple") ? "<abbr title=" . rtrim($growerdata['Comm Desc']) . "><img src='images/" . rtrim($growerdata['Comm Desc']) . ".png' width='25px' height'25px'/></abbr>" : "") . " " . $growerdata['VarDesc'] . "</td>
                                <td>" . $growerdata['FarmDesc'] . "</td>
                                <td><input class='blocknamer' placeholder='Name this block...' type=text name='" . $growerdata['PK'] . "bn' value='" . rtrim($growerdata['BlockDesc']) . "'></td>
                                <td>" . $growerdata['Str Desc'] . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 2) . 'act']) . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 1) . 'act']) . "</td>
                                <td><input 
                                onchange=\"((this.value != " . $growerdata[(date('Y') - 1) . 'act'] . ") ? ($('#" . $growerdata['PK'] . "sameCheckbox').slideUp(), $('#est" . $growerdata['PK'] . "').attr('bgcolor', '#9ef939')) : (($('#est" . $growerdata['PK'] . "').removeAttr('bgcolor')), $('#" . $growerdata['PK'] . "sameCheckbox').slideDown()))\"
                                style='width:110px;' type='number' class='estimatesubmitter' id='" . $growerdata['PK'] . "estbox' name='" . $growerdata['PK'] . "' value='" . $growerdata[date('Y') . 'est'] . "' placeholder='Bushels' " . (($growerdata['isSameAsLastYear']) ? 'readonly' : '') . ">
                                </input><br>
                                <div style='font-size: medium; " . (($growerdata[date('Y') . 'est'] <> $growerdata[(date('Y') - 1) . 'act']) ? 'display: none' : '') . "' id='" . $growerdata['PK'] . "sameCheckbox'>
                                <input onChange=\"(this.checked) ? ($.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : '') . "', { sameEst: " . $growerdata['PK'] . "}), $('#est" . $growerdata['PK'] . "').attr('bgcolor', '#9ef939'), $('#" . $growerdata['PK'] . "estbox').attr('readonly', true)) : ($.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : '') . "', { sameEst: " . $growerdata['PK'] . "}), $('#est" . $growerdata['PK'] . "').removeAttr('bgcolor'), $('#" . $growerdata['PK'] . "estbox').attr('readonly', false))\" type='checkbox' " . (($growerdata['isSameAsLastYear']) ? 'checked' : '') . ">Keep last year's deliveries as your estimate?</input></div></td>
                                </tr>";
                            } else {
                                echo "<tr id=\"est" . $growerdata['PK'] . "\" bgcolor='#FF999'><td><a href='javascript:void(0)' onclick=\"$.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : '') . "', { PK: " . $growerdata['PK'] . "}, function() {_paq.push(['trackEvent', 'Estimates', 'RestoreBlock']);$.notify('Block Restored. Refresh to see changes.', 'success');$('#est" . $growerdata['PK'] . "').slideUp();});\"  class='icon fa-undo' ></a></td
                                ><td>ID-" . $growerdata['PK'] . ":<br>" . (($growerdata['Comm Desc'] != "Apple") ? "<abbr title=" . rtrim($growerdata['Comm Desc']) . "><img src='images/" . rtrim($growerdata['Comm Desc']) . ".png' width='25px' height'25px'/></abbr>" : "") . " " . $growerdata['VarDesc'] . "</td>
                                <td>" . $growerdata['FarmDesc'] . "</td>
                                <td>" . $growerdata['BlockDesc'] . "</td>
                                <td>" . $growerdata['Str Desc'] . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 2) . 'act']) . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 1) . 'act']) . "</td>
                                <td><input type='search' style='width:110px;' name='" . $growerdata['PK'] . "' value='0' disabled readonly</input></td>
                                </tr>";
                            }
                        } else //not mobile
                        {
                            if ($growerdata['isDeleted'] == "0") {
                                echo "<tr id=\"est" . $growerdata['PK'] . "\" " . (($growerdata[date('Y') . 'est'] <> $growerdata[(date('Y') - 1) . 'act'] || $growerdata['isSameAsLastYear']) ? "bgcolor='#9ef939'" : "") . ">
                                <td><a href='javascript:void(0)'  onclick=\"$.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : '') . "', { PK: " . $growerdata['PK'] . "}, function(){_paq.push(['trackEvent', 'Estimates', 'DeleteBlock']);$.notify('Block Deleted. Refresh to see changes.', 'error');$('#est" . $growerdata['PK'] . "').slideUp()});\"  class=\"icon fa-trash-o\"></a></td>
                                <td>" . (($growerdata['Comm Desc'] != "Apple") ? "<abbr title=" . rtrim($growerdata['Comm Desc']) . "><img src='images/" . rtrim($growerdata['Comm Desc']) . ".png' width='25px' height='25px'/></abbr>" : "") . " " . $growerdata['VarDesc'] . "</td>
                                <td>" . $growerdata['FarmDesc'] . "</td>
                                <td><input class='blocknamer' placeholder='Name this block...' type=text name='" . $growerdata['PK'] . "bn' value='" . rtrim($growerdata['BlockDesc']) . "'></td>
                                <td>" . $growerdata['Str Desc'] . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 3) . 'act']) . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 2) . 'act']) . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 1) . 'est']) . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 1) . 'act']) . "</td>
                                <td><input 
                                onchange=\"((this.value != " . $growerdata[(date('Y') - 1) . 'act'] . ") ? ($('#" . $growerdata['PK'] . "sameCheckbox').slideUp(), $('#est" . $growerdata['PK'] . "').attr('bgcolor', '#9ef939')) : (($('#est" . $growerdata['PK'] . "').removeAttr('bgcolor')), $('#" . $growerdata['PK'] . "sameCheckbox').slideDown()))\" 
                                style='width:110px;' type='number' class='estimatesubmitter' id='" . $growerdata['PK'] . "estbox' name='" . $growerdata['PK'] . "' value='" . $growerdata[date('Y') . 'est'] . "' placeholder='Bushels' " . (($growerdata['isSameAsLastYear']) ? 'readonly' : '') . ">
                                </input><br>
                                <div style='font-size: medium; " . (($growerdata[date('Y') . 'est'] <> $growerdata[(date('Y') - 1) . 'act']) ? 'display: none' : '') . "' id='" . $growerdata['PK'] . "sameCheckbox'>
                                <input onChange=\"(this.checked) ? ($.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : '') . "', { sameEst: " . $growerdata['PK'] . "}), $('#est" . $growerdata['PK'] . "').attr('bgcolor', '#9ef939'), $('#" . $growerdata['PK'] . "estbox').attr('readonly', true),$.notify('Information Received', 'success')) : ($.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : '') . "', { sameEst: " . $growerdata['PK'] . "}), $('#est" . $growerdata['PK'] . "').removeAttr('bgcolor'), $('#" . $growerdata['PK'] . "estbox').attr('readonly', false), $.notify('Information Received', 'success'))\" type='checkbox' " . (($growerdata['isSameAsLastYear']) ? 'checked' : '') . ">Keep last year's deliveries as your estimate?</input></div></td>
                                </tr>";
                            } else {
                                echo "<tr id=\"est" . $growerdata['PK'] . "\" bgcolor='#FF999'>
                                <td><a href='javascript:void(0)' onclick=\"$.get('processBlock.php" . (isset($_GET['alt_acc']) ? "?alt_acc=" . $_GET['alt_acc'] : '') . "', { PK: " . $growerdata['PK'] . "}, function(){_paq.push(['trackEvent', 'Estimates', 'RestoreBlock']);$.notify('Block Restored. Refresh to see changes.', 'success');$('#est" . $growerdata['PK'] . "').slideUp();})\"  class='icon fa-undo' ></a></td>
                                <td>ID-" . $growerdata['PK'] . ":<br>" . (($growerdata['Comm Desc'] != "Apple") ? "<abbr title=" . rtrim($growerdata['Comm Desc']) . "><img src='images/" . rtrim($growerdata['Comm Desc']) . ".png' width='25px' height'25px'/></abbr>" : "") . " " . $growerdata['VarDesc'] . "</td>
                                <td>" . $growerdata['FarmDesc'] . "</td>
                                <td>" . $growerdata['BlockDesc'] . "</td>
                                <td>" . $growerdata['Str Desc'] . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 3) . 'act']) . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 2) . 'act']) . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 1) . 'est']) . "</td>
                                <td>" . number_format($growerdata[(date('Y') - 1) . 'act']) . "</td>
                                <td><input type='search' style='width:110px;' name='" . $growerdata['PK'] . "' value='0' disabled readonly</input></td></tr>";
                            }
                        }
                    }
                    ?>
                    </tbody>
                    </table></form>
            </div>
        </div>
    </section>


</div>

<!-- Footer -->
<div id="footer">

    <!-- Copyright -->
    <ul class="copyright">
        <li>&copy;</li>
    </ul>

</div>

</body>
</html>
