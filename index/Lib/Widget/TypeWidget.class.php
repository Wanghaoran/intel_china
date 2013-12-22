<?php
class TypeWidget extends Action {
    public $type = array(
        1 => array(
            'title' => '高端大气商务范',
        ),

        2 => array(
            'title' => '时尚动感志青春',
        ),

        3 => array(
            'title' => '家庭娱乐亲分享',
        ),
    );

    public function getType($key){
        return $this -> type[$key];
    }

    public function getfriends($uid){
        include_once('./saetv2.ex.class.php');
        $c = new SaeTClientV2(C('WB_AKEY'), C('WB_SKEY'), $_SESSION['token']['access_token']);

        //获取好友列表
        $result = $c -> bilateral_ids($uid);

        dump($result);
    }
}