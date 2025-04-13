<?php
$blog = 1;
include("blog.header.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title><?php bloginfo('name') ?><?php single_post_title(' :: ') ?><?php single_cat_title(' :: ') ?><?php single_month_title(' :: ') ?></title>

  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>

  <style type="text/css" media="screen">
    @import url(layout2b.css);
  </style>
  <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
  <link rel="alternate" type="application/rdf+xml" title="RDF" href="<?php bloginfo('rdf_url'); ?>"/>
  <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php bloginfo('rss2_url'); ?>"/>
    <?php comments_popup_script() ?>

</head>
<body>
<div id="header"><a href="" title="<?php bloginfo('name'); ?>"><?php bloginfo('name'); ?></a></div>

<div id="content">


  <!-- // b2 loop start -->
    <?php while ($row = mysqli_fetch_object($result)) {
        start_b2(); ?>


        <?php the_date("", "<h2>", "</h2>"); ?>
        <?php permalink_anchor(); ?>
      <div class="storyTitle"><?php the_title(); ?>
        <a href="?cat=<?php the_category_ID() ?>" title="category: <?php the_category() ?>"><span class="storyCategory">[<?php the_category() ?>]</span></a>&nbsp;-&nbsp;
        <span class="storyAuthor"><?php the_author() ?> - <?php the_author_email() ?></span> @ <a href="<?php permalink_link() ?>"><?php the_time() ?></a>
      </div>

      <div class="storyContent">
          <?php the_content(); ?>

        <div class="rightFlush">
            <?php link_pages("<br />Pages: ") ?>
            <?php comments_popup_link("Comments (0)", "Comments (1)", "Comments (%)") ?>

          <!-- this includes the comments and a form to add a new comment -->
            <?php include("b2comments.php"); ?>

        </div>

      </div>


      <!-- // this is just the end of the motor - don't touch that line either :) -->
    <?php } ?>


</div>

<div id="menu">

  <h4>quick links:</h4>

  <a href="http://some other site" title="another link">another link</a><br/>
  <a href="http://some other site" title="another link">another link</a><br/>
  <a href="http://some other site" title="another link">another link</a><br/>


  <h4>categories:</h4>

    <?php list_cats(0, 'All', 'name'); ?>

  <h4>search:</h4>

  <form name="searchform" method="get" action="<?php echo $PHP_SELF; /*$siteurl."/".$blogfilename*/ ?>">
    <p>
      <input type="text" name="s" size="15"/><br/>
      <input type="submit" name="submit" value="search"/>
    </p>
  </form>

  <h4>archives:</h4>

    <?php include("b2archives.php"); ?>
  <br/>


  <h4>other:</h4>

  <a href="b2login.php">login</a><br/>
  <a href="b2register.php">register</a><br/>
  <br/>

  <a href="b2rss.php"><img src="b2-img/xml.gif" alt="view this weblog as RSS !" width="36" height="14" border="0"/></a><br/>

</div>

</body>
</html>

