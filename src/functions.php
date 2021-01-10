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
                $_SESSION['id'] = User::where('pseudo', $pseudo)->find_one()->id;

                Flight::redirect('/');
            }
            else
            {
                Flight::redirect('/connexion?mdp=false');
            }
        }
        else 
        {
            Flight::redirect('/connexion?pseudo=inexist');
        }
    }
    else
    {
        Flight::redirect('/connexion?pseudo=empty');
    }
}

function registration($pseudo,$email,$mdp,$mdpConfirmation)
{
    if(!empty($pseudo) )
    {
        if(User::isAvalaible($pseudo))
        {
            if(($mdp==$mdpConfirmation) && (!empty($mdp) ))
            {
                if(preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#",$email))
                {
                    $pass_hache = password_hash($mdp, PASSWORD_DEFAULT);

                    if(User::addUser($pseudo,$pass_hache,$email))
                    {
                        Flight::redirect('/?registration=new');
                    }
                    else
                    {
                        Flight::redirect('/registration');
                    }
               
                }
                else
                {
                    Flight::redirect('/registration?email=false');
                }
            }
            else 
            {
                Flight::redirect('/registration?mdp=false');
            }
        }
        else
        {
            Flight::redirect('/registration?pseudo=unavailable');
        }
    }
    else
    {
        Flight::redirect('/registration?pseudo=false');
    }
}