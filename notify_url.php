<?php
/*
 * 打赏异步回调
 */
include '../../../config.inc.php';
require_once 'libs/payjs.php';
date_default_timezone_set('Asia/Shanghai');

$db = Typecho_Db::get();
$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('TleDaShang');
switch($option->tledashangpaytype){
	case "payjs":
		$data = $_POST;
		if($data['return_code'] == 1){
			$payjs = new Payjs("","",$option->tledashang_key,"");
			$sign_verify = $data['sign'];
			unset($data['sign']);
			if($payjs->sign($data) == $sign_verify&&$data['total_fee']==$data['attach']*100){
				$update = $db->update('table.tledashang_item')->rows(array('dashangstatus'=>'y'))->where('dashangnumber=?',$data['out_trade_no']);
				$updateRows= $db->query($update);
				echo 'success';
			}
		}
		break;
}
?>