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