<?php

/** @noinspection DuplicatedCode */

/* <Register> */

include("./b2config.php");
include($b2inc . "/b2functions.php");

$action = $_REQUEST['action'] ?? '';

if (!$users_can_register) {
    $action = 'disabled';
}

switch ($action) {
    case "register":

        function filter($value): false|int
        {
            return preg_match('/^[a-zA-Z0-9_\-|]+$/', $value);
        }

        $user_login = $_POST["user_login"];
        $user_email = $_POST["user_email"];
        $pass1 = $_POST["pass1"];
        $pass2 = $_POST["pass2"];


        if (!$user_login) {
            die ("<b>ERROR</b>: please enter a Login");
        }

        if (!$pass1 || !$pass2) {
            die ("<b>ERROR</b>: please enter your password twice");
        }

        if ($pass1 !== $pass2) {
            die ("<b>ERROR</b>: please type the same password in the two password fields");
        }
        $user_nickname = $user_login;

        if (!$user_email) {
            die ("<b>ERROR</b>: please type your e-mail address");
        }

        if (!is_email($user_email)) {
            die ("<b>ERROR</b>: the email address isn't correct");
        }

        $id = mysqli_connect($server, $loginsql, $passsql) or die ("<b>OOPS</b>: can't connect to the server !");
        mysqli_select_db($id, $base) or die ("<b>OOPS</b>: can't select the database $base : " . mysqli_error($id));

        $request = " SELECT user_login FROM $tableusers WHERE user_login = '$user_login'";
        $result = mysqli_query($id, $request) or die ("<b>OOPS</b>: can't check the login...");
        $lines = mysqli_num_rows($result);
        mysqli_free_result($result);
        if ($lines >= 1) {
            die ("<b>ERROR</b>: this login is already registered, please choose another one");
        }

        $user_ip = $_SERVER['REMOTE_ADDR'];
        $user_domain = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $user_browser = $_SERVER['HTTP_USER_AGENT'];

        $user_login = addslashes($user_login);
        $pass1 = addslashes($pass1);
        $user_nickname = addslashes($user_nickname);

        $query = "INSERT INTO $tableusers (user_login, user_pass, user_nickname, user_email, user_ip, user_domain, user_browser, dateYMDhour, user_level, user_idmode) VALUES ('$user_login','$pass1','$user_nickname','$user_email','$user_ip','$user_domain','$user_browser',NOW(),'$new_users_can_blog','nickname')";
        $result = mysqli_query($id, $query);
        if (!$result) {
            die ("<b>ERROR</b>: couldn't register you... please contact the <a href=\"mailto:$admin_email\">webmaster</a> !" . mysqli_error($id));
        }

        $stars = str_repeat("*", strlen($pass1));

        $message = "new user registration on your blog $blogname:\r\n\r\n";
        $message .= "login: $user_login\r\n\r\ne-mail: $user_email";

        @mail($admin_email, "new user registration on your blog $blogname", $message);

        ?>
      <html>
      <head>
        <title>b2 > Registration complete</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <link rel="stylesheet" href="<?= $b2inc ?>/b2.css" type="text/css">
        <style type="text/css">
          <!--
          <?php
          if (!str_contains($HTTP_USER_AGENT, "Nav")) {
          ?>
          textarea, input, select {
            background-color: #f0f0f0;
            border-width: 1px;
            border-color: #cccccc;
            border-style: solid;
            padding: 2px;
            margin: 1px;
          }

          <?php
          }
          ?>
          -->
        </style>
      </head>
      <body bgcolor="#ffffff" text="#000000" link="#cccccc" vlink="#cccccc" alink="#ff0000">

      <table width="100%">
        <tr>
          <td align="center" valign="middle">

            <table width="200" style="border: 1px solid #cccccc;" cellpadding="0" cellspacing="0">

              <tr>
                <td height="50" width="50">
                  <a href="/" target="_blank"><img src="b2-img/b2minilogo.png" border="0" alt="visit b2's homepage"/></a>
                </td>
                <td class="b2menutop" align="center">
                  registration<br/>complete
                </td>
              </tr>

              <tr>
                <td align="right" valign="bottom" height="150" colspan="2">

                  <table width="180">
                    <tr>
                      <td align="right" colspan="2">login: <b><?= $user_login ?>&nbsp;</b></td>
                    </tr>
                    <tr>
                      <td align="right" colspan="2">password: <b><?= $stars ?>&nbsp;</b></td>
                    </tr>
                    <tr>
                      <td align="right" colspan="2">e-mail: <b><?= $user_email ?>&nbsp;</b></td>
                    </tr>
                    <tr>
                      <td width="90">&nbsp;</td>
                      <td>
                        <form name="login" action="b2login.php" method="post">
                          <input type="hidden" name="log" value="<?= $user_login ?>"/>
                          <input type="submit" class="search" value="Login" name="submit"/></form>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

          </td>
        </tr>
      </table>

      </body>
      </html>

        <?php
        break;

    case "disabled":

        ?>
      <html>
      <head>
        <title>b2 > Registration Currently Disabled</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <link rel="stylesheet" href="<?= $b2inc ?>/b2.css" type="text/css">
        <style type="text/css">
          <!--
          <?php
          if (!str_contains($HTTP_USER_AGENT, "Nav")) {
          ?>
          textarea, input, select {
            background-color: #f0f0f0;
            border-width: 1px;
            border-color: #cccccc;
            border-style: solid;
            padding: 2px;
            margin: 1px;
          }

          <?php
          }
          ?>
          -->
        </style>
      </head>
      <body bgcolor="#ffffff" text="#000000" link="#cccccc" vlink="#cccccc" alink="#ff0000">

      <table width="100%">
        <tr>
          <td align="center" valign="middle">

            <table width="200" style="border: 1px solid #cccccc;" cellpadding="0" cellspacing="0">

              <tr>
                <td height="50" width="50">
                  <a href="/" target="_blank"><img src="b2-img/b2minilogo.png" border="0" alt="visit b2's homepage"/></a>
                </td>
                <td class="b2menutop" align="center">
                  registration disabled<br/>
                </td>
              </tr>

              <tr>
                <td align="center" valign="center" height="150" colspan="2">
                  <table width="80%">
                    <tr>
                      <td class="b2menutop">
                        User registration is currently not allowed.<br/>
                        <a href="<?= $siteurl . '/' . $blogfilename ?>">Home</a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

          </td>
        </tr>
      </table>

      </body>
      </html>

        <?php
        break;

    default:

        ?>
      <html>
      <head>
        <title>b2 > Register form</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <link rel="stylesheet" href="<?= $b2inc ?>/b2.css" type="text/css">
        <style type="text/css">
          <!--
          <?php
          if (!str_contains($HTTP_USER_AGENT, "Nav")) {
          ?>
          textarea, input, select {
            background-color: #f0f0f0;
            border-width: 1px;
            border-color: #cccccc;
            border-style: solid;
            padding: 2px;
            margin: 1px;
          }

          <?php
          }
          ?>
          -->
        </style>
      </head>
      <body bgcolor="#ffffff" text="#000000" link="#cccccc" vlink="#cccccc" alink="#ff0000">

      <table width="100%">
        <tr>
        <td align="center" valign="middle">

          <table width="200" style="border: 1px solid #cccccc;" cellpadding="0" cellspacing="0">

            <tr>
              <td height="50" width="50">
                <a href="/" target="_blank"><img src="b2-img/b2minilogo.png" border="0" alt="visit b2's homepage"/></a>
              </td>
              <td class="b2menutop" align="center">
                registration<br/>
              </td>
            </tr>

            <tr>
              <td align="right" valign="bottom" height="150" colspan="2">

                <form method="post" action="b2register.php">
                  <input type="hidden" name="action" value="register"/>
                  <table border="0" width="180" class="menutop" style="background-color: #ffffff">
                    <tr>
                      <td width="150" align="right">login</td>
                      <td>
                        <input type="text" name="user_login" size="8" maxlength="20"/>
                      </td>
                    </tr>
                    <tr>
                      <td align="right">password<br/>(twice)</td>
                      <td>
                        <input type="password" name="pass1" size="8" maxlength="100"/>
                        <br/>
                        <input type="password" name="pass2" size="8" maxlength="100"/>
                      </td>
                    </tr>
                    <tr>
                      <td align="right">e-mail</td>
                      <td>
                        <input type="text" name="user_email" size="8" maxlength="100"/>
                      </td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td><input type="submit" value="OK" class="search" name="submit">
                      </td>
                    </tr>
                  </table>

                </form>

              </td>
            </tr>
          </table>

        </td>
        </tr>
      </table>

      </body>
      </html>
        <?php

        break;
}