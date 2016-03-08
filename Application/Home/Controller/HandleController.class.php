<?php
namespace Home\Controller;

use Think\Controller;

class    HandleController extends BaseApiController
{

    public function accept() {
        /*技术员接单*/
        if (IS_POST) {
            $a = M('order');


            $map_order['order_id'] = $_POST['order_id'];


            $updata_order['status'] = 3;
            $updata_order['staff_confirm_time'] = time();

            $order = $a->where($map_order)->save($updata_order);
            if ($order) {

                $data['status'] = 1;
                $this->ajaxReturn($data, 'JSON');
            } else {
                $data['status'] = 0;
                $this->ajaxReturn($data, 'JSON');
            }
        }
    }

    public function check_refuse() {
        if (IS_POST) {
            $staffM = M('staff');
            $staffMap['staff_id'] = $_POST['staff_id'];
            $staff = $staffM->where($staffMap)->find();
            if ($staff['month_refuse_max'] <= 0) {
                $data['status'] = 1002;
                $data['des'] = "您当月可拒绝次数已使用完,无法再拒绝单子。";
                $this->ajaxReturn($data, 'JSON');
                exit;
            }

            $a = M('order');

            $map_order['order_id'] = $_POST['order_id'];

            $orderMax = $a->where($map_order)->find();

            if ($orderMax['refuse_max'] <= 0) {
                $data['status'] = 1003;
                $data['des'] = "该订单不可再拒绝。";
                $this->ajaxReturn($data, 'JSON');
                exit;
            }
            $data['status'] = 0;
            $this->ajaxReturn($data, 'JSON');
        }
    }

    public function refuse() {
        /*技术员拒绝接单*/
        if (IS_POST) {
            $staffM = M('staff');
            $staffMap['staff_id'] = $_POST['staff_id'];
            $staff = $staffM->where($staffMap)->find();
            if ($staff['month_refuse_max'] <= 0) {
                $data['status'] = 1002;
                $data['des'] = "您当月可拒绝次数已使用完,无法再拒绝单子。";
                $this->ajaxReturn($data, 'JSON');
                exit;
            }

            $a = M('order');

            $map_order['order_id'] = $_POST['order_id'];

            $orderMax = $a->where($map_order)->find();

            if ($orderMax['refuse_max'] <= 0) {
                $data['status'] = 1003;
                $data['des'] = "该订单不可再拒绝。";
                $this->ajaxReturn($data, 'JSON');
                exit;
            }
            $updata_order['status'] = 5;
            $updata_order['staff_id'] = 0;
            $updata_order['refuse_max'] = $orderMax['refuse_max'] - 1;
            $order = $a->where($map_order)->save($updata_order);
            if ($order) {
                $map_staff['user_id'] = $_SESSION['user_id'];
                $updata_staff['refuse_order_id'] = $_POST['order_id'];
                $updata_staff['month_refuse_max'] = $staff['month_refuse_max'] - 1;
                $staff = $staffM->where($map_staff)->save($updata_staff);
                if ($staff) {
                    $refuseM = M('refuse');
                    $refuse_data['staff_id'] = $_POST['staff_id'];
                    $refuse_data['order_id'] = $_POST['order_id'];
                    $refuse_data['time'] = time();
                    $refuse_data['reason'] = $_POST['reason'];
                    $refuseM->add($refuse_data);
                    $data['status'] = 0;
                    $this->ajaxReturn($data, 'JSON');
                } else {
                    $data['status'] = 1001;
                    $data['des'] = "staff数据库更新失败";
                    $this->ajaxReturn($data, 'JSON');
                }
            } else {
                $data['status'] = 1001;
                $data['des'] = "order数据库更新失败";
                $this->ajaxReturn($data, 'JSON');
            }
        } else {
            redirect('/Home/Index/index');
        }
    }


    public function cancel() {
        /*用户取消订单*/
        $order = M('order');
        $map['order_id'] = $_POST['order_id'];
        $updata['status'] = 2;//用户取消订单,status=2
        $a = $order->where($map)->save($updata);
        if ($a) {
            $data['status'] = 1;
            $data['info'] = '取消订单成功!';
            $this->ajaxReturn($data, 'JSON');
        } else {
            $data['status'] = 0;
            $data['info'] = '取消订单失败!';
            $this->ajaxReturn($data, 'JSON');
        }

    }

    public function complete() {
        /*用户确认完成订单*/
        $order = M('order');
        $map['order_id'] = $_POST['order_id'];
        $updata['status'] = 4; //用户确认订单已完成,status=4
        $updata['user_confirm_time'] = time();
        $a = $order->where($map)->save($updata);
        if ($a) {
            $data['status'] = 1;
            $data['info'] = '确认订单成功!';
            $this->ajaxReturn($data, 'JSON');
        } else {
            $data['status'] = 0;
            $data['info'] = '确认订单失败!';
            $this->ajaxReturn($data, 'JSON');
        }
    }


    public function registerpc() {
        if (IS_POST) {
            $a = M('computer');
            $data['user_id'] = $_SESSION['user_id'];
            $data['brand'] = $_POST['brand'];
            $data['model'] = $_POST['model'];

            //电脑的购买时间必须大于 20050101 且小于当前日期。且输入的格式必须为 201401
            //$data['buy_time']= mktime(0, 0, 0, 0, 0, $_POST['buy_time']);
            if (strlen($_POST['buy_time']) < 6) $this->ajaxReturn(array('status' => -1), 'JSON');
            $data['buy_time'] = strtotime(substr($_POST['buy_time'] . '0111111', 0, 8));
            if ($data['buy_time'] < strtotime('20050101') || $data['buy_time'] > time()) {
                $this->ajaxReturn(array('status' => -2), 'JSON');
            }

            $data['time'] = time();//用户增加电脑时间
            $computer = $a->add($data);
            if ($computer) {
                $map['computer_id'] = $computer;
                $info = $a->where($map)->find();
                $data['brand'] = $info['brand'];
                $data['model'] = $info['model'];
                $data['buy_time'] = date('Y年m月', $info['buy_time']);
                $data['status'] = 1;
                $data['info'] = 'add computer info success';
                $data['computer_id'] = $computer;
                $this->ajaxReturn($data, 'JSON');
            } else {
                $data['status'] = 0;
                $data['info'] = 'add computer info error';
                $this->ajaxReturn($data, 'JSON');
            }

        }
    }


    /*-------------修改个人信息--------------*/
    public function set() {

        if (IS_POST) {
            $map['user_id'] = $_SESSION['user_id'];
            $a = M('userextend');


            $updata['name'] = $_POST['name'];
            $updata['phone'] = $_POST['phone'];

            $userextend = $a->where($map)->save($updata);

            //如果数据库中用户信息修改成功，则给ajax返回 status==1，如果失败则返回 status==0
            if ($userextend) {
                $data['status'] = 1;
                $data['info'] = '修改成功!';
                $this->ajaxReturn($data, 'JSON');
            } else {
                $data['status'] = 0;
                $data['info'] = '修改失败!';
                $this->ajaxReturn($data, 'JSON');
            }

        }
    }

    /*----------用户报修--------------*/
    public function handle() {

        if (IS_POST) {

            $a = M('order');
            $data_order['number'] = make_number();
            $data_order['user_id'] = $_SESSION['user_id'];
            $data_order['time'] = time();
            $data_order['vip'] = $_SESSION['type'];
            $data_order['computer_id'] = $_POST['computer_id'];
            $order = $a->add($data_order);

            if ($order) {
                $b = M('orderextend');
                $data_extend['order_id'] = $order;
                $data_extend['description'] = $_POST['description'];
                $orderextend = $b->add($data_extend);
                if ($orderextend) {
                    $data['status'] = 1;
                    $this->ajaxReturn($data, 'JSON');

                } else {
                    $data['status'] = 0;
                    $this->ajaxReturn($data, 'JSON');
                }

            }

        }
    }


}

?>