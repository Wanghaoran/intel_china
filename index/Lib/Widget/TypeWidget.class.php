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
}