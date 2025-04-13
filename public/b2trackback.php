<?php if (!empty($tb)) { ?>
<!-- you can START editing here -->

	<?php // don't touch these 2 lines
	$queryc = "SELECT * FROM $tablecomments WHERE comment_post_ID = $id AND comment_content LIKE '%<trackback />%' ORDER BY comment_date";
	$resultc = mysql_query($queryc); if ($resultc) {
	?>

<a name="trackbacks"></a>
<div><strong><span style="color: #0099CC">::</span> trackbacks</strong></div>

<p>
( The URL to TrackBack this entry is:<br />
&nbsp;&nbsp;<em><?php trackback_url() ?></em> )
</p>

	<?php /* this line is b2's motor, do not delete it */ while($rowc = mysql_fetch_object($resultc)) { $commentdata = get_commentdata($rowc->comment_ID); ?>
	

<a name="tb<?php comment_ID() ?>"></a>
	

<!-- trackback -->
<p>
<?php comment_text() ?>
<br />
<strong><span style="color: #0099CC">&middot;</span></strong>
<em>Tracked on <a href="<?php comment_author_url(); ?>" title="<?php comment_author() ?>"><?php comment_author() ?></a> on <?php comment_date() ?> @ <?php comment_time() ?></em>
</p>
<p>&nbsp;</p>
<!-- /trackback -->


	<?php /* end of the loop, don't delete */ } ?>


<p>&nbsp;</p>
<div><b><span style="color: #0099CC">::</span> <a href="javascript:history.go(-1)">return to the blog</a></b></div>


	<?php /* if you delete this the sky will fall on your head */ } ?>


</div>



<!-- STOP editing there -->

<?php
	
} else {

if (!empty($_GET['tb_id'])) {
	// trackback is done by a GET
	$tb_id = $_GET['tb_id'];
	$url = $_GET['url'];
	$title = $_GET['title'];
	$excerpt = $_GET['excerpt'];
	$blog_name = $_GET['blog_name'];
} elseif (!empty($_POST['url'])) {
	// trackback is done by a POST
	$request_array = 'HTTP_POST_VARS';
	$tb_id = explode('/', $_SERVER['REQUEST_URI']);
	$tb_id = $tb_id[count($tb_id)-1];
	$url = $_POST['url'];
	$title = $_POST['title'];
	$excerpt = $_POST['excerpt'];
	$blog_name = $_POST['blog_name'];
}

if ((strlen(''.$tb_id)) && (empty($_GET['__mode'])) && (strlen(''.$url))) {

	@header('Content-Type: text/xml');


	require_once("b2config.php");
	require_once("$b2inc/b2template.functions.php");
	require_once("$b2inc/b2vars.php");
	require_once("$b2inc/b2functions.php");

	if (!$use_trackback) {
		trackback_response(1, 'Sorry, this weblog does not allow you to trackback its posts.');
	}

	dbconnect();

	$url = addslashes($url);
	$title = strip_tags($title);
	$title = (strlen($title) > 255) ? substr($title, 0, 252).'...' : $title;
	$excerpt = strip_tags($excerpt);
	$excerpt = (strlen($excerpt) > 255) ? substr($excerpt, 0, 252).'...' : $excerpt;
	$blog_name = htmlspecialchars($blog_name);
	$blog_name = (strlen($blog_name) > 255) ? substr($blog_name, 0, 252).'...' : $blog_name;

	$comment = '<trackback />';
	$comment .= "<b>$title</b><br />$excerpt";

	$author = addslashes($blog_name);
	$email = '';
	$original_comment = $comment;
	$comment_post_ID = $tb_id;
	$autobr = 1;

	$user_ip = $_SERVER['REMOTE_ADDR'];
	$user_domain = gethostbyaddr($user_ip);
	$time_difference = get_settings('time_difference');
	$now = date('Y-m-d H:i:s',(time() + ($time_difference * 3600)));

	$comment = convert_chars($comment);
	$comment = format_to_post($comment);

	$comment_author = $author;
	$comment_author_email = $email;
	$comment_author_url = $url;

	$author = addslashes($author);

	$query = "INSERT INTO $tablecomments VALUES ('0','$comment_post_ID','$author','$email','$url','$user_ip','$now','$comment','0')";
	$result = mysql_query($query);
	if (!$result) {
		die ("There is an error with the database, it can't store your comment...<br>Contact the <a href=\"mailto:$admin_email\">webmaster</a>");
	} else {

		if ($comments_notify) {

			$notify_message  = "New trackback on your post #$comment_post_ID.\r\n\r\n";
			$notify_message .= "website: $comment_author (IP: $user_ip , $user_domain)\r\n";
			$notify_message .= "url    : $comment_author_url\r\n";
			$notify_message .= "excerpt: \n".stripslashes($original_comment)."\r\n\r\n";
			$notify_message .= "You can see all trackbacks on this post there: \r\n";
			$notify_message .= "$siteurl/$blogfilename?p=$comment_post_ID&tb=1\r\n\r\n";

			$postdata = get_postdata($comment_post_ID);
			$authordata = get_userdata($postdata["Author_ID"]);
			$recipient = $authordata["user_email"];
			$subject = "trackback on post #$comment_post_ID \"".$postdata["Title"]."\"";

			@mail($recipient, $subject, $notify_message, "From: b2@".$_SERVER['SERVER_NAME']."\r\n"."X-Mailer: b2 $b2_version - PHP/" . phpversion());
			
		}

		trackback_response(0);
	}

}/* elseif (empty($_GET['__mode'])) {

	header('Content-type: application/xml');
	echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">\n<response>\n<error>1</error>\n";
	echo "<message>Tell me a lie. \nOr just a __mode or url parameter ?</message>\n";
	echo "</response>";

}*/


}

?>