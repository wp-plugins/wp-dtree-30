<?php
header("Content-type: text/css");
?>
/*--------------------------------------------------|
| WP-dTree 2.2 | www.silpstream.com/blog/           |
|---------------------------------------------------|
| Copyright (c) 2006 Christopher Hwang              |
| Release Date: July 2006                           |
|--------------------------------------------------*/
/*--------------------------------------------------|
| dTree 2.05 | www.destroydrop.com/javascript/tree/ |
|---------------------------------------------------|
| Copyright (c) 2002-2003 Geir Landrö               |
|--------------------------------------------------*/

<?php
if ( !isset($_GET['fontsize']) ) {
	$fontsize = "11";
} else {
	$fontsize = $_GET['fontsize'];
}
if ( !isset($_GET['mfontcolor']) ) {
	$mfontcolor = "000000";
} else {
	$mfontcolor = $_GET['mfontcolor'];
}
if ( !isset($_GET['lfontcolor']) ) {
	$lfontcolor = "999999";
} else {
	$lfontcolor = $_GET['lfontcolor'];
}
if ( !isset($_GET['lfontdecor']) ) {
	$lfontdecor = "none";
} else {
	$lfontdecor = $_GET['lfontdecor'];
}
if ( !isset($_GET['hfontcolor']) ) {
	$hfontcolor = "CCCCCC";
} else {
	$hfontcolor = $_GET['hfontcolor'];
}
if ( !isset($_GET['hfontdecor']) ) {
	$hfontdecor = "underline";
} else {
	$hfontdecor = $_GET['hfontdecor'];
}
$sfontdecor = $hfontdecor;
?>
#dtreecat, #dtreearc, #dtreepge, #dtreecatwrapper, #dtreearcwrapper, #dtreepgewrapper {
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: <?php echo $fontsize; ?>px;
	color: #<?php echo $mfontcolor; ?>;
	white-space: nowrap;
}
#dtreecat img, #dtreearc img, #dtreepge img {
	border: 0px;
	vertical-align: middle;
}
#dtreecat a, #dtreearc a, #dtreepge a, #dtreecatwrapper a, #dtreearcwrapper a, #dtreepgewrapper a {
	display: inline;
	padding: 0;
	color: #<?php echo $lfontcolor; ?>;
	text-decoration: <?php echo $lfontdecor; ?>;
}
#dtreecat a.node, #dtreearc a.node, #dtreepge a.node, #dtreecat a.nodeSel, #dtreearc a.nodeSel, #dtreepge a.nodeSel {
	white-space: nowrap;
	padding: 1px 2px 1px 2px;
}
#dtreecat a:hover, #dtreecat a.node:hover, #dtreecat a.nodeSel:hover, #dtreearc a:hover, #dtreearc a.node:hover, #dtreearc a.nodeSel:hover, #dtreepge a:hover, #dtreepge a.node:hover, #dtreepge a.nodeSel:hover {
	color: #<?php echo $hfontcolor; ?>;
	text-decoration: <?php echo $hfontdecor; ?>;
}
#dtreecat a.nodeSel, #dtreearc a.nodeSel, #dtreepge a.nodeSel {
	text-decoration: underline;
}
#dtreecat .clip, #dtreearc .clip, #dtreepge .clip {
	overflow: hidden;
}
