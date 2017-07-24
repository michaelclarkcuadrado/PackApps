<?php
require '../config.php';
$userInfo = packapps_authenticate_user('maintenance');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content='Purchasing dashboard'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>Maintenance Dashboard</title>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="../styles-common/materialIcons/material-icons.css">
    <link rel="stylesheet" href="../styles-common/material.min.css">
    <link rel="stylesheet" href="../styles-common/select2.min.css">
    <link rel="stylesheet" href="../scripts-common/dropify/css/dropify.min.css">
    <link rel="stylesheet" href="../styles-common/styles.css">
</head>
<body>
<div class="demo-layout mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">
    <header class="demo-header mdl-layout__header mdl-color--grey-100 mdl-color-text--grey-600">
        <div class="mdl-layout__header-row">
            <span class="mdl-layout-title">Issues Overview</span>
            <div class="mdl-layout-spacer"></div>
            <button id="openFilterBoxButton" class="mdl-button mdl-js-button mdl-button--icon">
                <i class="material-icons">filter_list</i>
            </button>
        </div>
    </header>
    <div class="demo-drawer mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">
        <header class="demo-drawer-header">
            <div class="demo-avatar-dropdown">
                <i style="margin: 2px" class="material-icons">account_circle</i>
                <span style='text-align: center;width:100%'><? echo $userInfo['Real Name'] ?></span>
                <div class="mdl-layout-spacer"></div>
                <button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                    <i class="material-icons" role="presentation">arrow_drop_down</i>
                    <span class="visuallyhidden">Accounts</span>
                </button>
                <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="accbtn">
                    <li class="mdl-menu__item"><i class="material-icons">verified_user</i><?echo $userInfo['Meaning']?> Access</li>
                    <li onclick="location.href = '/appMenu.php'" class="mdl-menu__item"><i class="material-icons">exit_to_app</i>Exit
                        to menu
                    </li>
                </ul>
            </div>
        </header>
        <nav class="demo-navigation mdl-navigation mdl-color--blue-grey-800">
            <a class="mdl-navigation__link" onClick="$('.mdl-card').fadeOut('fast');" href="index.php"><i
                        class="mdl-color-text--teal-400 material-icons"
                        role="presentation">home</i>Home</a>
            <a class="mdl-navigation__link" onClick="$('.mdl-card').fadeOut('fast');" href="issueoverview.php"><i
                        class="mdl-color-text--amber-400 material-icons"
                        role="presentation">assignment_late</i>Issues</a>
            <a class="mdl-navigation__link" onClick="$('.mdl-card').fadeOut('fast');" href="partoverview.php"><i
                        class="mdl-color-text--green-400 material-icons"
                        role="presentation">build</i>Parts</a>
        </nav>
    </div>
    <main class="mdl-layout__content mdl-color--grey-400">
        <div id="insertIssuesHere" class="widthfixer mdl-grid demo-cards">
            <!--            New Issue Card-->
            <div id='createNewIssueCard' class="mdl-card mdl-shadow--4dp mdl-cell mdl-cell--12-col mdl-cell--8-col-tablet mdl-cell--4-col-phone">
                <div style="" class="mdl-card__title mdl-color--yellow-400">
                    <h2 class="mdl-card__title-text"><i class="material-icons">add</i>New Maintenance Issue</h2>
                </div>
                <div class="mdl-grid mdl-card__supporting-text">
                    <div class="mdl-cell--12-col-desktop mdl-cell--8-col-tablet mdl-cell--4-col-phone" style="text-align: center">
                        <table class="table-only-border">
                            <form id='createissueform'>
                                <tr>
                                    <td class="td-only-border" style="text-align:center">
                                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                            <input class="mdl-textfield__input" required type="text" id="newtitleinput">
                                            <label class="mdl-textfield__label" for="sample3">Title</label>
                                        </div>

                                    </td>
                                </tr>
                                <tr>
                                    <td id="categoryradioshere" class="td-only-border" style="text-align:center">
                                        <!--Insert category radios here-->


                                    </td>
                                </tr>
                                <tr>
                                    <td class="td-only-border" style="text-align:center">
                                        <div class="mdl-textfield mdl-js-textfield">
                                            <textarea class="mdl-textfield__input" maxlength="1023" required type="text" rows= "3" id="newdescriptioninput" ></textarea>
                                            <label class="mdl-textfield__label" for="newdescriptioninput">Issue Description</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="td-only-border" style="text-align:center">
                                        Parts needed selector
                                    </td>
                                </tr>
                            </form>
                        </table>
                    </div>
                </div>
                <div class="mdl-card__menu">
                    <button id="closeNewIssueCardButton" class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect">
                        <i class="material-icons">close</i>
                    </button>
                </div>
            </div>
            <!--            Filter Card-->
            <div style="display:none" id='issueFilterbox'
                 class="mdl-card mdl-shadow--4dp mdl-cell mdl-cell--12-col mdl-cell--8-col-tablet mdl-cell--4-col-phone">
                <div style="" class="mdl-card__title mdl-color--yellow-400">
                    <h2 class="mdl-card__title-text"><i class="material-icons">filter_list</i>Filter Issues</h2>
                </div>
                <div class="mdl-grid mdl-card__supporting-text">
                    <div class="mdl-cell--12-col-desktop mdl-cell--8-col-tablet mdl-cell--4-col-phone" style="text-align: center">
                        <table class="table-only-border">
                            <tr>
                                <td class="mdl-cell mdl-cell--4-col-desktop mdl-cell--1-col-phone mdl-cell--2-col-tablet td-only-border" style="text-align:center">
                                    <b style='font-size:large'>Purpose:</b>
                                </td>
                                <td id="purposeCheckboxesInsertHere" class="td-only-border" style="text-align:center">
                                    <!-- Add purpose checkboxes here -->

                                </td>
                            </tr>
                            <tr>
                                <td class="mdl-cell mdl-cell--4-col-desktop mdl-cell--1-col-phone mdl-cell--2-col-tablet td-only-border" style="text-align:center">
                                    <b style='font-size:large'>Status:</b>
                                </td>
                                <td style="text-align:center" class="td-only-border">
                                    <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" style="width: initial; margin-right: 15px" for="checkbox-status-new">
                                        <input type="checkbox" value="new" id="checkbox-status-new" class="status_checkbox issue_filter_input mdl-checkbox__input">
                                        <span class="mdl-checkbox__label">New</span>
                                    </label>
                                    <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" style="width: initial; margin-right: 15px" for="checkbox-status-confirmed">
                                        <input type="checkbox" value="confirmed" id="checkbox-status-confirmed" class="status_checkbox issue_filter_input mdl-checkbox__input">
                                        <span class="mdl-checkbox__label">Confirmed</span>
                                    </label>
                                    <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" style="width: initial; margin-right: 15px" for="checkbox-status-inprogress">
                                        <input type="checkbox" value="inprogress" id="checkbox-status-inprogress" class="status_checkbox issue_filter_input mdl-checkbox__input">
                                        <span class="mdl-checkbox__label">In-Progress</span>
                                    </label>
                                    <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" style="width: initial; margin-right: 15px" for="checkbox-status-completed">
                                        <input type="checkbox" value="completed" id="checkbox-status-completed" class="status_checkbox issue_filter_input mdl-checkbox__input">
                                        <span class="mdl-checkbox__label">Completed</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="mdl-cell mdl-cell--4-col-desktop mdl-cell--1-col-phone mdl-cell--2-col-tablet td-only-border" style="text-align: center">
                                    <b style="font-size:large">Assignment:</b>
                                </td>
                                <td style="text-align: center" class="td-only-border">
                                    <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" style="width: initial; margin-right: 15px" for="checkbox-assignment-unassigned">
                                        <input type="checkbox" name="assignment-checkbox" id="checkbox-assignment-unassigned" class="issue_filter_input mdl-checkbox__input">
                                        <span class="mdl-checkbox__label">Unassigned</span>
                                    </label>
                                    <div style="display:initial">
                                        <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" style="width: initial; margin-right: 15px" for="checkbox-assignment-assignedto">
                                            <input type="checkbox" name="assignment-checkbox" id="checkbox-assignment-assignedto" class="issue_filter_input mdl-checkbox__input">
                                            <span class="mdl-checkbox__label">Assigned To:</span>
                                        </label>
                                        <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--3-col">
                                            <input class="issue_filter_input mdl-textfield__input" type="text" maxlength="20" id="assignedto-text">
                                            <label class="mdl-textfield__label" for="assignto-text">User</label>
                                        </div>
                                    </div>
                                    <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" style="width: initial; margin-right: 15px" for="checkbox-assignment-assignedtoself">
                                        <input type="checkbox" name="assignment-checkbox" id="checkbox-assignment-assignedtoself" class="issue_filter_input mdl-checkbox__input">
                                        <span class="mdl-checkbox__label">Assigned To Me</span>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="mdl-card__menu">
                    <button id="closeFilterBoxButton" class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect">
                        <i class="material-icons">close</i>
                    </button>
                </div>
            </div>
        </div>
    </main>
    <button id="addButton" style="position: fixed; right: 24px; bottom: 24px; padding-top: 24px; margin-bottom: 0; z-index: 90;"
            class="mdl-button mdl-shadow--8dp mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored mdl-color--yellow-400">
        <i class="material-icons">add</i>
    </button>
</div>
<div id='snackbar' style='z-index: 100' class="mdl-js-snackbar mdl-snackbar">
    <div class="mdl-snackbar__text"></div>
    <button class="mdl-snackbar__action" type="button"></button>
</div>
<!--Spinner overlay-->
<div class="overlay" style="display:none;z-index:100">
    <div style="display:flex;justify-content:center;align-items:center;width:100%;height:100%;">
        <div class="mdl-spinner mdl-js-spinner is-active"></div>
    </div>
</div>
<script src="../scripts-common/material.min.js"></script>
<script src="../scripts-common/jquery.min.js"></script>
<script src="../scripts-common/select2.min.js"></script>
<script src="../scripts-common/dropify/js/dropify.min.js"></script>
<!--<script src='../scripts-common/Chart.js'></script>-->
<script>
    var issues = {};
    $(document).ready(function () {
        $('#issueFilterbox').hide();
        $('#createNewIssueCard').hide();
        //init page with issues
        updateIssues(createJsonFromFilter());

        //get all purposes
        $.getJSON('API/getPurposes.php', function(data) {
            //code before function:
//            <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" style="width: initial; margin-right: 15px" for="checkbox-purpose-PURPOSE">
//                <input type="checkbox" value='PURPOSE' name="purpose-checkbox" id="checkbox-purpose-PURPOSE" class="issue_filter_input purpose_checkbox mdl-checkbox__input">
//                <span class="mdl-checkbox__label">PURPOSE</span>
//                </label>
            var htmlStringToInject = "";
            for(var index in data){
                if(data.hasOwnProperty(index)){
                    htmlStringToInject += "<label class=\"mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect\" style=\"width: initial; margin-right: 15px\" for=\"checkbox-purpose-"
                        + data[index]
                        + "\"><input required type=\"checkbox\" value='"
                        + index
                        + "' name=\"purpose-checkbox\" id=\"checkbox-purpose-"
                        + data[index]
                        + "\" class=\"mdl-checkbox__input issue_filter_input purpose_checkbox\"><span class=\"mdl-checkbox__label\">"
                        + data[index]
                        + "</span></label>";
                }
            }
            $("#purposeCheckboxesInsertHere").html(htmlStringToInject);
            //automatically change issues displayed on any filter change
            $('.issue_filter_input').on('change', function(){
                updateIssues(createJsonFromFilter());
            });
            //add radios to new issue card
            var htmlCategoryRadios = "";
            for(var index in data){
                if(data.hasOwnProperty(index)){
                    htmlCategoryRadios += "<label style='margin-right: 10px' class=\"mdl-radio mdl-js-radio mdl-js-ripple-effect\" for=\""
                        + data[index]
                        + "_radio\"><input type=\"radio\" id=\""
                        + data[index]
                        + "_radio\" class=\"mdl-radio__button\" name=\"categories\" value=\""
                        + index
                        + "\"><span class=\"mdl-radio__label\">"
                        + data[index]
                        + "</span></label>";
                }
            }
            $("#categoryradioshere").html(htmlCategoryRadios);
            componentHandler.upgradeDom();
        }).fail(function() {
            $('#openFilterBoxButton').hide();
            $('#addbutton').hide();
        });

        //Start Listeners

        //show filter box when hit
        $('#openFilterBoxButton').on("click", function(){
            $('main').animate({scrollTop: 0}, 'fast', 'swing');
            $('#issueFilterbox').slideDown();
            $(this).hide();
        });

        //close filter box and show opener again
        $('#closeFilterBoxButton').on("click", function() {
            $('#issueFilterbox').slideUp();
            $('#openFilterBoxButton').show();
        });

        //show creation card
        $("#addButton").on("click", function() {
            $('main').animate({scrollTop: 0}, 'fast', 'swing');
            $("#createNewIssueCard").slideDown();
            $(this).fadeOut();
        });

        //close creation card
        $("#closeNewIssueCardButton").on("click", function() {
            $("#createNewIssueCard").slideUp();
            $('#addButton').fadeIn();
        });

        //automatically focus user box if hitting assigned to checkbox
        $('#checkbox-assignment-assignedto').on('change, keydown', function(){
            if($(this).is(':checked')){
                $("#assignedto-text").focus();
            }
        });

        //end listeners
    });
    var statuses = ['New', 'Confirmed', 'In Progress', 'Completed'];

    function refreshIssueCard(issueID) {
        var jsonfilter = createJsonFromFilter();
        $.getJSON('API/getIssues.php', {'filterJson': jsonfilter}, function (data) {
            issues = data;
            $("#issue-card-"+issueID).replaceWith(genIssueCard(issues, issueID));
            $("#issue-card-"+issueID).show();
            componentHandler.upgradeDom();
        });
    }

    function updateIssues(jsonfilter){
        $('.issue-card').remove();
        //get issues and display
        $.getJSON('API/getIssues.php', {'filterJson' : jsonfilter}, function(data){
            issues = data;
            var htmlStringToInject = "";
            for(var issue in data){
                if(data.hasOwnProperty(issue)){
                    htmlStringToInject += genIssueCard(data, issue);
                }
            }
            $('#insertIssuesHere').append(htmlStringToInject);
            componentHandler.upgradeDom();
            $(".issue-card").fadeIn('fast');
        });
    }

    function genIssueCard(data, issue){
        //Code before function
//    <div style="display: none" id="issue-card-ISSUEID" class="mdl-card issue-card mdl-shadow--4dp mdl-cell mdl-cell--6-col-desktop mdl-cell--4-col-phone">
//            <div class="mdl-card__title mdl-color--yellow-400">
//            <h2 class="mdl-card__title-text">#ID - TITLE</h2>
//        </div>
//        <div class="mdl-card__supporting-text" style="position: relative">
//    <div id='cardCover-issue-ISSUEID' class=\"mdl-card\" style='display: none;position: absolute; top:0px;left:0px;width:100%;height:100%'></div>
//            <div class="issue-buttons">
//            <div style="color: white; white-space: nowrap" class="chip mdl-color--green-500">
//            PURPOSE
//            </div>
//            <div style="color: white; white-space: nowrap" class="chip mdl-color--blue-500">
//            <button id="back-status-button-ISSUEID" onclick="statusDecrease(ISSUEID)" class="mdl-button mdl-js-button mdl-button--icon">
//            <i class="material-icons">chevron_left</i>
//            </button>
//            <span style='margin:5px' id="status-display-ISSUEID">STATUS</span>
//            <button id="forward-status-button-ISSUEID" onclick="statusIncrease(ISSUEID)" class="mdl-button mdl-js-button mdl-button--icon">
//            <i class="material-icons">chevron_right</i>
//            </button>
//            </div>
//            <div style="color:white; white-space: nowrap" class="chip mdl-color--red-600">
//            Parts Needed: ##
//    <button id="partsneeded-button-ISSUEID" class="mdl-button mdl-js-button mdl-button--icon">
//            <i class="material-icons">more_horiz</i>
//            </button>
//            <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="partsneeded-button-ISSUEID">
//            <li class="mdl-menu__item" disabled><u>Add To Cart</u></li>
//        <li class="mdl-menu__item" onclick="addAllItemstoCart(ISSUEID)">Add All Items</li>
//        <li class="mdl-menu__item" onclick="addItemToCart(ITEMID)">ITEM DESC : NUM_NEEDED</li>
//        </ul>
//        </div>
//        </div>
//        <ul class="mdl-list">
//            <li class="mdl-list__item mdl-list__item" style="padding:6px">
//            <span class="mdl-list__item-primary-content">
//            <i class="material-icons mdl-list__item-icon">assignment_late</i>
//            <span><p>Issue description. Blue line destroyed!</p></span>
//        </span>
//        </li>
//        <li id="solution-description-ISSUEID" class="mdl-list__item mdl-list__item" style="padding:6px">
//            <span class="mdl-list__item-primary-content">
//            <i class="material-icons mdl-list__item-icon">assignment_turned_in</i>
//            <span><p>Solution description. Blue line glued back together</p></span>
//        </span>
//        </li>
//        <li id="issue-photo-bar-ISSUEID" class="mdl-list__item mdl-list__item" onclick="issuePhoto(issueid, PHOTO_EXISTS)" style="cursor:pointer;padding:6px">
//            <span class="mdl-list__item-primary-content">
//            <i class="material-icons mdl-list__item-icon">add_a_photo</i> <!--photo_camera-->
//            <span>Add a Photo / View Photo</span>
//        </span>
//        </li>
//        <li class="mdl-list__item mdl-list__item" style="padding:6px">
//            <span class="mdl-list__item-primary-content">
//            <i class="material-icons mdl-list__item-icon">history</i>
//            <span>Issue History</span>
//        </span>
//        <span class="mdl-list__item-secondary-content">
//            <i id="expand-button-history-ISSUEID" onclick="expandHistory(issueID, $(this))" class="material-icons mdl-list__item-secondary-action">expand_more</i>
//            </span>
//            </li>
//            <div id="history-panel-ISSUEID" class="sublist_supplier">
//            <li class="mdl-list__item" style="padding:6px; min-height: initial">
//            Created: DATE_CREATED By CREATOR_NAME
//        </li>
//        <li class="mdl-list__item" style="padding:6px; min-height: initial">
//            Confirmed: DATE_CONFIRMED By CONFIRMER_NAME
//        </li>
//        <li class="mdl-list__item" style="padding:6px; min-height: initial">
//            Work Started: DATE_STARTED By STARTER_NAME
//        </li>
//        <li class="mdl-list__item" style="padding:6px; min-height: initial">
//            Completed: DATE_COMPLETED By COMPLETER_NAME
//        </li>
//        </div>
//        <li class="mdl-list__item mdl-list__item" style="padding:6px">
//            <span class="mdl-list__item-primary-content">
//            <i class="material-icons mdl-list__item-icon">location_on</i>
//            <span>Location</span>
//            </span>
//            <span class="mdl-list__item-secondary-content">
//            <i id="issue-location-button-ISSUEID" class="material-icons mdl-list__item-secondary-action">expand_more</i>
//            </span>
//            </li>
//            </ul>
//            <small id="issue-assignedto-text-ISSUEID" class="mdl-card__subtitle-text">Assigned to: ASSIGNEE</small>
//        </div>
//        <div class="mdl-card__menu">
//            <button id="issue-menu-button-ISSUEID" class="mdl-button mdl-js-button mdl-button--icon">
//            <i class="material-icons">more_vert</i>
//            </button>
//            <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="issue-menu-button-ISSUEID">
//            <li onclick="editItem(ISSUEID)" class="mdl-menu__item">Edit Issue</li>
//        <li <?php //if($userInfo['permissionLevel'] < 3){echo "style='display:none'";}?>// onclick="reassignItem(ISSUEID)" class="mdl-menu__item">Assign / Reassign</li>
//            <li <?php //if($userInfo['permissionLevel'] < 3){echo "style='display:none'";}?>// onclick="deleteItem(ISSUEID)"class="mdl-menu__item">Delete Issue</li>
//        </ul>
//        </div>dd
//        </div>
        var statusDesc = getStatusDesc(data[issue]);
        return "<div style=\"display: none\" id=\"issue-card-"
            + data[issue]['issue_id']
            + "\" class=\"mdl-card issue-card mdl-shadow--4dp mdl-cell mdl-cell--6-col-desktop mdl-cell--4-col-phone\"><div class=\"mdl-card__title mdl-color--yellow-400\"><h2 class=\"mdl-card__title-text\">"
            + (statusDesc == 'Completed' ? "<i class='material-icons'>done</i><s> " : "")
            + "#"
            + data[issue]['issue_id']
            + " - "
            + data[issue]['title']
            + (statusDesc == 'Completed' ? "</s>" : "")
            + "</h2></div><div class=\"mdl-card__supporting-text\" style=\"position: relative\"><div id='cardCover-issue-"
            + data[issue]['issue_id']
            + "' class=\"mdl-card\" style='display: none;position: absolute; top:0px;left:0px;width:100%;height:100%'></div><div class=\"issue-buttons\"><div style=\"color: white; white-space: nowrap\" class=\"chip mdl-color--green-500\">"
            + data[issue]['Purpose']
            + "</div><div style=\"color: white; white-space: nowrap\" class=\"chip mdl-color--blue-500\"><button "
            + (statusDesc == 'New' ? "style='display:none'" : '')
            + " id=\"back-status-button-"
            + data[issue]['issue_id']
            + "\" onclick=\"statusDecrease("
            + data[issue]['issue_id']
            + ")\" class=\"mdl-button mdl-js-button mdl-button--icon\"><i class=\"material-icons\">chevron_left</i></button><span style='margin:5px' id=\"status-display-"
            + data[issue]['issue_id']
            + "\">"
            + statusDesc
            + "</span><button "
            + (statusDesc == 'Completed' ? "style='display:none'" : '')
            + " id=\"forward-status-button-"
            + data[issue]['issue_id']
            + "\" onclick=\"statusIncrease("
            + data[issue]['issue_id']
            + ")\" class=\"mdl-button mdl-js-button mdl-button--icon\"><i class=\"material-icons\">chevron_right</i></button></div>"
            + (data[issue]['needsParts'] < 1 ? "" : "<div style=\"color:white; white-space: nowrap\" class=\"chip mdl-color--red-600\">Parts Needed: "
                + Object.keys(data[issue]['partsNeeded']).length
                + "<button id=\"partsneeded-button-"
                + data[issue]['issue_id']
                + "\" class=\"mdl-button mdl-js-button mdl-button--icon\"><i class=\"material-icons\">more_horiz</i></button><ul class=\"mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect\" for=\"partsneeded-button-"
                + data[issue]['issue_id']
                + "\"><li class=\"mdl-menu__item\" disabled><u>Add To Cart</u></li><li class=\"mdl-menu__item\" onclick=\"addAllItemstoCart("
                + data[issue]['issue_id']
                + ")\">Add All Items</li>"
                + generateItemMenuList(data[issue]['partsNeeded'])
                + "</ul></div>")
            + "</div><ul class=\"mdl-list\"><li class=\"mdl-list__item mdl-list__item\" style=\"padding:6px\"><span class=\"mdl-list__item-primary-content\"><i class=\"material-icons mdl-list__item-icon\">assignment_late</i><span><p>"
            + data[issue]['issue_description']
            + "</p></span></span></li>"
            + (data[issue]['isCompleted'] < 1 ? "" : "<li id=\"solution-description-"
                + data[issue]['issue_id']
                + "\" class=\"mdl-list__item mdl-list__item\" style=\"padding:6px\"><span class=\"mdl-list__item-primary-content\"><i class=\"material-icons mdl-list__item-icon\">assignment_turned_in</i><span><p>"
                + data[issue]['solution_description']
                + "</p></span></span></li>")
            + "<li id=\"issue-photo-bar-"
            + data[issue]['issue_id']
            + "\" class=\"mdl-list__item mdl-list__item\" onclick=\"issuePhoto("
            + data[issue]['issue_id']
            + ", "
            + (data[issue]['hasPhotoAttached'] > 0)
            + ")\" style=\"cursor:pointer;padding:6px\"><span class=\"mdl-list__item-primary-content\"><i class=\"material-icons mdl-list__item-icon\">"
            + (data[issue]['hasPhotoAttached'] > 0 ? "photo_camera" : "add_a_photo")
            + "</i><span>"
            + (data[issue]['hasPhotoAttached'] > 0 ? "View Photo" : "Add a Photo")
            + "</span></span></li><li class=\"mdl-list__item mdl-list__item\" style=\"padding:6px\"><span class=\"mdl-list__item-primary-content\"><i class=\"material-icons mdl-list__item-icon\">history</i><span>Issue History</span></span><span class=\"mdl-list__item-secondary-content\"><i id=\"expand-button-history-"
            + data[issue]['issue_id']
            + "\" onclick=\"expandHistory("
            + data[issue]['issue_id']
            + ", $(this))\" class=\"material-icons mdl-list__item-secondary-action\">expand_more</i></span></li><div style=\"display: none\" id=\"history-panel-"
            + data[issue]['issue_id']
            + "\" class=\"sublist_supplier\">"
            + "<li class=\"mdl-list__item\" style=\"padding:6px; min-height: initial\">Created: "
            + data[issue]['dateCreated']
            + " By "
            + data[issue]['createdBy']
            + "</li>"
            + (data[issue]['isConfirmed'] > 0 ? "<li class=\"mdl-list__item\" style=\"padding:6px; min-height: initial\">Confirmed: "
                + data[issue]['dateConfirmed']
                + " By "
                + data[issue]['confirmedBy']
                + "</li>" : "")
            + (data[issue]['isInProgress'] > 0 ? "<li class=\"mdl-list__item\" style=\"padding:6px; min-height: initial\">Work Started: "
                + data[issue]['DateInProgress']
                + " By "
                + data[issue]['inProgressBy']
                + "</li>" : "")
            + (data[issue]['isCompleted'] > 0 ? "<li class=\"mdl-list__item\" style=\"padding:6px; min-height: initial\">Completed: "
                + data[issue]['dateCompleted']
                + " By "
                + data[issue]['completedBy']
                + "</li>" : "")
            + "</div><li class=\"mdl-list__item mdl-list__item\" style=\"padding:6px\"><span class=\"mdl-list__item-primary-content\"><i class=\"material-icons mdl-list__item-icon\">location_on</i><span>Location</span></span><span class=\"mdl-list__item-secondary-content\"><i id=\"issue-location-button-"
            + data[issue]['issue_id']
            + "\" class=\"material-icons mdl-list__item-secondary-action\">close</i></span></li></ul><small id=\"issue-assignedto-text-"
            + data[issue]['issue_id']
            + "\" class=\"mdl-card__subtitle-text\">Assigned to: "
            + data[issue]['assignedTo']
            + "</small></div><div class=\"mdl-card__menu\"><button id=\"issue-menu-button-"
            + data[issue]['issue_id']
            + "\" class=\"mdl-button mdl-js-button mdl-button--icon\"><i class=\"material-icons\">more_vert</i></button><ul class=\"mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect\" for=\"issue-menu-button-"
            + data[issue]['issue_id']
            + "\"><li <?php if($userInfo['permissionLevel'] < 3){echo "style='display:none'";}?> onclick=\"reassignItem("
            + data[issue]['issue_id']
            + ")\" class=\"mdl-menu__item\">Assign / Reassign</li><li <?php if($userInfo['permissionLevel'] < 3){echo "style='display:none'";}?> onclick=\"deleteItem("
            + data[issue]['issue_id']
            + ", $(this))\"class=\"mdl-menu__item\">Delete Issue</li></ul></div></div>";
    }

    function snack(message, length) {
        var data = {
            message: message,
            timeout: length
        };
        document.querySelector('#snackbar').MaterialSnackbar.showSnackbar(data);
    }

    function generateItemMenuList(parts){
        var indexes = Object.keys(parts);
        var partsString = "";
        for(var itemID in indexes){
            partsString += "<li class=\"mdl-menu__item\" onclick=\"addItemToCart("+ indexes[itemID] + ")\">" + parts[indexes[itemID]]['NeededItemDesc'] + ": "+ parts[indexes[itemID]]['NeededItemQty'] +"</li>";
        }
        return partsString;
    }

    function createJsonFromFilter(){
        var filterObj = {};
        //check purpose boxes
        filterObj['purposes'] = [];
        $('.purpose_checkbox').each(function() {
            if($(this).is(':checked')){
                filterObj['purposes'].push($(this).val());
            }
        });
        //check status boxes
        filterObj['statuses'] = [];
        $('.status_checkbox').each(function() {
            if($(this).is(':checked')){
                filterObj['statuses'].push($(this).val());
            }
        });
        //check assignment filters
        filterObj['assignments'] = {};
        filterObj['assignments']['unassigned'] = $('#checkbox-assignment-unassigned').is(':checked');
        filterObj['assignments']['assignedto'] = $('#checkbox-assignment-assignedto').is(':checked');
        filterObj['assignments']['assignedtoname'] = $('#assignedto-text').val();
        filterObj['assignments']['assignedtoself'] = $('#checkbox-assignment-assignedtoself').is(':checked');
        return JSON.stringify(filterObj);
    }

    function getStatusDesc(issue){
        var status = "New";
        if(issue['isConfirmed'] > 0){
            status = "Confirmed";
        }
        if(issue['isInProgress'] > 0){
            status = "In Progress";
        }
        if(issue['isCompleted'] > 0){
            status = "Completed";
        }
        return status;
    }

    function addAllItemstoCart(issueID){

    }

    function addItemToCart(itemID){
        //get cheapest supplier for item, and itemName
        var items = [];
        items.push(itemID);
        items = JSON.stringify(items);
        $.get('API/getItemSupplierInfo.php', {itemID: items}, function(data) {
            data = data[itemID];
            if (sessionStorage.getItem(data.suppID) === null) {
                var obj = {};
                obj['suppName'] = data.suppName;
                obj['items'] = {};
                obj['items'][itemID] = data.itemName;
                sessionStorage.setItem(data.suppID, JSON.stringify(obj));
            } else {
                var existingObj = JSON.parse(sessionStorage.getItem(data.suppID));
                if (!existingObj['items'].hasOwnProperty(itemID)) {
                    existingObj['items'][itemID] = data.itemName;
                }
                sessionStorage.setItem(data.suppID, JSON.stringify(existingObj));
            }
        })
    }

    function statusIncrease(issueID){
        if(getStatusDesc(issues[issueID]) == 'In Progress'){
            var solDesc = prompt("Describe the solution applied:");
            if(solDesc === null){
                return;
            }
        }
        $.post('API/statusChange.php', {direction: 1, issue: issueID, solDesc: solDesc}, function(data){
            refreshIssueCard(issueID);
        }).fail(function() {
            snack("Issue already completed.", 4000)
        });
    }

    function statusDecrease(issueID){
        var go = true;
        if(getStatusDesc(issues[issueID]) == 'Completed'){
            go = confirm("Are you sure you want to re-open this issue?");
        }
        if(go){
            $.post('API/statusChange.php', {direction: 0, issue: issueID}, function(data){
                refreshIssueCard(issueID);
            }).fail(function() {
                snack("Issue is new.", 4000)
            });
        }
    }

    function issuePhoto(issueID, isPhotographed){
        if(isPhotographed){
            var panel = $('#issue-photo-bar-'+issueID);
            panel.html("<button onclick=deleteImage("
                + issueID
                + ") class='mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab'><i class='material-icons'>delete</i></button><a target='_blank' href='API/getImage.php?i="
                + issueID
                + "'><img style='max-width:100%' src='API/getImage.php?i="
                + issueID
                + "'></a>").off('click').css('cursor', 'initial');
        } else {
            clearCardCover(issueID);
            var cardCover = $("#cardCover-issue-"+issueID);
            cardCover.html("<input type='file' data-show-remove=\"false\" accept='image/jpeg' class='dropify' id='image-dropper-"
                + issueID
                + "'></input><button onclick=refreshIssueCard("
                + issueID
                + ") class='mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab'><i class='material-icons'><div id='spinner-imageload-"
                + issueID
                + "'></div>backspace</i></button>");
            var dropper = $('#image-dropper-'+issueID).dropify();
            dropper.on('change', function(changeevent){
                $('.overlay').show();
                var form = new FormData();
                form.append("picture", ($("#image-dropper-"+issueID))[0].files[0]);
                form.append("issue", issueID);
                $.ajax({
                    url: 'API/uploadImage.php',
                    type: 'POST',
                    data: form,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('.overlay').hide();
                        snack('Image uploaded.', 2500);
                    }
                }).fail(function(){
                    changeevent.preventDefault();
                    $('.overlay').hide();
                    snack('Image upload failed.', 2500);
                });
            });
            cardCover.fadeIn();
            $('.dropify-wrapper').css('width', 'initial');
        }
    }

    /*
     * Hide image and send delete request
     */
    function deleteImage(issueID){
        var go = confirm("Are you sure you want to delete this image?");
        if(go){
            $('.overlay').show();
            $.get('API/deleteImage.php', {i: issueID}, function(){
                refreshIssueCard(issueID);
                $('.overlay').hide();
            }).fail(function(){
                $('.overlay').hide();
                snack("Server communication failed.", 2500);
            });
        }
    }

    function expandHistory(issueID, button){
        $("#history-panel-"+issueID).slideToggle();
        button.toggleClass('rotate');
    }

    /*
    * Clears all html from cover pane and recedes it
    * */
    function clearCardCover(issueID){
        $('#cardCover-issue-'+issueID).fadeOut().html("");
    }

    /*
    * Creates a dropdown screen on the card and populates it with a dropdown dialog of users.
    * When user is picked, it fires to setAssignee.php and updates the card.
    * */
    function reassignItem(issueID){
        clearCardCover(issueID);
        var cardCover = $("#cardCover-issue-"+issueID);
        cardCover.html("<div onclick='clearCardCover("
            + issueID
            + ")' style='position: absolute; cursor:pointer; top: 5px; right:10px;'><i class='material-icons'>close</i></div><ul class='mdl-list'><li class='mdl-list__item'><span class='mdl-list__item-primary-content'><i class='material-icons mdl-list__item-icon'>chevron_right</i>Assign the following person as the lead on this issue:</span></li><li class='mdl-list__item'><span class='mdl-list__item-primary-content'><i class='material-icons mdl-list__item-icon'>person</i><select style='width: 100%' id='assignee-select2-"
            + issueID
            + "'><option disabled selected>Choose a user:</option></select></span></li></ul>");
        $.getJSON('API/getMaintenanceUsers.php', function(data){
            $('#assignee-select2-'+issueID).select2({
                data: data
            }).on('select2:select', function(evt){
                $.post('API/setAssignee.php', {'name' : evt.params.data.id, 'issueID': issueID}, function(){
                    $("#issue-assignedto-text-"+issueID).html("Assigned to: "+evt.params.data.text);
                }).fail(function(){
                    snack("Could not set assignee. Check your permissions.", 10000);
                });
            });
        });
        cardCover.fadeIn();
    }

    /*
    * Deletes issue card and removes it from the deck
    * */
    function deleteItem(issueID, button){
        if(confirm("Are you sure you want to permanently delete this issue?")){
            $.get('API/deleteIssue.php', {issue: issueID}, function(data){
                $('#issue-card-'+issueID).slideUp('slow');
                snack('Issue deleted.', 5000);
            }).fail(function(){
                snack('Insufficient permissions.', 6000);
            });
        }
    }
</script>
</body>
</html>