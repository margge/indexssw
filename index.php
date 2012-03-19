<?php
require 'Slim/Slim.php';
$app = new Slim();

//GET route
$app->get('/hello/:name', function ($name) use ($app) {
    $uri = $app->request()->getRootUri();
    echo "Hello, $name. <form method='post' action='$uri/person'><input type='submit' value='Create person'/></form>";
});

//POST route
$app->post('/person', function () {
    return "Creating new person... wait ackerman(4,3) for me to finish.<br/>";
    //$app = Slim::getInstance();
    //$app->response()->body('More body content');
});

//PUT route
$app->put('/person/:id', function ($id) {
    //Update Person identified by $id
});

//DELETE route
$app->delete('/person/:id', function ($id) {
    //Delete Person identified by $id
});

$app->run();
