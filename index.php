<?php
	//ini_set('display_errors', '1');
	//ini_set('display_startup_errors', '1');
	//error_reporting(E_ALL);

	$genToken = substr(str_shuffle(MD5(microtime())), 0, 16);

	// Check for a request
	if(isset($_GET['rq']) && strlen($_GET['rq']) == 16 && ctype_alnum($_GET['rq'])) {
		header('Access-Control-Allow-Origin: *');

		// Check if the log exists
		$query = $_GET['rq'];

		// Name the file after the Url
		$file = $query . ".txt";

		if(!file_exists($file)) {
			fclose(fopen($file, 'w'));
		}

		// Get the current contents of the file
		$current = file_get_contents($file);

		// Show the url
		$reqUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$reqUrl = str_replace("&inspect", "", $reqUrl);

		$urlTpl = '<div class="input-group mb-3">';
		$urlTpl .= '<div class="input-group-prepend">';
		$urlTpl .= '<span class="input-group-text" id="inputGroup-sizing-default">Request URL</span>';
		$urlTpl .= '</div>';
		$urlTpl .= '<input type="text" class="form-control" onFocus="this.select();" value="' . $reqUrl . '" style="background-color: #fff;" readonly>';
		$urlTpl .= '</div>';

		// Are we inspecting the logs
		if(isset($_GET['inspect'])) {
			// No data yet
			if(empty($current)) {
				// No data, show the Url
				$getTpl = file_get_contents("req.tpl.html");
				$getTpl = str_replace("REQ_URL", $urlTpl, $getTpl);
				$getTpl = str_replace("CURRENT_HERE", "", $getTpl);
				echo $getTpl;
			}else{
				// There is data, load the content
				$getTpl = file_get_contents("req.tpl.html");
				$getTpl = str_replace("REQ_URL", $urlTpl, $getTpl);
				$getTpl = str_replace("CURRENT_HERE", $current, $getTpl);
				echo $getTpl;
			}
		// Clearing the file
		}else if(isset($_GET['clear'])) {
			// Clear file contents
			if(file_exists($file)) {
				file_put_contents($file, "");

				// Redirect back
				header("Location: ?rq=" . $_GET['rq'] . "&inspect");
				exit();
			}
		// Requesting
		}else{
			$postdata = file_get_contents("php://input");

			// Header
			$newlog = '<br /><div class="card"><h5 class="card-header">';
			$newlog .= $_SERVER["REQUEST_METHOD"] . ' ' . $_SERVER['REQUEST_URI'] . '<span class="float-right">' . date('Y-m-d H:i:s') . '</span>';
			$newlog .= '</h5>';

			// Body
			$newlog .= '<div class="card-body">';
			$newlog .= '<div class="row">';
			$newlog .= '<div class="col-md-6">';
    
			//$newlog .= "IP: ".$_SERVER['REMOTE_ADDR'] . "<br />";

			// Header
			$newlog .= '<h5>Headers</h5>';
			foreach (getallheaders() as $name => $value) {
				$newlog .= "<b>$name</b>: $value<br />";
			}
			$newlog .= '</div>';

			// Body
			$newlog .= '<div class="col-md-6">';
			$newlog .= '<h5>Body</h5>';
			$newlog .= "<pre>" . $postdata . "</pre>";
			$newlog .= "</div></div>";
			$newlog .= "</div></div>";
			$current = $newlog . $current;
			$current = keepLines($current, 700);
			file_put_contents($file, $current);
			header("HTTP/1.1 200 OK");
			exit();
		}
	}else{
		$genLink = '<br /><a class="btn btn-info" href="index.php?rq=' . $genToken . '&inspect" role="button">Generate Url</a>';

		$getTpl = file_get_contents("req.tpl.html");
		$getTpl = str_replace("REQ_URL", "", $getTpl);
		$getTpl = str_replace("CURRENT_HERE", $genLink, $getTpl);
		echo $getTpl;
	}

	function movePage($num,$url){
		static $http = array (
			100 => "HTTP/1.1 100 Continue",
			101 => "HTTP/1.1 101 Switching Protocols",
			200 => "HTTP/1.1 200 OK",
			201 => "HTTP/1.1 201 Created",
			202 => "HTTP/1.1 202 Accepted",
			203 => "HTTP/1.1 203 Non-Authoritative Information",
			204 => "HTTP/1.1 204 No Content",
			205 => "HTTP/1.1 205 Reset Content",
			206 => "HTTP/1.1 206 Partial Content",
			300 => "HTTP/1.1 300 Multiple Choices",
			301 => "HTTP/1.1 301 Moved Permanently",
			302 => "HTTP/1.1 302 Found",
			303 => "HTTP/1.1 303 See Other",
			304 => "HTTP/1.1 304 Not Modified",
			305 => "HTTP/1.1 305 Use Proxy",
			307 => "HTTP/1.1 307 Temporary Redirect",
			400 => "HTTP/1.1 400 Bad Request",
			401 => "HTTP/1.1 401 Unauthorized",
			402 => "HTTP/1.1 402 Payment Required",
			403 => "HTTP/1.1 403 Forbidden",
			404 => "HTTP/1.1 404 Not Found",
			405 => "HTTP/1.1 405 Method Not Allowed",
			406 => "HTTP/1.1 406 Not Acceptable",
			407 => "HTTP/1.1 407 Proxy Authentication Required",
			408 => "HTTP/1.1 408 Request Time-out",
			409 => "HTTP/1.1 409 Conflict",
			410 => "HTTP/1.1 410 Gone",
			411 => "HTTP/1.1 411 Length Required",
			412 => "HTTP/1.1 412 Precondition Failed",
			413 => "HTTP/1.1 413 Request Entity Too Large",
			414 => "HTTP/1.1 414 Request-URI Too Large",
			415 => "HTTP/1.1 415 Unsupported Media Type",
			416 => "HTTP/1.1 416 Requested range not satisfiable",
			417 => "HTTP/1.1 417 Expectation Failed",
			500 => "HTTP/1.1 500 Internal Server Error",
			501 => "HTTP/1.1 501 Not Implemented",
			502 => "HTTP/1.1 502 Bad Gateway",
			503 => "HTTP/1.1 503 Service Unavailable",
			504 => "HTTP/1.1 504 Gateway Time-out"
		);
		header($http[$num]);
		header ("Location: $url");
	 }

	function keepLines($str, $num=10) {
		$lines = explode("\n", $str);
		$firsts = array_slice($lines, 0, $num);
		return implode("\n", $firsts);
	}
?>