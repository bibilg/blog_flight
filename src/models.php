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
        return Post::select('title')->select('content')->select('id')
        ->select_expr('DATE_FORMAT(creation_date, \'%d/%m/%Y à %Hh%imin%ss\')', 'creation_date')
        ->order_by_asc('id')->limit(5)->find_many();
    }

    public static function findOneWithFrDate($postId)
    {
        return Post::select('title')->select('content')->select('id')
        ->select_expr('DATE_FORMAT(creation_date, \'%d/%m/%Y à %Hh%imin%ss\')', 'creation_date')
        ->find_one($postId);
    }

    public static function exists($postId)
    {
        $ids = Post::select('id')->find_many();

        foreach($ids as $id)
        {
            if($id->id == $postId)
            {
                return true;
            }
        }

        return false;
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

    public static function exists($commentId)
    {
        $ids = Comment::select('id')->find_many();

        foreach($ids as $id)
        {
            if($id->id == $commentId)
            {
                return true;
            }
        }

        return false;
    }

    public static function getCommentsWithUserPseudo($postId)
    {
        return Comment::table_alias('c')
        ->select('c.comment' , 'comment')
        ->select_expr('DATE_FORMAT(comment_date, \'%d/%m/%Y à %Hh%imin%ss\')', 'comment_date')
        ->select('c.user_id' , 'user_id')
        ->select('u.pseudo' , 'pseudo')
        ->select('c.id' , 'comment_id')
        //->select_many(array('c.comment' => 'comment'), array('c.comment_date' => 'comment_date'), array('c.user_id' => 'user_id'), array('u.pseudo' => 'pseudo'))
        ->join('user', array('c.user_id', '=', 'u.id'), 'u')
        ->find_many();
    }

    public static function addComment($message,$userId,$postId)
    {
        $commentToAdd = Comment::create();
        $commentToAdd->comment= $message;
        $commentToAdd->user_id= $userId;
        $commentToAdd->post_id= $postId;
        $commentToAdd->set_expr('comment_date', 'NOW()');
        $commentToAdd->save();

        $comment_added_id = $commentToAdd->id(); // Fetch id of instance just added

        if(!empty($comment_added_id))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}