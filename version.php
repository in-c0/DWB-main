<?php

	$_version = "1.6.9";
	$_date = "23/03/2025";
	$_text = "Copyright 2025 (C) Owned & created by Dan - ";
	$isDev = true;
	$checkerItem = "DAN MADE THIS";
	$isLocalTest = true;
	$kakdoaw = base64_encode ($checkerItem);
	if(!isset($response))
	{
		echo '<script> var kakdoaw = "'.$kakdoaw.'" </script>';
	}
?>