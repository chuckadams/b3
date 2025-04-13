<?php

/** @noinspection PhpUnused */
/** @noinspection DuplicatedCode */

/* new and improved ! now with more querystring stuff ! */

if (!isset($querystring_start)) {
    $querystring_start = '?';
    $querystring_equal = '=';
    $querystring_separator = '&amp;';
}

if (!function_exists('_')) {
    function _($string)
    {
        return $string;
    }
}

/* functions... */

function get_currentuserinfo(): void
{ // a bit like get_userdata(), on steroids
    global $_COOKIE, $user_login, $userdata, $user_level, $user_ID, $user_nickname, $user_email, $user_url, $user_pass_md5;
    // *** retrieving user's data from cookies and db - no spoofing
    $user_login = $_COOKIE["cafeloguser"];
    $userdata = get_userdatabylogin($user_login);
    $user_level = $userdata["user_level"];
    $user_ID = $userdata['ID'];
    $user_nickname = $userdata["user_nickname"];
    $user_email = $userdata["user_email"];
    $user_url = $userdata["user_url"];
    $user_pass_md5 = md5($userdata["user_pass"]);
}

function dbconnect()
{
    global $connexion, $server, $loginsql, $passsql, $base;
    $connexion = mysqli_connect($server, $loginsql, $passsql) or die("Can't connect to the database server. MySQL said:<br />" . mysqli_error($connexion));
    $connexionbase = mysqli_select_db($connexion, $base) or die("Can't connect to the database $base. MySQL said:<br />" . mysqli_error($connexion));
    return $connexion && $connexionbase;
}

function db_oops($query): never
{
    global $connexion;
    $error = '<p>Oops, MySQL error!</p><p>Your query:<br />' . $query;
    $error .= '</p><p>MySQL said:<br />' . mysqli_error($connexion) . '</p>';
    die($error);
}

/***** Formatting functions *****/

function autobrize($content): string
{
    $content = preg_replace("/<br>\n/", "\n", $content);
    $content = preg_replace("/<br \/>\n/", "\n", $content);
    return preg_replace("/(\015\012)|(\015)|(\012)/", "<br />\n", $content);
}

function unautobrize($content): string
{
    $content = preg_replace("/<br>\n/", "\n", $content);   //for PHP versions before 4.0.5
    return preg_replace("/<br \/>\n/", "\n", $content);
}

function format_to_edit($content): string
{
    global $autobr;
    $content = stripslashes($content);
    if ($autobr) {
        $content = unautobrize($content);
    }
    return htmlspecialchars($content);
}

function format_to_post($content): string
{
    global $post_autobr, $comment_autobr;
    $content = addslashes($content);
    if ($post_autobr || $comment_autobr) {
        $content = autobrize($content);
    }
    return $content;
}

function zeroise($number, $threshold): string
{ // function to add leading zeros when necessary
    $l = strlen($number);
    if ($l < $threshold) {
        for ($i = 0; $i < $threshold - $l; ++$i) {
            $number = '0' . $number;
        }
    }
    return $number;
}

function backslashit($string): string
{
    return preg_replace('/([a-z])/i', '\\\\\1', $string);
}

function mysql2date($dateformatstring, $mysqlstring, $use_b2configmonthsdays = 1): string|false
{
    global $month, $weekday;
    $m = $mysqlstring;
    if (empty($m)) {
        return false;
    }
    $i = mktime(substr($m, 11, 2), substr($m, 14, 2), substr($m, 17, 2), substr($m, 5, 2), substr($m, 8, 2), substr($m, 0, 4));
    if (!empty($month) && !empty($weekday) && $use_b2configmonthsdays) {
        $datemonth = $month[date('m', $i)];
        $dateweekday = $weekday[date('w', $i)];
        $dateformatstring = ' ' . $dateformatstring;
        $dateformatstring = preg_replace("/([^\\\])D/", "\\1" . backslashit(substr($dateweekday, 0, 3)), $dateformatstring);
        $dateformatstring = preg_replace("/([^\\\])F/", "\\1" . backslashit($datemonth), $dateformatstring);
        $dateformatstring = preg_replace("/([^\\\])l/", "\\1" . backslashit($dateweekday), $dateformatstring);
        $dateformatstring = preg_replace("/([^\\\])M/", "\\1" . backslashit(substr($datemonth, 0, 3)), $dateformatstring);
        $dateformatstring = substr($dateformatstring, 1);
    }
    return @date($dateformatstring, $i);
}

function addslashes_gpc($gpc): string
{
    return addslashes($gpc);
}

function date_i18n($dateformatstring, $unixtimestamp): string
{
    global $month, $weekday;
    $i = $unixtimestamp;
    if (!empty($month) && !empty($weekday)) {
        $datemonth = $month[date('m', $i)];
        $dateweekday = $weekday[date('w', $i)];
        $dateformatstring = ' ' . $dateformatstring;
        $dateformatstring = preg_replace("/([^\\\])D/", "\\1" . backslashit(substr($dateweekday, 0, 3)), $dateformatstring);
        $dateformatstring = preg_replace("/([^\\\])F/", "\\1" . backslashit($datemonth), $dateformatstring);
        $dateformatstring = preg_replace("/([^\\\])l/", "\\1" . backslashit($dateweekday), $dateformatstring);
        $dateformatstring = preg_replace("/([^\\\])M/", "\\1" . backslashit(substr($datemonth, 0, 3)), $dateformatstring);
        $dateformatstring = substr($dateformatstring, 1);
    }
    return @date($dateformatstring, $i);
}

function get_weekstartend($mysqlstring, $start_of_week): array
{
    $my = substr($mysqlstring, 0, 4);
    $mm = substr($mysqlstring, 8, 2);
    $md = substr($mysqlstring, 5, 2);
    $day = mktime(0, 0, 0, $md, $mm, $my);
    $weekday = date('w', $day);
    $i = 86400;
    while ($weekday > $start_of_week) {
        $weekday = date('w', $day);
        $day -= 86400;
        $i = 0;
    }
    $week['start'] = $day + 86400 - $i;
    $week['end'] = $day + 691199;
    return $week;
}

function convert_chars($content, $flag = "html"): string
{ // html/unicode entities output, defaults to html
    $newcontent = "";

    global $convert_chars2unicode, $leavecodealone, $use_htmltrans;
    global $b2_htmltrans, $b2_htmltranswinuni;

    ### this is temporary - will be replaced by proper config stuff
    $convert_chars2unicode = 1;
    if ($leavecodealone || !$use_htmltrans) {
        $convert_chars2unicode = 0;
    }
    ###

    // converts HTML-entities to their display values in order to convert them again later

    $content = preg_replace("/<title>(.+?)<\/title>/", "", $content);
    $content = preg_replace("/<category>(.+?)<\/category>/", "", $content);

#	$content = str_replace("&amp;","&#38;",$content);
    $content = strtr($content, $b2_htmltrans);

    for ($i = 0; $i < strlen($content); ++$i) {
        $j = $content[$i];
        $jnext = $content[$i + 1] ?? null;
        $jord = ord($j);
        if ($convert_chars2unicode) {
            switch ($flag) {
                case "unicode":
                    //				$j = str_replace("&","&#38;",$j);
                    if ($jord >= 128 || $j === "&") {
                        $j = "&#" . $jord . ";";
                    }
                    break;
                case "html":
                    if ($jord >= 128) {
                        $j = "&#" . $jord . ";"; // $j = htmlentities($j);
                    } elseif ($j === "&" && $jnext !== "#") {
                        $j = "&amp;";
                    }
                    break;
                case "xml":
                    if ($jord >= 128) {
                        $j = "&#" . $jord . ";"; // $j = htmlentities($j);
                        //					$j = htmlentities($j);
                    } elseif ($j === "&" && $jnext !== "#") {
                        $j = "&#38;";
                    }
                    break;
            }
        }

        $newcontent .= $j;
    }

    // now converting: Windows CP1252 => Unicode (valid HTML)
    // (if you've ever pasted text from MSWord, you'll understand)

    $newcontent = strtr($newcontent, $b2_htmltranswinuni);

    // you can delete these 2 lines if you don't like <br /> and <hr />
    return str_replace(["<br>", "<hr>"], ["<br />", "<hr />"], $newcontent);
}

function convert_bbcode($content): string
{
    global $b2_bbcode, $use_bbcode;
    if ($use_bbcode) {
        $content = preg_replace($b2_bbcode["in"], $b2_bbcode["out"], $content);
    }
    return convert_bbcode_email($content);
}

function convert_bbcode_email($content): string
{
    $bbcode_email["in"] = [
        '#\[email](.+?)\[/email]#is',
        '#\[email=(.+?)](.+?)\[/email]#is',
    ];
    $bbcode_email["out"] = [
        "'<a href=\"mailto:'.antispambot('\\1').'\">'.antispambot('\\1').'</a>'",    // E-mail
        "'<a href=\"mailto:'.antispambot('\\1').'\">\\2</a>'",
    ];

    return preg_replace($bbcode_email["in"], $bbcode_email["out"], $content);
}

function convert_gmcode($content): string
{
    global $b2_gmcode, $use_gmcode;
    if ($use_gmcode) {
        $content = preg_replace($b2_gmcode["in"], $b2_gmcode["out"], $content);
    }
    return $content;
}

function convert_smilies($content): string
{
    global $use_smilies;
    global $b2_smiliessearch, $b2_smiliesreplace;
    if ($use_smilies) {
        $content = str_replace($b2_smiliessearch, $b2_smiliesreplace, $content);
    }
    return $content;
}

function antispambot($emailaddy, $mailto = 0): string
{
    $emailNOSPAMaddy = '';
    mt_srand((float)microtime() * 1000000);
    for ($i = 0; $i < strlen($emailaddy); ++$i) {
        $j = (int)floor(random_int(0, 1 + $mailto));
        if ($j === 0) {
            $emailNOSPAMaddy .= '&#' . ord(substr($emailaddy, $i, 1)) . ';';
        } elseif ($j === 1) {
            $emailNOSPAMaddy .= substr($emailaddy, $i, 1);
        } elseif ($j === 2) {
            $emailNOSPAMaddy .= '%' . zeroise(dechex(ord(substr($emailaddy, $i, 1))), 2);
        }
    }
    return str_replace('@', '&#64;', $emailNOSPAMaddy);
}

function make_clickable($text): string
{ // original function: phpBB, extended here for AIM & ICQ
    $ret = " " . $text;
    $ret = preg_replace("#([\n ])([a-z]+?)://([^, <>{}\n\r]+)#i", "\\1<a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a>", $ret);
    $ret = preg_replace("#([\n ])aim:([^,< \n\r]+)#i", "\\1<a href=\"aim:goim?screenname=\\2\\3&message=Hello\">\\2\\3</a>", $ret);
    $ret = preg_replace("#([\n ])icq:([^,< \n\r]+)#i", "\\1<a href=\"http://wwp.icq.com/scripts/search.dll?to=\\2\\3\">\\2\\3</a>", $ret);
    $ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.~]+)((?:/[^,< \n\r]*)?)#i", "\\1<a href=\"http://www.\\2.\\3\\4\" target=\"_blank\">www.\\2.\\3\\4</a>", $ret);
    $ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([^,< \n\r]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
    return substr($ret, 1);
}

function is_email($user_email): bool
{
    // the email validation regex was a bad idea then, and breaks completely in 2025
    return str_contains($user_email, '@') && str_contains($user_email, '.');
}

function phpcurlme($string): string
{
    // by Matt - http://www.photomatt.net/scripts/phpcurlme

    // This should take care of the single quotes
    $string = preg_replace("/'([dmst])([ .,?!)\/<])/i", "&#8217;$1$2", $string);
    $string = preg_replace("/'([lrv])([el])([ .,?!)\/<])/i", "&#8217;$1$2$3", $string);
    $string = preg_replace("/([^=])(\s+)'([^ >])?(.*?)([^=])'(\s*)([^>&])/S", "$1$2&#8216;$3$4$5&#8217;$6$7", $string);

    // time for the doubles
    $string = preg_replace('/([^=])(\s+)"([^ >])?(.*?)([^=])"(\s*)([^>&])/S', "$1$2&#8220;$3$4$5&#8221;$6$7", $string);
    // multi-paragraph
    $string = preg_replace('/<p>"(.*)<\/p>/U', "<p>&#8220;$1</p>", $string);

    // not a quote, but whatever
    return str_replace(['---', '--'], ['&#8212;', '&#8211;'], $string);
}

function strip_all_but_one_link($text, $mylink): string
{
    $match_link = '#(<a.+?href.+?' . '>)(.+?)(</a>)#';
    preg_match_all($match_link, $text, $matches);
    $count = count($matches[0]);
    for ($i = 0; $i < $count; $i++) {
        if (!str_contains($matches[0][$i], $mylink)) {
            $text = str_replace($matches[0][$i], $matches[2][$i], $text);
        }
    }
    return $text;
}

/***** // Formatting functions *****/

function get_lastpostdate()
{
    global $tableposts, $time_difference, $pagenow, $connexion, $querycount;
    $now = date("Y-m-d H:i:s", time() + $time_difference * 3600);
    if ($pagenow !== 'b2edit.php') {
        $showcatzero = 'post_category > 0 AND';
    } else {
        $showcatzero = '';
    }
    $sql = "SELECT * FROM $tableposts WHERE $showcatzero post_date <= '$now' ORDER BY post_date DESC LIMIT 1";
    $result = mysqli_query($connexion, $sql) or die("Your SQL query: <br />$sql<br /><br />MySQL said:<br />" . mysqli_error($connexion));
    $querycount++;
    return mysqli_fetch_object($result)->post_date;
}

function user_pass_ok($user_login, $user_pass): bool
{
    $userdata = get_userdatabylogin($user_login);
    return $user_pass === $userdata['user_pass'];
}

function get_userdata($userid)
{
    global $tableusers, $querycount, $connexion;
    $sql = "SELECT * FROM $tableusers WHERE ID = '$userid'";
    $result = mysqli_query($connexion, $sql) or die("Your SQL query: <br />$sql<br /><br />MySQL said:<br />" . mysqli_error($connexion));
    $querycount++;
    return mysqli_fetch_array($result);
}

function get_userdata2($userid): array
{ // for team-listing
    global $row;
    $user_data['ID'] = $userid;
    $user_data['user_login'] = $row->user_login;
    $user_data['user_firstname'] = $row->user_firstname;
    $user_data['user_lastname'] = $row->user_lastname;
    $user_data['user_nickname'] = $row->user_nickname;
    $user_data['user_level'] = $row->user_level;
    $user_data['user_email'] = $row->user_email;
    $user_data['user_url'] = $row->user_url;
    return $user_data;
}

function get_userdatabylogin($user_login)
{
    global $tableusers, $querycount, $connexion;
    $sql = "SELECT * FROM $tableusers WHERE user_login = '$user_login'";
    $result = mysqli_query($connexion, $sql) or die("Your SQL query: <br />$sql<br /><br />MySQL said:<br />" . mysqli_error($connexion));
    $querycount++;
    return mysqli_fetch_array($result);
}

function get_userid($user_login)
{
    global $tableusers, $querycount, $connexion;
    $sql = "SELECT ID FROM $tableusers WHERE user_login = '$user_login'";
    $result = mysqli_query($connexion, $sql) or die("No user with the login <i>$user_login</i>");
    $querycount++;
    return mysqli_fetch_array($result)[0];
}

function get_usernumposts($userid)
{
    global $tableposts, $querycount, $connexion;
    $sql = "SELECT * FROM $tableposts WHERE post_author = $userid";
    $result = mysqli_query($connexion, $sql) or die("Your SQL query: <br />$sql<br /><br />MySQL said:<br />" . mysqli_error($connexion));
    $querycount++;
    return mysqli_num_rows($result);
}

function get_settings($setting)
{
    global $tablesettings, $querycount, $connexion;
    $sql = "SELECT * FROM $tablesettings";
    $result = mysqli_query($connexion, $sql) or die("Your SQL query: <br />$sql<br /><br />MySQL said:<br />" . mysqli_error($connexion));
    $querycount++;
    /** @noinspection PhpVariableVariableInspection */
    return mysqli_fetch_object($result)->$setting;
}

function get_postdata($postid)
{
    global $tableposts, $querycount, $connexion;
    $sql = "SELECT * FROM $tableposts WHERE ID = $postid";
    $result = mysqli_query($connexion, $sql) or die("Your SQL query: <br />$sql<br /><br />MySQL said:<br />" . mysqli_error($connexion));
    $querycount++;
    if (mysqli_num_rows($result)) {
        $myrow = mysqli_fetch_object($result);
        return [
            'ID' => $myrow->ID,
            'Author_ID' => $myrow->post_author,
            'Date' => $myrow->post_date,
            'Content' => $myrow->post_content,
            'Title' => $myrow->post_title,
            'Category' => $myrow->post_category,
        ];
    }

    return false;
}

function get_postdata2(): array
{
    global $row;
    return [
        'ID' => $row->ID,
        'Author_ID' => $row->post_author,
        'Date' => $row->post_date,
        'Content' => $row->post_content,
        'Title' => $row->post_title,
        'Category' => $row->post_category,
#		'Notify' => $row->post_notifycomments,
#		'Clickable' => $row->post_make_clickable,
        'Karma' => $row->post_karma, // this isn't used yet
    ];
}

function get_commentdata($comment_ID): array|false
{
    global $tablecomments, $querycount, $connexion;
    $query = "SELECT * FROM $tablecomments WHERE comment_ID = $comment_ID";
    $result = mysqli_query($connexion, $query);
    $querycount++;
    return mysqli_fetch_array($result);
}

function get_catname($cat_ID)
{
    global $tablecategories, $querycount, $connexion;
    $sql = "SELECT * FROM $tablecategories where cat_ID = $cat_ID";
    $result = mysqli_query($connexion, $sql) or die('Oops, couldn\'t query the db for categories.');
    $querycount++;
    return mysqli_fetch_object($result)->cat_name;
}

function profile($user_login): void
{
    global $user_data;
    /** @noinspection UnnecessaryLabelJS (false positive) */
    echo "<a href=\"#\" OnClick=\"javascript:window.open('b2profile.php?user="
        . $user_data["user_login"]
        . "','Profile','toolbar=0,status=1,location=0,directories=0,menuBar=1,scrollbars=1,resizable=0,width=480,height=320,left=100,top=100');\">$user_login</a>";
}

function dropdown_categories(): void
{
    global $postdata, $tablecategories, $querycount, $connexion;
    $postdata ??= ['Category' => 1];
    $query = "SELECT * FROM $tablecategories";
    $result = mysqli_query($connexion, $query);
    $querycount++;
    $width = "170px";
    echo '<select name="post_category" style="width:' . $width . ';" tabindex="2" id="category">';
    while ($row = mysqli_fetch_object($result)) {
        echo "<option value=\"" . $row->cat_ID . "\"";
        if ($row->cat_ID === $postdata["Category"]) {
            echo " selected";
        }
        echo ">" . $row->cat_name . "</option>";
    }
    echo "</select>";
}

function touch_time($edit = 1): void
{
    global $month, $postdata, $time_difference;
    $postdata['Date'] ??= date('Y-m-d H:i:s');
    echo $postdata['Date'];
    echo '<br /><br /><input type="checkbox" class="checkbox" name="edit_date" value="1" id="timestamp" /><label for="timestamp"> Edit timestamp</label><br />';

    $time_adj = time() + $time_difference * 3600;
    $jj = $edit ? mysql2date('d', $postdata['Date']) : date('d', $time_adj);
    $mm = $edit ? mysql2date('m', $postdata['Date']) : date('m', $time_adj);
    $aa = $edit ? mysql2date('Y', $postdata['Date']) : date('Y', $time_adj);
    $hh = $edit ? mysql2date('H', $postdata['Date']) : date('H', $time_adj);
    $mn = $edit ? mysql2date('i', $postdata['Date']) : date('i', $time_adj);
    $ss = $edit ? mysql2date('s', $postdata['Date']) : date('s', $time_adj);

    echo '<input type="text" name="jj" value="' . $jj . '" size="2" maxlength="2" />' . "\n";
    echo "<select name=\"mm\">\n";
    for ($i = 1; $i < 13; ++$i) {
        echo "\t\t\t<option value=\"$i\"";
        if ($i === $mm) {
            echo " selected";
        }
        if ($i < 10) {
            $ii = "0" . $i;
        } else {
            $ii = (string)$i;
        }
        echo ">" . $month[$ii] . "</option>\n";
    }
    echo "</select>";
    ?>

  <input type="text" name="aa" value="<?= $aa ?>" size="4" maxlength="5"/> @
  <input type="text" name="hh" value="<?= $hh ?>" size="2" maxlength="2"/> :
  <input type="text" name="mn" value="<?= $mn ?>" size="2" maxlength="2"/> :
  <input type="text" name="ss" value="<?= $ss ?>" size="2" maxlength="2"/>
    <?php
}

function alert_error($msg): void
{ // displays a warning box with an error message (original by KYank)
    ?>
  <html>
  <head>
    <script language="JavaScript">
      <!--
      alert("<?= $msg ?>")
      history.back()
      //-->
    </script>
  </head>
  <body>
  <!-- this is for non-JS browsers (actually we should never reach that code, but hey, just in case...) -->
  <?= $msg ?><br/>
  <a href="<?= $_SERVER["HTTP_REFERER"] ?>">go back</a>
  </body>
  </html>
    <?php
    exit;
}

function alert_confirm($msg): void
{ // asks a question - if the user clicks Cancel then it brings them back one page
    ?>
  <script language="JavaScript">
    <!--
    if (!confirm("<?= $msg ?>")) {
      history.back()
    }
    //-->
  </script>
    <?php
}

function redirect_js($url, $title = "..."): void
{
    ?>
  <script language="JavaScript">
    <!--
    function redirect() {
      window.location = "<?= $url ?>"
    }

    setTimeout("redirect();", 100)
    //-->
  </script>
  <p>Redirecting you : <b><?= $title ?></b><br/>
    <br/>
    If nothing happens, click <a href="<?= $url ?>">here</a>.</p>
    <?php
    exit();
}

// functions to count the page generation time (from phpBB2)
// ( or just any time between timer_start() and timer_stop() )

function timer_start(): true
{
    global $timestart;
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $timestart = $mtime;
    return true;
}

function timer_stop($display = 0, $precision = 3)
{ //if called like timer_stop(1), will echo $timetotal
    global $timestart, $timeend;
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $timeend = $mtime;
    $timetotal = $timeend - $timestart;
    if ($display) {
        echo number_format($timetotal, $precision);
    }
    return $timetotal;
}

// updates the RSS feed !
function rss_update($num_posts = "", $file = "./b2rss.xml")
{
    global $use_rss, $b2_version, $querystring_start, $querystring_equal, $querystring_separator, $connexion, $time_difference;
    global $admin_email, $siteurl, $blogfilename, $posts_per_rss, $rss_language;
    global $tableposts, $postdata, $row;

    if (!$rss_language) {
        $rss_language = 'en';
    }

    if ($use_rss) {
        $nposts = $num_posts ? 5 : $posts_per_rss;

        $date_now = gmdate("D, d M Y H:i:s") . " GMT";

        # let's build the rss file

        $rss = '<?xml version="1.0"?' . ">\n";
        $rss .= "<!-- generator=\"b2/$b2_version\" -->\n";
        $rss .= "<rss version=\"0.92\">\n";
        $rss .= "\t<channel>\n";
        $rss .= "\t\t<title>" . convert_chars(strip_tags(get_bloginfo("name")), "unicode") . "</title>\n";
        $rss .= "\t\t<link>" . convert_chars(strip_tags(get_bloginfo("url")), "unicode") . "</link>\n";
        $rss .= "\t\t<description>" . convert_chars(strip_tags(get_bloginfo("description")), "unicode") . "</description>\n";
        $rss .= "\t\t<lastBuildDate>$date_now</lastBuildDate>\n";
        $rss .= "\t\t<docs>http://backend.userland.com/rss092</docs>\n";
        $rss .= "\t\t<managingEditor>$admin_email</managingEditor>\n";
        $rss .= "\t\t<webMaster>$admin_email</webMaster>\n";
        $rss .= "\t\t<language>$rss_language</language>\n";

        $now = date('Y-m-d H:i:s', time() + $time_difference * 3600);
        $sql = "SELECT * FROM $tableposts WHERE post_date <= '$now' AND post_category > 0 ORDER BY post_date DESC LIMIT $nposts";
        $result = mysqli_query($connexion, $sql) or die("Your SQL query: <br />$sql<br /><br />MySQL said:<br />" . mysqli_error($connexion));

        while ($row = mysqli_fetch_object($result)) {
            $postdata = get_postdata2();

            $rss .= "\t\t<item>\n";
            $rss .= "\t\t\t<title>" . convert_chars(strip_tags(get_the_title()), "unicode") . "</title>\n";

//		we could add some specific RSS here, but not yet. uncomment if you wish, it's functionnal
//			$rss .= "\t\t\t<category>".convert_chars(strip_tags(get_the_category()),"unicode")."</category>\n";

            $content = stripslashes($row->post_content);
            $content = explode("<!--more-->", $content);
            $content = $content[0];
            $rss .= "\t\t\t<description>" . convert_chars(make_url_footnote($content), "unicode") . "</description>\n";

            $rss .= "\t\t\t<link>" . htmlentities(
                    "$siteurl/$blogfilename" . $querystring_start . 'p' . $querystring_equal . $row->ID . $querystring_separator . 'c' . $querystring_equal . '1',
                ) . "</link>\n";
            $rss .= "\t\t</item>\n";
        }

        $rss .= "\t</channel>\n";
        $rss .= "</rss>";

        $f = @fopen((string)$file, "wb+");
        if ($f) {
            @fwrite($f, $rss);
            @fclose($f);

            return true;
        }

        return false;
    }

    return false;
}

function make_url_footnote($content): string
{
    global $siteurl;
    preg_match_all('/<a(.+?)href=\"(.+?)\"(.*?)>(.+?)<\/a>/', $content, $matches);
    $j = 0;
    $links_summary = '';
    for ($i = 0; $i < count($matches[0]); $i++) {
        $links_summary = !$j ? "\n" : $links_summary;
        $j++;
        $link_match = $matches[0][$i];
        $link_number = '[' . $i + 1 . ']';
        $link_url = $matches[2][$i];
        $link_text = $matches[4][$i];
        $content = str_replace($link_match, $link_text . ' ' . $link_number, $content);
        $link_url = strncasecmp($link_url, 'http://', 7) !== 0 ? $siteurl . $link_url : $link_url;
        $links_summary .= "\n" . $link_number . ' ' . $link_url;
    }
    $content = strip_tags($content);
    $content .= $links_summary;
    return $content;
}

function getposttitle($content): string
{
    global $post_default_title;
    if (preg_match('/<title>(.+?)<\/title>/is', $content, $matchtitle)) {
        $post_title = $matchtitle[0];
        $post_title = preg_replace('/<title>/i', '', $post_title);
        $post_title = preg_replace('/<\/title>/i', '', $post_title);
    } else {
        $post_title = $post_default_title;
    }
    return $post_title;
}

function getpostcategory($content): string
{
    global $post_default_category;
    if (preg_match('/<category>(.+?)<\/category>/is', $content, $matchcat)) {
        $post_category = $matchcat[0];
        $post_category = preg_replace('/<category>/i', '', $post_category);
        $post_category = preg_replace('/<\/category>/i', '', $post_category);
    } else {
        $post_category = $post_default_category;
    }
    return $post_category;
}

function removepostdata($content): string
{
    $content = preg_replace('/<title>(.+?)<\/title>/si', '', $content);
    $content = preg_replace('/<category>(.+?)<\/category>/si', '', $content);
    return trim($content);
}

/*
 balanceTags

 Balances Tags of string using a modified stack.

 @param text      Text to be balanced
 @return          Returns balanced text
 @author          Leonard Lin (leonard@acm.org)
 @version         v1.1
 @date            November 4, 2001
 @license         GPL v2.0
 @notes
 @changelog
             1.2  ***TODO*** Make better - change loop condition to $text
             1.1  Fixed handling of append/stack pop order of end text
                  Added Cleaning Hooks
             1.0  First Version
*/

function balanceTags($text, $is_comment = 0)
{
    global $use_balanceTags;
    if (!$use_balanceTags) {
        return $text;
    }
    if ($is_comment) {
        // sanitise HTML attributes, remove frame/applet tags
        $text = preg_replace('#( on[a-z]+|style|class|id)="(.*?)"#i', '', $text);
        $text = preg_replace('#( on[a-z]+|style|class|id)=\'(.*?)\'#i', '', $text);
        $text = preg_replace('#([a-z]+)="(([ \t])*?)(javascript|vbscript|about):(.*?)"#i', '$1=""', $text);
        $text = preg_replace('#([a-z]+)=\'(([ \t])*?)(javascript|vbscript|about):(.*?)\'#i', '$1=""', $text);
        $text = preg_replace('#<(/?)([a-z]{0,2})(frame|applet)(.*?)>#i', '', $text);
    }

    $tagstack = [];
    $stacksize = 0;
    $tagqueue = '';
    $newtext = '';

    # b2 bug fix for comments - in case you REALLY meant to type '< !--'
    $text = str_replace('< !--', '<    !--', $text);

    # b2 bug fix for LOVE <3 (and other situations with '<' before a number)
    $text = preg_replace('#<([0-9])#', '&lt;$1', $text);

    while (preg_match("/<(\/?\w*)\s*([^>]*)>/", $text, $regex)) {
        $newtext .= $tagqueue;

        $i = strpos($text, $regex[0]);
        $l = strlen($tagqueue) + strlen($regex[0]);

        // clear the shifter
        $tagqueue = '';

        // Pop or Push
        if ($regex[1][0] === "/") { // End Tag
            $tag = strtolower(substr($regex[1], 1));

            // if too many closing tags
            if ($stacksize <= 0) {
                $tag = '';
                //or close to be safe $tag = '/' . $tag;
            } // if stacktop value = tag close value then pop
            elseif ($tagstack[$stacksize - 1] === $tag) { // found closing tag
                $tag = '</' . $tag . '>'; // Close Tag
                // Pop
                array_pop($tagstack);
                $stacksize--;
            } else { // closing tag not at top, search for it
                for ($j = $stacksize - 1; $j >= 0; $j--) {
                    if ($tagstack[$j] === $tag) {
                        // add tag to tagqueue
                        for ($k = $stacksize - 1; $k >= $j; $k--) {
                            $tagqueue .= '</' . array_pop($tagstack) . '>';
                            $stacksize--;
                        }
                        break;
                    }
                }
                $tag = '';
            }
        } else { // Begin Tag
            $tag = strtolower($regex[1]);

            // Tag Cleaning

            // Push if not img or br or hr
            if ($tag !== 'br' && $tag !== 'img' && $tag !== 'hr') {
                $stacksize = array_push($tagstack, $tag);
            }

            // Attributes
            // $attributes = $regex[2];
            $attributes = $regex[2];
            if ($attributes) {
                // fix to avoid CSS defacements
                if ($is_comment) {
                    $attributes = str_replace(['style=', 'class=', 'id='], 'title=', $attributes);
                }
                $attributes = ' ' . $attributes;
            }

            $tag = '<' . $tag . $attributes . '>';
        }

        $newtext .= substr($text, 0, $i) . $tag;
        $text = substr($text, $i + $l);
    }

    // Clear Tag Queue
    $newtext .= $tagqueue;

    // Add Remaining text
    $newtext .= $text;

    // Empty Stack
    while ($x = array_pop($tagstack)) {
        $newtext .= '</' . $x . '>'; // Add remaining tags to close
    }

    # b2 fix for the bug with HTML comments
    return str_replace(["< !--", "<    !--"], ["<!--", "< !--"], $newtext);
}

?>