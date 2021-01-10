<?php

session_start();

require "vendor/autoload.php";

function connexion($pseudo, $password)
{
    
    
    if(!empty($pseudo))
    {
        
        if(User::pseudoExist($pseudo))
        {
            $pass= User::getPassByPseudo($pseudo)->pass;

            $isPasswordCorrect = password_verify($password, $pass);

            if($isPasswordCorrect)
            {
                // MAYBE Fetch Id and assigned in $_SESSION['id'] ?
                $_SESSION['pseudo'] = $pseudo;

                Flight::redirect('/');

            }
            else
            {
                header('Location:index.php?action=connexion&mdp=false');
            }
        }
        else 
        {
            header('Location:index.php?action=connexion&pseudo=inexist');
        }
    }
    else
    {
        header('Location:index.php?action=connexion&pseudo=empty');
    }
}

function test()
{
    Flight::redirect('/');
}