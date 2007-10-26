<?php
header("Content-type: text/css");
?>
/*
*	WP-dTree 3.2 | Ulf Benjaminsson | 20071015
*		Added RSS-icons (normal and hover)
*		Added postcount (default fontcolor = link fontcolor)
*		Uncluttered the initialization a bit. 
*		Fixed images breaking the tree in certain themes.
*/

/*
*	WP-dTree 2.2 | www.silpstream.com/blog/           
*	Copyright (c) 2006 Christopher Hwang             
* 	Release Date: July 2006                           
*/

/*
*	dTree 2.05 | www.destroydrop.com/javascript/tree/ 
*	Copyright (c) 2002-2003 Geir Landr� 
*/

<?php
$fontsize = "11";
$mfontcolor = "000000";
$lfontcolor = "999999";
$lfontdecor = "none";
$hfontcolor = "CCCCCC";
$hfontdecor = "underline";
$rssicon = "";
$rssicon2 = "";
if ( isset($_REQUEST['fontsize']) ) {
	$fontsize = $_REQUEST['fontsize'];
} 
if ( isset($_REQUEST['mfontcolor']) ) {
	$mfontcolor = $_REQUEST['mfontcolor'];
} 
if ( isset($_REQUEST['lfontcolor']) ) {
	$lfontcolor = $_REQUEST['lfontcolor'];
}
if ( isset($_REQUEST['lfontdecor']) ) {
	$lfontdecor = $_REQUEST['lfontdecor'];
}
if ( isset($_REQUEST['hfontcolor']) ) {
	$hfontcolor = $_REQUEST['hfontcolor'];
}
if ( isset($_REQUEST['hfontdecor']) ) {	
	$hfontdecor = $_REQUEST['hfontdecor'];
} 
$sfontdecor = $hfontdecor;
if ( isset($_REQUEST['rssgfx']) ) {
	$rssicon = $_REQUEST['rssgfx'];
}
if ( isset($_REQUEST['rssgfxh']) ) {
	$rssicon2 = $_REQUEST['rssgfxh'];
}
?>
#dtreec, #dtreea, #dtreep, #dtreecatwrapper, #dtreearcwrapper, #dtreepgewrapper {
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: <?php echo $fontsize; ?>px;
	color: #<?php echo $mfontcolor; ?>;
	white-space: nowrap;
}
#dtreec img, #dtreea img, #dtreep img {
	border: 0px;
	vertical-align: middle;		
	float: none; 
	margin: 0 0px 0px 0;
}
#dtreec a, #dtreea a, #dtreep a, #dtreecatwrapper a, #dtreearcwrapper a, #dtreepgewrapper a {
	display: inline;
	padding: 0;
	color: #<?php echo $lfontcolor; ?>;
	text-decoration: <?php echo $lfontdecor; ?>;
}

/*RSS icons for categories. Don't ask.*/
a.dtreerss  {	
   padding-right: 25px; 
   background: url('<?php echo $rssicon; ?>') no-repeat center right;  
}

a.dtreerss:hover  {	
    padding-right: 25px; 
    background: url('<?php echo $rssicon2; ?>') no-repeat center right;
}

#postcount {
	display: inline;
	padding: 0;
	color: #<?php echo $lfontcolor; ?>;
	text-decoration: none;
}

#dtreec a.node, #dtreea a.node, #dtreep a.node, #dtreec a.nodeSel, #dtreea a.nodeSel, #dtreep a.nodeSel {
	white-space: nowrap;
	padding: 1px 2px 1px 2px;
}
#dtreec a:hover, #dtreec a.node:hover, #dtreec a.nodeSel:hover, #dtreea a:hover, #dtreea a.node:hover, #dtreea a.nodeSel:hover, #dtreep a:hover, #dtreep a.node:hover, #dtreep a.nodeSel:hover {
	color: #<?php echo $hfontcolor; ?>;
	text-decoration: <?php echo $hfontdecor; ?>;
}
/*If you want some cool highlighting on the active node, you can change it here. Default is a simple underline.*/
#dtreec a.nodeSel, #dtreea a.nodeSel, #dtreep a.nodeSel {
	text-decoration: underline;
}
#dtreec .clip, #dtreea .clip, #dtreep .clip {
	overflow: hidden;
}
