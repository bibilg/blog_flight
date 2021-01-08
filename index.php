<?php

require "vendor/autoload.php";

// Load and configure twig
$loader = new \Twig\Loader\FilesystemLoader(dirname(__FILE__) . '/views');
$twigConfig = array(
    // 'cache' => './cache/twig/',
    // 'cache' => false,
    'debug' => true,
);

// Filter how connect a data base 
Flight::before('start', function(&$params, &$output){
    ORM::configure('sqlite:tweets.sqlite3');
});

// Allow Flight to use twig :
Flight::register('view', '\Twig\Environment', array($loader, $twigConfig), function ($twig) {
    $twig->addExtension(new \Twig\Extension\DebugExtension()); // Add the debug extension
    $twig->addGlobal('ma_valeur', "Hello There!"); // Can use 'ma_valeur' in all twig views

    $twig->addFilter(new \Twig\TwigFilter('trad', function($string){
        return $string;
    })); // Filter how 
});

//For call more simply the views
Flight::map('render', function($template, $data=array()){

    Flight::view()->display($template, $data);
    // After that, we can write : Flight::render('child_view.twig');
    
});

/* ----- Starting routing ------*/
Flight::route('/', function(){
    echo "Hello World!";
});

Flight::start();