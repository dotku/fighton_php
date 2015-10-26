<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {

	public function pitchroom(){
		$model_pitchroom = D('pitchroom');
		$input['id'] = I('path.1', '', int);
		$info_pitchroom = $model_pitchroom->find($input['id']);
		$output['info_pitchroom'] = $info_pitchroom;

		// 如果房间已经创建
		if ($info_pitchroom) {
			// 判断是否要开启视频，只有 host 和 guest 需要判断，visitor 不需要判断
			if ($_SESSION['login']){
				if ($_SESSION['login']['id'] == $info_pitchroom['hostid']) { $this->is_host = true;}
				if ($_SESSION['login']['id'] == $info_pitchroom['guestid']) { $this->is_guest = true;}
				
				if (($_SESSION['login']['id'] == $info_pitchroom['guestid']) || ($_SESSION['login']['id'] == $info_pitchroom['hostid'])) {
					 $pitchroom_status = $info_pitchroom['status']; //0, 表示关闭; 1, 表示开启
					 $output['pitchroom_status'] = $pitchroom_status;
					 $this->is_owner = true;
				}
			}
			//var_dump($_SESSION['login']['id']);
			//var_dump($info_pitchroom['guestid']);
			$this->output = $output;
			$this->display('pitchroom_meeting');
		} else {
			$output = $model_pitchroom->order('timestamp desc')->limit(10)->select();
			$this->output = $output;
			$this->display();
		}
	}
	
	public function pitchroom_create(){
		$model_pitchroom = D('pitchroom');
		$model_user = D('user');
		if ($_POST){
			// 检查用户权限
			// var_dump($_SESSION);
			if ($_SESSION['login']) {
				$input = $_POST;
				$input['hostid'] = $_SESSION['login']['id'];
				$input['hash'] = md5(rand() + time());
				//var_dump($input['hash']);
				if($model_pitchroom->add($input)){
					//var_dump($input['hash']);
					$_SESSION['pitchroom'] = $model_pitchroom->where(array('hash'=>$input['hash']))->find();
					//var_dump($_SESSION['pitchroom']['id']);
					$this->redirect("./pitchroom/" . $_SESSION['pitchroom']['id']);
				} else {
					var_dump($model_pitchroom->find());
					return;
				};
			} else {
				$this->error('您无权限创建房间，请先登录', U('/signup'));
			}
		}
		//$this->redirect(U('/pitchroom'));
	}
	public function pitchroom_start(){
		$input['id'] = I('path.1', '', int);
		$info_pitchroom = D('pitchroom')->find($input['id']);
		if ($info_pitchroom['hostid'] == $_SESSION['login']['id']) {
			$model_pitchroom = D('pitchroom');
			$input_pitchroom['id'] = $input['id'];
			$input_pitchroom['status'] = 1;
			$model_pitchroom->save($input_pitchroom);
			$_SESSION['pitchroom'] = $model_pitchroom->find($input['id']);
			$this->redirect('/pitchroom/' . $_SESSION['pitchroom']['id']);
		} else {
			if ($_SESSION['login']['id'] != $info_pitchroom['hostid']) {
				var_dump($info_pitchroom);
				//$this->error('仅能控制开始自己的视频房间，请先创建', U('/pitchroom'));
			} else {
				$this->error('无权限开始视频，请登陆', U('/signup'));
			}
		}
	}
	public function pitchroom_update() {
		$model_pitchroom = D('pitchroom');
		$info_pitchroom = $model_pitchroom->find($_GET['id']);
		if ($info_pitchroom['hostid'] == $_SESSION['login']['id']
			|| $info_pitchroom['guestid'] == $_SESSION['login']['id']
		) {
			if ($model_pitchroom->save($_GET)){
				$return['msg'] = '更新完成';
				$_SESSION['pitchroom'] = $model_pitchroom->find($_GET['id']);
			} else {
				$return['msg'] = '更新失败';
			}
			$return['input'] = $_GET;
			$return['output'] = $model_pitchroom->find($_GET['id']);
			$return = json_encode($return);
			echo $return;
		} else {
			$return['msg'] = '无权限操作';
			$return['input'] = $_GET;
			$return = json_decode($return);
			echo $return;
		}
	}
	public function pitchroom_leave(){
		$model_pitchroom = D('pitchroom');
		if ($_SESSION['pitchroom']['hostid'] == $_SESSION['login']['id']) {
			$data_pitchroom['id'] = $_SESSION['pitchroom']['id'];
			$input['status'] = 0;
			$model_pitchroom->find($data_pitchroom)->save($input);
		}
		unset($_SESSION['pitchroom']);
		$this->redirect('/pitchroom');
	}
	
	public function pitchroom_invite(){
		$id = intval(I('path.1'));
		if ($id) {
			$model_pitchroom = D('pitchroom');
			$data['id'] = $_SESSION['pitchroom']['id'];
			$data['guestid'] = $id;
			$model_pitchroom->save($data);
			$_SESSION['pitchroom'] = $model_pitchroom->find($data['id']);
			var_dump($model_pitchroom->find($data['id']));
			$this->redirect('/pitchroom/' . $data['id']);
		} else {
			$this->display();
		}
	}
	public function user_profile() {
		if (!$_SESSION['login']) {
			$this->redirect('/user_signup');
		} else {
			$this->display();
		}
	}
	public function user_signout(){
		$model_pitchroom = D('pitchroom');
		$where['hostid'] = $_SESSION['login']['id'];
		$data['status'] = 0;
		$model_pitchroom->where($where)->save($data);
		session_destroy();
		//var_dump($_SESSION);
		$this->success("感谢使用，欢迎下次再回来:)", U('/'));
	}
	
	public function user_profile_update() {
		$model_user = D('user');
		if ($_POST) {
		   $model_user->save($_POST);
		   $info_user = $model_user->find($_POST['id']);
		   var_dump($info_user);
		   var_dump($_POST);
		}
		$this->redirect('/user_profile');
	}
	
	public function user_signup(){
		$model_user = D('user');
		// 如果有表格提交
		if (isset($_POST['username']) && isset($_POST['password'])) {
			$input['username'] = $_POST['username'];
			$input['password'] = md5($_POST['password']);
			
			// 不允许密码为空
			if (!$_POST['password']) {
				$this->error('请输入密码');
				return;
			} else {
				$input['password'] = md5($_POST['password']);
			}
			
			
			if ($model_user->where($input)->find()) {
				// 匹配成功，成功登录
				$_SESSION['login'] = $model_user->where($input)->find();
				unset($_SESSION['login']['password']);
			} else {
				// 找到用户，说明密码错误；提示信息不提及密码问题，防止黑客破解
				if ($model_user->where(array('username' =>$input['username']))->find()) {
					$this->error('密码或用户名错误');
					return;
				} else {
					// 没有找到用户，新建用户
					$model_user->add($input);
					$_SESSION['login'] = $model_user->where($input)->find();
					unset($_SESSION['login']['password']);
					$this->success('注册成功，以新用户身份登录', U('/'));
					return;
				}
			}
		}
		if (!$_SESSION['login']) {
			// 只有在未登录情况下才显示内容
			$this->display();
		} else {
			$this->success('回来啦?! 欢迎欢迎 :D', U('/pitchroom'));
		}
	}
}