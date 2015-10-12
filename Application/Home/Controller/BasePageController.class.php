<?php
	namespace Home\Controller;
	use Think\Controller;



	class BasePageController extends Controller
	{

		/*------初始化...------*/
		public function _initialize()
		{
			if (C('STOP_REPAIR')) {
				$this->error('您好,飞扬报修系统由于一些原因暂时关闭系统。系统重新开放后，我们将会在四川大学飞扬俱乐部官方微信/微博进行通知，尽请留意！', '', 8);
				exit;
			}

			    if($_GET['access_token']){

					if($_GET['access_token']!=$_SESSION['access_token']){
						session(null);
						session('access_token',$_GET['access_token']);
						redirect('/Home/AccountPage/ucLogin?access_token='.$_GET['access_token']);
						exit;
					}


					$uid=is_tokenLogin($_GET['access_token']);
					if(!$uid){
						$this->error('登录超时,请重新登录',C('UC_LOGIN_URL'));
					}
				}else if($_SESSION['access_token']){
					$self=__SELF__;

						redirect(__SELF__.'?access_token='.$_SESSION['access_token']);

				} else {
					not_login();
				}
		}
	}
?>
