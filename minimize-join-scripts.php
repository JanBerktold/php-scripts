<?php
	// © Jan Gieseler, 2013

	// Script combines & minimizes all .js files in the parent folder
	// Result is cached until a change to the files is dealt

	header('Content-type: application/x-javascript');

	$cache_path = 'cache.txt';
	$index_path = 'cache-index.txt';

	function getScriptsInDirectory(){
		$array = Array();
		$scripts_in_directory = scandir('.');
		foreach ($scripts_in_directory as $script_name) {
			if (preg_match('/(.+)\.js/', $script_name))
			{
				array_push($array, $script_name);
			}
		}
		return $array;
	}

	function compilingRequired(){
		global $cache_path;
		global $index_path;
		if (file_exists($cache_path) && file_exists($index_path))
		{
			$cache_time = filemtime($cache_path);
			$files = getScriptsInDirectory();
			foreach ($files as $script_name) {
				if(filemtime($script_name) > $cache_time)
				{
					return true;
				}
			}

			$array = explode(PHP_EOL, file_get_contents($index_path));
			foreach($array as $name)
			{
				if (!file_exists($name))
				{
					return true;
				}
			}

			return false;
		}
		return true;
	}

	function compressScript($buffer) {
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		$buffer = str_replace(array("\r\n","\r","\n","\t",'  ','    ','     '), '', $buffer);
		$buffer = preg_replace(array('(( )+{)','({( )+)'), '{', $buffer);
		$buffer = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $buffer);
		$buffer = preg_replace(array('(;( )+)','(( )+;)'), ';', $buffer);
		return $buffer;
	}

	if (compilingRequired())
	{
		if (file_exists($cache_path)){
			unlink($cache_path);	
			unlink($index_path);
		}

		$scripts_in_directory = getScriptsInDirectory();
		$file_handler = fopen($cache_path, 'w+');
		$cache_handler = fopen($index_path, 'w+');

		foreach ($scripts_in_directory as $name)
		{
			if (strlen(file_get_contents($cache_path)) > 0){
				fwrite($file_handler, str_repeat(PHP_EOL, 2));
			}

			fwrite($file_handler, '/**** ' . $name . ' ****/' . str_repeat(PHP_EOL, 2));
			fwrite($file_handler, compressScript(file_get_contents($name)));
			fwrite($cache_handler, $name . PHP_EOL);
		}

		echo file_get_contents($cache_path);
	}
	else
	{
		echo file_get_contents($cache_path);
	}

?>