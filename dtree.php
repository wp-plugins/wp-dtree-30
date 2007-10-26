/*
WP-dTree 3.3 (ulfben 2007-10-26)
	Fixed $curdir being undefined on some servers. (contributed by Zarquod)
	Added base URL to the tree so we won't have to pass it in for every node.
	Added a truncate-title function so we wont have to pass redundant data.
	Removed the text and graphic for the root-node. 

WP-dTree 3.2 (ulfben 2007-10-08)
	Added duration parameter to the GET array.
	Removed title on root-node.				
*/

/*--------------------------------------------------|
| WP-dTree 2.2 | www.silpstream.com/blog/           |
|---------------------------------------------------|
| Copyright (c) 2006 Christopher Hwang              |
| Release Date: July 2006                           |
| Modifications:                                    |
| v2.2 Added support for generating page trees      |
|      Added support for excluding specific posts   |
|      from tree                                    |
|      Updated option menu                          |
|      Rewrite of base code                         |
|      Fixed support for tooltips on non-linked     |
|      folders                                      |
|      Added option for not displaying posts in     |
|      archive tree                                 |
| v2.1 Patch to work with Regulus theme             |
|      Ability to change open/close all link        |
|      Set folders as links option                  |
|      Highlight current position in blog           |
| v2.0 Support for scriptaculous effects added      |
|      Category based menu added                    |
|      Support for dTree options was built in       |
|      Option menu added to admin panel             |
|	v1.0 Work arounds added for wordpress beautified |
|      permalinks.                                  |
|--------------------------------------------------*/
/*--------------------------------------------------|
| dTree 2.05 | www.destroydrop.com/javascript/tree/ |
|---------------------------------------------------|
| Copyright (c) 2002-2003 Geir Landr�               |
|                                                   |
| This script can be used freely as long as all     |
| copyright messages are intact.                    |
|                                                   |
| Updated: 17.04.2003                               |
|--------------------------------------------------*/
<?php
$curdir = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']); //fix contributed by Zarquod: http://wordpress.org/support/topic/136547
$effStyle = "blind";
$withEff = 1;
$duration = 0.5;
$trunc = 16; 
if ( isset($_REQUEST['eff']) ) {
	$effStyle = $_REQUEST['eff'];
}
if ( isset($_REQUEST['witheff']) ) {
	$withEff = $_REQUEST['witheff'];
}
if ( isset($_REQUEST['effdur']) ) {
	$duration = $_REQUEST['effdur'];
}
if ( isset($_REQUEST['trunc']) ) {
	$trunc = $_REQUEST['trunc'];
}
if ( 'slide' == $effStyle ) {
	$effCall_o = "SlideDown";
	$effCall_c = "SlideUp";
	$effParam = ", {duration:".$duration."}";
}
elseif ( 'blind' == $effStyle ) {
	$effCall_o = "BlindDown";
	$effCall_c = "BlindUp";
	$effParam = ", {duration:".$duration."}"; 
}
elseif ( 'appear' == $effStyle ) {
	$effCall_o = "Appear";
	$effCall_c = "Fade";
	$effParam = ", {duration:".$duration."}";
}
elseif ( 'grow' == $effStyle ) {
	$effCall_o = "Grow";
	$effCall_c = "Shrink";
	$effParam = ", {direction: 'center', duration:".$duration."}";
}
?>

// Node object
function Node(id, pid, name, url, title, count, rsspath) { 
	this.id = id;
	this.pid = pid;
	this.name = name;
	this.url = url;
	this.title = title;
	this.count = count; //for postcounts
	this.rsspath = rsspath; //for feed link.
	var target, icon, iconOpen, open; //these were originally passed as parameters, but were never used in wp-dtree.	
	this.target = target;
	this.icon = icon;
	this.iconOpen = iconOpen;
	this._io = open || false;
	this._is = false;
	this._ls = false;
	this._hc = false;
	this._ai = 0;
	this._p;
};

// Tree object
function dTree(objName, baseUrl) {
	this.config = {
		target			: null,
		folderLinks		: false,
		useSelection	: false,
		useCookies		: true,
		useLines		: true,
		useIcons		: true,
		useStatusText	: false,
		closeSameLevel	: false,
		inOrder			: false
	}
	this.icon = {
		root		: '<?php echo $curdir; ?>/dtree-img/empty.gif',
		folder		: '<?php echo $curdir; ?>/dtree-img/folder.gif',
		folderOpen	: '<?php echo $curdir; ?>/dtree-img/folderopen.gif',
		node		: '<?php echo $curdir; ?>/dtree-img/page.gif',
		empty		: '<?php echo $curdir; ?>/dtree-img/empty.gif',
		line		: '<?php echo $curdir; ?>/dtree-img/line.gif',
		join		: '<?php echo $curdir; ?>/dtree-img/join.gif',
		joinBottom	: '<?php echo $curdir; ?>/dtree-img/joinbottom.gif',
		plus		: '<?php echo $curdir; ?>/dtree-img/plus.gif',
		plusBottom	: '<?php echo $curdir; ?>/dtree-img/plusbottom.gif',
		minus		: '<?php echo $curdir; ?>/dtree-img/minus.gif',
		minusBottom	: '<?php echo $curdir; ?>/dtree-img/minusbottom.gif',
		nlPlus		: '<?php echo $curdir; ?>/dtree-img/nolines_plus.gif',
		nlMinus		: '<?php echo $curdir; ?>/dtree-img/nolines_minus.gif'	
	};
	this._url = baseUrl; 
	this.obj = objName;
	this.aNodes = [];
	this.aIndent = [];
	this.root = new Node(-1);
	this.selectedNode = null;
	this.selectedFound = false;
	this.completed = false;
};

// Adds a new node to the node array
dTree.prototype.a = function(id, pid, title, path, count, rsspath) {
	if(typeof(count) != "undefined" && count != ""){
		count = "<div id='postcount'>(" + count + ")</div>";
	}
	if(typeof(rsspath) != "undefined" && rsspath != ""){
		rsspath = "<a class='dtreerss' style='padding-right:15px' href='" + this._url + rsspath + "'> </a>";	
	}	
	name = this.truncate(title, <?php echo $trunc; ?>);
	url = this._url + path;
	this.aNodes[this.aNodes.length] = new Node(id, pid, name, url, title, count, rsspath); 
};
 
dTree.prototype.truncate = function(str, length) {
    var length = length || 16;
    var truncation = '...';
    if(str.length > length)   {
    	return str.slice(0, length - truncation.length) + truncation;
    }
    return str;
 };

// Open/close all nodes
dTree.prototype.openAll = function() {
	this.oAll(true);
};
dTree.prototype.closeAll = function() {
	this.oAll(false);
};

// Outputs the tree to the page
dTree.prototype.toString = function() {
	var str = '<div id="dtree' + this.obj + '">\n';
	if (document.getElementById) {
		if (this.config.useCookies) this.selectedNode = this.getSelected();
		str += this.addNode(this.root);
	} else str += 'Browser not supported.';
	str += '</div>';
	if (!this.selectedFound) this.selectedNode = null;
	this.completed = true;
	return str;
};

// Creates the tree structure
dTree.prototype.addNode = function(pNode) {
	var str = '';
	var n=0;
	if (this.config.inOrder) n = pNode._ai;
	for (n; n < this.aNodes.length; n++) {
		if (this.aNodes[n].pid == pNode.id) {
			var cn = this.aNodes[n];
			cn._p = pNode;
			cn._ai = n;
			this.setCS(cn);
			if (!cn.target && this.config.target) cn.target = this.config.target;
			if (cn._hc && !cn._io && this.config.useCookies) cn._io = this.isOpen(cn.id);
			if (!this.config.folderLinks && cn._hc) cn.url = null;
			if (this.config.useSelection && cn.id == this.selectedNode && !this.selectedFound) {
					cn._is = true;
					this.selectedNode = n;
					this.selectedFound = true;
			}
			str += this.node(cn, n);
			if (cn._ls) break;
		}
	}
	return str;
};

// Creates the node icon, url and text
dTree.prototype.node = function(node, nodeId) {	
	var str = '<div class="dTreeNode">' + this.indent(node, nodeId);	
	if (this.config.useIcons) {
		if (!node.icon) node.icon = (this.root.id == node.pid) ? this.icon.root : ((node._hc) ? this.icon.folder : this.icon.node);
		if (!node.iconOpen) node.iconOpen = (node._hc) ? this.icon.folderOpen : this.icon.node;
		if (this.root.id != node.pid) {		
			str += '<img id="i' + this.obj + nodeId + '" src="' + ((node._io) ? node.iconOpen : node.icon) + '" alt="" />';
		}
	}
	if(this.root.id != node.pid){
		if (node.url) {
			str += '<a id="s' + this.obj + nodeId + '" class="' + ((this.config.useSelection) ? ((node._is ? 'nodeSel' : 'node')) : 'node') + '" href="' + node.url + '"';
			if (node.title) str += ' title="' + node.title + '"';
			if (node.target) str += ' target="' + node.target + '"';
			if (this.config.useStatusText) str += ' onmouseover="window.status=\'' + node.name + '\';return true;" onmouseout="window.status=\'\';return true;" ';
			if (this.config.useSelection && ((node._hc && this.config.folderLinks) || !node._hc))
				str += ' onclick="javascript: ' + this.obj + '.s(' + nodeId + ');"';
			str += '>';
		}
		else if ((!this.config.folderLinks || !node.url) && node._hc && node.pid != this.root.id) {
			str += '<a href="javascript: ' + this.obj + '.o(' + nodeId + ');"'
			if (node.title) str += ' title="' + node.title + '"';
			str += ' class="node">';
		}
		str += node.name;	
		if (node.url || ((!this.config.folderLinks || !node.url) && node._hc)) str += '</a>';	
	}
	if(node.count){
		str += node.count;
	}
	if(node.rsspath){
		str	+= node.rsspath;
	}
	str += ' </div>';	
	if (node._hc) {
		str += '<div id="d' + this.obj + nodeId + '" class="clip" style="display:' + ((this.root.id == node.pid || node._io) ? 'block' : 'none') + ';">';
		str += this.addNode(node);	
		str += '</div>';
	}	
	this.aIndent.pop();
	return str;
};

// Adds the empty and line icons
dTree.prototype.indent = function(node, nodeId) {
	var str = '';
	if (this.root.id != node.pid) {
		for (var n=0; n<this.aIndent.length; n++)
			str += '<img src="' + ( (this.aIndent[n] == 1 && this.config.useLines) ? this.icon.line : this.icon.empty ) + '" alt="" />';
		(node._ls) ? this.aIndent.push(0) : this.aIndent.push(1);
		if (node._hc) {
			str += '<a href="javascript: ' + this.obj + '.o(' + nodeId + ');"><img id="j' + this.obj + nodeId + '" src="';
			if (!this.config.useLines) str += (node._io) ? this.icon.nlMinus : this.icon.nlPlus;
			else str += ( (node._io) ? ((node._ls && this.config.useLines) ? this.icon.minusBottom : this.icon.minus) : ((node._ls && this.config.useLines) ? this.icon.plusBottom : this.icon.plus ) );
			str += '" alt="" /></a>';
		} else str += '<img src="' + ( (this.config.useLines) ? ((node._ls) ? this.icon.joinBottom : this.icon.join ) : this.icon.empty) + '" alt="" />';
	}
	return str;
};

// Checks if a node has any children and if it is the last sibling
dTree.prototype.setCS = function(node) {
	var lastId;
	for (var n=0; n<this.aNodes.length; n++) {
		if (this.aNodes[n].pid == node.id) node._hc = true;
		if (this.aNodes[n].pid == node.pid) lastId = this.aNodes[n].id;
	}
	if (lastId==node.id) node._ls = true;
};

// Returns the selected node
dTree.prototype.getSelected = function() {
	var sn = this.getCookie('cs' + this.obj);
	return (sn) ? sn : null;
};

// Highlights the selected node
dTree.prototype.s = function(id) {
	if (!this.config.useSelection) return;
	var cn = this.aNodes[id];
	if (cn._hc && !this.config.folderLinks) return;
	if (this.selectedNode != id) {
		if (this.selectedNode || this.selectedNode==0) {
			eOld = document.getElementById("s" + this.obj + this.selectedNode);
			eOld.className = "node";
		}
		eNew = document.getElementById("s" + this.obj + id);
		eNew.className = "nodeSel";
		this.selectedNode = id;
		if (this.config.useCookies) this.setCookie('cs' + this.obj, cn.id);
	}
};

// Toggle Open or close
dTree.prototype.o = function(id) {
	var cn = this.aNodes[id];
	this.nodeStatus(!cn._io, id, cn._ls);
	cn._io = !cn._io;
	if (this.config.closeSameLevel) this.closeLevel(cn);
	if (this.config.useCookies) this.updateCookie();
};

// Open or close all nodes
dTree.prototype.oAll = function(status) {
	for (var n=0; n<this.aNodes.length; n++) {
		if (this.aNodes[n]._hc && this.aNodes[n].pid != this.root.id) {
			// silpstream: hack to work with scriptaculous
			if (this.aNodes[n]._io != status) this.nodeStatus(status, n, this.aNodes[n]._ls)
			this.aNodes[n]._io = status;
		}
	}
	if (this.config.useCookies) this.updateCookie();
};

// Opens the tree to a specific node
dTree.prototype.openTo = function(nId, bSelect, bFirst) {
	if (!bFirst) {
		for (var n=0; n<this.aNodes.length; n++) {
			if (this.aNodes[n].id == nId) {
				nId=n;
				break;
			}
		}
	}
	var cn=this.aNodes[nId];
	if (cn.pid==this.root.id || !cn._p) return;
	cn._io = true;
	cn._is = bSelect;
	if (this.completed && cn._hc) this.nodeStatus(true, cn._ai, cn._ls);
	if (this.completed && bSelect) this.s(cn._ai);
	else if (bSelect) this._sn=cn._ai;
	this.openTo(cn._p._ai, false, true);
};

// Closes all nodes on the same level as certain node
dTree.prototype.closeLevel = function(node) {
	for (var n=0; n<this.aNodes.length; n++) {
		if (this.aNodes[n].pid == node.pid && this.aNodes[n].id != node.id && this.aNodes[n]._hc) {
			this.nodeStatus(false, n, this.aNodes[n]._ls);
			this.aNodes[n]._io = false;
			this.closeAllChildren(this.aNodes[n]);
		}
	}
}

// Closes all children of a node
dTree.prototype.closeAllChildren = function(node) {
	for (var n=0; n<this.aNodes.length; n++) {
		if (this.aNodes[n].pid == node.id && this.aNodes[n]._hc) {
			if (this.aNodes[n]._io) this.nodeStatus(false, n, this.aNodes[n]._ls);
			this.aNodes[n]._io = false;
			this.closeAllChildren(this.aNodes[n]);
		}
	}
}

// Change the status of a node(open or closed)
dTree.prototype.nodeStatus = function(status, id, bottom) {
	eDiv	= document.getElementById('d' + this.obj + id);
	eJoin	= document.getElementById('j' + this.obj + id);
	if (this.config.useIcons) {
		eIcon	= document.getElementById('i' + this.obj + id);
		eIcon.src = (status) ? this.aNodes[id].iconOpen : this.aNodes[id].icon;
	}
	eJoin.src = (this.config.useLines)?
	((status)?((bottom)?this.icon.minusBottom:this.icon.minus):((bottom)?this.icon.plusBottom:this.icon.plus)):
	((status)?this.icon.nlMinus:this.icon.nlPlus);
	// silpstream: hack to work with scriptaculous
<?php if ( $withEff ) { ?>
	(status) ? Effect.<?php echo $effCall_o; ?>(eDiv<?php echo $effParam; ?>) : Effect.<?php echo $effCall_c; ?>(eDiv<?php echo $effParam; ?>);
<?php } else { ?>
	eDiv.style.display = (status) ? 'block': 'none';
<?php } ?>
};


// [Cookie] Clears a cookie
dTree.prototype.clearCookie = function() {
	var now = new Date();
	var yesterday = new Date(now.getTime() - 1000 * 60 * 60 * 24);
	this.setCookie('co'+this.obj, 'cookieValue', yesterday);
	this.setCookie('cs'+this.obj, 'cookieValue', yesterday);
};

// [Cookie] Sets value in a cookie
dTree.prototype.setCookie = function(cookieName, cookieValue, expires, path, domain, secure) {
	document.cookie =
		escape(cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '; path=/')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
};

// [Cookie] Gets a value from a cookie
dTree.prototype.getCookie = function(cookieName) {
	var cookieValue = '';
	var posName = document.cookie.indexOf(escape(cookieName) + '=');
	if (posName != -1) {
		var posValue = posName + (escape(cookieName) + '=').length;
		var endPos = document.cookie.indexOf(';', posValue);
		if (endPos != -1) cookieValue = unescape(document.cookie.substring(posValue, endPos));
		else cookieValue = unescape(document.cookie.substring(posValue));
	}
	return (cookieValue);
};

// [Cookie] Returns ids of open nodes as a string
dTree.prototype.updateCookie = function() {
	var str = '';
	for (var n=0; n<this.aNodes.length; n++) {
		if (this.aNodes[n]._io && this.aNodes[n].pid != this.root.id) {
			if (str) str += '.';
			str += this.aNodes[n].id;
		}
	}
	this.setCookie('co' + this.obj, str);
};

// [Cookie] Checks if a node id is in a cookie
dTree.prototype.isOpen = function(id) {
	var aOpen = this.getCookie('co' + this.obj).split('.');
	for (var n=0; n<aOpen.length; n++)
		if (aOpen[n] == id) return true;
	return false;
};

// If Push and pop is not implemented by the browser
if (!Array.prototype.push) {
	Array.prototype.push = function array_push() {
		for(var i=0;i<arguments.length;i++)
			this[this.length]=arguments[i];
		return this.length;
	}
};
if (!Array.prototype.pop) {
	Array.prototype.pop = function array_pop() {
		lastElement = this[this.length-1];
		this.length = Math.max(this.length-1,0);
		return lastElement;
	}
};