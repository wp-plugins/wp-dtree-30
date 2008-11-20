<?php
header('Content-type: text/css');
?>
/*
*	WP-dTree 3.5 | Ulf Benjaminsson | 20081120
*		Added a few '!important' attributes to avoid inheriting some common settings from themes, that'll break the tree layout
*		Added variables for: 
*			selected node / mouse over decorations
*			font-family
*		Explicitly sets line height (to fontsize) to avoid breakage.
*		Fixed the issue of RSS icons not showing in IE if postcounts are on.			
*
*		PLEASE NOTE: I do not do style, layout or design support!
*			If you think the tree looks wierd or broken, run the plugin in the default WP theme (Kubric) to make sure it's not your theme that breaks it.
*			If you do find a problem with my code or CSS, let me know so I can update the plugin.
*/

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
*	Copyright (c) 2002-2003 Geir Landrö
*/

<?php
global $fontf;
$fontf = 'Verdana, Geneva, Arial, Helvetica, sans-serif';
global $fontsize;
$fontsize = '11';
global $mfontcolor;
$mfontcolor = '000000'; //font color
global $lfontcolor;
$lfontcolor = '999999'; //link font color
global $sfontdecor; //selected node font decor
$sfontdecor = 'underline';
global $lfontdecor;
$lfontdecor = 'none';
global $hfontcolor;
$hfontcolor = 'CCCCCC'; //hover link color
global $hfontdecor;
$hfontdecor = 'underline'; //hover link decor
global $rssicon;
$rssicon = '';
global $rssicon2;
$rssicon2 = '';
if ( isset($_REQUEST['fontf']) ) {
	$fontf = urldecode($_REQUEST['fontf']);
} 
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
if ( isset($_REQUEST['sfontdecor']) ) {	
	$sfontdecor = $_REQUEST['sfontdecor'];
} 
if ( isset($_REQUEST['rssgfx']) ) {
	$rssicon = $_REQUEST['rssgfx'];
}
if ( isset($_REQUEST['rssgfxh']) ) {
	$rssicon2 = $_REQUEST['rssgfxh'];
}
?>
#dtreec, #dtreea, #dtreep, #dtreel, #dtreecatwrapper, #dtreearcwrapper, #dtreepgewrapper, #dtreelnkwrapper {
	font-family: <?php echo $fontf ?>;
	margin: 3px 0px 0px 0px; /*spacing from the open/close all links*/
	font-size: <?php echo $fontsize; ?>px;
	color: #<?php echo $mfontcolor; ?>;
	white-space: nowrap;
	text-align:left; !important
}
#dtreec img, #dtreea img, #dtreep img, #dtreel img {
	border: 0px;
	vertical-align: middle;		
	float: none; 
	margin: 0 0px 0px 0;
	padding: 0px !important;
	line-height: <?php echo $fontsize; ?>px !important;
}
#dtreec a, #dtreea a, #dtreep a, #dtreel a, #dtreecatwrapper a, #dtreearcwrapper a, #dtreepgewrapper a, #dtreelnkwrapper a {
	display: inline;
	padding: 0;
	color: #<?php echo $lfontcolor; ?>;
	text-decoration: <?php echo $lfontdecor; ?>;
}

/*RSS icons for categories. Don't ask.*/
#dtreec a.dtreerss, #dtreea a.dtreerss, #dtreep a.dtreerss, #dtreel a.dtreerss  {	
   padding-right: 25px; 
   background: url('<?php echo $rssicon; ?>') no-repeat center right;  
   text-decoration: none;   
}

#dtreec a.dtreerss:hover, #dtreea a.dtreerss:hover, #dtreep a.dtreerss:hover, #dtreel a.dtreerss:hover  {	
    padding-right: 25px; 
    background: url('<?php echo $rssicon2; ?>') no-repeat center right;
    text-decoration: none;
}

#postcount {
	display: inline;
	padding: 0;
	color: #<?php echo $lfontcolor; ?>;
	text-decoration: none;
}

#dtreec a.node, #dtreea a.node, #dtreep a.node, #dtreel a.node, #dtreec a.nodeSel, #dtreea a.nodeSel, #dtreep a.nodeSel, #dtreel a.nodeSel {
	white-space: nowrap;
	padding: 1px 2px 1px 2px;
}
#dtreec a:hover, #dtreec a.node:hover, #dtreec a.nodeSel:hover, #dtreea a:hover, #dtreea a.node:hover, #dtreea a.nodeSel:hover, #dtreep a:hover, #dtreep a.node:hover, #dtreep a.nodeSel:hover, #dtreel a:hover, #dtreel a.node:hover, #dtreel a.nodeSel:hover {
	color: #<?php echo $hfontcolor; ?>;
	text-decoration: <?php echo $hfontdecor; ?>;
}
/*If you want some cool highlighting on the active node, you can change it here. Default is a simple underline.*/
#dtreec a.nodeSel, #dtreea a.nodeSel, #dtreep a.nodeSel, #dtreel a.nodeSel {
	text-decoration: <?php echo $sfontdecor ?>;
}
#dtreec .clip, #dtreea .clip, #dtreep .clip, #dtreel .clip {
	overflow: hidden;
	width: 100%;
}
