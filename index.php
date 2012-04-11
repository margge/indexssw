<?php
session_start();
require 'Slim/Slim.php';
require 'views/HamlView.php';
require 'db/db_config.php';

$app = new Slim(array(
    'view' => 'HamlView'
));

$app->get('/', function() use ($app){
    if(isset($_SESSION['user_id'])){
        $app->render('index.haml');
    }  else {
        $app->redirect($app->request()->getRootUri()."/login");
    }
});

$app->get('/login', function() use ($app){
    if(isset($_SESSION['user_id'])){
        $app->redirect($app->request()->getRootUri());
    }  else {
        $app->render('login.haml');
    }
});

$app->get('/logout', function() use ($app){
    session_destroy();
    $app->redirect($app->request()->getRootUri());
});

$app->post('/login', function() use ($app){
    $email = $app->request()->post("email");
    if(!$email){
        $app->flash('error', 'User email is required');
        $app->redirect($app->request()->getRootUri());
    }
    $password = $app->request()->post("password");
    if(!$password){
        $app->flash('error', 'Password is required');
        $app->redirect($app->request()->getRootUri());
    }
    $user = ORM::for_table('users')->where('email', $email, 'password',
            sha1($password))->find_one();
    if(!$user){
        $app->flash('error', 'User does not exist or password is incorrect');
        $app->redirect($app->request()->getRootUri());
    }  else {
        if($user->last_time_seen + (1000 * 60 * 5 ) > time()){
            // user is still logged in
            $app->flash('error', 'You have another session started; sorry dude.');
            $app->redirect($app->request()->getRootUri()."/login");
        }else{
            $_SESSION['user_id'] = $user->email;
            $user->last_time_seen = time();
            $user->save();
            $app->redirect($app->request()->getRootUri());
        }
    }
});

$app->get('/register', function() use ($app){
    $app->render('register.haml');

});

$app->post('/register', function() use ($app){
    $email = $app->request()->post("email");
    if(!$email){
        $app->flash('error', 'User email is required');
        $app->redirect($app->request()->getRootUri()."/register");
    }
    $password = $app->request()->post("password");
    if(!$password){
        $app->flash('error', 'Password is required');
        $app->redirect($app->request()->getRootUri()."/register");
    }
    $confirm_password = $app->request()->post("password_confirm");
    if(!$confirm_password){
        $app->flash('error', 'Password confirmation is required');
        $app->redirect($app->request()->getRootUri()."/register");
    }
    if($password != $confirm_password){
        $app->flash('error', 'Passwords do not match');
        $app->redirect($app->request()->getRootUri()."/register");
    }
    $user = ORM::for_table('users')->where('email', $email)->find_one();
    if($user){
        $app->flash('error', 'User already exist, you dumbass');
        $app->redirect($app->request()->getRootUri()."/register");
    }
    
    $user = ORM::for_table('users')->create();
    $user->email = $email;
    $user->password = sha1($password);
    if($user->save()){
        $app->flash('info', 'User created, now you can login');
        $app->redirect($app->request()->getRootUri()."/login");
    }  else {
        $app->flash('error', 'Could not create user. Try again');
        $app->redirect($app->request()->getRootUri()."/register");
    }
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
