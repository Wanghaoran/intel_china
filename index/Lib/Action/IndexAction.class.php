<?php
class IndexAction extends Action {
    public function index(){
        include_once('./saetv2.ex.class.php');
        $o = new SaeTOAuthV2(C('WB_AKEY'), C('WB_SKEY'));
        $code_url = $o -> getAuthorizeURL(C('WB_CALLBACK_URL'));
        redirect($code_url);
    }

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

    public function info(){
        $User = M('User');
        if($User -> find($_GET['viewer'])){
            redirect(PHP_FILE . '/index/result');
        }
        $this -> display();
    }

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

    public function result(){
        include_once('./saetv2.ex.class.php');
        $c = new SaeTClientV2(C('WB_AKEY'), C('WB_SKEY'), $_SESSION['token']['access_token']);
        $uid_get = $c->get_uid();
        dump($uid_get);
        $uid = $uid_get['uid'];
        //get Friend Ids
        $temp = $c-> bilateral_ids( $uid);
        dump($temp);
        $User = M('User');
        $result = $User -> find($uid);
        dump($result);

    }

}