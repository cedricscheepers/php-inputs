<?php
	require 'inputs.php';
	
	$inputs = new Inputs($lang, array(
		'gender'		=>	'switch|ids,gender|on,Male|off,Female|checked,true|required,
		'country'		=>	'text|ids,country|placeholder,Country|focus',
		'birthdate'		=>	'date|ids,bday|required|value,1990-01-01|min,1920|max,2010',
		'about' 		=>	'textarea|ids,about|value,Test',
		'tel_c'			=>	'tel|ids,telnr_c|spellcheck,false|max_length,20|value,123456|required'
	));
				
	echo $inputs -> field('gender');
	echo $inputs -> field('country');
	echo $inputs -> field('birthdate');
	echo $inputs -> field('about');
	echo $inputs -> field('tel_c');
?>