<?php
class IndexAction extends Action {
    public function index(){
        redirect('https://api.weibo.com/oauth2/authorize?client_id=1077506463&redirect_uri=http://www.cnhtk.cn/intel_china/index/shows');
    }

    public function shows(){
        $this -> display();
    }

    public function info(){
        if(!empty($_GET['id'])){
            $info = R('Type/getType', array($_GET['id']), 'Widget');
            dump($info);
        }
    }

}