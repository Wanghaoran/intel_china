<?php
class IndexAction extends Action {
    public function index(){
        $this -> display();
    }

    public function info(){
        if(!empty($_GET['id'])){
            $info = R('Type/getType', array($_GET['id']), 'Widget');
            dump($info);
        }
    }

}