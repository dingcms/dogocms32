<?php

/**
 * GoodsSortAction.class.php
 * 商品分类
 * @author 正侠客 <lookcms@gmail.com>
 * @copyright 2012- http://www.dingcms.com http://www.dogocms.com All rights reserved.
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @version dogocms 1.0 2012-11-5 11:23
 * @package  Controller
 * @todo 分类各项操作
 */
namespace Admin\Action;
use Think\Action;
class GoodsSortAction extends BaseAction {

    /**
     * index
     * 分类信息列表
     * @access public
     * @return array
     * @version dogocms 1.0
     */
    public function index()
    {
        $this->display();
    }

    /**
     * add
     * 分类添加
     * @access public
     * @return array
     * @version dogocms 1.0
     */
    public function add()
    {
        $status = array(
            '20' => ' 开启 ',
            '10' => ' 禁用 '
        );
        $this->assign('status', $status);
        $this->display();
    }

    /**
     * edit
     * 分类数据编辑
     * @access public
     * @return array
     * @version dogocms 1.0
     */
    public function edit()
    {
        $m = D('GoodsSort');
        $id = I('get.id');
        $condition['id'] = array('eq', $id);
        $data = $m->where($condition)->find();
        $status = array(
            '20' => ' 开启 ',
            '10' => ' 禁用 '
        );
        $this->assign('status', $status);
        $this->assign('v_status', $data['status']);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * insert
     * 分类插入数据
     * @access public
     * @return boolean
     * @version dogocms 1.0
     */
    public function insert()
    {
//添加功能还需要验证数据不能为空的字段
        $m = D('GoodsSort');
        $parent_id = I('post.parent_id');
        $text = I('post.text');
        if (empty($text)) {
            $this->dmsg('1', '分类名不能为空！', false, true);
        }
        if ($parent_id != 0) {
            $condition['id'] = array('eq', $parent_id);
            $data = $m->field('path')->where($condition)->find();
            $data_up['path'] = $data['path'] . $parent_id . ',';
        }
        $data_up['en_name'] = I('post.en_name');
        if (empty($data_up['en_name'])) {
            $pinyin = new \Org\Util\Pinyin();
            $data_up['en_name'] = $pinyin->output($text);
        }
        $data_up['updatetime'] = time();
        $data_up['text'] = $text;
        $data_up['model_id'] = I('post.model_id');
        $data_up['status'] = I('post.status')['0'];
        $data_up['parent_id'] = $parent_id;
        $data_up['keywords'] = I('post.keywords');
        $data_up['description'] = I('post.description');
        $data_up['myorder'] = I('post.myorder');
        if ($m->create($_POST)) {
            $rs = $m->add();
            if ($rs) {
                $this->dmsg('2', '操作成功！', true);
            } else {
                $this->dmsg('1', '操作失败！', false, true);
            }
        }//if
    }

    /**
     * update
     * 分类更新
     * @access public
     * @return boolean
     * @version dogocms 1.0
     */
    public function update()
    {
        $m = D('GoodsSort');
        $id = I('post.id');
        $parent_id = I('post.parent_id');
        $tbname = 'GoodsSort'; //可修改为相应的表名
        $text = I('post.text');
        if (empty($text)) {
            $this->dmsg('1', '分类名不能为空！', false, true);
        }
        if ($parent_id != 0) {//不为0时判断是否为子分类
            if ($id == $parent_id) {
                $this->dmsg('1', '不能选择自身分类为父级分类！', false, true);
            }
            $condition_sort['id'] = array('eq', $parent_id);
            $condition_sort['path'] = array('like', '%,' . $id . ',%');
            $cun = $m->field('id')->where($condition_sort)->find(); //判断id选择是否为其的子类
            if ($cun) {
                $this->dmsg('1', '不能选择当前分类的子类为父级分类！', false, true);
            }
            $condition_pid['id'] = array('eq', $parent_id);
            $data = $m->field('path')->where($condition_pid)->find();
            $sort_path = $data['path'] . $parent_id . ','; //取得不为0时的path
            $data_up['path'] = $data['path'] . $parent_id . ',';
            updatePath($id, $sort_path, $tbname);
        } else {//为0，path为,
            $condition_id['id'] = array('eq', $id);
            $data = $m->field('parent_id')->where($condition_id)->find();
            if ($data['parent_id'] != $parent_id) {//相同不改变
                $sort_path = ','; //取得不为0时的path
                updatePath($id, $sort_path, $tbname);
            }
            $data_up['path'] = ','; //应该是这个
        }
        $data_up['en_name'] = I('post.en_name');
        if (empty($data_up['en_name'])) {
            $pinyin = new \Org\Util\Pinyin();
            $data_up['en_name'] = $pinyin->output($text);
        }
        $data_up['updatetime'] = time();
        $data_up['text'] = $text;
        $data_up['model_id'] = I('post.model_id');
        $data_up['status'] = I('post.status')['0'];
        $data_up['parent_id'] = $parent_id;
        $data_up['keywords'] = I('post.keywords');
        $data_up['description'] = I('post.description');
        $data_up['myorder'] = I('post.myorder');
        
        $condition_sortid['id'] = array('eq', $id);
        $rs = $m->where($condition_sortid)->save($data_up);
        if ($rs == true) {
            $this->dmsg('2', '操作成功！', true);
        } else {
            $this->dmsg('1', '未有操作或操作失败！', false, true);
        }
    }

    /**
     * delete
     * 分类信息删除操作
     * @access public
     * @return boolean
     * @version dogocms 1.0
     */
    public function delete()
    {
        $m = D('GoodsSort');
        $id = I('post.id');
        if (empty($id)) {
            $this->dmsg('1', '未有id值，无法删除！', false, true);
        }
        $condition_path['path'] = array('like', '%,' . $id . ',%');
        $data = $m->field('id')->where($condition_path)->select();
        if (is_array($data)) {
            $this->dmsg('1', '该分类下还有子级分类，无法删除！', false, true);
        }
        $t = D('GoodsList');
        $condition_sort['sort_id'] = array('eq', $id);
        $t_data = $t->field('sort_id')->where($condition_sort)->find();
        if (is_array($t_data)) {
            $this->dmsg('1', '该分类下还有文档信息，无法删除！', false, true);
        }
        $condition_id['id'] = array('eq', $id);
        $del = $m->where($condition_id)->delete();
        if ($del == true) {
            $this->dmsg('2', '操作成功！', true);
        } else {
            $this->dmsg('1', '操作失败！', false, true);
        }//if
    }

    /**
     * json
     * 分类信息json数据
     * @access public
     * @return array
     * @version dogocms 1.0
     */
    public function json()
    {
        $m = D('GoodsSort');
        $list = $m->field('id,parent_id,text')->select();
        $navcatCount = $m->count("id");
        $a = array();
        $array = array();
        if ($list) {
            foreach ($list as $k => $v) {
                $a[$k] = $v;
                $a[$k]['_parentId'] = intval($v['parent_id']); //_parentId为easyui中标识父id
            }
        }
        $array['total'] = $navcatCount;
        $array['rows'] = $a;
        echo json_encode($array);
    }

    /**
     * jsonTree
     * 分类json树结构数据
     * @access public
     * @return array
     * @version dogocms 1.0
     */
    public function jsonTree()
    {
        $qiuyun = new \Org\Util\Qiuyun;
        $m = D('GoodsSort');
        $tree = $m->field('id,parent_id,text')->select();
        $tree = $qiuyun->list_to_tree($tree, 'id', 'parent_id', 'children');
        $tree = array_merge(array(array('id' => 0, 'text' => L('sort_root_name'))), $tree);
        echo json_encode($tree);
    }

    /**
     * jsonSortTree
     * 分类树信息json数据
     * @access public
     * @return array
     * @version dogocms 1.0
     */
    public function jsonSortTree()
    {
        $qiuyun = new \Org\Util\Qiuyun;
        $m = D('GoodsSort');
        $tree = $m->field('id,parent_id,text')->select();
        $tree = $qiuyun->list_to_tree($tree, 'id', 'parent_id', 'children');
        $tree = array_merge(array(array('id' => 0, 'text' => '全部文档')), $tree);
        echo json_encode($tree);
    }

}
