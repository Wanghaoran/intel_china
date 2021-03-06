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

    //获取全部好友 Uid
    public function getfriends($uid){
        include_once('./saetv2.ex.class.php');
        $c = new SaeTClientV2(C('WB_AKEY'), C('WB_SKEY'), $_SESSION['token']['access_token']);

        //获取好友列表
        $result = $c -> bilateral_ids($uid);

        //计算分页
        $page = ceil($result['total_number'] / 1000);

        if($page > 1 ){
            for($i = 2; $i <= $page; $i++){
                $tmp_result = array();
                $tmp_result = $c -> bilateral_ids($uid, $i);
                $result['ids'] = array_merge($result['ids'], $tmp_result['ids']);
                usleep(500000);
            }
        }

        return $result;
    }
}