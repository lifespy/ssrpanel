<?php
/**
 * Created by 傲慢与偏见.
 * User: tango
 * Date: 2017/11/27
 * Time: 11:14
 */

namespace App\Http\Controllers;

use App\Http\Models\ReferralLog;
use App\Http\Models\User;
use App\Http\Models\YftOrder;
use Config;
use Illuminate\Http\Request;
use Response;

class AnyPayController extends BaseController
{
    private static $config;

    /**
     * 默认构造方法.
     */
    public function __construct()
    {
        self::$config = $this->systemConfig();
    }

    /**
     * desc 订单提交页面
     * @param Request $request
     * @return string
     */
    public function subOrder(Request $request){

            $yftLib = new QuickPayFunction();
            $pay_config = new PayConfig();
            $pay_config->init();

            /**************************请求参数**************************/

            //订单名称
//            $subject = $request->get('subject');//必填
            $subject = "余额充值";//必填

            //付款金额
            $total_fee = $request->get('price');//必填 需为整数

            //服务器异步通知页面路径
            $notify_url = $request->server('REQUEST_SCHEME')."://".$request->server('HTTP_HOST') . $pay_config->pay_config["notify_url"];

            //需http://格式的完整路径，不能加?id=123这类自定义参数

            //页面跳转同步通知页面路径
            $return_url = $request->server('REQUEST_SCHEME')."://".$request->server('HTTP_HOST') . $pay_config->pay_config["return_url"];
            //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

            $secret = $pay_config->pay_config["secret"];

            $accesskey = $pay_config->pay_config["accesskey"];

            //生成订单号
            $ss_order_no = AnyPayController::generateYftOrderNum(8);

            /************************************************************/
            //构造要请求的参数数组，无需改动
            $parameter = [];
            if ($pay_config->pay_config["type"] == "aliPay") {
                $parameter = [
                    "total_fee" => $total_fee,
                    "notify_url" => $notify_url,
                    "return_url" => $return_url,
                    "secret" => $secret,
                    "out_trade_no" => $ss_order_no
                ];
            } else {
                $parameter = [
                    "secret" => $secret,
                    "notify_url" => $notify_url,
                    "accesskey" => $accesskey,
                    "return_url" => $return_url,
                    "subject" => $subject,
                    "total_fee" => $total_fee
                ];
            }

            //向数据库插入订单信息
            $user = $request->session()->get("sessionUser");
            $yft_order_info = new YftOrder();
            $yft_order_info->user_id = $user->id;
            $yft_order_info->ss_order = $ss_order_no;
            $yft_order_info->price = $total_fee;
            $yft_order_info->state = 0;
            $yft_order_info->save();

            //建立请求
            $html_text = $yftLib->buildRequestForm($parameter, $ss_order_no,$pay_config);
            return $html_text;
    }

    /**
     * desc 易付通异步通知调用方法
     * @param Request $request
     * @return string
     */
    public function yft_callback(Request $request)
    {
        //价格
        $total_fee = $request->get("total_fee");//必填
        //易付通返回的订单号
        $yft_order_no = $request->get("trade_no");
        //面板生成的订单号
        $ss_order_no = $request->get("out_trade_no");//必填
        //订单说明
        $subject = $request->get("subject");//必填
        //付款状态
        $trade_status = $request->get("trade_status");//必填
        //加密验证字符串
        $sign = $request->get("sign");//必填

        $verifyNotify = AnyPayController::md5Verify(floatval($total_fee), $ss_order_no, $yft_order_no, $trade_status, $sign);
        if ($verifyNotify) {//验证成功
            if ($trade_status == 'TRADE_SUCCESS') {
                /*
                加入您的入库及判断代码;
                >>>>>>>！！！为了保证数据传达到回调地址，会请求4次。所以必须要先判断订单状态，然后再插入到数据库，这样后面即使请求3次，也不会造成订单重复！！！！<<<<<<<
                判断返回金额与实金额是否想同;
                判断订单当前状态;
                完成以上才视为支付成功
                */
                $orderInfo = new YftOrder();
                $orderInfo = $orderInfo->where("ss_order", "=", $ss_order_no)->first();
                if ($orderInfo == "" || $orderInfo == null) {
                    return "订单不存在！";
                }

                if ($orderInfo->price != $total_fee) {
                    return "订单信息异常！";
                }

                $userInfo = new User();
                $userInfo = $userInfo->where("id", "=", $orderInfo->user_id)->first();

                if (sizeof($orderInfo) != 0 && $orderInfo->state == 0) {
                    $oldMoney = $userInfo->balance;
                    $userInfo->balance = $total_fee + $oldMoney;
                    //更新用户余额信息
                    $userInfo->save();
                    //更新订单信息
                    $orderInfo->yft_order = $yft_order_no;
                    $orderInfo->state = 1;
                    $orderInfo->save();
                    //充值返利处理 start
                    if ($userInfo->referral_uid != "" && $userInfo->referral_uid != 0 && $userInfo->ref_by != null && Config::get('code_payback') != 0 && Config::get('code_payback') != null) {
                        $gift_user = User::where("id", "=", $userInfo->referral_uid)->first();
                        $gift_user->balance = ($gift_user->balance + ($total_fee * self::$config["referral_percent"]));
                        $gift_user->save();

                        $payback = new ReferralLog();
                        $payback->amount = $total_fee;
                        $payback->user_id = $userInfo->id;
                        $payback->ref_user_id = $userInfo->referral_uid;
                        $payback->ref_amount = $total_fee * self::$config["referral_percent"];
                        $payback->state = 0;
                        $payback->save();
                    }
                    //充值返利处理 end
                } else {
                    return "订单号异常或交易已完成!";
                }
                header("location:" . $request->server('REQUEST_SCHEME')."://".$request->server('HTTP_HOST') . "/user");
                return "支付成功";
            } else {
                return "支付失败";
            }
        } else {
            //验证失败
            return "订单信息异常！请联系管理员";
        }
    }

    /**
     * desc 充值记录查看
     * @return null
     */
    public function rechargeList(Request $request){

        $view['rechargeList'] = YftOrder::query()->where('price','>=', 0)->orderBy('id', 'desc')->paginate(10)->appends($request->except('page'));

        return Response::view("user/rechargeList",$view);
    }

    /**
     * desc 验证返回结果
     * @param $p1
     * @param $p2
     * @param $p3
     * @param $p4
     * @param $sign 传入要比对的sign
     * @return boolean 返回比对结果
     */
    private static function md5Verify($p1, $p2, $p3, $p4, $sign)
    {
        $preStr = $p1 . $p2 . $p3 . $p4 . "yft";
        $mySign = md5($preStr);
        if ($mySign == $sign) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * desc 生成订单号
     * @param int $length
     * @return string
     */
    public static function generateYftOrderNum($length = 8)
    {
        //字符集，可任意添加你需要的字符
        $date = time();
        $date = "yft".date("YmdHis",$date);
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = "";
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $date.$password;
    }

}