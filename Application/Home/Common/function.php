<?php
function make_number(){
  $year_code = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','I','S','T','U','V','W','X','Y','Z');



                return $year_code[intval(date('Y'))-2014].
                strtoupper(dechex(date('m'))).date('d').
                substr(time(),-5).substr(microtime(),2,5).sprintf('%02d',rand(0,99));


            }

		/*------------判断此用户是否有订单未完成----------------*/
    function checkOrder($user_id){
        	$a=M('order');
        	$userorder_map['user_id']=$user_id;
        	$userorder_map['status']=array('in','0,1,3,5');
        	$order=$a->where($userorder_map)->select();//查找此用户最近的一个订单
        	if($order){
        		return true;//用户有订单，尚未完成维修
        	}else{
        		return false;//用户没有报修过或订单已完成
        	}
        }




		/*---------判断用户是否添加过电脑--------------*/
        function checkComputer($user_id){
            $a=M('computer');
            $computer=$a->where(array('user_id'=>$user_id))->find();
            if($computer){
                return true;
            }else{
                return false;
            }
        }

	function is_user($tel,$field='tel'){
		$a=M('user');
		$b=$a->where(array($field=>$tel))->find();
		if(empty($b['user_id'])){
			return false;
		}else{
			//return $b['user_id'];
			return $b;

		}
	}
function is_userExtend($tel,$field='phone'){
	$a=M('userextend');
	$b=$a->where(array($field=>$tel))->find();
	if(empty($b['user_id'])){
		return false;
	}else{
		//return $b['user_id'];
		return $b;

	}
}
	function is_login(){
		if(session('user_id')){
			return true;
		}else{
			return false;
		}
	}
	function sendmail($title,$content,$email){
		//todo
		return true;
	}
	function is_localhost(){

global $ip;
if (getenv("HTTP_CLIENT_IP"))
$ip = getenv("HTTP_CLIENT_IP");
else if(getenv("HTTP_X_FORWARDED_FOR"))
$ip = getenv("HTTP_X_FORWARDED_FOR");
else if(getenv("REMOTE_ADDR"))
$ip = getenv("REMOTE_ADDR");
else $ip = "Unknow";
if($ip=='127.0.0.1'){
return true;
}else{
	return false;
}

	}
	function is_admin_login(){
		if(session('user_id') && session('admin_id') && (session('type')==3)){
			return true;
		}else{
			return false;
		}
	}
	function is_staff_login(){
		if(session('user_id') && session('staff_id') && (session('type')==2)){
			return true;
		}else{

			return false;
		}
	}
		function is_user_login(){
		if(session('user_id') && ((session('type')==1)||(session('type')==0))){
			return true;
		}else{
			return false;
		}
	}
	function not_login($from_url=''){
		redirect(C('UC_LOGIN_URL'));
	}

function not_admin_login($from_url=''){
$not_login_admin_with_weixin_key_url=U('Home/Account/admin_login');
			redirect($not_login_admin_with_weixin_key_url);
		}
function is_tokenLogin($token){
	if(!$token){
		return false;
		exit;
	}
	$userinfo= json_decode(curl(C('UC_API').'/check?appid='.C('APP_ID').'&appkey='.C('APP_KEY').'&token='.$token),1);
	if($userinfo['code']!=0){
		return false;
		exit;
	}
	return $userinfo['uid'];

}
function init(){
	if (C('STOP_REPAIR')) {
		$this->error('您好,飞扬报修系统由于一些原因暂时关闭系统。系统重新开放后，我们将会在四川大学飞扬俱乐部官方微信/微博进行通知，尽请留意！', '', 8);
		exit;
	}

	if($_GET['access_token']){
		$userinfo=is_tokenLogin($_GET['access_token']);
		if($userinfo){
			$this->user_id=session('user_id');
			$this->ucid=session('ucid');
			$this->type=session('type');
			$this->tel=$userinfo['tel'];
		}else{
			login_expire();
		}
	}else if($_SESSION['access_token']){
		$self=__SELF__;
		if(substr($self,-1,1)=='/'){
			redirect(__SELF__.'access_token/'.$_SESSION['access_token']);
		}else{
			redirect(__SELF__.'/access_token/'.$_SESSION['access_token']);
		}
	} else {
		not_login();
	}
}
function tokenLogin($token){
	if(!$token){
		$this->error('没有token,请重新登录',C('UC_LOGIN_URL'));
		exit;
	}
	$userinfo= json_decode(curl(C('UC_API').'/getuserinfo?appid='.C('APP_ID').'&appkey='.C('APP_KEY').'&token='.$token),1);
	if($userinfo['code']!=0){
		$this->error('获取资料失败,请重新登录',C('UC_LOGIN_URL'));
		exit;
	}
	$a=is_user($userinfo['data'][0]['uid'],'ucid');
	$telUser=is_user($userinfo['data'][0]['tel']);
	if($a){
		$user_id=$a['user_id'];
		$type=$a['type'];
		session('user_id',$user_id);
		session('type',$type);
		session('ucid',$a['ucid']);
		session('access_token',$token);
		if($_SESSION['type']==3){
			$b=M('admin');
			$admin_map['user_id']=$user_id;
			$c=$b->where($admin_map)->find();
			session('admin_id',$c['admin_id']);
			redirect(U('/Home/Admin/index'));
		}elseif($_SESSION['type']==2){
			$b=M('staff');
			$staff_map['user_id']=$user_id;
			$c=$b->where($staff_map)->find();
			//dump($c);exit;
			session('staff_id',$c['staff_id']);
			redirect(U('/Home/Staff/not'));

		}else{
			//dump($_SESSION);exit;
			redirect(U('Home/Index/index'));
		}
	}else if($telUser){
		$user=M('user');
		$usermap['user_id']=$telUser['user_id'];
		$data['ucid']=$userinfo['data'][0]['uid'];
		$user->where($usermap)->save($data);
		$user_id=$telUser['user_id'];
		$type=$telUser['type'];
		session('user_id',$user_id);
		session('type',$type);
		session('ucid',$a['ucid']);
		session('access_token',$token);
		if($_SESSION['type']==3){
			$b=M('admin');
			$admin_map['user_id']=$user_id;
			$c=$b->where($admin_map)->find();
			session('admin_id',$c['admin_id']);
			redirect(U('/Home/Admin/index'));
		}elseif($_SESSION['type']==2){
			//echo '2';exit;
			$b=M('staff');
			$staff_map['user_id']=$user_id;
			$c=$b->where($staff_map)->find();
			//dump($c);exit;
			session('staff_id',$c['staff_id']);
			redirect(U('/Home/Staff/not'));

		}else{
			//dump($_SESSION);exit;
			redirect(U('Home/Index/index'));
		}
	}else{
       session('access_token',$token);
		session('tel',$userinfo['data'][0]['tel']);
		redirect(U('Home/Account/register'));
	}
}
	 function get_week_start(){
	 $date=date('Y-m-d');  //当前日期

$first=1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期

$w=date('w',strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6

return strtotime("$date -".($w ? $w - $first : 6).' days'); //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
	 }

function curl($url){
	$curl = curl_init();                                 //初始化curl对象
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, 0);               //0为头文件不可见
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);       //0(或false)
	$data_return = curl_exec($curl);

	return $data_return;
	curl_close($curl);
}
?>
