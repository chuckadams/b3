<?php

/** @noinspection DuplicatedCode */

require_once("b2config.php");
require_once(__DIR__ . '/' . $b2inc . "/b2template.functions.php");
require_once(__DIR__ . '/' . $b2inc . "/b2verifauth.php");
require_once(__DIR__ . '/' . $b2inc . "/b2vars.php");
require_once(__DIR__ . '/' . $b2inc . "/b2functions.php");

if (!isset($blogID)) {
    $blog_ID = 1;
}
if (!isset($debug)) {
    $debug = 0;
}
timer_start();

get_currentuserinfo();

$request = " SELECT * FROM $tablesettings ";
$result = mysqli_query($connexion, $request);
$querycount++;
while ($row = mysqli_fetch_object($result)) {
    $posts_per_page = $row->posts_per_page;
    $what_to_show = $row->what_to_show;
    $archive_mode = $row->archive_mode;
    $time_difference = $row->time_difference;
    $autobr = $row->AutoBR;
    $date_format = stripslashes($row->date_format);
    $time_format = stripslashes($row->time_format);
}

// let's deactivate quicktags on IE Mac and Lynx, because they don't work there.
if (($is_macIE) || ($is_lynx)) {
    $use_quicktags = 0;
}

$b2varstoreset = [
    'profile',
    'standalone',
    'redirect',
    'redirect_url',
    'a',
    'popuptitle',
    'popupurl',
    'text',
];

$profile = $_REQUEST['profile'] ?? '';
$standalone = $_REQUEST['standalone'] ?? 0;
$redirect = $_REQUEST['redirect'] ?? 0;
$redirect_url = $_REQUEST['redirect_url'] ?? '';
$a = $_REQUEST['a'] ?? '';
$popuptitle = $_REQUEST['popuptitle'] ?? '';
$popupurl = $_REQUEST['popupurl'] ?? '';
$text = $_REQUEST['text'] ?? '';

if (!$standalone) {
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
  <title>b2 > <?= $title ?></title>
  <link rel="stylesheet" href="<?= $b2inc ?>/b2.css" type="text/css">
  <!--suppress CssUnusedSymbol -->
  <style type="text/css">
    <!--
    <?php
    if (!$is_NS4) {
    ?>
    td.menutop {
      padding-top: 2px;
      padding-bottom: 2px;
      border-color: #999999;
      border-top-width: 1px;
      border-bottom-width: 1px;
      border-left-width: 0;
      border-right-width: 0;
      border-style: dashed;
    }

    textarea, input, select {
      background-color: #f0f0f0;
      border-width: 1px;
      border-color: #cccccc;
      border-style: solid;
      padding: 2px;
      margin: 1px;
    }

    .checkbox {
    <?php
    if ((str_contains($HTTP_USER_AGENT, "MSIE")) && (!str_contains($HTTP_USER_AGENT, "Mac"))) {
    ?> background-color: #ffffff;
      border-width: 0;
      padding: 0;
      margin: 0;
    }

    <?php
    }
    }
    ?>
    -->
  </style>
    <?php if ($redirect) { ?>
      <script>
        setTimeout(() => window.location.assign("<?= $redirect_url ?>"), 600)
      </script>
    <?php } ?>
  <!--suppress JSUnresolvedReference, JSDeprecatedSymbols -->
  <script language="javascript">
    function profile(userID) {
      window.open("b2profile.php?action=viewprofile&user=" + userID, "Profile", "width=500, height=450, location=0, menubar=0, resizable=0, scrollbars=1, status=1, titlebar=0, toolbar=0, screenX=60, left=60, screenY=60, top=60")
    }

    function preview(form) {
      let preview_date = "<?= date("Y-m-d H:i:s") ?>"
      let preview_userid = "<?= $user_ID ?>"
      let preview_title = form.post_title.value
      let preview_category = form.post_category.value
      let preview_content = form.content.value
      let preview_autobr = form.post_autobr.value
      preview_date = escape(preview_date)
      preview_userid = escape(preview_userid)
      preview_title = escape(preview_title)
      preview_category = escape(preview_category)
      preview_content = escape(preview_content)
      preview_autobr = escape(preview_autobr)
      window.open("<?= "$siteurl/$blogfilename" ?>?preview=1&preview_date=" + preview_date + "&preview_userid=" + preview_userid + "&preview_title=" + preview_title + "&preview_category=" + preview_category + "&preview_content=" + preview_content + "&preview_autobr=" + preview_autobr, "Preview", "location=0,menubar=1,resizable=1,scrollbars=yes,status=1,toolbar=0")
    }

    function launchupload() {
      window.open("b2upload.php", "b2upload", "width=380,height=360,location=0,menubar=0,resizable=1,scrollbars=yes,status=1,toolbar=0")
    }

    //  End -->
  </script>
</head>
<body bgcolor="#ffffff" text="#000000">

<table width="100%" cellpadding="0" cellspacing="0" align="center">
    <?php
    if ($profile === "") {
        $profile = 0;
    }
    if ($profile === 0) {
    ?>
  <tr>
    <td valign="top">
        <?php include($b2inc . "/b2menutop.php") ?>
    </td>
  </tr>
  <tr>
      <?php
      }
      ?>
    <td valign="top">
      <!--suppress CheckImageSize -->
      <img src="b2-img/blank.gif" border="0" width="35" height="24"/>
      <div class="panelbody">
<?php } ?>