<?php

/** @noinspection DuplicatedCode */

$title = "Profile";
/* <Profile | My Profile> */

function add_magic_quotes($array)
{
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            $array[$k] = add_magic_quotes($v);
        } else {
            $array[$k] = addslashes($v);
        }
    }
    return $array;
}

$_GET = add_magic_quotes($_GET);
$_POST = add_magic_quotes($_POST);
$_COOKIE = add_magic_quotes($_COOKIE);

$action = $_REQUEST["action"] ?? '';
$standalone = $_REQUEST["standalone"] ?? '';
$redirect = $_REQUEST["redirect"] ?? '';
$profile = $_REQUEST["profile"] ?? '';
$user = $_REQUEST["user"] ?? '';

require_once("b2config.php");
require_once("$b2inc/b2functions.php");

dbconnect();

switch ($action) {
    case "update":

        require_once("$b2inc/b2verifauth.php");

        get_currentuserinfo();

        /* checking the nickname has been typed */
        if (empty($_POST["newuser_nickname"])) {
            die ("<strong>ERROR</strong>: please enter your nickname (can be the same as your login)");
        }

        /* if the ICQ UIN has been entered, check to see if it has only numbers */
        if (!empty($_POST["newuser_icq"]) && !(preg_match('/^[0-9]+$/', $_POST["newuser_icq"]))) {
            die ("<strong>ERROR</strong>: your ICQ UIN can only be a number, no letters allowed");
        }

        /* checking e-mail address */
        if (empty($_POST["newuser_email"])) {
            die ("<strong>ERROR</strong>: please type your e-mail address");
        }

        if (!is_email($_POST["newuser_email"])) {
            die ("<strong>ERROR</strong>: the email address isn't correct");
        }

        if ($_POST["pass1"] === "") {
            if ($_POST["pass2"] !== "") {
                die ("<strong>ERROR</strong>: you typed your new password only once. Go back to type it twice.");
            }
            $updatepassword = "";
        } else {
            if ($_POST["pass2"] === "") {
                die ("<strong>ERROR</strong>: you typed your new password only once. Go back to type it twice.");
            }
            if ($_POST["pass1"] !== $_POST["pass2"]) {
                die ("<strong>ERROR</strong>: you typed two different passwords. Go back to correct that.");
            }
            $newuser_pass = $_POST["pass1"];
            $updatepassword = "user_pass='$newuser_pass', ";
            setcookie("cafelogpass", md5($newuser_pass), time() + 31536000);
        }

        $newuser_firstname = addslashes($_POST["newuser_firstname"]);
        $newuser_lastname = addslashes($_POST["newuser_lastname"]);
        $newuser_nickname = addslashes($_POST["newuser_nickname"]);
        $newuser_icq = addslashes($_POST["newuser_icq"]);
        $newuser_aim = addslashes($_POST["newuser_aim"]);
        $newuser_msn = addslashes($_POST["newuser_msn"]);
        $newuser_yim = addslashes($_POST["newuser_yim"]);
        $newuser_email = addslashes($_POST["newuser_email"]);
        $newuser_url = addslashes($_POST["newuser_url"]);
        $newuser_idmode = addslashes($_POST["newuser_idmode"]);

        $query = "UPDATE $tableusers SET user_firstname='$newuser_firstname', "
            . $updatepassword
            . "user_lastname='$newuser_lastname', user_nickname='$newuser_nickname', user_icq='$newuser_icq', user_email='$newuser_email', user_url='$newuser_url', user_aim='$newuser_aim', user_msn='$newuser_msn', user_yim='$newuser_yim', user_idmode='$newuser_idmode' WHERE ID = $user_ID";
        $result = mysqli_query($connexion, $query);
        if (!$result) {
            die (
                "<strong>ERROR</strong>: couldn't update your profile... please contact the <a href=\"mailto:$admin_email\">webmaster</a> !<br /><br />$query<br /><br />"
                . mysqli_error($connexion)
            );
        }

        ?>
      <html>
      <body onload="window.close();">
      Profile updated !<br/>
      If that window doesn't close itself, close it yourself :p
      </body>
      </html>
        <?php

        break;

    case "viewprofile":

        require_once("$b2inc/b2verifauth.php");
        /*	$profile=1;

            get_currentuserinfo();

        */
        $profiledata = get_userdata($user);
        if ($_COOKIE["cafeloguser"] === $profiledata["user_login"]) {
            header("Location: b2profile.php");
        }

        $profile = 1; /**/
        include("b2header.php");
        ?>

      <div class="menutop" align="center">
          <?= $profiledata["user_login"] ?>
      </div>

      <form name="form" action="b2profile.php" method="post">
        <input type="hidden" name="action" value="update"/>
        <table width="100%">
          <tr>
            <td width="250">

              <table cellpadding="5" cellspacing="0">
                <tr>
                  <td align="right"><strong>login</strong></td>
                  <td><?= $profiledata["user_login"] ?></td>
                </tr>
                <tr>
                  <td align="right"><strong>first name</strong></td>
                  <td><?= $profiledata["user_firstname"] ?></td>
                </tr>
                <tr>
                  <td align="right"><strong>last name</strong></td>
                  <td><?= $profiledata["user_lastname"] ?></td>
                </tr>
                <tr>
                  <td align="right"><strong>nickname</strong></td>
                  <td><?= $profiledata["user_nickname"] ?></td>
                </tr>
                <tr>
                  <td align="right"><strong>email</strong></td>
                  <td><?= make_clickable($profiledata["user_email"]) ?></td>
                </tr>
                <tr>
                  <td align="right"><strong>URL</strong></td>
                  <td><?= $profiledata["user_url"] ?></td>
                </tr>
                <tr>
                  <td align="right"><strong>ICQ</strong></td>
                  <td><?php if ($profiledata["user_icq"] > 0) {
                          echo make_clickable("icq:" . $profiledata["user_icq"]);
                      } ?></td>
                </tr>
                <tr>
                  <td align="right"><strong>AIM</strong></td>
                  <td><?= make_clickable("aim:" . $profiledata["user_aim"]) ?></td>
                </tr>
                <tr>
                  <td align="right"><strong>MSN IM</strong></td>
                  <td><?= $profiledata["user_msn"] ?></td>
                </tr>
                <tr>
                  <td align="right"><strong>YahooIM</strong></td>
                  <td><?= $profiledata["user_yim"] ?></td>
                </tr>
              </table>

            </td>
            <td valign="top">

              <table cellpadding="5" cellspacing="0">
                <tr>
                  <td>
                    <strong>ID</strong> <?= $profiledata["ID"] ?></td>
                </tr>
                <tr>
                  <td>
                    <strong>level</strong> <?= $profiledata["user_level"] ?>
                  </td>
                </tr>
                <tr>
                  <td>
                    <strong>posts</strong>
                      <?php
                      $posts = get_usernumposts($user);
                      echo $posts;
                      ?>
                  </td>
                </tr>
                <tr>
                  <td>
                    <strong>identity</strong><br/>
                      <?php
                      switch ($profiledata["user_idmode"]) {
                          case "nickname":
                              $r = $profiledata["user_nickname"];
                              break;
                          case "login":
                              $r = $profiledata["user_login"];
                              break;
                          case "firstname":
                              $r = $profiledata["user_firstname"];
                              break;
                          case "lastname":
                              $r = $profiledata["user_lastname"];
                              break;
                          case "namefl":
                              $r = $profiledata["user_firstname"] . " " . $profiledata["user_lastname"];
                              break;
                          case "namelf":
                              $r = $profiledata["user_lastname"] . " " . $profiledata["user_firstname"];
                              break;
                      }
                      echo $r;
                      ?>
                  </td>
                </tr>
              </table>

            </td>
        </table>

      </form>
        <?php

        break;

    default:

        $profile = 1;
        include("b2header.php");
        $profiledata = get_userdata($user_ID);

        ?>

      <form name="form" action="b2profile.php" method="post">
        <input type="hidden" name="action" value="update"/>
        <input type="hidden" name="checkuser_id" value="<?= $user_ID ?>"/>
        <table width="100%">
          <tr>
            <td width="200" valign="top">

              <table cellpadding="5" cellspacing="0">
                <tr>
                  <td align="right"><strong>login</strong></td>
                  <td><?= $profiledata["user_login"] ?></td>
                </tr>
                <tr>
                  <td align="right"><strong>first name</strong></td>
                  <td><input type="text" name="newuser_firstname" value="<?= $profiledata["user_firstname"] ?>" class="postform"/></td>
                </tr>
                <tr>
                  <td align="right"><strong>last name</strong></td>
                  <td><input type="text" name="newuser_lastname" value="<?= $profiledata["user_lastname"] ?>" class="postform"/></td>
                </tr>
                <tr>
                  <td align="right"><strong>nickname</strong></td>
                  <td><input type="text" name="newuser_nickname" value="<?= $profiledata["user_nickname"] ?>" class="postform"/></td>
                </tr>
                <tr>
                  <td align="right"><strong>email</strong></td>
                  <td><input type="text" name="newuser_email" value="<?= $profiledata["user_email"] ?>" class="postform"/></td>
                </tr>
                <tr>
                  <td align="right"><strong>URL</strong></td>
                  <td><input type="text" name="newuser_url" value="<?= $profiledata["user_url"] ?>" class="postform"/></td>
                </tr>
                <tr>
                  <td align="right"><strong>ICQ</strong></td>
                  <td><input
                        type="text" name="newuser_icq" value="<?php if ($profiledata["user_icq"] > 0) {
                          echo $profiledata["user_icq"];
                      } ?>" class="postform"
                    /></td>
                </tr>
                <tr>
                  <td align="right"><strong>AIM</strong></td>
                  <td><input type="text" name="newuser_aim" value="<?= $profiledata["user_aim"] ?>" class="postform"/></td>
                </tr>
                <tr>
                  <td align="right"><strong>MSN IM</strong></td>
                  <td><input type="text" name="newuser_msn" value="<?= $profiledata["user_msn"] ?>" class="postform"/></td>
                </tr>
                <tr>
                  <td align="right"><strong>YahooIM</strong></td>
                  <td><input type="text" name="newuser_yim" value="<?= $profiledata["user_yim"] ?>" class="postform"/></td>
                </tr>
              </table>

            </td>
            <td valign="top">
              <table cellpadding="5" cellspacing="0">
                <tr>
                  <td>
                    <strong>ID</strong> <?= $profiledata["ID"] ?></td>
                </tr>
                <tr>
                  <td>
                    <strong>level</strong> <?= $profiledata["user_level"] ?>
                  </td>
                </tr>
                <tr>
                  <td>
                    <strong>posts</strong>
                      <?php
                      $posts = get_usernumposts($user_ID);
                      echo $posts;
                      ?>
                  </td>
                </tr>
                <tr>
                  <td>
                    <strong>identity</strong> on the blog:<br>
                    <select name="newuser_idmode" class="postform">
                      <option
                          value="nickname"<?php
                      if ($profiledata["user_idmode"] === "nickname") {
                          echo " selected";
                      } ?>><?= $profiledata["user_nickname"] ?></option>
                      <option
                          value="login"<?php
                      if ($profiledata["user_idmode"] === "login") {
                          echo " selected";
                      } ?>><?= $profiledata["user_login"] ?></option>
                      <option
                          value="firstname"<?php
                      if ($profiledata["user_idmode"] === "firstname") {
                          echo " selected";
                      } ?>><?= $profiledata["user_firstname"] ?></option>
                      <option
                          value="lastname"<?php
                      if ($profiledata["user_idmode"] === "lastname") {
                          echo " selected";
                      } ?>><?= $profiledata["user_lastname"] ?></option>
                      <option
                          value="namefl"<?php
                      if ($profiledata["user_idmode"] === "namefl") {
                          echo " selected";
                      } ?>><?= $profiledata["user_firstname"] . " " . $profiledata["user_lastname"] ?></option>
                      <option
                          value="namelf"<?php
                      if ($profiledata["user_idmode"] === "namelf") {
                          echo " selected";
                      } ?>><?= $profiledata["user_lastname"] . " " . $profiledata["user_firstname"] ?></option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>
                    <br/>
                    new <strong>password</strong> (twice)<br>
                    <input type="password" name="pass1" size="16" value="" class="postform"/><br>
                    <input type="password" name="pass2" size="16" value="" class="postform"/>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" align="center"><br/><input class="search" type="submit" value="Update" name="submit"><br/>Note: closes the popup window.</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </form>
        <?php

        break;
}

include($b2inc . "/b2footer.php") ?>