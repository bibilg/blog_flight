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
        
        $session=null;
        if(isset($_SESSION['pseudo']) && isset($_SESSION['id']))
        {
            $session['pseudo'] = $_SESSION['pseudo'];
            $session['id'] = $_SESSION['id'];
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
            'session' => $session
            )
        );

    }
);

Flight::route(
    '/connexion', function() {

        $get_pseudo=null;
        if(isset($_GET['pseudo']))
        {
            $get_pseudo=$_GET['pseudo'];
        }

        $get_mdp=null;
        if(isset($_GET['mdp']))
        {
            $get_mdp=$_GET['mdp'];
        }
        
        Flight::render('connexion.twig', array(
            'get_pseudo' => $get_pseudo,
            'get_mdp' => $get_mdp
        ));
    }
);

Flight::route(
    '/logout', function() {

        $_SESSION = array(); // Deleted session's variables
        session_destroy();

        Flight::redirect('/?logout=true');
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
            Flight::redirect('/connexion');
        }

    }
);

Flight::route(
    '/registration', function(){

        $get_pseudo=null;
        if(isset($_GET['pseudo']))
        {
            $get_pseudo=$_GET['pseudo'];
        }

        $get_mdp=null;
        if(isset($_GET['mdp']))
        {
            $get_mdp=$_GET['mdp'];
        }

        $get_email=null;
        if(isset($_GET['email']))
        {
            $get_email=$_GET['email'];
        }

        Flight::render('registration.twig', array(
            'get_pseudo' => $get_pseudo,
            'get_mdp' => $get_mdp,
            'get_email' => $get_email
        ));

    }
);

Flight::route(
    '/registrationConfirmation', function(){

        if( isset($_POST['pseudo'])  &&  isset($_POST['email']) && isset($_POST['mdp']) && isset($_POST['confirmation_mdp']) )
        { 
            registration($_POST['pseudo'],$_POST['email'],$_POST['mdp'],$_POST['confirmation_mdp']);              
        }
        else
        {
            //TODO:  Error handling
        }
    }
);

Flight::route(
    '/post/@postId', function($postId){

        $session=null;
        if(isset($_SESSION['pseudo']) && isset($_SESSION['id']))
        {
            $session['pseudo'] = $_SESSION['pseudo'];
            $session['id'] = $_SESSION['id'];
        }

        if(Post::exists($postId))
        {
            $post = Post::findOneWithFrDate($postId);

            $comments = Comment::getCommentsWithUserPseudo($postId);

            Flight::render('post.twig', array(
                'post' => $post,
                'comments' => $comments,
                'session' => $session
            ));
        }
        else
        {
            Flight::redirect('/');
        }


    }
);

Flight::route(
    '/post/addComment/@postId', function($postId){

        if(isset($_POST['userid']) && isset($_POST['message']))
        {
            if(!empty($_POST['userid']) && !empty($_POST['message'])) // If datas are not empty
            {
                Comment::addComment($_POST['message'],$_POST['userid'],$postId); // Function which add comments on db
                Flight::redirect('/post/' . $postId);
            }
            else 
            {
                throw new Exception('Les champs ne doivent pas être vides'); // TODO : handling this, why ??
            }
        }
        else
        {
            throw new Exception('Problème lors de l\'ajout de commentaire'); // TODO : handling this, why ??
        }

    }
);

Flight::route(
    '/comment/@commentId', function($commentId) {

        $session=null;
        if(isset($_SESSION['pseudo']) && isset($_SESSION['id']))
        {
            $session['pseudo'] = $_SESSION['pseudo'];
            $session['id'] = $_SESSION['id'];
        }

        if(Comment::exists($commentId))
        {
            $comment = Comment::find_one($commentId);

            $user = User::find_one($comment->user_id);

            Flight::render('comment.twig', array(
                'session' => $session,
                'comment' => $comment,
                'user' => $user
            ));
        }
        else
        {
            Flight::redirect('/');
        }

    }
    
);


Flight::route(
    '/comment/editComment/@commentId', function($commentId){

        if(isset($_POST['newContent'])) 
        {
            if(!empty($_POST['newContent'])) 
            {
                if(Comment::editContent($commentId, $_POST['newContent']))
                {
                    $postId = Comment::find_one($commentId)->post_id;
                    Flight::redirect('/post/' . $postId);
                }
            }
            else
            {
                throw new Exception('Erreur : aucun contenu dans le nouveau message'); // If the comment is empty
            }
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