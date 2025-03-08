<?php
require("config.php");
?>
	<!DOCTYPE html>
	<html>

	<head>
		<meta http-equiv="Content-Type" content="text/html" />
		<meta charset="UTF-8">
		<title>Multics Cache to cache auto exchanger</title>
		<meta name="Keywords" content="best cache exchanger, multics cache exchanger, cahces exchanger,multics auto exchanger tool">
		<meta name="theme-color" content="#2c7dbc">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link rel="stylesheet" href="dist/css/app.min.css">
		<link rel="icon" type="image/x-icon" href="favicon.png" />
		<style>
		input[type=text],
		select {
			width: 100%;
			padding: 12px 20px;
			margin: 8px 0;
			display: inline-block;
			border: 1px solid #ccc;
			border-radius: 4px;
			box-sizing: border-box;
		}
		
		input[type=number] {
			width: 100%;
			padding: 12px 20px;
			margin: 8px 0;
			display: inline-block;
			border: 1px solid #ccc;
			border-radius: 4px;
			box-sizing: border-box;
		}
		
		input[type=submit] {
			width: 100%;
			background-color: #4CAF50;
			color: white;
			padding: 14px 20px;
			margin: 8px 0;
			border: none;
			border-radius: 4px;
			cursor: pointer;
		}
		
		input[type=submit]:hover {
			background-color: #45a049;
		}
		</style>
	</head>

	<body>
		<svg style="visibility: hidden; position: absolute; top: -1000px; left: -1000px;">
			<linearGradient id="svgGradient" x1="0%" y1="50%" x2="0%" y2="0%" gradientUnits="userSpaceOnUse">
				<stop offset="0%" stop-color="#2c7dbc" />
				<stop offset="100%" stop-color="#7cccc5" /> </linearGradient>
		</svg>
		<header class="header section section--header">
			<div class="section__layout">
				<a href="index.php" class="header__logo"> <span data-aos="fade-right" data-aos-delay="50">Cache</span> <span data-aos="fade-right" data-aos-delay="75">Auto Exchanger</span> </a>
				<div class="nav-icon"> <span></span> <span></span> <span></span> <span></span> </div>
				<nav class="header__nav">
					<div class="header__nav-layout" data-aos="fade-down" data-aos-delay="75">
						<ul class="header__nav-links">
							<li><a href="cache-to-cache-exchange.php">Cache</a></li>
							<li><a href="cccam-to-cccam-exchange.php">CCcam</a></li>
							<li><a href="mgcamd-to-mgcamd-exchange.php">MGcamd</a></li>
							<li><a href="profile-to-profile-exchange.php">Profile Nline</a></li>
						</ul>
						<a href="https://xtream-masters.com/trail_license.php" class="button button--outline button--color-white button--size-m"> <span>Multics+Oscam Panel</span> </a>
						<a href="https://paksat.pk/cache.php" class="button button--outline button--color-white button--size-m"> <span>Cache Clearner</span> </a>
					</div>
				</nav>
			</div>
		</header>
		<main>
			<section class="section section--home">
				<div class="home-circle" data-aos="fade-left" data-aos-delay="50"></div>
				<div class="section__layout">
					<div class="section__content">
						<h1 data-aos="fade-up" data-aos-delay="50">Cache Exchange</h1> </div>
					<div class="home-image" data-aos="fade-left" data-aos-delay="150">
						<br>
						<Br>
						<svg>
							<use xlink:href="dist/svg/symbols.svg#message-plane" />
						</svg>
						<br>
						<Br> </div>
				</div>
			</section>
			<section class="section section--features" id="features-anchor">
				<div class="section__layout" data-aos="fade-up" data-aos-offset="-100" data-aos-delay="200">
					<div class="grid">
						<div class="grid__cell">
							<div class="section__content" data-aos="fade-up">
								<h2>
           Cache Exchanging Reception [ <?php echo $multics_version; ?> ]
          </h2>
								<p> Add your cache peer if it will valid then you will receive our cache in this page.</p>
								<?php if ($port)
{ //ok
}
else
{ echo'<h3><font color="red">Temporarily exchange disabled, Open Soon.</font></h3>';}
?>
									<div class="hideforum">
										<center>
											<br><b><input type="text" id="mybip" name="host" placeholder="Domain or IP" id="host" value="" pattern="^[a-z.A-Z0-9]+$" required>  
<input id="ip" type="text" placeholder="Valid IP" name="ip" DISABLED>
<input type="number" id="mybport" placeholder="Port" name="port" pattern="^[0-9]+$" required></b></br>
											<input id="processBtn2" type="submit">
										</center>
									</div>
									<center>
										<div id="result2" <?php if ($port) { } else { echo 'disabled'; } ?>></div> <img id="sharesImg2" width="50px" src="images/filetransfer.gif" /></center>
									<br><b><a href="https://github.com/cline-pk/multics-design">multics design, multics modification, multi card server template, beautiful design of multics server</a></b> </b>
									</br>
	</body>
	<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
	<script type="text/javascript">
	$(function() {
		$("#mybip").change(function() {
			var addressinput = $(this).val();
			$.post("ip.php", {
				host: addressinput
			}, function(data) {
				document.getElementById("ip").value = data;
			});
		});
	});
	$('#sharesImg2').hide();
	$("#processBtn2").click(function() {
		$('#sharesImg2').show();
		var mybip = document.getElementById("mybip").value;
		var mybport = document.getElementById("mybport").value;
		var cache = 'cache';
		$.post("ashan.php", {
			ip: mybip,
			port: mybport,
			call: cache
		}, function(data) {
			$('#sharesImg2').hide();
			$("#result2").html(data);
		});
	});

	function myFunction() {
		var copyText = document.getElementById("prince");
		copyText.select();
		document.execCommand("copy");
	}
	</script>
	</div>
	</div>
	</div>
	</div>
	</section>
	</main>
	<?php include 'footer.php'; ?>