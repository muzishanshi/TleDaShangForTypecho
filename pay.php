<?php
include '../../../config.inc.php';
include_once "include/phpqrcode.php";
require_once 'libs/youzan/YZGetTokenClient.php';
require_once 'libs/youzan/YZTokenClient.php';

$db = Typecho_Db::get();
$prefix = $db->getPrefix();
$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('TleDaShang');
$plug_url = $options->pluginUrl;

$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';

$dashangqq = isset($_POST['dashangqq']) ? addslashes($_POST['dashangqq']) : '';
$dashangmoney = isset($_POST['dashangmoney']) ? addslashes($_POST['dashangmoney']) : '';
$dashangmsg = isset($_POST['dashangmsg']) ? addslashes($_POST['dashangmsg']) : '';
$dashanggid = isset($_GET['dashanggid']) ? addslashes($_GET['dashanggid']) : 0;
$time=time();
switch($option->tledashangpaytype){
	case "scan":
		if($action=="submitdashang"){
			$data = array(
				'dashangqq'   =>  $dashangqq,
				'dashanggid'=>$dashanggid,
				'dashangmoney'   =>  $dashangmoney,
				'dashangmsg'     =>  $dashangmsg,
				'dashangtime'=>date('Y-m-d H:i:s',$time)
			);
			$insert = $db->insert('table.tledashang_item')->rows($data);
			$insertId = $db->query($insert);
			
			$errorCorrectionLevel = "H"; // 纠错级别：L、M、Q、H  
			$matrixPointSize = "5"; // 点的大小：1到10  
			$qrcodeimg="qrcode.png";
			$logo = 'images/logo.png';
			
			QRcode::png($plug_url."/TleDaShang/return_url.php?dashangid=".$insertId."&dashangmoney=".$dashangmoney, $qrcodeimg, $errorCorrectionLevel, $matrixPointSize,5);
			
			if ($option->qrcodeLogoType=='pic') {
				$QR = imagecreatefromstring(file_get_contents($qrcodeimg));
				$logo = imagecreatefromstring(file_get_contents($logo));
				$QR_width = imagesx($QR);//二维码图片宽度
				$QR_height = imagesy($QR);//二维码图片高度
				$logo_width = imagesx($logo);//logo图片宽度
				$logo_height = imagesy($logo);//logo图片高度
				$logo_qr_width = $QR_width / 5;
				$scale = $logo_width/$logo_qr_width;
				$logo_qr_height = $logo_height/$scale;
				$from_width = ($QR_width - $logo_qr_width) / 2;
				//重新组合图片并调整大小
				imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,$logo_qr_height, $logo_width, $logo_height);
				imagepng($QR, $qrcodeimg);
			}else if($option->qrcodeLogoType=='text'){
				$font = dirname(__file__)."/css/simhei.ttf";//必须绝对路径
				$info = getimagesize($qrcodeimg);                          // 获取图片信息
				$type = image_type_to_extension($info[2],false);      // 获取图片扩展名
				$fun  = "imagecreatefrom{$type}";                     // 构建处理图片方法名-关键是这里
				$QR = $fun($qrcodeimg);                                   // 调用方法处理
				
				$colorArr=hex2rgb($option->qrcodeLogoTextColor);
				$color = imagecolorallocate($QR, $colorArr[0], $colorArr[1], $colorArr[2]);
				$string = $option->qrcodeLogoText;
				
				$fontSize  = $option->qrcodeLogoTextSize;
				$QR_width = imagesx($QR);//二维码图片宽度
				$QR_height = imagesy($QR);//二维码图片高度
				$from_height = $QR_height/2;
				
				$fontBox = imagettfbbox($fontSize, 0, $font, $string);//文字水平居中实质
				
				imagettftext($QR, $fontSize, 0, ceil(($QR_width - $fontBox[2]) / 2), $from_height, $color, $font, $string);
				imagepng($QR, $qrcodeimg);
			}
			
			$qrcode_base64_img = base64EncodeImage($qrcodeimg);
			$json=json_encode(array("type"=>"scan","qr_code"=>$qrcode_base64_img));
			echo $json;
			unlink($qrcodeimg);
			return;
		}
		break;
	case "youzan":
		$token = new YZGetTokenClient( $option->tledashangyz_client_id , $option->tledashangyz_client_secret );
		$type = $option->tledashangshoptype;
		$keys['kdt_id'] = $option->tledashangyz_shop_id;
		$keys['redirect_uri'] = $option->tledashangyz_redirect_url;
		$token=$token->get_token( $type , $keys );
		
		$data = array(
			'dashangqq'   =>  $dashangqq,
			'dashanggid'=>$dashanggid,
			'dashangmoney'   =>  $dashangmoney,
			'dashangmsg'     =>  $dashangmsg,
			'dashangtime'=>date('Y-m-d H:i:s',$time)
		);
		$insert = $db->insert('table.tledashang_item')->rows($data);
		$insertId = $db->query($insert);

		$client = new YZTokenClient($token['access_token']);
		$method = 'youzan.pay.qrcode.create';
		$api_version = '3.0.0';
		$my_params = [
			'qr_name' => '打赏|'.$insertId,
			'qr_price' => $dashangmoney*100,
			'qr_type' => $option->tledashangqrcodetype,
		];
		$my_files = [];
		$payqrcode=$client->post($method, $api_version, $my_params, $my_files);
		
		$json=json_encode(array("type"=>"youzan","qr_code"=>$payqrcode["response"]["qr_code"],"qr_url"=>$payqrcode["response"]["qr_url"]));
		echo $json;
		break;
}

function hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);
   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   return array($r, $g, $b);
}

function base64EncodeImage ($image_file) {
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}
/*
//QRcode::png说明
QRcode::png( 
	$text,——转换成二维码的文本，必填项 
	$outfile=false,——生成本地文件名。默认不保存到本地，直接输出 
	$level=QR_ECLEVEL_L,——质量。默认为最低质量，还有QR_ECLEVEL_M、QR_ECLEVEL_Q、QR_ECLEVEL_H参数值 
	$size=3,——大小 
	$margin=4,——外边距 
	$saveandprint=false,——是否保存。如选true，则$outfile项必须传值 
	$backcolor=0xFFFFFF,——背景色。默认为纯白色 
	$forecolor=0x000000——前景色。默认为纯黑色 
);
//修改二维码前景色背景色
//phpqrcode.php文件中，QRimage类中image方法修改其中两行即可。
$col[0] = ImageColorAllocate($base_image,255,255,255);
$col[1] = ImageColorAllocate($base_image,0,0,0);
*/
?>