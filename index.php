<?php
require 'Slim/Slim.php';
require 'views/HamlView.php';

$app = new Slim(array(
    'view' => 'HamlView'
));

$app->get('/', function() use ($app){
    $app->render('index.haml');
});

$app->get('/temp', function() use ($app){
    require_once "templates/index.html";
});

$app->post('/calculate', function() use ($app){             
	// let's make sure we received the correct params
	if($app->request()->post("individuals") === NULL){
		die("No se enviaron individuos");
	}                                                
	
	if($app->request()->post("formula") === NULL){
		die("No se especific&oacute; la f&oacute;rmula");
	}
	
	// separate the individuals (a comma-separated string)
	$individuals = explode(",", $app->request()->post("individuals"));
	
	// get the formula calculator
	$formula = $app->request()->post("formula");
	if($formula == "simpson"){
		require 'calc/simpson.php';
		$calculator = new Simpson($individuals);
	} else if($formula == "shannon_wiener") {
		require 'calc/shannon_wiener.php';
		$calculator = new ShannonWiener($individuals);
	} else {
		die("Formula desconocida ($formula)");
	}
	
	echo $calculator->calc();
});

$app->run();
