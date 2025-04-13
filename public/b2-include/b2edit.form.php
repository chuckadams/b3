<?php
echo $tabletop;

$action ??= $_POST['action'] ?? $_GET['action'] ?? '';
$user_ID ??= 69696969696969; // silly value but it should already be set
$edited_post_title ??= '';
$user_level ??= 0;

switch($action) {
	case "post":
		$submitbutton_text = "Blog this !";
		$toprow_title = "New Post";
		$form_action = "post";
		$form_extra = "";
		$colspan = 3;
		break;
	case "edit":
		$submitbutton_text ="Edit this !";
		$toprow_title = "Editing Post #".$postdata["ID"];
		$form_action = "editpost";
		$form_extra = "\" />\n<input type=\"hidden\" name=\"post_ID\" value=\"$post";
		$colspan = 2;
		break;
	case "editcomment":
		$submitbutton_text ="Edit this !";
		$toprow_title = "Editing Comment #".$commentdata["comment_ID"];
		$form_action = "editedcomment";
		$form_extra = "\" />\n<input type=\"hidden\" name=\"comment_ID\" value=\"$comment\" />\n<input type=\"hidden\" name=\"comment_post_ID\" value=\"".$commentdata["comment_post_ID"];
		$colspan = 3;
		break;
}

?>

<form name="post" action="b2edit.php" method="POST">
<input type="hidden" name="user_ID" value="<?php echo $user_ID ?>" />
<input type="hidden" name="action" value="<?php echo $form_action.$form_extra ?>" />

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
	<td<?php 

if ($action != "editcomment") {

// this is for everything but comment editing
	?>>
	<table height="60" align="left" cellpadding="0" cellspacing="0">
		<td height="60" width="190">
		<label for="title"><b>Title :</b></label><br />
		<input type="text" name="post_title" size="20" tabindex="1" style="width: 170px;" value="<?php echo $edited_post_title; ?>" id="title" />
		</td>
		<td>
		<label for="category"><b>Category :</b></label><br /><?php dropdown_categories(); ?>
		</td>
	</table>
	<?php

} else {
	
// this is for comment editing
	?> colspan="2">&nbsp;</td>
</tr>

<tr>
	<td>
	<label for="name"><b>Name :</b></label><br />
	<input type="text" name="newcomment_author" size="20" value="<?php echo format_to_edit($commentdata["comment_author"]) ?>" tabindex="1" id="name" /></td>
	<td>
	<label for="email"><b>E-mail :</b></label><br />
	<input type="text" name="newcomment_author_email" size="20" value="<?php echo format_to_edit($commentdata["comment_author_email"]) ?>" tabindex="2" id="email" /></td>
	<td>
	<label for="URL"><b>URL :</b></label><br />
	<input type="text" name="newcomment_author_url" size="20" value="<?php echo format_to_edit($commentdata["comment_author_url"]) ?>" tabindex="3" id="URL" />
	<?php
	
}

	?>
	</td>
</tr>
<tr>
<td colspan="<?php echo $colspan; ?>">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<td valign="bottom">
<?php
if ($action != 'editcomment') {
	echo '<label for="content"><b>Post :</b></label>';
} else {
	echo '<br /><label for="content"><b>Comment :</b></label>';
}
?>
</td>
<td valign="bottom" align="right">
<?php if ($use_quicktags) include($b2inc.'/b2quicktags.php'); ?>
</td>
</table>

<textarea rows="9" cols="40" style="width:100%" name="content" tabindex="4" wrap="virtual" id="content"><?php echo $content ?></textarea><br />

<input type="checkbox" class="checkbox" name="post_autobr" value="1" <?php
if ($autobr)
echo " checked" ?> tabindex="7" id="autobr" /><label for="autobr"> Auto-BR (converts line-breaks into &lt;br /> tags)</label><br />

<?php if ($use_preview) { ?>
<input type="button" value="preview" onclick="preview(this.form);" class="search" tabindex="8" />
<?php } ?>

<input type="submit" name="submit" value="<?php echo $submitbutton_text ?>" class="search" style="font-weight: bold;" tabindex="5" /> 

<?php if ( ($use_fileupload) && ($user_level >= $fileupload_minlevel) && ((str_contains($fileupload_allowedusers, " ".$user_login." ")) || (trim($fileupload_allowedusers)=="")) ) { ?>
<input type="button" value="upload a file/image" onclick="launchupload();" class="search"  tabindex="10" />
<?php }

// if the level is 5+, allow user to edit the timestamp - not on 'new post' screen though
#if (($user_level > 4) && ($action != "post"))
if ($user_level > 4) {
	touch_time(($action=="edit"));
}
?>
<script language="JavaScript">
<!--
//	document.blog.post_content.focus();
//-->
</script>
</td>
</tr>
</table>
<?php echo $tablebottom ?>
</form>