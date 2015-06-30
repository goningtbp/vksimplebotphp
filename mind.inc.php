<?php
$file = file('mind.txt');
		$mind = array();
		$switcher = 0;
		$preg = "";
		foreach($file as $line){
			if($switcher == 0){
				// Если регулярка
				$preg = trim(preg_replace("/[^A-ZА-ЯЁ ]+/ui","", $line));
				$mind[$preg] = "";
				$switcher = 1;
			}else{
				// Если ответ на регулярку
				$mind[$preg] = $line;
				$switcher = 0;
			}
		}
?>