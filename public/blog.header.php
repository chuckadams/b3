<?php
$use_cache = 1;

/* Including config and functions files */
require_once(__DIR__ . '/b2config.php');
require_once(__DIR__ . '/' . $b2inc . '/b2template.functions.php');
require_once(__DIR__ . '/' . $b2inc . '/b2vars.php');
require_once(__DIR__ . '/' . $b2inc . '/b2functions.php');

$author = $_REQUEST['author'] ?? '';
$c = $_REQUEST['c'] ?? '';
$calendar = $_REQUEST['calendar'] ?? '';
$cat = $_REQUEST['cat'] ?? '';
$debug = $_REQUEST['debug'] ?? '';
$exact = $_REQUEST['exact'] ?? '';
$m = $_REQUEST['m'] ?? '';
$more = $_REQUEST['more'] ?? '';
$order = $_REQUEST['order'] ?? '';
$orderby = $_REQUEST['orderby'] ?? '';
$p = $_REQUEST['p'] ?? '';
$page = $_REQUEST['page'] ?? '';
$paged = $_REQUEST['paged'] ?? '';
$pb = $_REQUEST['pb'] ?? '';
$postend = $_REQUEST['postend'] ?? '';
$posts = $_REQUEST['posts'] ?? '';
$poststart = $_REQUEST['poststart'] ?? '';
$preview = $_REQUEST['preview'] ?? '';
$s = $_REQUEST['s'] ?? '';
$search = $_REQUEST['search'] ?? '';
$sentence = $_REQUEST['sentence'] ?? '';
$tb = $_REQUEST['tb'] ?? '';
$w = $_REQUEST['w'] ?? '';
$withcomments = $_REQUEST['withcomments'] ?? '';

/* Connecting to the db */
dbconnect();

/* Sending HTTP headers */
@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");                // Date in the past
@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified 
@header("Cache-Control: no-store, no-cache, must-revalidate");    // HTTP/1.1
@header("Cache-Control: post-check=0, pre-check=0", false);
@header("Pragma: no-cache");                                    // HTTP/1.0

/* Getting settings from db */
$posts_per_page = get_settings('posts_per_page');
$what_to_show = get_settings('what_to_show');
$archive_mode = get_settings('archive_mode');
$dateformat = stripslashes(get_settings('date_format'));
$timeformat = stripslashes(get_settings('time_format'));
$autobr = get_settings('AutoBR');
$time_difference = get_settings('time_difference');

/* First let's clear some variables */
$whichcat = '';
$whichauthor = '';
$result = '';
$where = '';
$limits = '';
$distinct = '';

if ($pagenow !== 'b2edit.php') {
    timer_start();
}

if ($posts) {
    $posts = (int)$posts;
    $posts_per_page = $posts;
}
// if a month is specified in the querystring, load that month
if ($m !== '') {
    $m = '' . (int)$m;
    $where .= ' AND YEAR(post_date)=' . substr($m, 0, 4);
    if (strlen($m) > 5) {
        $where .= ' AND MONTH(post_date)=' . substr($m, 4, 2);
    }
    if (strlen($m) > 7) {
        $where .= ' AND DAYOFMONTH(post_date)=' . substr($m, 6, 2);
    }
    if (strlen($m) > 9) {
        $where .= ' AND HOUR(post_date)=' . substr($m, 8, 2);
    }
    if (strlen($m) > 11) {
        $where .= ' AND MINUTE(post_date)=' . substr($m, 10, 2);
    }
    if (strlen($m) > 13) {
        $where .= ' AND SECOND(post_date)=' . substr($m, 12, 2);
    }
}

if ($w !== '') {
    $w = '' . (int)$w;
    $where .= ' AND WEEK(post_date,1)=' . $w;
}

// if a post number is specified, load that post
if (($p !== '') && ($p !== 'all')) {
    $p = (int)$p;
    $where = ' AND ID = ' . $p;
}

// if a search pattern is specified, load the posts that match
if (!empty($s)) {
    $s = addslashes_gpc($s);
    $search = ' AND (';
    // puts spaces instead of commas
    $s = preg_replace('/, +/', '', $s);
    $s = str_replace([',', '"'], ' ', $s);
    $s = trim($s);
    if ($exact) {
        $n = '';
    } else {
        $n = '%';
    }
    if (!$sentence) {
        $s_array = explode(' ', $s);
        $search .= '(post_title LIKE \'' . $n . $s_array[0] . $n . '\') OR (post_content LIKE \'' . $s_array[0] . '\')';
        for ($i = 1; $i < count($s_array); ++$i) {
            $search .= ' OR (post_title LIKE \'' . $n . $s_array[$i] . $n . '\') OR (post_content LIKE \'' . $n . $s_array[$i] . $n . '\')';
        }
        $search .= ' OR (post_title LIKE \'' . $n . $s . $n . '\') OR (post_content LIKE \'' . $n . $s . $n . '\')';
        $search .= ')';
    } else {
        $search = ' AND ((post_title LIKE \'' . $n . $s . $n . '\') OR (post_content LIKE \'' . $n . $s . $n . '\'))';
    }
}

// category stuff
if ((empty($cat)) || ($cat === 'all')) {
    $whichcat = '';
} else {
    $cat = urldecode($cat);
    $cat = addslashes_gpc($cat);
    if (str_contains($cat, '-')) {
        $eq = '!==';
        $andor = 'AND';
        $cat = explode('-', $cat);
        $cat = (int)$cat[1];
    } else {
        $eq = '=';
        $andor = 'OR';
    }
    $cat_array = explode(' ', $cat);
    $whichcat .= ' AND (post_category ' . $eq . ' ' . (int)$cat_array[0];
    for ($i = 1; $i < (count($cat_array)); ++$i) {
        $whichcat .= ' ' . $andor . ' post_category ' . $eq . ' ' . (int)$cat_array[$i];
    }
    $whichcat .= ')';
}

// author stuff
if ((empty($author)) || ($author === 'all')) {
    $whichauthor = '';
} else {
    $author = urldecode($author);
    $author = addslashes_gpc($author);
    if (str_contains($author, '-')) {
        $eq = '!==';
        $andor = 'AND';
        $author = explode('-', $author);
        $author = '' . (int)$author[1];
    } else {
        $eq = '=';
        $andor = 'OR';
    }
    $author_array = explode(' ', $author);
    $whichauthor .= ' AND (post_author ' . $eq . ' ' . (int)$author_array[0];
    for ($i = 1; $i < (count($author_array)); ++$i) {
        $whichauthor .= ' ' . $andor . ' post_author ' . $eq . ' ' . (int)$author_array[$i];
    }
    $whichauthor .= ')';
}

$where .= $search . $whichcat . $whichauthor;

if ((empty($order)) || ((strtoupper($order) !== 'ASC') && (strtoupper($order) !== 'DESC'))) {
    $order = 'DESC';
}

// order by stuff
if (empty($orderby)) {
    $orderby = 'date ' . $order;
} else {
    // used to filter values
    $allowed_keys = ['author', 'date', 'category', 'title'];
    $orderby = urldecode($orderby);
    $orderby = addslashes_gpc($orderby);
    $orderby_array = explode(' ', $orderby);
    if (!in_array($orderby_array[0], $allowed_keys, true)) {
        $orderby_array[0] = 'date';
    }
    $orderby = $orderby_array[0] . ' ' . $order;
    if (count($orderby_array) > 1) {
        for ($i = 1; $i < (count($orderby_array)); ++$i) {
            // Only allow certain values for safety
            if (in_array($orderby_array[$i], $allowed_keys, true)) {
                $orderby .= ',post_' . $orderby_array[$i] . ' ' . $order;
            }
        }
    }
}

if ((!$whichcat) && (!$m) && (!$p) && (!$w) && (!$s) && empty($poststart) && empty($postend)) {
    if ($what_to_show === 'posts') {
        $limits = ' LIMIT ' . $posts_per_page;
    } elseif ($what_to_show === 'days') {
        $lastpostdate = get_lastpostdate();
        $lastpostdate = mysql2date('Y-m-d 00:00:00', $lastpostdate);
        $lastpostdate = mysql2date('U', $lastpostdate);
        $otherdate = date('Y-m-d H:i:s', ($lastpostdate - (($posts_per_page - 1) * 86400)));
        $where .= ' AND post_date > \'' . $otherdate . '\'';
    }
}

if (!empty($postend) && ($postend > $poststart) && (!$m) && (!$w) && (!$whichcat) && (!$s) && (!$p)) {
    if ($what_to_show === 'posts' || ($what_to_show === 'paged' && (!$paged))) {
        $poststart = (int)$poststart;
        $postend = (int)$postend;
        $posts = $postend - $poststart;
        $limits = ' LIMIT ' . $poststart . ',' . $posts;
    } elseif ($what_to_show === 'days') {
        $poststart = (int)$poststart;
        $postend = (int)$postend;
        $posts = $postend - $poststart;
        $lastpostdate = get_lastpostdate();
        $lastpostdate = mysql2date('Y-m-d 00:00:00', $lastpostdate);
        $lastpostdate = mysql2date('U', $lastpostdate);
        $startdate = date('Y-m-d H:i:s', ($lastpostdate - (($poststart - 1) * 86400)));
        $otherdate = date('Y-m-d H:i:s', ($lastpostdate - (($postend - 1) * 86400)));
        $where .= ' AND post_date > \'' . $otherdate . '\' AND post_date < \'' . $startdate . '\'';
    }
} elseif (($what_to_show === 'paged') && (!$p) && (!$more)) {
    if ($pagenow !== 'b2edit.php') {
        $pgstrt = '';
        if ($paged) {
            $pgstrt = ((int)$paged - 1) * $posts_per_page . ', ';
        }
        $limits = 'LIMIT ' . $pgstrt . $posts_per_page;
    } elseif (($m) || ($w) || ($s) || ($whichcat)) {
            $limits = '';
        } else {
            $pgstrt = '';
            if ($paged) {
                $pgstrt = ((int)$paged - 1) * $posts_per_page . ', ';
            }
            $limits = 'LIMIT ' . $pgstrt . $posts_per_page;
        }
} elseif (($m) || ($p) || ($w) || ($s) || ($whichcat) || ($author)) {
    $limits = '';
}

if ($p === 'all') {
    $where = '';
}

$now = date('Y-m-d H:i:s', (time() + ($time_difference * 3600)));

if ($pagenow !== 'b2edit.php') {
    if ((empty($poststart)) || (empty($postend)) || !($postend > $poststart)) {
        $where .= ' AND post_date <= \'' . $now . '\'';
    }
    $where .= ' AND post_category > 0';
    $distinct = 'DISTINCT';
}

$request = " SELECT $distinct * FROM $tableposts WHERE true" . $where . " ORDER BY post_$orderby $limits";

if ($preview) {
    $request = 'SELECT 1-1'; // dummy mysql query for the preview
}

//echo $request;
$result = mysqli_query($connexion, $request);