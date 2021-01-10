<?php

class User extends Model
{
    //public static $_table = 'user';

    public function comments(){
        return $this->has_many('Comment'); // Note we use the model name literally - not a pluralised version
    }

    public static function getPassByPseudo($pseudo)
    {
        return User::select('pass')->where('pseudo' , $pseudo)->find_one();
    }

    public static function pseudoExist($pseudoname){

        $pseudos = User::select('pseudo')->find_many();

        foreach($pseudos as $pseudo)
        {
            if($pseudo->pseudo == $pseudoname)
            {
                return true;
            }
        }

        return false;
    }

    public static function isAvalaible($pseudoname)
    {
        $pseudos = User::select('pseudo')->find_many();

        foreach($pseudos as $pseudo)
        {
            if($pseudo->pseudo == $pseudoname)
            {
                return false;
            }
        }

        return true;
    }

    public static function addUser($pseudo,$pass_hache,$email)
    {
        $user = User::create();
        $user->pseudo= $pseudo;
        $user->pass = $pass_hache;
        $user->email = $email;
        $user->set_expr('inscription_date', 'NOW()');
        $user->save();

        $user_added = User::where('pseudo' , $pseudo)->find_one();

        if(!empty($user_added->pseudo))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
}

class Post extends Model
{
    //public static $_table = 'post';

    public function comments(){
        return $this->has_many('Comment'); // Note we use the model name literally - not a pluralised version
    }

    public static function getPosts()
    {
        return Post::order_by_asc('id')->limit(5)->find_many();
    }
}

class Comment extends Model
{
    //public static $_table = 'comment';
    
    public function post() {
        return $this->has_one('Post');
    }

    public function user() {
        return $this->has_one('User');
    }
}