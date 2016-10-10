<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;


use app\admin\Model\UserModel;
use app\admin\model\UserType;
use think\Controller;
use think\Db; 
class User extends Base
{
    //用户列表

	public function member()
	{    
		$group_id = input('group_id');
		$name = input('name');
		if(!empty($name)){
				$data = Db::table('user') ->where('name',$name) -> paginate(2);
				$merit = Db::table('user_group') -> select();
		}else if(!empty($group_id)){
				$merit = Db::table('user_group') ->where('group_id',$group_id) -> select();
				$data = Db::table('user') -> join('user_group','user_group.user_id=user.user_id','LEFT') -> where('user_group.group_id',$group_id)->paginate(10);
		}else{
			$data = Db::name('user') -> paginate(15);
			$merit = Db::table('user_group') -> select();
		}
		$info = Db::name('group')->select();

		$tempData = $data->all();

		foreach($info as $k => $v) {
			$groupData[$v['group_id']] = $v['group_name'];
		}

		foreach($merit as $k => $v) {
			$userGroupData[$v['user_id']] = $v['group_id'];
		}

		foreach($tempData as $k => $v) {
			$tempData[$k]['group_name'] = '';
			if (isset($userGroupData[$v['user_id']])) {
				$tempData[$k]['group_name'] = isset($groupData[$userGroupData[$v['user_id']]]) ? $groupData[$userGroupData[$v['user_id']]] : '';
			}
		}

		$data->setItems($tempData);
		unset($tempData);
		unset($groupData);
		$this -> assign('info',$info);
		$this -> assign('data',$data);
		$this -> assign('merit',$merit);
		return $this -> fetch();
	}
	
	public function delete(){
		if(!empty($_POST)){
			Db::startTrans();
			try{
				$rlt_1 = Db::table('user')->where("user_id",$_POST['id'])->delete();
			 	if($rlt_1 === false) {
			 		Db::rollback();
			 		return '删除失败';
			 	}
			 	$rlt_2 = Db::table('user_group') -> where("user_id",$_POST['id'])->delete();
			 	if ($rlt_2 === false) {
			 		Db::rollback();
			 		return '删除失败';
			 	}
			 	Db::commit();    
			 	return '删除成功';
			}catch (\Exception $e) {
			    // 回滚事务
			    Db::rollback();
			}
		}
	}
	public function deletes()
	{
		if(!empty($_POST)){
			Db::startTrans();
			try{
			 	$rlt_1 = Db::table('user')->where("user_id in(".$_POST['invalue'].")")->delete();
			 	if($rlt_1 === false) {
			 		Db::rollback();
			 		return '删除失败';
			 	}
			 	$rlt_2 = Db::table('user_group')->where("user_id in(".$_POST['invalue'].")")->delete();
			 	if ($rlt_2 === false) {
			 		Db::rollback();
			 		return '删除失败';
			 	}
			 	Db::commit();    
			 	return '删除成功';
			}catch (\Exception $e) {
			    // 回滚事务
			    Db::rollback();
			    return '删除失败';
			}
		}
	}
}