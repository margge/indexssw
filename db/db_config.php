<?php
require_once("idiorm.php");
ORM::configure('sqlite:./indexssw.sqlite');
$db = ORM::get_db();
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY,
    email TEXT,
    password TEXT,
    last_time_seen INTEGER);");
$db->exec("CREATE TABLE IF NOT EXISTS history (
    id INTEGER PRIMARY KEY,
    user_id INTEGER,
    payload TEXT,
    created_at INTEGER);");