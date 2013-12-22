<?php
class IndexAction extends Action {

    //授权跳转页
    public function index(){
        include_once('./saetv2.ex.class.php');
        $o = new SaeTOAuthV2(C('WB_AKEY'), C('WB_SKEY'));
        $code_url = $o -> getAuthorizeURL(C('WB_CALLBACK_URL'));
        redirect($code_url);
    }

    //授权回调页
    public function shows(){
        include_once('./saetv2.ex.class.php');
        $o = new SaeTOAuthV2(C('WB_AKEY'), C('WB_SKEY'));
        if (isset($_REQUEST['code'])) {
            $keys = array();
            $keys['code'] = $_REQUEST['code'];
            $keys['redirect_uri'] = C('WB_CALLBACK_URL');
            try {
                $token = $o->getAccessToken( 'code', $keys ) ;
            } catch (OAuthException $e) {

            }
        }
        if ($token) {
            $_SESSION['token'] = $token;
            setcookie('weibojs_'.$o -> client_id, http_build_query($token));
            redirect(PHP_FILE . '/index/info' );
        }else{
            die('授权失败');
        }
    }

    //活动首页
    public function info(){
        include_once('./saetv2.ex.class.php');
        $c = new SaeTClientV2(C('WB_AKEY'), C('WB_SKEY'), $_SESSION['token']['access_token']);
        $uid_get = $c -> get_uid();
        $uid = $uid_get['uid'];
        $User = M('User');
        if($a = $User -> find($uid)){
            redirect(PHP_FILE . '/index/result');
        }
        $this -> display();
    }

    //记录新加入人员信息
    public function recordnew(){
        $User = M('User');
        $data = array();
        $data['uid'] = $_POST['uid'];
        $data['join_time'] = time();
        $data['type'] = $_POST['type'];
        $data['type_name'] = $_POST['type_name'];
        $data['idstr'] = $_POST['idstr'];
        if($User -> add($data)){
            $this -> success('success');
        }else{
            $this -> error('error');
        }
    }

    //活动个人主页
    public function result(){
        include_once('./saetv2.ex.class.php');
        $c = new SaeTClientV2(C('WB_AKEY'), C('WB_SKEY'), $_SESSION['token']['access_token']);
        $uid_get = $c->get_uid();
        dump($uid_get);
        $uid = $uid_get['uid'];

        //用户信息
        $user_info = $c -> show_user_by_id($uid);
        $this -> assign('user_info', $user_info);

        //活动信息
        $User = M('User');
        $site_info = $User -> field('type_name,score,idstr') -> find($uid);
        $this -> assign('site_info', $site_info);

        //All Friends
        $friends_list = R('Type/getfriends', array($uid), 'Widget');


        /* --------------------- 友情指数排行榜 Start ---------------------- */

        $where_index = array();
        //$where_index['uid'] = array('in', $friends_list['ids']); //只显示自己的好友

        //最低三位
        $index_asc = $User -> field('uid,score') -> limit(3) -> where($where_index) -> order('score ASC') -> select();
        $index_asc = array_reverse($index_asc);
        //最高三位
        $index_desc = $User -> field('uid,score') -> limit(3) -> where($where_index) -> order('score DESC') -> select();

        //获取头像及昵称
        foreach($index_asc as $key => $value){
            $temp_user_info = $c -> show_user_by_id($value['uid']);
            $index_asc[$key]['screen_name'] = $temp_user_info['screen_name'];
            $index_asc[$key]['avatar_large'] = $temp_user_info['avatar_large'];
            usleep(200000);
        }

        foreach($index_desc as $key => $value){
            $temp_user_info = $c -> show_user_by_id($value['uid']);
            $index_desc[$key]['screen_name'] = $temp_user_info['screen_name'];
            $index_desc[$key]['avatar_large'] = $temp_user_info['avatar_large'];
            usleep(200000);
        }

        $this -> assign('index_asc', $index_asc);
        $this -> assign('index_desc', $index_desc);


        /* --------------------- 好友参与 Start ---------------------- */

        $where_join = array();
        $where_join['uid'] = array('in', $friends_list['ids']); //只显示自己的好友

        $friend_join = $User -> field('uid,score,type_name') -> where($where_join) -> limit(10) -> order('join_time DESC') -> select();

        //获取头像及昵称
        foreach($friend_join as $key => $value){
            $temp_user_info = $c -> show_user_by_id($value['uid']);
            $friend_join[$key]['screen_name'] = $temp_user_info['screen_name'];
            $friend_join[$key]['avatar_large'] = $temp_user_info['avatar_large'];
            usleep(200000);
        }

        $this -> assign('friend_join', $friend_join);

        /* --------------------- 好友参与 End ---------------------- */


        /* --------------------- 最新评论 Start ---------------------- */

        $commit = $c -> get_comments_by_sid($site_info['idstr'], 1, 3);
        $this -> assign('commit', $commit['comments']);

        /* --------------------- 最新评论 End ---------------------- */

        $this -> display();

    }

    //更多好友页
    public function morefriends(){
        include_once('./saetv2.ex.class.php');
        $c = new SaeTClientV2(C('WB_AKEY'), C('WB_SKEY'), $_SESSION['token']['access_token']);
        $uid_get = $c->get_uid();
        $uid = $uid_get['uid'];

        //用户信息
        $user_info = $c -> show_user_by_id($uid);
        $this -> assign('user_info', $user_info);

        //All Friends
        $friends_list = R('Type/getfriends', array($uid), 'Widget');
        $User = M('User');
        $where = array();
        $where['uid'] = array('in', $friends_list['ids']);
        $result = $User -> field('uid,score,idstr,type_name') -> where($where) -> order('join_time DESC') -> select();

        //获取头像及昵称
        foreach($result as $key => $value){
            $temp_user_info = $c -> show_user_by_id($value['uid']);
            $result[$key]['screen_name'] = $temp_user_info['screen_name'];
            $result[$key]['avatar_large'] = $temp_user_info['avatar_large'];
            usleep(200000);
        }

        //获取微博信息
        foreach($result as $key => $value){
            $temp_weibo_info = $c -> show_status($value['idstr']);
            $result[$key]['text'] = $temp_weibo_info['text'];
            usleep(200000);
        }

        $this -> assign('result', $result);

        $this -> display();
    }

}