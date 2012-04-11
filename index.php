<?php
session_start();
require 'Slim/Slim.php';
require 'views/HamlView.php';
require 'db/db_config.php';
define("MAX_SESSION_DURATION", 60 * 5);// five minutes

$app = new Slim(array(
    'view' => 'HamlView'
));

$app->hook('verify_session', 'verify_session');
function verify_session(){
    $app = Slim::getInstance();
    if(isset($_SESSION['user_id'])){
        $user = ORM::for_table('users')->where('email', $_SESSION['user_id'])->find_one();
        if($user->last_time_seen + MAX_SESSION_DURATION < time()){
            $user->last_time_seen = 0;
            $user->save();
            session_destroy();
            $app->redirect($app->request()->getRootUri()."/");
        }
    }
}

$app->hook('update_session', 'update_session');
function update_session(){
    if(isset($_SESSION['user_id'])){
        $user = ORM::for_table('users')->where('email', $_SESSION['user_id'])->find_one();
        $user->last_time_seen = time();
        $user->save();
    }
}


if($app->request()->getResourceUri() != "/login" || $app->request()->getResourceUri() != "/logout"){
    $app->applyHook('verify_session');
    $app->applyHook('update_session');
}

$app->get('/', 'root');
function root(){
    $app = Slim::getInstance();
    if(isset($_SESSION['user_id'])){
        $app->render('index.haml');
    }  else {
        $app->redirect($app->request()->getRootUri()."/login");
    }
}

$app->get('/login', 'login');
function login(){
    $app = Slim::getInstance();
    if(isset($_SESSION['user_id'])){
        $app->redirect($app->request()->getRootUri()."/");
    } else {
        $app->render('login.haml');
    }
}

$app->get('/logout', 'logout');
function logout(){
    $app = Slim::getInstance();
    if(isset($_SESSION['user_id'])){
        $user = ORM::for_table('users')->where('email', $_SESSION['user_id'])->find_one();
        $user->last_time_seen = 0;
        $user->save();
    }
    session_destroy();
    $app->redirect($app->request()->getRootUri()."/");
}

$app->get('/my_history', 'my_history');
function my_history(){
    $app = Slim::getInstance();
    $history = ORM::for_table('history')->where('user_id', $_SESSION['user_id'])->find_many();
    $app->render('history.haml', array('history' => $history));
}

$app->get('/history', 'history');
function history(){
    $app = Slim::getInstance();
    $history = ORM::for_table('history')->where_not_equal('user_id', $_SESSION['user_id'])->find_many();
    $app->render('history.haml', array('history' => $history, 'all' => true));
}

$app->post('/login', 'login_post');
function login_post(){
    $app = Slim::getInstance();
    $email = $app->request()->post("email");
    if(!$email){
        $app->flash('error', 'User email is required');
        $app->redirect($app->request()->getRootUri()."/login");
    }
    $password = $app->request()->post("password");
    if(!$password){
        $app->flash('error', 'Password is required');
        $app->redirect($app->request()->getRootUri()."/login");
    }
    $user = ORM::for_table('users')->where('email', $email, 'password',
            sha1($password))->find_one();
    if(!$user){
        $app->flash('error', 'User does not exist or password is incorrect');
        $app->redirect($app->request()->getRootUri()."/login");
    }  else {
        if($user->last_time_seen + MAX_SESSION_DURATION > time()){
            // user is still logged in
            $app->flash('error', 'You have another session started; sorry dude.');
            $app->redirect($app->request()->getRootUri()."/login");
        }else{
            $_SESSION['user_id'] = $user->email;
            $user->last_time_seen = time();
            $user->save();
            $app->redirect($app->request()->getRootUri()."/");
        }
    }
}

$app->get('/register', 'register');
function register(){
    $app = Slim::getInstance();
    $app->render('register.haml');

}

$app->post('/register', 'register_post');
function register_post(){
    $app = Slim::getInstance();
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
}

$app->post('/calculate', 'calculate');
function calculate(){
    $app = Slim::getInstance();
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

    $result = $calculator->calc();
    echo $result;
    
    // register in the history...
    // let's first see if there is room for a history entry
    $history_size = ORM::for_table('history')->where('user_id',
            $_SESSION['user_id'])->count();
    while($history_size >= 5){
        // remove latest history
        $latest_record = ORM::for_table('history')->where('user_id',
            $_SESSION['user_id'])->order_by_asc('created_at')->find_one();
        $latest_record->delete();
        $history_size = ORM::for_table('history')->where('user_id',
            $_SESSION['user_id'])->count();
    }
    // let's save this new record
    $record = ORM::for_table('history')->create();
    $record->user_id = $_SESSION['user_id'];
    $record->payload = serialize(array('method' => $formula,
        'params' => $individuals, 'result' => $result));
    $record->created_at = time();
    $record->save();
}

$app->run();
