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

        include_once('./saetv2.ex.class.php');
        $c = new SaeTClientV2(C('WB_AKEY'), C('WB_SKEY'), $_SESSION['token']['access_token']);
        $uid_get = $c->get_uid();
        $uid = $uid_get['uid'];

        //用户信息
        $user_info = $c -> show_user_by_id($uid);
        $this -> assign('user_info', $user_info);

        $User = M('User');
        $data = array();
        $data['uid'] = $_POST['uid'];
        $data['join_time'] = time();
        $data['type'] = $_POST['type'];
        $data['type_name'] = $_POST['type_name'];
        $data['idstr'] = $_POST['idstr'];
        $data['text'] = $_POST['text'];
        $data['screen_name'] = $user_info['screen_name'];
        $data['profile_image_url'] = $user_info['profile_image_url'];
        $data['avatar_large'] = $user_info['avatar_large'];
        $data['avatar_hd'] = $user_info['avatar_hd'];
        $data['profile_url'] = $user_info['profile_url'];
        $data['bi_followers_count'] = $user_info['bi_followers_count'];
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
        $uid = $uid_get['uid'];

        //用户信息
//        $user_info = $c -> show_user_by_id($uid);
//        $this -> assign('user_info', $user_info);

        //活动信息
        $User = M('User');
        $site_info = $User -> field('type_name,score,idstr,avatar_hd,screen_name,avatar_large') -> find($uid);
        $this -> assign('site_info', $site_info);

        //All Friends - API
        $friends_list = R('Type/getfriends', array($uid), 'Widget');


        /* --------------------- 友情指数排行榜 Start ---------------------- */

        $where_index = array();
        //$where_index['uid'] = array('in', $friends_list['ids']); //只显示自己的好友

        //最低三位
        $index_asc = $User -> field('uid,score,screen_name,avatar_large') -> limit(3) -> where($where_index) -> order('score ASC') -> select();
        $index_asc = array_reverse($index_asc);
        //最高三位
        $index_desc = $User -> field('uid,score,screen_name,avatar_large') -> limit(3) -> where($where_index) -> order('score DESC') -> select();


        $this -> assign('index_asc', $index_asc);
        $this -> assign('index_desc', $index_desc);


        /* --------------------- 好友参与 Start ---------------------- */

        $where_join = array();
        $where_join['uid'] = array('in', $friends_list['ids']); //只显示自己的好友

        $friend_join = $User -> field('uid,score,type_name,screen_name,avatar_large') -> where($where_join) -> limit(5) -> order('join_time DESC') -> select();

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
        $result = $User -> field('uid,score,idstr,type_name,screen_name,avatar_large,text') -> where($where) -> order('join_time DESC') -> select();


        $this -> assign('result', $result);

        $this -> display();
    }

    //提交微博评论
    public function commits(){
        include_once('./saetv2.ex.class.php');
        $c = new SaeTClientV2(C('WB_AKEY'), C('WB_SKEY'), $_SESSION['token']['access_token']);
        $result = $c -> send_comment($_POST['idstr'], $_POST['text']);
        if($result){
            $this -> success('Success');
        }else{
            $this -> error('Error');
        }
    }

    //表态
    public function toposition(){
        include_once('./saetv2.ex.class.php');
        $c = new SaeTClientV2(C('WB_AKEY'), C('WB_SKEY'), $_SESSION['token']['access_token']);
        $uid_get = $c->get_uid();
        $uid = $uid_get['uid'];

        $Position = M('Position');

        //一天只能点评一次
        $where_limit = array();
        $where_limit['addtime'] = array('ELT', time() - 86400);
        $where_limit['uid'] = $_POST['uid'];
        $where_limit['uid_by'] = $uid;
        if($Position -> where($where_limit) -> find()){
            $this -> error('一天只能给好友点评一次哦，试试为别人点评吧！');
        }

        //记录点评
        $data = array();
        $data['uid'] = $_POST['uid'];
        $data['uid_by'] = $uid;
        $data['type'] = $_POST['type'];
        $data['addtime'] = time();
        if($Position -> add($data)){
            //增加或减少积分
            $User = M('User');
            if($data['type'] == 1){
                $User -> where(array('uid' => $data['uid'])) -> setInc('score', 5);
            }else{
                $User -> where(array('uid' => $data['uid'])) -> setDec('score', 5);
            }
            $this -> success('点评成功，感谢参与！');
        }else{
            $this -> error('点评失败，数据通信失败！');
        }

    }

}