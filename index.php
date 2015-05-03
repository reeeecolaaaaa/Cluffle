<?php
session_start();
date_default_timezone_set("UTC");
$request=$_REQUEST;

/*
require_once("classes/dbinterface.inc.php");
require_once("classes/tracking.inc.php");
global $db;
$db=new dbinterface();
$tracking = new tracking();
$tracking->saveEverything();
*/

global $useragent;
$useragent="Cluffle";

//$_SESSION["active"] = !loggedin() ? false : $_SESSION["active"];
$_SESSION["chapter"]= isset($_SESSION["chapter"]) ? $_SESSION["chapter"] : 1;
$_SESSION["chapterdata"]= isset($_SESSION["chapterdata"]) ? $_SESSION["chapterdata"] : array();
$_SESSION["chapterbuffer"]= isset($_SESSION["chapterbuffer"]) ? $_SESSION["chapterbuffer"] : array();
$_SESSION["before"]= isset($_SESSION["before"]) ? $_SESSION["before"] : "";
$_SESSION["after"]= isset($_SESSION["after"]) ? $_SESSION["after"] : "";
$_SESSION["count"]= isset($_SESSION["count"]) ? $_SESSION["count"] : 0;
$_SESSION["page"]= isset($_SESSION["page"]) ? $_SESSION["page"] : 1;
$_SESSION["query"]= isset($_SESSION["query"]) ? $_SESSION["query"] : "";
$_SESSION["action"]= isset($_SESSION["action"]) ? $_SESSION["action"] : "";


$request["action"] = isset($request["action"]) ? $request["action"] : "";
switch ($request["action"]) {
	case "login":
		login($request["username"],$request["password"]);
		break;
	case "logout":
		session_destroy();
		header("Location: index.php");
		break;
	case "groups":
		groups($request);
		break;
	default:
		if (!isset($_REQUEST["q"]) || $_REQUEST["q"]=="") {
			frontpage();
		} else {
			search($_REQUEST);
		}
		break;
}

function frontpage() {
	global $useragent;
	
	/*if (loggedin()) {
		$header='<div id="navbarmenu">
					<div id="navbarmenuicon"></div>
					<div id="navbarmenunotification"></div>
					<div id="navbarmenuadd"></div>
					<div id="navbarmenuprofile" tabindex="1">
						<a href="index.php?action=logout" id="navbarlogout">
							Logout
						</a>
					</div>
				</div>
				<div id="navbarlinks">
					<a target=_blank href="http://www.reddit.com/u/'.$_SESSION["username"].'">+'.$_SESSION["username"].'</a>
					<a href="#">Gmail</a>
					<a href="#">Images</a>
				</div>';
	} else {
		$header='<div id="navbarmenu">
				<div id="navbarmenuicon"></div>
				<label for="logincheckbox" id="navbarmenubutton">Sign In</label>
				<input type="checkbox" id="logincheckbox" value="1" style="display:none;">
				<div id="navbarlogin">
					<form method="post">
						<input type="hidden" name="action" value="login">
						<input type="text" name="username" placeholder="User">
						<input type="password" name="password" placeholder="Password">
						<button id="loginbutton" type="submit">Sign In</button>
					</form>
				</div>
				</div>
				<div id="navbarlinks">
					<a href="http://www.reddit.com/u/Lutan" target="_blank">+You</a>
					<a href="#">Gmail</a>
					<a href="#">Images</a>
				</div>';
	}*/
	
	$header='<div id="navbarmenu">
				<div id="navbarmenuicon"></div>
				<div id="navbarmenubutton">Sign In</div>
				</div>
				<div id="navbarlinks">
					<a href="http://www.reddit.com/u/Lutan" target="_blank">+You</a>
					<a href="#">Gmail</a>
					<a href="#">Images</a>
				</div>';
	
	echo '<!DOCTYPE HTML>
<head>
<meta charset="utf-8">
<title>Google</title>
<link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAYdEVYdFNvZnR3YXJlAHBhaW50Lm5ldCA0LjAuNWWFMmUAAAN3SURBVFhHtZdpTxRBEIbnb5j4E/zgPxIQNIpHNB4hGo8Y9RPeV0g0JhtWBA8EL5QAggdEFKMkiCC3RDlcBJdju6Wtdyad7WlqekaIJE/ITtd0vdNdVV3t4W9TtdpYkBYtBencn8K0UP8T+IAv+PSdF1UtbyiozM1xxnHsvCPUkcdCnXpGPI9me83Kd+ETvj1SVG8Pujj2RKjWfqmm5qSS0s1YRqqzLfw8AL49UpLlBm223BaqsVcqIaRq6JHqQF3w7DR94cBk3unCklTXXgp/vLiKn0sD3x43YLP5llAfRgMH9d1yxfi26rCI2QWpDpIA244jkYCaLuFPnKOv30H7ztkceij8cS2iiwRzdjaxArbS183RF2HSSdp3zkbTPpgXIIh9tbydSawABJGedGLWLeBSa94WXKTfnJ1JrID0u/ykSzmpShyBted+WACCkbMziRWQehueFFHP2YEiClYzDlADODuTWAEnqciYApq/RG8D0g5pCrtpipe4NASxAvBVKChawCJtQ1k9b3uUipS2u5pg+UGsAIBlx/7ryYenpdp1N2yDotQ9HkR/FcWNOeYikQBwplmomWxexCRlRLpTqHJ6jjjBKmF1kkS+SWIBAIdKJTntHJaqb0KqfkILAjc7+Pdc/JMAmwsvwgGKasjZuViTgOtvwgJa+qQftJxtFGsSsP+B8PfdFPF+RKrSiPOCY9UCdt8L0q5jKCwAfKOAxHHMvWeTWADSDtGOU+7XfNAXTP+WanAqCEZ7JX7SWFS9MIkVgPyu+xQ0GshxZMB5Cr5Sq81CpzQ+ExYxQvUirho6BaDR6P0RTAYBqAWcnQZpihUyRVxp4201TgFNVPf1RFh+zsYGX4yKqN9r/Ow+wiMFoOPV5RdLj9Xg7DjKqB5oAW1fVyngnNGIQEgRY+NCl+3aj6sUgJquBYDDj3g7DjSx8xQzeA+pytloIgXspX5On+0A0Z90FbR41Ahu3MQdhHQP0ALAK9pPO/1skI4ZWn7UhyQVEQIy9kMN+j+z0wWzVIQQ2ZcpveAMAYdlRrq10Y0J8fJ6QLLXMYYM3YxEihkIgVOv53tQ/UwxJtnFwPGJBn4ODvj2ilPZ9YWVuTHOwAbluLxJqBvt1C1TX4DagM73+NNgtbh3HIzCt39DLkktryMRFXRtHqL/mf8JfNCdsAI+Pc/z/gLM/qSASCHNiAAAAABJRU5ErkJggg==" />
<style>';
require("css/frontpage.css");
echo '</style>
<script>';
require("js/jquery-2.1.1.min.js");
echo '</script>
<script>';
require("js/script.js");
echo '</script>
<style>
body {
	position:absolute;
	top:0;
	left:0;
	right:0;
	bottom:0;
	background-color:white;
	padding:0;
	margin:0;
	min-width:980px;
}
</style>
</head>

<body>
<div id="help" class="popup" style="display:none;">
	<h2>Help</h2>
	<div class="closebutton">Close</div>
	<p>
		You can support this project by helping me fund it. The costs to keep it alive (paying for the hosting / paying for energy drinks) are relatively high for a college student like me.<br><br><br>
		PayPal - send me a message on Reddit for my mail adress (unfortunately I couldn\'t make a donate button - need to be an organization for that)<br><br>
		BTC - 1BWvVmUh3FemK25jStSK8qJVGHR5YRhp9c
	</p>
</div>
<div id="business" class="popup" style="display:none;">
	<h2>Business</h2>
	<div class="closebutton">Close</div>
	<p>
		Interested in buying this project? Message me on Reddit.
	</p>
</div>
<div id="about" class="popup" style="display:none;">
	<h2>About</h2>
	<div class="closebutton">Close</div>
	<p>Want to use Reddit without people knowing you are using it? This page provides you with the known Google interface for your Reddit needs.<br><br>
	
	Browse a subreddit by entering it into the box ("/r/internetisbeautiful" or "/r/web_design").<br>
	Search for a term as you would on reddit.<br>
	Press the "I\'m feeling lucky" - button to browse a random subreddit.<br><br>
	
	Cluffle is your stealth mode to avoid things like suspicious coworkers and classmates.<br>
	Reddit is blocked at work? Cluffle also works as a proxy.<br>
	You want the usual Reddit interface while using the proxy? Just go to <a href="http://proxy.cluffle.com" target=_blank>proxy.cluffle.com</a>.
	</p>
</div>
<div class="noselect" id="navbar">
	'.$header.'
</div>
<div id="content">
	<div id="contentlogo">
	</div>
	<form id="luckyform" action="index.php">
		<input type="hidden" name="q" value="/r/random">
	</form>
	<form action="index.php">
		<input type="text" id="contentinput" autofocus name="q" autocomplete="off">
		<div class="noselect" id="contentbuttons">
			<button type=submit id="contentbutton1"></button>
			<a id="contentbutton2" href="index.php?q=/r/random"></a>
		</div>
	</form>
</div>
<div class="noselect" id="footer">
	<a div="help">Help</a>
	<a div="business">Business</a>
	<a div="about">About</a>

	<span id="footerright">
		<a class="pullright" href="http://www.reddit.com/u/Lutan" target="_blank">Contact</a>
		<a class="pullright" href="http://proxy.cluffle.com" target=_blank>Proxypage</a>
	</span>
</div>
</body>';
}

function search($request) {
	global $useragent;
	$q=str_replace("//","/",$request["q"]);
	
	if (!isset($request["action"]) || $request["action"]=="") {
		$request["action"]="hot";
	}
	
	$sub="";
	if (isset($request["action"])) {
		switch ($request["action"]) {
			case "new":
				$sub="/new";
				break;
			case "rising":
				$sub="/rising";
				break;
			case "controversial":
				$sub="/controversial";
				break;
			case "top":
				$sub="/top";
				break;
			case "gilded":
				$sub="/gilded";
				break;
			case "promoted":
				$sub="/promoted";
				break;
			case "wiki":
				$sub="/wiki";
				break;
			default:
				$sub="/hot";
				break;
		}
	}
	
	$page=isset($request["page"]) && $request["page"] > 0 ? $request["page"] : 1;
	$pages=array();
	$count=0;
	$chapter=$_SESSION["chapter"];
	$anchor="";
		
	$_SESSION["page"]=$page;
	$back = $page>1 ? true : false;
	$curchapter=(($page - 1) - ($page - 1) % 10) / 10 + 1;
	
	if ($curchapter > $chapter && !isset($_SESSION["chapterbuffer"][$curchapter])) {
		$count=$curchapter*100;
		$anchor="&after=".$_SESSION["after"];
	}
	
	if (isset($_SESSION["chapterbuffer"][$curchapter])) {
		$_SESSION["chapterdata"]=$_SESSION["chapterbuffer"][$curchapter][0];
		$_SESSION["before"]=$_SESSION["chapterbuffer"][$curchapter][1];
		$_SESSION["after"]=$_SESSION["chapterbuffer"][$curchapter][2];
	}
	
	
	//LOAD CURRENT CHAPTER
	if (!isset($_SESSION["chapterbuffer"][$curchapter]) || $q=="/r/random" || $q!=$_SESSION["query"] || $request["action"]!=$_SESSION["action"]) {
		$_SESSION["chapterbuffer"]=array();
		$_SESSION["action"]=$request["action"];
		
		$urls=array(
						"www.reddit.com/".trim($q,"/").$sub.".json?limit=100&count=".$count.$anchor,
						"www.reddit.com/search.json?q=".urlencode($q)."&limit=100&count=".$count.$anchor
					);

		$data=HTMLrequest($urls);
		
		if ($q=="/r/random") {
			$q="/r/".$data->data->children[0]->data->subreddit;
		}
		
		$_SESSION["query"]=$q;
		@$_SESSION["chapterdata"]=$data->data->children;
		@$_SESSION["before"]=$data->data->before;
		@$_SESSION["after"]=$data->data->after;
		$_SESSION["chapter"]=$curchapter;
		$_SESSION["chapterbuffer"][$curchapter]=array($_SESSION["chapterdata"],$_SESSION["before"],$_SESSION["after"]);
	}
	
	//LOAD NEXT CHAPTER
	if ($_SESSION["after"]!="" && !isset($_SESSION["chapterbuffer"][$curchapter + 1])) {
		$count=$curchapter*100;
		$anchor="&after=".$_SESSION["after"];
		$urls=array(
						"www.reddit.com/".$q.$sub.".json?limit=100&count=".$count.$anchor,
						"www.reddit.com/search.json?q=".urlencode($q)."&limit=100&count=".$count.$anchor
					);
		$data=HTMLrequest($urls);
		$_SESSION["chapterbuffer"][$curchapter + 1]=array($data->data->children,$data->data->before,$data->data->after);;
	}
	
	$results=array();
	$start=($page - 1) % 10 * 10;
	for ($i=$start;$i<$start+10;$i++) {
		if (isset($_SESSION["chapterdata"][$i])) {
			$entry=$_SESSION["chapterdata"][$i];
		} else {
			break;
		}
		$result=array();
		$result["strTitel"]=isset($entry->data->title)? $entry->data->title : $entry->data->link_title;
		$result["strTitel"]=substr($result["strTitel"],0,130);
		if (!isset($entry->data->is_self) || $entry->data->is_self==1) {
			if (isset($entry->data->link_id)) {
				$buffer=$entry->data->link_id;
				$buff=explode("_",$buffer);
				if (count($buff)==2) {
					$buffer=$buff[1];
				}
			} else {
				$buffer=$entry->data->id;
			}
			$result["strLink"]="index.php?action=groups&comment=".$buffer;
		} else {
			require_once("proxy.inc.php");
			$whitelist=array("imgur.com","reddit.com");
			$proxy=new proxy();
			$parse=$proxy->parseUrl($entry->data->url,$whitelist);
			$parse=in_array($parse[2],$whitelist);
			if ($parse!==false && isset($request["enableproxy"]) && $request["enableproxy"]=="true") {
				$result["strLink"]="http://proxy.cluffle.com/index.php?url=".$entry->data->url;
			} else {
				$result["strLink"]=$entry->data->url;
			}
		}
		if (isset($entry->data->num_comments) && $entry->data->num_comments!="") {
			$result["numComments"]=$entry->data->num_comments;
		} else {
			$result["numComments"]="[unknown]";
		}
		$result["strComment"]=$entry->data->id;
		$result["strDomain"]="/r/".$entry->data->subreddit;
		if (isset($entry->data->selftext_html) && $entry->data->selftext_html!="") {
			$buffer=$entry->data->selftext_html;
		} else if (isset($entry->data->body_html) && $entry->data->body_html!="") {
			$buffer=$entry->data->body_html;
		} else if (isset($entry->data->selftext) && $entry->data->selftext!="") {
			$buffer=$entry->data->selftext;
		} else if (isset($entry->data->body) && $entry->data->body!="") {
			$buffer=$entry->data->body;
		} else {
			$buffer="";
		}
		
		$result["nsfw"]=isset($entry->data->over_18) && (boolean) $entry->data->over_18;
		
		$result["strBeschreibung"]=strip_tags(htmlspecialchars_decode($buffer),'<a>');
		
		if ($result["strBeschreibung"]=="") {
			$result["strCreated"]=time_passed($entry->data->created_utc);
		} else {
			$result["strCreated"]=time_passed($entry->data->created_utc)." - ";
		}
		$results[]=$result;
	}
	
	
	$maxchap=count($_SESSION["chapterbuffer"]);
	$maxpage=ceil(count($_SESSION["chapterbuffer"][$maxchap][0]) / 10);
	$maxpage=($maxchap - 1) * 10 + $maxpage;

	$pagelinks=array();
	$start=$page-6<=0 ? 1 : $page - 5;
	$pagenumbers="";
	for ($i=$page-6<=0 ? 1 : $page - 5; $i<$page;$i++) {
		$curpage=array();
		$curpage["link"]='index.php?q='.$q.'&page='.$i;
		$curpage["number"]=$i;
		$curpage["current"]=0;
		$pagelinks[]=$curpage;
	}
	$curpage=array();
	$curpage["link"]='#';
	$curpage["number"]=$i;
	$curpage["current"]=1;
	$pagelinks[]=$curpage;
	$forward=false;
	for ($i++;$i<=($start + 9 <= $maxpage ? $start + 9 : $maxpage);$i++) {
		$curpage["link"]='index.php?q='.$q.'&page='.$i;
		$curpage["number"]=$i;
		$curpage["current"]=0;
		$pagelinks[]=$curpage;
		$forward=true;
	}
	
	$shownav = $maxpage > 1;
	
	$backlink='index.php?q='.$q.'&page='.($page - 1);
	$forwardlink='index.php?q='.$q.'&page='.($page + 1);
	
	$resultstats="About 2,630,000,000 results (0.26 seconds)";
	if (empty($results)) {
		$resultstats="These aren't the results you are looking for. 404.";
	}
	
	$resultcount=count($results);
	$buffer=$request;
	if (!isset($request["enableproxy"]) || $request["enableproxy"]!="true") {
		$request["enableproxy"]="";
		$buffer["enableproxy"]="true";
		$buffertext="Enable Proxy";
	} else {
		$buffer["enableproxy"]="";
		$buffertext="Disable Proxy";
	}
	$buffer=array_filter($buffer);
	$query='<a href="index.php?'.http_build_query($buffer).'">'.$buffertext.'</a>';
	
	$display=array();
	$display["results"]=$results;
	$display["resultstats"]=$resultstats;
	$display["resultcount"]=count($results);
	$display["query"] = $q;
	$display["back"]=$back;
	$display["forward"]=$forward;
	$display["backlink"]=$backlink;
	$display["forwardlink"]=$forwardlink;
	$display["pagelinks"]=$pagelinks;
	$display["sub"]=$sub;
	$display["showNav"]=$shownav;
	$display["proxyquery"]=$query;
	$display["request"]=$request;
	searchpage($display);
}

function loadcomments($request) {
	$comments=array();
	$data=array();
	if (isset($request["comment"]) && ctype_alnum($request["comment"]) && $request["comment"]!="") {
		$urls=array(
						"www.reddit.com/comments/".$request["comment"].".json",
					);
		$data=HTMLrequest($urls);
		if (!isset($data[0]->data->children[0]->data->subreddit)) {
			break;
		}
		$comments["subreddit"]=$data[0]->data->children[0]->data->subreddit;
		$comments["title"]=$data[0]->data->children[0]->data->title;
		$comments["score"]=$data[0]->data->children[0]->data->score;
		$comments["num_comments"]=$data[0]->data->children[0]->data->num_comments;
		if (isset($data[0]->data->children[0]->data->is_self) && $data[0]->data->children[0]->data->is_self!=1 && isset($data[0]->data->children[0]->data->url) && $data[0]->data->children[0]->data->url!="") {
			$comments["title"]='<a href="'.$data[0]->data->children[0]->data->url.'" target=_blank>'.$comments["title"].'</a>';
		}
		$buffer=array();
		$buffer[]=(array) $data[0]->data->children[0];
		foreach ($data[1]->data->children as $child) {
			$buffer[]=(array) $child;
		}
		$buffer2=array();
		foreach ($buffer as $buff) {
			$buffer2=array_merge($buffer2,group_r($buff,0));
		}
		$comments["comments"]=$buffer2;
	}
	return $comments;
}

function group_r($comment,$counter) {
	$buffer=array();
	if (!isset($comment["data"]->author)) {
		return array();
	}
	$buffer["author"]=$comment["data"]->author;
	$buffer["score"]=$comment["data"]->score;
	if (isset($comment["data"]->selftext_html) && $comment["data"]->selftext_html!="") {
			$buffer["text"]=$comment["data"]->selftext_html;
		} else if (isset($comment["data"]->body_html) && $comment["data"]->body_html!="") {
			$buffer["text"]=$comment["data"]->body_html;
		} else if (isset($comment["data"]->selftext) && $comment["data"]->selftext!="") {
			$buffer["text"]=$comment["data"]->selftext;
		} else if (isset($comment["data"]->body) && $comment["data"]->body!="") {
			$buffer["text"]=$comment["data"]->body;
		} else {
			$buffer["text"]="[no content]";
		}
	$buffer["text"]=nl2br(strip_tags(htmlspecialchars_decode($buffer["text"]),'<a>'));
	$buffer["timestamp"]=$comment["data"]->created_utc;
	$buffer["counter"]=$counter;
	$buffer=array($buffer);
	if (!isset($comment["data"]->replies) || empty($comment["data"]->replies)) {
		return $buffer;
	}
	foreach ($comment["data"]->replies->data->children as $comment) {
		$buffer=array_merge($buffer,group_r((array) $comment,($counter + 1)));
	}
	return $buffer;
}

function groups($request) {
	$comments=loadcomments($request);
	/*if (loggedin()) {
		$subs="";
		foreach ($_SESSION["subscribed"] as $sub) {
			$subs.='<a href="index.php?q='.$sub["url"].'" class="subitem"><span>'.$sub["name"].'</span></a>';
		}
		$header='
				<div id="navbarlinks">
					<a target=_blank href="http://www.reddit.com/u/'.$_SESSION["username"].'">+'.$_SESSION["username"].'</a>
					<div id="subtoggle" href="#">
						<div id="subscribed">
							'.$subs.'
						</div>
						subscribed
					</div>
				</div>
				<div id="navbarmenu">
					<div id="navbarmenuicon"></div>
					<div id="navbarmenunotification"></div>
					<div id="navbarmenuadd"></div>
					<div id="navbarmenuprofile" tabindex="1">
						<a href="index.php?action=logout" id="navbarlogout">
							Logout
						</a>
					</div>
				</div>';
	} else {
		$header='<div id="navbarmenu">
					<div id="navbarmenuicon"></div>
					<label for="logincheckbox" id="navbarmenubutton">Sign In</label>
					<input type="checkbox" id="logincheckbox" value="1" style="display:none;">
					<div id="navbarlogin">
						<form method="post">
							<input type="hidden" name="q" value="'.$_SESSION["query"].'">
							<input type="hidden" name="action" value="login">
							<input type="text" name="username" placeholder="User">
							<input type="password" name="password" placeholder="Password">
							<button id="loginbutton" type="submit">Sign In</button>
						</form>
					</div>
				</div>';
	}*/
	
	$header='<div id="navbarmenu">
					<div id="navbarmenuicon"></div>
					<div id="navbarmenubutton">Sign In</div>
				</div>';
				
	
	echo '
		<!DOCTYPE HTML>
		<head>
			<meta charset="utf-8">
			<title>Google Groups</title>
			<link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAYdEVYdFNvZnR3YXJlAHBhaW50Lm5ldCA0LjAuNWWFMmUAAAN3SURBVFhHtZdpTxRBEIbnb5j4E/zgPxIQNIpHNB4hGo8Y9RPeV0g0JhtWBA8EL5QAggdEFKMkiCC3RDlcBJdju6Wtdyad7WlqekaIJE/ITtd0vdNdVV3t4W9TtdpYkBYtBencn8K0UP8T+IAv+PSdF1UtbyiozM1xxnHsvCPUkcdCnXpGPI9me83Kd+ETvj1SVG8Pujj2RKjWfqmm5qSS0s1YRqqzLfw8AL49UpLlBm223BaqsVcqIaRq6JHqQF3w7DR94cBk3unCklTXXgp/vLiKn0sD3x43YLP5llAfRgMH9d1yxfi26rCI2QWpDpIA244jkYCaLuFPnKOv30H7ztkceij8cS2iiwRzdjaxArbS183RF2HSSdp3zkbTPpgXIIh9tbydSawABJGedGLWLeBSa94WXKTfnJ1JrID0u/ykSzmpShyBted+WACCkbMziRWQehueFFHP2YEiClYzDlADODuTWAEnqciYApq/RG8D0g5pCrtpipe4NASxAvBVKChawCJtQ1k9b3uUipS2u5pg+UGsAIBlx/7ryYenpdp1N2yDotQ9HkR/FcWNOeYikQBwplmomWxexCRlRLpTqHJ6jjjBKmF1kkS+SWIBAIdKJTntHJaqb0KqfkILAjc7+Pdc/JMAmwsvwgGKasjZuViTgOtvwgJa+qQftJxtFGsSsP+B8PfdFPF+RKrSiPOCY9UCdt8L0q5jKCwAfKOAxHHMvWeTWADSDtGOU+7XfNAXTP+WanAqCEZ7JX7SWFS9MIkVgPyu+xQ0GshxZMB5Cr5Sq81CpzQ+ExYxQvUirho6BaDR6P0RTAYBqAWcnQZpihUyRVxp4201TgFNVPf1RFh+zsYGX4yKqN9r/Ow+wiMFoOPV5RdLj9Xg7DjKqB5oAW1fVyngnNGIQEgRY+NCl+3aj6sUgJquBYDDj3g7DjSx8xQzeA+pytloIgXspX5On+0A0Z90FbR41Ahu3MQdhHQP0ALAK9pPO/1skI4ZWn7UhyQVEQIy9kMN+j+z0wWzVIQQ2ZcpveAMAYdlRrq10Y0J8fJ6QLLXMYYM3YxEihkIgVOv53tQ/UwxJtnFwPGJBn4ODvj2ilPZ9YWVuTHOwAbluLxJqBvt1C1TX4DagM73+NNgtbh3HIzCt39DLkktryMRFXRtHqL/mf8JfNCdsAI+Pc/z/gLM/qSASCHNiAAAAABJRU5ErkJggg==" />
			<style>';
			require("css/searchpage.css");
			echo '</style>
			<style>';
			require("css/groups.css");
			echo '</style>
			<script>';
			require("js/jquery-2.1.1.min.js");
			echo '</script>
			<style>
				body {
					position:relative;
					background-color:white;
					padding:0;
					margin:0;
					padding-top:172px;
					min-width:980px;
				}
			</style>
		</head>
		<body>
			<div id="navbar">
				<a href="index.php">
					<img id="logo" src="gfx/logo.png"></img>
				</a>
				<form>
					<input type="text" id="searchbox" name="q" value="'.$_SESSION["query"].'" autocomplete=off>
					<label id="searchbutton" for="searchbuttoninput">
						<input id="searchbuttoninput" type="submit" style="display:none;">
					</label>
				</form>
				'.$header.'
			</div>
			<img id="snippet1" src="gfx/snippet1.png">
			<img id="snippet2" src="gfx/snippet2.png">
			<img id="snippet3" src="gfx/snippet3.png">
			<div id="content">
				<a id="subredditlink" href="index.php?q=/r/'.$comments["subreddit"].'">'.$comments["subreddit"].'</a> <span style="font-size:11px;">&gt;</span><br>
				<span id="threadheader">'.$comments["title"].'</span><br>
				<span id="threadsubheader">'.$comments["score"].' points and '.$comments["num_comments"].' comments.</span><br>';
				foreach ($comments["comments"] as $comment) {
					echo '<div class="comment">
							<div class="iconwrapper">
								<img class="icon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACIAAAAiCAYAAAA6RwvCAAAAnUlEQVR42u3UsQ2AIBAFUOemoKGESajYhA3oWABCfeabUKiFNijRX3xDzkt4ILLknGWGLHi01l4NIYQQQsirkFKKhBDEGLMFY9Qeh2BipdQuqD0OwS4cIaj9FzLNp5nmsPIe6am1SoxRvPfinBOt9RaMUcM79AyFpJTEWns6pMegB73DIFj1FaIHvcMgdxE934fw9yWEEEIIuQuZISuz4exV/fiHnwAAAABJRU5ErkJggg==">
							</div>
							<div class="commentthingywrapper">
								<span class="date">
									'.time_passed($comment["timestamp"]).'
								</span>
								<img class="commentthingy" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEoAAAAdCAYAAAANW/o+AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAYdEVYdFNvZnR3YXJlAHBhaW50Lm5ldCA0LjAuNWWFMmUAAAIDSURBVGhD7Zk7b8JAEIT9F+mpaWkRNZRQQ0tNC/wQKHgJWpAoeDa4uGhWWusczmYX7hKcuPg82vPeRjceSwSi+/1u1uu1mc/nJQ7gDTyKttutOR6P5na7FR4cyrX+DvAGHkUhhv8Woc6CuWTU9XqlhaLrarUS92oUcyNcpBs+XTlRkl6NUqL4KfwFQp2lTJRQy0QJSRLlullEght1uVxowad2u93cOoQul0txr0YxN8JFukGqMKVSqWTWoZQTJenVaJBEDQYDMmUymaRqkLfPhxYmUTAHhvT7/VTNtNttMxwO6V+CvDmvatBE8VN4l/F4TGa0Wq1UnQVeRzbMF8/O0mg0MnH1M0miXDe1sAF8eNuULKrVqpnNZg+zXkVyFq1JIDHqfD7TwjtqJ8qunwGzptOp+O/k6WKxEPXaJkn6MddbogCb0+v1UjXTbDaJWq2WWkf9fdYraM4iSRLjNVGsbA4UNUxjQ+y+/X5vRqNRYhr3P5ufp9JEaZUShYt0g1T5c1NWbetut6PXtV6vO+9rlBMl6dUoJYqfgm86nU5ubQOzkCzXPQ2hzpIkynXzp8Fr6FrX8C+M8kFplJDgRp1OJ1oouuLnJWmvRjE3wkW64dOVEyXp1Sglip/CXyDUWcpECZUSha8QDocDLRadEImCN/Q1SxzHZrPZ0C8NJY/AmziOzRf/I1Cj9JGkuwAAAABJRU5ErkJggg==">
							</div>
							<div class="commentcontent" style="padding-left:'.($comment["counter"]*10).'px;">
								<div class="commentuser">
									'.$comment["author"].'
								</div>
								<div class="commenttext">
									'.$comment["text"].'
								</div>
							</div>
						</div>';
				}
				echo '
			</div>
	';
}

function HTMLrequest($urls,$counter=0) {
	if ($counter > 5) {
		return;
	}
	global $useragent;
	$curl=curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_USERAGENT => $useragent
	));
	foreach ($urls as $key=>$url) {
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url
		));
		
		$request=curl_exec($curl);
		$info=curl_getinfo($curl,CURLINFO_REDIRECT_URL);
		if ($info) {
			curl_close($curl);
			$urls[$key]=$info;
			return HTMLrequest($urls,$counter++);
			die();
		}
		
		$info2=curl_getinfo($curl,CURLINFO_HTTP_CODE);
		if (!isset($info2) || substr($info2,0,1)!="2") {
			continue;
		}
		
		$data=json_decode($request);
		$echo=$url;
		if ($request && (!isset($data->error) || empty($data->error))) {
			curl_close($curl);
			return $data;
		} else {
			unset($urls);
		}
	}
	curl_close($curl);
}

function searchpage($d) {
	$request=$d["request"];
	$proxyadd="";
	$proxyform="";
	if (isset($request["enableproxy"]) && $request["enableproxy"]=="true") {
		$proxyadd="&enableproxy=".$request["enableproxy"];
		$proxyform="<input type=hidden name=enableproxy value=".$request["enableproxy"].">";
	}
	/*
	if (loggedin()) {
		$subs="";
		foreach ($_SESSION["subscribed"] as $sub) {
			$subs.='<a href="index.php?q='.$sub["url"].'" class="subitem"><span>'.$sub["name"].'</span></a>';
		}
		$header='
				<div id="navbarlinks">
					<a target=_blank href="http://www.reddit.com/u/'.$_SESSION["username"].'">+'.$_SESSION["username"].'</a>
					<div id="subtoggle" href="#">
						<div id="subscribed">
							'.$subs.'
						</div>
						subscribed
					</div>
				</div>
				<div id="navbarmenu">
					<div id="navbarmenuicon"></div>
					<div id="navbarmenunotification"></div>
					<div id="navbarmenuadd"></div>
					<div id="navbarmenuprofile" tabindex="1">
						<a href="index.php?action=logout" id="navbarlogout">
							Logout
						</a>
					</div>
				</div>';
	} else {
		$header='<div id="navbarmenu">
					<div id="navbarmenuicon"></div>
					<label for="logincheckbox" id="navbarmenubutton">Sign In</label>
					<input type="checkbox" id="logincheckbox" value="1" style="display:none;">
					<div id="navbarlogin">
						<form method="post">
							<input type="hidden" name="q" value="'.$d["query"].'">
							<input type="hidden" name="action" value="login">
							<input type="text" name="username" placeholder="User">
							<input type="password" name="password" placeholder="Password">
							<button id="loginbutton" type="submit">Sign In</button>
						</form>
					</div>
				</div>';
	}*/
	
	$header='<div id="navbarmenu">
					<div id="navbarmenuicon"></div>
					<div id="navbarmenubutton">Sign In</div>
				</div>';
	
	$active=array("","","","","","","","");
	switch ($d["sub"]) {
		case "/new":
			$active[1]="active";
			$sublink="&action=new";
			break;
		case "/rising":
			$active[2]="active";
			$sublink="&action=rising";
			break;
		case "/controversial":
			$active[3]="active";
			$sublink="&action=controversial";
			break;
		case "/top":
			$active[4]="active";
			$sublink="&action=top";
			break;
		case "/gilded":
			$active[5]="active";
			$sublink="&action=gilded";
			break;
		case "/promoted":
			$active[6]="active";
			$sublink="&action=promoted";
			break;
		case "/wiki":
			$active[7]="active";
			$sublink="&action=wiki";
			break;
		default:
			$active[0]="active";
			$sublink="&action=hot";
			break;
	}
	
	$sublink.=$proxyadd;
	
	echo '
		<!DOCTYPE HTML>
		<head>
			<meta charset="utf-8">
			<title>'.$d["query"].' - Google Search</title>
			<link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAYdEVYdFNvZnR3YXJlAHBhaW50Lm5ldCA0LjAuNWWFMmUAAAN3SURBVFhHtZdpTxRBEIbnb5j4E/zgPxIQNIpHNB4hGo8Y9RPeV0g0JhtWBA8EL5QAggdEFKMkiCC3RDlcBJdju6Wtdyad7WlqekaIJE/ITtd0vdNdVV3t4W9TtdpYkBYtBencn8K0UP8T+IAv+PSdF1UtbyiozM1xxnHsvCPUkcdCnXpGPI9me83Kd+ETvj1SVG8Pujj2RKjWfqmm5qSS0s1YRqqzLfw8AL49UpLlBm223BaqsVcqIaRq6JHqQF3w7DR94cBk3unCklTXXgp/vLiKn0sD3x43YLP5llAfRgMH9d1yxfi26rCI2QWpDpIA244jkYCaLuFPnKOv30H7ztkceij8cS2iiwRzdjaxArbS183RF2HSSdp3zkbTPpgXIIh9tbydSawABJGedGLWLeBSa94WXKTfnJ1JrID0u/ykSzmpShyBted+WACCkbMziRWQehueFFHP2YEiClYzDlADODuTWAEnqciYApq/RG8D0g5pCrtpipe4NASxAvBVKChawCJtQ1k9b3uUipS2u5pg+UGsAIBlx/7ryYenpdp1N2yDotQ9HkR/FcWNOeYikQBwplmomWxexCRlRLpTqHJ6jjjBKmF1kkS+SWIBAIdKJTntHJaqb0KqfkILAjc7+Pdc/JMAmwsvwgGKasjZuViTgOtvwgJa+qQftJxtFGsSsP+B8PfdFPF+RKrSiPOCY9UCdt8L0q5jKCwAfKOAxHHMvWeTWADSDtGOU+7XfNAXTP+WanAqCEZ7JX7SWFS9MIkVgPyu+xQ0GshxZMB5Cr5Sq81CpzQ+ExYxQvUirho6BaDR6P0RTAYBqAWcnQZpihUyRVxp4201TgFNVPf1RFh+zsYGX4yKqN9r/Ow+wiMFoOPV5RdLj9Xg7DjKqB5oAW1fVyngnNGIQEgRY+NCl+3aj6sUgJquBYDDj3g7DjSx8xQzeA+pytloIgXspX5On+0A0Z90FbR41Ahu3MQdhHQP0ALAK9pPO/1skI4ZWn7UhyQVEQIy9kMN+j+z0wWzVIQQ2ZcpveAMAYdlRrq10Y0J8fJ6QLLXMYYM3YxEihkIgVOv53tQ/UwxJtnFwPGJBn4ODvj2ilPZ9YWVuTHOwAbluLxJqBvt1C1TX4DagM73+NNgtbh3HIzCt39DLkktryMRFXRtHqL/mf8JfNCdsAI+Pc/z/gLM/qSASCHNiAAAAABJRU5ErkJggg==" />
			<style>';
			require("css/searchpage.css");
			echo '</style>
			<script>';
			require("js/jquery-2.1.1.min.js");
			echo '</script>
			<script>';
			require("js/script.js");
			echo '</script>
			<style>
				body {
					background-color:white;
					padding:0;
					margin:0;
					padding-top:172px;
					min-width:980px;
				}
			</style>
		</head>
		<body>
			<div id="navbar">
				<a href="index.php">
					<img id="logo" src="gfx/logo.png"></img>
				</a>
				<form>
					<input type="text" id="searchbox" name="q" value="'.$d["query"].'" autocomplete=off>
					'.$proxyform.'
					<label for=searchsubmitbutton id="searchbutton">
						<input id="searchsubmitbutton" type="submit" style="display:none;">
					</label>
				</form>
				'.$header.'
			</div>
			<div id="navbar2">
				<a href="index.php?q='.$d["query"].$proxyadd.'">
					<div class="navbar2sub '.$active[0].'">
						Hot
					</div>
				</a>
				<a href="index.php?action=new&q='.$d["query"].$proxyadd.'">
					<div class="navbar2sub '.$active[1].'">
						New
					</div>
				</a>
				<a href="index.php?action=rising&q='.$d["query"].$proxyadd.'">
					<div class="navbar2sub '.$active[2].'">
						Rising
					</div>
				</a>
				<a href="index.php?action=controversial&q='.$d["query"].$proxyadd.'">
					<div class="navbar2sub '.$active[3].'">
						Controversial
					</div>
				</a>
				<a href="index.php?action=top&q='.$d["query"].$proxyadd.'">
					<div class="navbar2sub '.$active[4].'">
						Top
					</div>
				</a>
				<a href="index.php?action=gilded&q='.$d["query"].$proxyadd.'">
					<div class="navbar2sub '.$active[5].'">
						Gilded
					</div>
				</a>
				<a href="index.php?action=promoted&q='.$d["query"].$proxyadd.'">
					<div class="navbar2sub '.$active[6].'">
						Promoted
					</div>
				</a>
				<a href="index.php?action=wiki&q='.$d["query"].$proxyadd.'">
					<div class="navbar2sub '.$active[7].'">
						Wiki
					</div>
				</a>
				<img id="cog" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEYAAAAcCAYAAADcO8kVAAAABGdBTUEAALGPC/xhBQAAAAlwSFlzAAAOwgAADsIBFShKgAAAABh0RVh0U29mdHdhcmUAcGFpbnQubmV0IDQuMC41ZYUyZQAAAyhJREFUWEftmMsva1EUh/2LWtQ71QgxUGUgIq1IhCDCBANFIkW1QeIdIQbVNJFgYEIi8X4Tr7bj381v5Z6jZd9Zz9bknsF3Jpal+7Me+7QglUrh8vLS5i9XV1egk4Kbmxuk02mbDK6vr20xKmwx/0DE8MGesvmCc+bXxfBDbG5uYm5uTlheXsbJyYkyVhd5IWZvbw/Nzc0oLCwU6urqsL6+jmQyqYzXgXYxn5+feHx8BOfa29sb7u7uEIlEUFNTY4qpqqrC2NiYrM7393eJub+/x8fHhzKnFWgXw7+1sLCA4eFhTE9Pi4CmpiYUFRWZYpxOJxoaGiRmZmYGo6OjCIfDOD09Vea0Aq1iWCE7Ozuor6+Xw5eWlsLlcplCvlNSUiIxlObxeLC4uKitarSKYWsMDAzA4XBkCaisrERnZydCoZBUSF9fH9xud1YMf4cxbClV7lwjYvjgoLMaihkaGpJKyJQyODiIo6MjvL6+SlVxIwWDwSw5xcXF6OrqEjGq3LmGn1WbGB58f39fKsKomra2NhwcHPyIPT4+Rnd3tymmo6MDu7u7Moy/x1qBVjGEFbGxsWEO2/7+fry8vCjjJicnTTETExN4eHj4EWcV2sVQwsrKiimG1aM6MOPGx8clhtXF1uLa/h5nFVrF8LDcSu3t7WYr8WIXi8Vk2xhxvOscHh7C7/ebFdPS0oLV1VVpx8ycVqFVzNnZGXp7e2WQGgfmOua24fx4enrC8/OzzBxur4qKCjOOFRYIBHB7e6vMnWtEDB/8L1kNrwUjIyNyhzEObMhh5VAaZ05ra2uWFMIK6+npkbZT5c41FxcX+sRwoMbjcTQ2NkoFlJWVCZkCMuHlr7y8XGJ5KWQrcSupcucarWIIB+j29rZc8flGPT8/Lys7s70owufzYXZ2FltbW4hGo/JSeX5+rsxpBdrFcMhyjrAlWEF8oVxaWkJtba0pprq6GlNTUzJPWCGM4fzRVS1EuxgViUQCXq/XFGN87aCK1UVeiOG2Wltbk9YhrCDefFWxusgLMfmIiOGDvW/zBQe9LUaBiGErqX74v8LtJ61kfDFj80UymcQflewslOkqy/kAAAAASUVORK5CYII=">
			</div>
			<div id="navbar3">
				<div id="resultstats">
					'.$d["resultstats"].'
				</div>
			</div>
			<div id="results">
				';
				if (empty($d["results"])) {
					$d["results"]=page404();
				}
				
				
				foreach ($d["results"] as $r) {
					if (isset($r["strOverwrite"])) {
						$commentgreen='
						<a href="'.htmlentities($r["strLink"]).'">'.($r["strTitel"]).'</a><br>
						<a class="resultlink" href="#">'.$r["strOverwrite"].'</a>
						';
					} else {
						if (isset($r["nsfw"]) && $r["nsfw"]) {
							$nsfw="[NSFW] ";
						} else {
							$nsfw="";
						}
						$commentlink='index.php?action=groups&comment='.$r["strComment"];
						$commentstring=$nsfw.$r["numComments"].' comments';
						$commentgreen='
						<a target="_blank" href="'.htmlentities($r["strLink"]).'">'.($r["strTitel"]).'</a><br>
						<a class="resultlink" target=_blank href="'.$commentlink.'">'.$commentstring.'</a> 
						<span class="resultlink">-</span> 
						<a class="resultlink" target=_blank href="index.php?q='.$r["strDomain"].'">'.htmlentities($r["strDomain"]).'</a>
						';
					}
					
					
					echo '
						<div class="resultdiv">
							'.$commentgreen.'
							<br>
							<span class="resulttext">
								'.$r["strCreated"].str_replace("<a","<a target='_blank'",closetags($r["strBeschreibung"])).'
							</span>
						</div>
					';
				}
			if ($d["showNav"]) {
				if (!$d["back"]) {
					//Kein Zurück
					$back='
					<td>
						<span id="G1"></span>
					</td>
					';
				} else {
					$back='
					<td>
						<a href="'.$d["backlink"].$sublink.'">
							<span id="G2"></span><span id="back">Previous</span>
						</a>
					</td>
					';
					//Zurück
				}
				
				$toggle=0;
				$pagenumbers="";
				foreach ($d["pagelinks"] as $page) {
					if ($toggle==0 && $page["current"]==1) {
						$pagenumbers.='<td id="cur">
									<span class="o"></span><span class="bottomnavtext">'.$page["number"].'</span>
								</td>';
						$toggle=1;
					} else if ($toggle==0) {
						$pagenumbers.='<td>
									<a href="'.$page["link"].$sublink.'"><span class="o"></span><span class="bottomnavtext">'.$page["number"].'</span></a>
								</td>';
					} else {
						$pagenumbers.='<td>
									<a href="'.$page["link"].$sublink.'"><span class="o"></span><span class="bottomnavtext">'.$page["number"].'</span></a>
								</td>';
					}
				}
				
				if ($d["forward"]) {
					$next='<td>
								<a href="'.$d["forwardlink"].$sublink.'"><span id="gle"></span><span id="weiter" style="font-weight:700;">Next</span></a>
							</td>
							';
				} else {
					$next='<td>
								<span id="gle" style="cursor:default; width:62px;"></span>
							</td>
							';
				}
				
				echo '
				<div id="bottomnavwrapper">
					<table id="bottomnav">
						<tbody>
							<tr valign=top>
								'.$back.$pagenumbers.'
								'.$next.'
							</tr>
						</tbody>
					</table>
				</div>';
			} else {
				echo '
				<div id="bottomnavwrapper">
				</div>';
			}
				echo '
			</div>
			<div id="footer">
				<a div="help" style="margin-left:135px;">Help</a>
				<a div="business">Business</a>
				<a div="about">About</a>
				<a target=_blank href="http://www.reddit.com/user/Lutan">Contact</a>
				'.$d["proxyquery"].'
			</div>
			<div id="help" class="popup" style="display:none;">
	<h2>Help</h2>
	<div class="closebutton">Close</div>
	<p>
		You can support this project by helping me fund it. The costs to keep it alive (paying for the hosting / paying for energy drinks) are relatively high for a college student like me.<br><br><br>
		PayPal - send me a message on Reddit for my mail adress (unfortunately I couldn\'t make a donate button - need to be an organization for that)<br><br>
		BTC - 1BWvVmUh3FemK25jStSK8qJVGHR5YRhp9c
	</p>
</div>
<div id="business" class="popup" style="display:none;">
	<h2>Business</h2>
	<div class="closebutton">Close</div>
	<p>
		Interested in buying this project? Message me on Reddit.
	</p>
</div>
<div id="about" class="popup" style="display:none;">
	<h2>About</h2>
	<div class="closebutton">Close</div>
	<p>Want to use Reddit without people knowing you are using it? This page provides you with the known Google interface for your Reddit needs.<br><br>
	
	Browse a subreddit by entering it into the box ("/r/internetisbeautiful" or "/r/web_design").<br>
	Search for a term as you would on reddit.<br>
	Press the "I\'m feeling lucky" - button to browse a random subreddit.<br><br>
	
	Cluffle is your stealth mode to avoid things like suspicious coworkers and classmates.<br>
	Reddit is blocked at work? Cluffle also works as a proxy.<br>
	You want the usual Reddit interface while using the proxy? Just go to <a href="http://proxy.cluffle.com" target=_blank>proxy.cluffle.com</a>.
	</p>
</div>
		</body>
	';
}

    // close opened html tags
    function closetags ($html) {
        #put all opened tags into an array
        preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $html, $result );
        $openedtags = $result[1];
        #put all closed tags into an array
        preg_match_all ( "#</([a-z]+)>#iU", $html, $result );
        $closedtags = $result[1];
        $len_opened = count ( $openedtags );
        # all tags are closed
        if( count ( $closedtags ) == $len_opened )
        {
        return $html;
        }
        $openedtags = array_reverse ( $openedtags );
        # close tags
        for( $i = 0; $i < $len_opened; $i++ )
        {
            if ( !in_array ( $openedtags[$i], $closedtags ) )
            {
            $html .= "</" . $openedtags[$i] . ">";
            }
            else
            {
            unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
            }
        }
        return $html;
    }
    // close opened html tags
	
	
function time_passed($timestamp){
    //type cast, current time, difference in timestamps
    $timestamp      = (int) $timestamp;
    $current_time   = time();
    $diff           = $current_time - $timestamp;
   
    //intervals in seconds
    $intervals      = array (
        'year' => 31556926, 'month' => 2629744, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'minute'=> 60
    );
   
    //now we just find the difference
    if ($diff == 0)
    {
        return 'just now';
    }   

    if ($diff < 60)
    {
        return $diff == 1 ? $diff . ' second ago' : $diff . ' seconds ago';
    }       

    if ($diff >= 60 && $diff < $intervals['hour'])
    {
        $diff = floor($diff/$intervals['minute']);
        return $diff == 1 ? $diff . ' minute ago' : $diff . ' minutes ago';
    }       

    if ($diff >= $intervals['hour'] && $diff < $intervals['day'])
    {
        $diff = floor($diff/$intervals['hour']);
        return $diff == 1 ? $diff . ' hour ago' : $diff . ' hours ago';
    }   

    if ($diff >= $intervals['day'] && $diff < $intervals['week'])
    {
        $diff = floor($diff/$intervals['day']);
        return $diff == 1 ? $diff . ' day ago' : $diff . ' days ago';
    }   

    if ($diff >= $intervals['week'] && $diff < $intervals['month'])
    {
        $diff = floor($diff/$intervals['week']);
        return $diff == 1 ? $diff . ' week ago' : $diff . ' weeks ago';
    }   

    if ($diff >= $intervals['month'] && $diff < $intervals['year'])
    {
        $diff = floor($diff/$intervals['month']);
        return $diff == 1 ? $diff . ' month ago' : $diff . ' months ago';
    }   

    if ($diff >= $intervals['year'])
    {
        $diff = floor($diff/$intervals['year']);
        return $diff == 1 ? $diff . ' year ago' : $diff . ' years ago';
    }
}
/*	
function loggedin() {
	return isset($_SESSION["active"]) && $_SESSION["active"];
}

function login() {
	global $useragent;
	if (loggedin()) {
		header("Location: index.php");
		die();
	}
	$curl=curl_init();
	
	//LOGIN AND SHIT
	$param=array(
		"api_type" => "json",
		"user" => $_REQUEST["username"],
		"passwd" => $_REQUEST["password"],
		"rem" => true
	);
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => "www.reddit.com/api/login",
		CURLOPT_USERAGENT => $useragent,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => http_build_query($param)
	));
	$request=curl_exec($curl);
	$data=json_decode($request);
	if (isset($data->json->errors[0])) {
		header("Location: index.php");
		die();
	}
	// END OF LOGIN
	
	// SUBSCRIBED SUBREDDITS
	$param=array(
		"api_type" => "json",
		"user" => $_REQUEST["username"],
		"passwd" => $_REQUEST["password"],
		"rem" => true
	);
	curl_setopt_array($curl, array(	
			CURLOPT_POST => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => "http://www.reddit.com/subreddits/mine/subscriber.json",
			CURLOPT_USERAGENT => $useragent,
			CURLOPT_HTTPHEADER => array("X-Modhash" => $data->json->data->modhash),
			CURLOPT_COOKIE => "reddit_session=".$data->json->data->cookie
		));
	$request=curl_exec($curl);
	$data=json_decode($request);
	if (isset($data->json->errors[0])) {
		header("Location: index.php");
		die();
	}
	// END OF SUBSCRIBED SUBREDDITS
	
	$subscribed=array();
	foreach ($data->data->children as $child) {
		$buffer=array();
		$buffer["name"]=$child->data->display_name;
		$buffer["url"]=$child->data->url;
		$subscribed[]=$buffer;
	}
	
	$_SESSION["active"]=true;
	$_SESSION["subscribed"]=$subscribed;
	$_SESSION["username"]=$_REQUEST["username"];
	$_SESSION["modhash"]=$data->json->data->modhash;
	$_SESSION["cookie"]=$data->json->data->cookie;
	header("Location: index.php");
	die();
}
*/
function page404() {
	$buffer=array();
	$return=array();
	
	$buffer["strLink"]="#";
	$buffer["strTitel"]="I want to be the very best - like no one ever was.";
	$buffer["strOverwrite"]="Basically what I want to tell you is: 404.";
	$buffer["strCreated"]="0 fucks left - ";
	$buffer["strBeschreibung"]="Herp derpsum derpus pee ter herderder. Pee derps sherper herderder, merp herpy herp derpus. Sherpus terpus pee berp derperker derpy tee herderder. Herderder merp, serp merpus herpem sherpus. Me le derp perp derps derperker ";
	$return[]=$buffer;
	$buffer["strLink"]="#";
	$buffer["strTitel"]="To find them is my real test, to fix them is my cause.";
	$buffer["strOverwrite"]="Either this category doesn't exist on this sub or you suck at searching.";
	$buffer["strCreated"]="0 fucks left - ";
	$buffer["strBeschreibung"]="Herp derpsum derpus pee ter herderder. Pee derps sherper herderder, merp herpy herp derpus. Sherpus ";
	$return[]=$buffer;
	$buffer["strLink"]="#";
	$buffer["strTitel"]="I will travel across the code, searchin' far and wide.";
	$buffer["strOverwrite"]="The thing is, I still have to fill this page to make it look like Google.";
	$buffer["strCreated"]="0 fucks left - ";
	$buffer["strBeschreibung"]="Herp derpsum derpus pee ter herderder. Pee derps sherper herderder, merp herpy herp derpus. Sherpus terpus pee berp derperker derpy tee herderder. H";
	$return[]=$buffer;
	$buffer["strLink"]="#";
	$buffer["strTitel"]="Each syntax to understand the error that's inside!";
	$buffer["strOverwrite"]="And I'm running out of text.";
	$buffer["strCreated"]="0 fucks left - ";
	$buffer["strBeschreibung"]="Herp derpsum derpus pee ter herderder. Pee derps sherper herderder, merp herpy herp derpus. Sherpus terpus pee berp derperker";
	$return[]=$buffer;
	$buffer["strLink"]="#";
	$buffer["strTitel"]="Errorlog! It's you and me. I know it's my destiny!";
	$buffer["strOverwrite"]='Fun fact: Cluffle is defined as a complete waste of time.';
	$buffer["strCreated"]="0 fucks left - ";
	$buffer["strBeschreibung"]="Herp derpsum derpus pee ter herderder. Pee derps sherper herderder, merp herpy herp derpus. Sherpus terpus pee berp derperker derpy tee herderder. Herderder merp, serp merpus herpem sherpus. Me le derp perp derps derperker ";
	$return[]=$buffer;
	$buffer["strLink"]="#";
	$buffer["strTitel"]="Errorlog! Ooh you're my best friend on a page we must defend!";
	$buffer["strOverwrite"]="Wasting time is the whole purpose of this page.";
	$buffer["strCreated"]="0 fucks left - ";
	$buffer["strBeschreibung"]="Herp derpsum derpus pee ter herderder. Pee derps sherper herderder, merp herpy herp";
	$return[]=$buffer;
	$buffer["strLink"]="#";
	$buffer["strTitel"]="Errorlog! A heart so true. Our courage will pull us through.";
	$buffer["strOverwrite"]="Wasting time while at work or school. Awesome.";
	$buffer["strCreated"]="0 fucks left - ";
	$buffer["strBeschreibung"]="Herp derpsum derpus pee ter herderder. Pee derps sherper herderder, merp herpy herp derpus. Sherpus terpus pee berp derperker derpy tee herderder. Herderder merp, serp merpus herpem she";
	$return[]=$buffer;
	$buffer["strLink"]="#";
	$buffer["strTitel"]="You teach me and I'll teach you. Errorlog! Gotta fix 'em all!";
	$buffer["strOverwrite"]="Trouble with filters? Use the proxy feature on the bottom.";
	$buffer["strCreated"]="0 fucks left - ";
	$buffer["strBeschreibung"]="Herp derpsum derpus pee ter herderder. Pee derps sherper herderder, merp herpy herp derpus. Sherpus terpus pee berp d";
	$return[]=$buffer;

	return $return;
}


?>