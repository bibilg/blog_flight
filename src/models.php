<?php

class User extends Model
{
    //public static $_table = 'user';

    public function comments(){
        return $this->has_many('Comment'); // Note we use the model name literally - not a pluralised version
    }
    
}

class Post extends Model
{
    //public static $_table = 'post';

    public function comments(){
        return $this->has_many('Comment'); // Note we use the model name literally - not a pluralised version
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