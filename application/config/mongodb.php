<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Generally will be localhost if you're querying from the machine that Mongo is installed on
$config['mongo_host'] = "localhost";

// Generally will be 27017 unless you've configured Mongo otherwise
$config['mongo_port'] = 27017;

// The database you want to work from (required)
$config['mongo_db'] = "tenlayer";

// Leave blank if Mongo is not running in auth mode
$config['mongo_username'] = "";
$config['mongo_password'] = "";

// Persistant connections
$config['mongo_persist'] = TRUE;
<<<<<<< HEAD
$config['mongo_persist_key'] = 'ci_mongo_persist';
=======
$config['mongo_persist_key'] = 'ci_mongo_persist';
>>>>>>> 3ce843f72624626b321f5a0fec9332a8b7c161d4
