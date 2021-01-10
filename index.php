<?php

/**
 * Controller of the blog
 * 
 * PHP version 7
 *
 * @category WhatIsIt
 * @package  WhatIsIt
 * @author   Bryan LEGRAS <bibilg@bibilg.fr>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://localhost/
 */

session_start();
require "vendor/autoload.php";

// Load and configure twig
$loader = new \Twig\Loader\FilesystemLoader(dirname(__FILE__) . '/views');
$twigConfig = array(
    // 'cache' => './cache/twig/',
    // 'cache' => false,
    'debug' => true,
);

// Filter how connect a data base 
Flight::before(
    'start', function (&$params, &$output) {
        ORM::configure('mysql:host=localhost;dbname=blog_flight');
        ORM::configure('username', 'root');
        ORM::configure('password', 'root');
    }
);

// Allow Flight to use twig :
Flight::register(
    'view', '\Twig\Environment', array($loader, $twigConfig), function ($twig) {
        $twig->addExtension(new \Twig\Extension\DebugExtension()); // Add the debug extension
        $twig->addGlobal('ma_valeur', "Hello There!"); // Can use 'ma_valeur' in all twig views

        $twig->addFilter(
            new \Twig\TwigFilter(
                'trad', function ($string) {
                    return $string;
                }
            )
        ); // Filter how 
    }
);

//For call more simply the views
Flight::map(
    'render', function ($template, $data=array()) {

        Flight::view()->display($template, $data);
        // After that, we can write : Flight::render('child_view.twig');
    
    }
);

/* ----- Starting routing ------*/

Flight::route(
    '/', function () {

        $posts = Post::getPosts();
        
        $session_pseudo=null;
        if(isset($_SESSION['pseudo']))
        {
            $session_pseudo = $_SESSION['pseudo'];
        }

        $message=null;
        if (isset($_GET['registration']) && ($_GET['registration']=='new') ) {
            $message= 'Inscription enregistrée, vous pouvez vous connecter';
        } 
        elseif (isset($_GET['logout']) && ($_GET['logout']=='true')) {
            $message= 'Déconnexion réussie';
        }

        Flight::render('index.twig', array(
            'posts' => $posts,
            'message' => $message,
            'session_pseudo' => $session_pseudo
            )
        );

    }
);

Flight::route(
    '/connexion', function() {

        Flight::render('connexion.twig');
    }
);

Flight::route(
    '/connexionConfirmation', function() {

        if( isset($_POST['pseudo']) && isset($_POST['mdp']) )
        {
            connexion($_POST['pseudo'],$_POST['mdp']);
        }
        else
        {
            Flight::redirect('/');
        }

    }
);

Flight::route(
    '/test', function() {
        $user = User::getPassByPseudo('bibi')->pass;

        var_dump($user);
    }
);

Flight::start();