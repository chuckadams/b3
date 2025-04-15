<?php

/** @noinspection DuplicatedCode */

$title = "Categories";
/* <Categories> */

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

$action = $_REQUEST['action'] ?? '';
$standalone = $_REQUEST['standalone'] ?? '';
$cat = $_REQUEST['cat'] ?? '';

switch ($action) {
    case "addcat":

        $standalone = 1;
        require_once("./b2header.php");

        if ($user_level < 3) {
            die ("Cheatin' uh ?");
        }

        $cat_name = addslashes($_POST["cat_name"]);

        $query = "INSERT INTO $tablecategories (cat_ID,cat_name) VALUES ('0', '$cat_name')";
        $result = mysqli_query($connexion, $query) or die("Couldn't add category <b>$cat_name</b>");

        header("Location: b2categories.php");

        break;

    case "Delete":

        $standalone = 1;
        require_once("./b2header.php");

        $cat_ID = (int)$_POST["cat_ID"];
        $cat_name = get_catname($cat_ID);
        $cat_name = addslashes($cat_name);

        if ($cat_ID === 1) {
            die("Can't delete the <b>$cat_name</b> category: this is the default one");
        }

        if ($user_level < 3) {
            die ("Cheatin' uh ?");
        }

        $query = "DELETE FROM $tablecategories WHERE cat_ID=\"$cat_ID\"";
        $result = mysqli_query($connexion, $query) or die("Couldn't delete category <b>$cat_name</b>" . mysqli_error($connexion));

        $query = "UPDATE $tableposts SET post_category='1' WHERE post_category='$cat_ID'";
        $result = mysqli_query($connexion, $query) or die("Couldn't reset category on posts where category was <b>$cat_name</b>");

        header("Location: b2categories.php");

        break;

    case "Rename":

        require_once("./b2header.php");
        $cat_name = get_catname($_POST["cat_ID"]);
        $cat_name = addslashes($cat_name);
        ?>
        <?= $blankline ?>
        <?= $tabletop ?>
      <p><b>Old</b> name: <?= $cat_name ?></p>
      <p>
      <form name="renamecat" action="b2categories.php" method="post">
        <b>New</b> name:<br/>
        <input type="hidden" name="action" value="editedcat"/>
        <input type="hidden" name="cat_ID" value="<?= $_POST["cat_ID"] ?>"/>
        <input type="text" name="cat_name" value="<?= $cat_name ?>"/><br/>
        <input type="submit" name="submit" value="Edit it !" class="search"/>
      </form>
        <?= $tablebottom ?>

        <?php

        break;

    case "editedcat":

        $standalone = 1;
        require_once("./b2header.php");

        if ($user_level < 3) {
            die ("Cheatin' uh ?");
        }

        $cat_name = addslashes($_POST["cat_name"]);
        $cat_ID = addslashes($_POST["cat_ID"]);

        $query = "UPDATE $tablecategories SET cat_name='$cat_name' WHERE cat_ID=$cat_ID";
        $result = mysqli_query($connexion, $query) or die("Couldn't edit category <b>$cat_name</b>: " . mysqli_error($connexion));

        header("Location: b2categories.php");

        break;

    default:

        $standalone = 0;
        require_once("./b2header.php");
        if ($user_level < 3) {
            die("You have no right to edit the categories for this blog.<br>Ask for a promotion to your <a href=\"mailto:$admin_email\">blog admin</a> :)");
        }
        ?>

        <?= $blankline ?>
        <?= $tabletop ?>
      <table width="" cellpadding="5" cellspacing="0">
        <tr>
          <td>
            <form name="cats" method="post">
              <b>Edit</b> a category:<br/>
                <?php
                $query = "SELECT * FROM $tablecategories ORDER BY cat_ID";
                $result = mysqli_query($connexion, $query);
                echo "<select name=\"cat_ID\">\n";
                while ($row = mysqli_fetch_object($result)) {
                    echo "\t<option value=\"" . $row->cat_ID . "\"";
                    if ($row->cat_ID === (int)$cat) {
                        echo " selected";
                    }
                    echo ">" . $row->cat_ID . ": " . $row->cat_name . "</option>\n";
                }
                echo "</select>\n";
                ?><br/>
              <input type="submit" name="action" value="Delete" class="search"/>
              <input type="submit" name="action" value="Rename" class="search"/>
            </form>
            <p>
              <b>Add</b> a category:<br/>
            <form name="addcat" action="b2categories.php" method="post">
              <input type="hidden" name="action" value="addcat"/>
              <input type="text" name="cat_name"/><br/>
              <input type="submit" name="submit" value="Add it !" class="search"/></form>
          </td>
        </tr>
      </table>
        <?= $tablebottom ?>

      <br/>

        <?= $tabletop ?>
      <b>Note:</b><br/>
      Deleting a category does not delete posts from that category.<br/>It will just set them back to the default category <b><?= get_catname(1) ?></b>.
        <?= $tablebottom ?>

        <?php
        break;
}

/* </Categories> */
include($b2inc . "/b2footer.php"); ?>