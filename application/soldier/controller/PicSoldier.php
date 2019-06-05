<?php


namespace app\soldier\controller;

use app\common\controller\Log;
use app\soldier\model\PicSoldier as PicSoldierModel;
use app\soldier\validate\PicSoldier as PicSoldierValidate;
use think\Db;

class PicSoldier extends Base
{
    /**
     * 初始化模型、验证器
     */
    public function __construct()
    {
        parent::__construct();
        $this->currentModel = new PicSoldierModel();
        $this->currentValidate = new PicSoldierValidate();
    }

    /**
     * 获取军人信息
     * @param $soldier_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function getData($soldier_id)
    {
        $data = $this->currentModel->where('id', $soldier_id)->field('id,type,username,join_time,rank')->find();
        if ($data) $data = $data->toArray();
        //头像
        $data['headimgurl'] = Db::name('user')->where('user_id', $this->user_id)->value('headimgurl');
        //兵种
        $type_item = ['1'=>'中国人民解放军陆军', '2'=>'中国人民解放军空军', '3'=>'中国人民解放军海军', '4'=>'中国人民武装警察部队', '5'=>'中国人民解放军特种部队'];
        $data['type_text'] = $type_item[$data['type']] ?? '';
        //军官
        $rank_item = ['1'=>'义务兵', '2'=>'士官', '3'=>'军官'];
        $data['rank_text'] = $rank_item[$data['rank']] ?? '';
        $data['join_date'] = date('Y', strtotime($data['join_time']));

        //点赞列表
        $list = Db::name('pic_soldier_like')->where('soldier_id', $data['id'])->field('user_id,remark,create_time')->select();;
        $user_list = Db::name('user')->column('headimgurl,nickname', 'user_id');
        foreach ($list as $k=>$v) {
            $list[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            $list[$k]['headimgurl'] = $user_list[$v['user_id']]['headimgurl'];
            $list[$k]['nickname'] = $user_list[$v['user_id']]['nickname'];
        }

        $this->result['data']['own'] = $data;
        $this->result['data']['list'] = $list;
        return $this->result;
    }

    /**
     * 保存
     */
    public function save()
    {
        //验证数据
        $this->currentValidate->checkData('save');

        try {
            if (!empty($this->data['img_url'])) {
                $this->data['img_url'] = json_encode($this->data['img_url']);
            }
            $this->data['user_id'] = $this->user_id;

            //如果该用户已生成，则更新
            $id = Db::name('pic_soldier')->where('user_id', $this->user_id)->value('id');
            if($id) $this->data['id'] = $id;

            //保存数据
            $this->currentModel->save($this->data);
        } catch (\Exception $e) {
            $msg = !empty($this->currentModel->getError()) ? $this->currentModel->getError() : $e->getMessage();
            (new Log())->saveErrorLog($msg . ' [' . $e->getFile() . ':' . $e->getLine() . ']');
            $this->result['code'] = 0;
            $this->result['msg'] = $msg;
            return $this->result;
        }
        $pk = $this->currentModel->getPk();
        $this->result['data']['soldier_id'] = $this->currentModel->getData($pk);
        return $this->result;
    }

    /**
     * 保存点赞信息
     * @param $soldier_id
     * @return array
     */
    public function saveLike($soldier_id)
    {
        $count = Db::name('pic_soldier_like')->where('soldier_id', $soldier_id)->where('user_id', $this->user_id)->count();

        if ($count == 0) {
            $save_data = [];
            $save_data['soldier_id'] = $soldier_id;
            $save_data['user_id'] = $this->user_id;
            $save_data['remark'] = $this->data['remark'] ?? '';
            $save_data['create_time'] = time();
            Db::name('pic_soldier_like')->insert($save_data);
        } else {
            $this->result['code'] = 0;
            $this->result['msg'] = '点赞信息已存在，请勿重复操作！';
        }
        return $this->result;
    }

    /**
     * 获取参与人数
     */
    public function getJoinNum()
    {
        $base_num = 5000;
        $count = $this->currentModel->count();
        $this->result['data']['number'] = $base_num + $count;
        return $this->result;
    }

    /**
     * 获取排行榜
     * @return array
     * @throws \think\exception\DbException
     */
    public function getRanking()
    {
        //获取发起人填写的信息
        $soldier_list = Db::name('pic_soldier')->column('username,type,user_id', 'id');
        $user_list = Db::name('user')->column('headimgurl', 'user_id');

        //获取点赞数量排行
        $soldier_like = Db::name('pic_soldier_like')
            ->field('soldier_id,count(*) as number')
            ->group('soldier_id')
            ->order('number desc')
            ->limit(30)
            ->select();

        //兵种
        $type_item = ['1'=>'陆军', '2'=>'空军', '3'=>'海军', '4'=>'武警', '5'=>'特种兵'];
        foreach ($soldier_like as $k=>$v) {
            //头像
            $soldier_like[$k]['headimgurl'] = $user_list[$soldier_list[$v['soldier_id']]['user_id']] ?? '';
            //昵称
            $soldier_like[$k]['username'] = $soldier_list[$v['soldier_id']]['username'] ?? '';
            //兵种
            $soldier_like[$k]['type'] = $type_item[$soldier_list[$v['soldier_id']]['type']] ?? '';
        }

        $this->result['data'] = $soldier_like;
        return $this->result;
    }



}