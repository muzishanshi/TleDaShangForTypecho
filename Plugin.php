<?php
/**
 * TleDaShangForTypecho打赏插件，进入插件设置可检测版本更新。<a href="https://github.com/muzishanshi/TleDaShangForTypecho" target="_blank">Github地址</a>
 * @package TleDaShang For Typecho
 * @author 二呆
 * @version 1.0.1
 * @link http://www.tongleer.com/
 * @date 2018-10-26
 */
class TleDaShang_Plugin implements Typecho_Plugin_Interface{
    // 激活插件
    public static function activate(){
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		self::createTableDashangItem($db);
		self::funWriteThemePage($db,'page_tledashang.php');
		self::funWriteDataPage($db,'文章打赏记录','tledashang_item','page_tledashang.php','publish');
        return _t('插件已经激活，需先配置插件信息！');
    }

    // 禁用插件
    public static function deactivate(){
		//删除页面模板
		$db = Typecho_Db::get();
		$queryTheme= $db->select('value')->from('table.options')->where('name = ?', 'theme'); 
		$rowTheme = $db->fetchRow($queryTheme);
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/page_tledashang.php');
        return _t('插件已被禁用');
    }

    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form){
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$options = Typecho_Widget::widget('Widget_Options');
		$plug_url = $options->pluginUrl;
		//版本检查
		$version=file_get_contents('http://api.tongleer.com/interface/TleDaShang.php?action=update&version=1');
		$headDiv=new Typecho_Widget_Helper_Layout();
		$headDiv->html('版本检查：'.$version);
		$headDiv->render();
		//QQ、微信、支付宝链接设置
		$qqUrl = new Typecho_Widget_Helper_Form_Element_Text('qqUrl', array('value'), 'https://i.qianbao.qq.com/wallet/sqrcode.htm?m=tenpay&f=wallet&u=2293338477&a=1&n=Mr.%E8%B4%B0%E5%91%86&ac=26A9D4109C10A5D5C08964FCFD5634EAC852E009B700ECDA2A064092BCF6C016', _t('QQ支付二维码url'), _t('可使用<a href="https://cli.im/deqr/" target="_blank">草料二维码</a>将二维码图片转成url地址填入其中'));
        $form->addInput($qqUrl);
		$wechatUrl = new Typecho_Widget_Helper_Form_Element_Text('wechatUrl', array('value'), 'wxp://f2f0XXfQeK36aDieMEjmveUENW16IZMdDk_c', _t('微信支付二维码url'), _t('可使用<a href="https://cli.im/deqr/" target="_blank">草料二维码</a>将二维码图片转成url地址填入其中'));
        $form->addInput($wechatUrl);
		$aliUrl = new Typecho_Widget_Helper_Form_Element_Text('aliUrl', array('value'), 'HTTPS://QR.ALIPAY.COM/FKX03546YRHSVIW3YUK925', _t('支付宝支付二维码url'), _t('可使用<a href="https://cli.im/deqr/" target="_blank">草料二维码</a>将二维码图片转成url地址填入其中'));
        $form->addInput($aliUrl);
		//打赏二维码生成设置
		$qrcodeApi = new Typecho_Widget_Helper_Form_Element_Text('qrcodeApi', array('value'), 'http://api.tongleer.com/web/?action=qrcode&url=', _t('二维码接口'), _t(''));
        $form->addInput($qrcodeApi);
		$qrcodeLogoType = new Typecho_Widget_Helper_Form_Element_Radio('qrcodeLogoType', array(
            'pic'=>_t('图片'),
            'text'=>_t('文本'),
			'none'=>_t('无')
        ), 'pic', _t('二维码logo类型'), _t("若选择文本logo，则需配置以下文本内容、颜色、大小。"));
        $form->addInput($qrcodeLogoType->addRule('enum', _t(''), array('pic', 'text', 'none')));
		$qrcodeLogoText = new Typecho_Widget_Helper_Form_Element_Text('qrcodeLogoText', array('value'), '赏', _t('二维码logo文本'), _t(''));
        $form->addInput($qrcodeLogoText);
		$qrcodeLogoTextColor = new Typecho_Widget_Helper_Form_Element_Text('qrcodeLogoTextColor', array('value'), '#FF0000', _t('二维码logo文本颜色'), _t(''));
        $form->addInput($qrcodeLogoTextColor);
		$qrcodeLogoTextSize = new Typecho_Widget_Helper_Form_Element_Text('qrcodeLogoTextSize', array('value'), '25', _t('二维码logo文本大小'), _t(''));
        $form->addInput($qrcodeLogoTextSize);
		//有赞、三合一转账二维码支付选项
		$tledashangpaytype = new Typecho_Widget_Helper_Form_Element_Radio('tledashangpaytype', array(
            'scan'=>_t('三合一转账二维码支付'),
            'youzan'=>_t('有赞支付')
        ), 'scan', _t('有赞'), _t("支付渠道"));
        $form->addInput($tledashangpaytype->addRule('enum', _t(''), array('scan', 'youzan')));
		//有赞设置
		$tledashangyz_client_id = new Typecho_Widget_Helper_Form_Element_Text('tledashangyz_client_id', null, '', _t('有赞client_id'), _t('在<a href="https://www.youzanyun.com/" target="_blank">有赞云官网</a>授权绑定有赞微小店APP的店铺后注册的client_id'));
        $form->addInput($tledashangyz_client_id);
		$tledashangyz_client_secret = new Typecho_Widget_Helper_Form_Element_Text('tledashangyz_client_secret', null, '', _t('有赞client_secret'), _t('在<a href="https://www.youzanyun.com/" target="_blank">有赞云官网</a>授权绑定有赞微小店APP的店铺后注册的client_secret'));
        $form->addInput($tledashangyz_client_secret);
		$tledashangyz_shop_id = new Typecho_Widget_Helper_Form_Element_Text('tledashangyz_shop_id', null, '', _t('有赞授权店铺id'), _t('在<a href="https://www.youzanyun.com/" target="_blank">有赞云官网</a>授权绑定有赞微小店APP的店铺后注册的授权店铺id'));
        $form->addInput($tledashangyz_shop_id);
		$tledashangyz_redirect_url = new Typecho_Widget_Helper_Form_Element_Text('tledashangyz_redirect_url', array("value"), $plug_url.'/TleDaShang/return_url.php', _t('有赞消息推送网址'), _t('在<a href="https://www.youzanyun.com/" target="_blank">有赞云官网</a>授权绑定有赞微小店APP的店铺后注册的消息推送网址'));
        $form->addInput($tledashangyz_redirect_url);
		
		$tledashangqrcodetype = new Typecho_Widget_Helper_Form_Element_Radio('tledashangqrcodetype', array(
            'QR_TYPE_FIXED_BY_PERSON'=>_t('无金额'),
            'QR_TYPE_NOLIMIT'=>_t('固定金额且可以重复支付'),
			'QR_TYPE_DYNAMIC'=>_t('固定金额且只可支付一次')
        ), 'QR_TYPE_DYNAMIC', _t('固定金额且只可支付一次'), _t("支付二维码种类"));
        $form->addInput($tledashangqrcodetype->addRule('enum', _t(''), array('QR_TYPE_FIXED_BY_PERSON', 'QR_TYPE_NOLIMIT', 'QR_TYPE_DYNAMIC')));
		
		$tledashangshoptype = new Typecho_Widget_Helper_Form_Element_Radio('tledashangshoptype', array(
            'oauth'=>_t('工具型'),
            'self'=>_t('自用型')
        ), 'self', _t('自用型'), _t("店铺应用种类"));
        $form->addInput($tledashangshoptype->addRule('enum', _t(''), array('oauth', 'self')));
		//打赏二维码其他设置
		$alertmsg = new Typecho_Widget_Helper_Form_Element_Text('alertmsg', array('value'), '谢谢打赏，我会加倍努力！', _t('二维码下方文字提示'), _t(''));
        $form->addInput($alertmsg);
		$pagerec = new Typecho_Widget_Helper_Form_Element_Text('pagerec', array('value'), '10', _t('打赏列表每页记录数'), _t(''));
        $form->addInput($pagerec);
		//独立页面打赏设置
		$tledashangqq = new Typecho_Widget_Helper_Form_Element_Text('tledashangqq', array("value"), '2293338477', _t('QQ号'), _t('通过QQ号自动获取头像地址和联系QQ链接'));
        $form->addInput($tledashangqq);
		$tledashangtalk = new Typecho_Widget_Helper_Form_Element_Text('tledashangtalk', array("value"), '恭喜发财', _t('想说的话'), _t('如果填写用户可看到打赏你的缘由'));
        $form->addInput($tledashangtalk);
		$tledashangisaudio = new Typecho_Widget_Helper_Form_Element_Radio('tledashangisaudio', array(
            'y'=>_t('启用'),
            'n'=>_t('禁用')
        ), 'y', _t('是否开启背景歌曲'), _t("启用后打赏页面会出现歌曲"));
        $form->addInput($tledashangisaudio->addRule('enum', _t(''), array('y', 'n')));
    }

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form){
    }

    // 获得插件配置信息
    public static function getConfig(){
        return Typecho_Widget::widget('Widget_Options')->plugin('TleDaShang');
    }
	
	/*创建打赏数据表*/
	public static function createTableDashangItem($db){
		$prefix = $db->getPrefix();
		//$db->query('DROP TABLE IF EXISTS '.$prefix.'tledashang_item');
		//`dashangtype` enum("wxpay","alipay","","qqpay","ALIPAY","bank_pc") COLLATE utf8_general_ci DEFAULT NULL,
		$db->query('CREATE TABLE IF NOT EXISTS `'.$prefix.'tledashang_item` (
		  `dashangid` bigint(20) NOT NULL AUTO_INCREMENT,
		  `dashangnumber` varchar(125) COLLATE utf8_general_ci NOT NULL,
		  `dashanggid` bigint(20) DEFAULT NULL,
		  `dashangqq` bigint(20) DEFAULT NULL,
		  `dashangmoney` double(10,2) DEFAULT NULL,
		  `dashangmsg` varchar(20) COLLATE utf8_general_ci DEFAULT NULL,
		  `dashangtype` varchar(20) COLLATE utf8_general_ci DEFAULT NULL,
		  `dashangstatus` enum("y","n") COLLATE utf8_general_ci DEFAULT "n",
		  `dashangtime` datetime DEFAULT NULL,
		  PRIMARY KEY (`dashangid`)
		) AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
	}
	
	/*公共方法：将页面写入数据库*/
	public static function funWriteDataPage($db,$title,$slug,$template,$status="hidden"){
		date_default_timezone_set('Asia/Shanghai');
		$query= $db->select('slug')->from('table.contents')->where('template = ?', $template); 
		$row = $db->fetchRow($query);
		if(count($row)==0){
			$contents = array(
				'title'      =>  $title,
				'slug'      =>  $slug,
				'created'   =>  time(),
				'text'=>  '<!--markdown-->',
				'password'  =>  '',
				'authorId'     =>  Typecho_Cookie::get('__typecho_uid'),
				'template'     =>  $template,
				'type'     =>  'page',
				'status'     =>  $status,
			);
			$insert = $db->insert('table.contents')->rows($contents);
			$insertId = $db->query($insert);
			$slug=$contents['slug'];
		}else{
			$slug=$row['slug'];
		}
	}
	
	/*公共方法：将页面写入主题目录*/
	public static function funWriteThemePage($db,$filename){
		$queryTheme= $db->select('value')->from('table.options')->where('name = ?', 'theme'); 
		$rowTheme = $db->fetchRow($queryTheme);
		$path=dirname(__FILE__).'/../../themes/'.$rowTheme['value'];
		if(!is_writable($path)){
			Typecho_Widget::widget('Widget_Notice')->set(_t('主题目录不可写，请更改目录权限。'.__TYPECHO_THEME_DIR__.'/'.$rowTheme['value']), 'success');
		}
		if(!file_exists($path."/".$filename)){
			$regfile = @fopen(dirname(__FILE__)."/page/".$filename, "r") or die("不能读取".$filename."文件");
			$regtext=fread($regfile,filesize(dirname(__FILE__)."/page/".$filename));
			fclose($regfile);
			$regpage = fopen($path."/".$filename, "w") or die("不能写入".$filename."文件");
			fwrite($regpage, $regtext);
			fclose($regpage);
		}
	}
	
	/**
     * 输出内容
     * @access public
     * @return void
     */
    public static function printDashang($obj){
		$db = Typecho_Db::get();
		$options = Typecho_Widget::widget('Widget_Options');
		$option=$options->plugin('TleDaShang');
		$plug_url = $options->pluginUrl;
		?>
		<style>
		.carousel {
		  border-top: 1px solid #333;
		  border-color:#EEEEEE;
		  height: 306px;
		  padding:0 0 0 2px;
		  width: 100%;
		}
		.carousel#carousel-vertical {
		  height: 306px;
		  padding: 0;
		  width: auto;
		}
		.carousel#carousel-responsive {
		  width: auto;
		}
		.carousel li, 
		.carousel > div > div {
		  color: #666;
		  padding:2px 0 2px 2px;
		}
		.carousel#carousel-vertical li, 
		.carousel#carousel-vertical > div > div {
		  padding:2px 2px 0 2px;
		}
		.carousel ul li:nth-child(even), 
		.carousel > div > div:nth-child(even) {
		  color:#666;
		}
		
		.btn-ranking{display:inline;width:100px;height:50px;line-height:50px;color:#fff;border-radius:10px;font-size:16px;text-align:center;padding:10px;margin-right:10px;}
		.btn-dashang{display:inline;width:100px;height:50px;line-height:50px;color:#fff;border-radius:10px;font-size:16px;text-align:center;padding:10px;margin-left:10px;}
		.btn-dashang:link,.btn-dashang:visited,.btn-dashang:hover,.btn-dashang:active{background:#D9534F;color:#FFFFFF;}
		.btn-ranking:link,.btn-ranking:visited,.btn-ranking:hover,.btn-ranking:active{background:#D9534F;color:#FFFFFF;}
		</style>
		<center>
			<a href='javascript:void(0)' style="background:#66CC66;" class="btn-ranking" id="btn_tle_dashang_ranking" title="">赞赏排名</a>
			<a href='javascript:void(0)' style="background:#D9534F;" id="btn_tle_dashang" class='btn-dashang' title='如果觉得该作者的文章对你有帮助，那么就小礼物走一走，随意打赏给他，您的支持将鼓励作者继续创作！'>赞赏支持</a>
		</center>
		<div style="clear:both;"></div>
		<div class="carousel" id="carousel-responsive" style="display:none;">
			<ul style="list-style-type: none;margin: 0;padding: 0;text-align:center;">
				<?php
				$query= $db->select()->from('table.tledashang_item')->join('table.contents', 'table.contents.cid = table.tledashang_item.dashanggid',Typecho_Db::INNER_JOIN)->where('table.tledashang_item.dashangstatus = ?', 'y')->where('table.contents.status = ?', 'publish')->where('table.contents.cid = ?', $obj->cid)->order('dashangtime',Typecho_Db::SORT_DESC)->order('dashangmoney',Typecho_Db::SORT_DESC)->offset(0)->limit($option->pagerec);
				$rows = $db->fetchAll($query);
				foreach ($rows as $value) {
				?>
				<li>
					<?php
						if($value['dashangqq']==0){
							?>
							<img src="<?=$plug_url;?>/TleDaShang/images/default.jpg" width="100" />
							<?php
						}else{
							?>
							<a href="http://wpa.qq.com/msgrd?v=3&uin=<?=$value['dashangqq'];?>&site=qq&menu=yes" target="_blank"><img src="http://q1.qlogo.cn/g?b=qq&nk=<?=$value['dashangqq'];?>&s=100&t=<?=time();?>" /></a>
							<?php
						}
					?>
					<div><small>打赏了<?=$value['dashangmoney'];?>元</small></div>
					<div><small><?=$value['dashangmsg'];?></small></div>
				</li>
				<?php
				}
				?>
				<li>
					<img src="<?=$plug_url;?>/TleDaShang/images/waiting.jpg" width="100" />
					<div><small>等你来打赏……</small></div>
					<div>&nbsp;</div>
				</li>
			</ul>
		</div>
		<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
		<script src="https://cdn.bootcss.com/layer/3.1.0/layer.js"></script>
		<script src="<?=$plug_url;?>/TleDaShang/js/jquery-qrcode.min.js"></script>
		<script src="<?=$plug_url;?>/TleDaShang/js/floatingcarousel.min.js" type="text/javascript"></script>
		<script type="text/javascript">
		/*$(function(){*/
			/*随机金额*/
			function randomData(){
			   var moneys=[[1,'赞助站长'],[2.1,'文章写的太好了'],[3.14,'高智商人才'],[5.20,'爱你哟'],[6.66,'真的很6'],[8.88,'恭喜发财'],[10,'膜拜大神']];
			   var value = moneys[Math.round(Math.random()*(moneys.length-1))];
			   $('#dashangmsg').val(value[1]);
			   $('#dashangmoney').val(value[0]);
			}
			$("#btn_tle_dashang_ranking").click(function(){
				if($("#carousel-responsive").css("display")=="block"){
					$("#carousel-responsive").css("display","none");
				}else{
					$("#carousel-responsive").css("display","");
				}
			});
			$("#btn_tle_dashang").click(function(){
				layer.confirm('<div><table><tr><td>QQ：</td><td><input id="dashangqq" type="number" placeholder="QQ号(可选)" style="width:200px;" /></td><td>&nbsp;</td></tr><tr><td>金额：</td><td><input id="dashangmoney" type="text" placeholder="打赏金额(必选)" style="width:200px;" /></td><td>元</td></tr><tr><td>留言：</td><td><input id="dashangmsg" style="width:200px;" type="text" maxLength="7" list="dashangmsgselect" placeholder="打赏留言(可选)"><datalist id="dashangmsgselect" style="display:none;"><option value="赞助站长">赞助站长</option><option value="文章写的太好了">文章写的太好了</option></datalist></td><td>&nbsp;</td></tr></table></div>', {
				btn: ['我要打赏','不打赏了'],
					title:'给作者打赏',
					success: function(index, layero){
						randomData();
						$("#dashangqq").keyup(function(){
							$("#dashangqq").val($("#dashangqq").val().replace(/[^\d.]/g,""));
							$("#dashangqq").val($("#dashangqq").val().replace(/\.{2,}/g,"."));
							$("#dashangqq").val($("#dashangqq").val().replace(/^\./g,""));
							$("#dashangqq").val($("#dashangqq").val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
							$("#dashangqq").val($("#dashangqq").val().replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3"));
						});
						$("#dashangmoney").keyup(function(){
							$("#dashangmoney").val($("#dashangmoney").val().replace(/[^\d.]/g,""));
							$("#dashangmoney").val($("#dashangmoney").val().replace(/\.{2,}/g,"."));
							$("#dashangmoney").val($("#dashangmoney").val().replace(/^\./g,""));
							$("#dashangmoney").val($("#dashangmoney").val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
							$("#dashangmoney").val($("#dashangmoney").val().replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3"));
						});
					}
				}, function(){
					if($("#dashangmoney").val()==''){
						layer.msg('请输入打赏金额');
						return false;
					}
					var ii = layer.load(2, {shade:[0.1,'#fff']});
					$.ajax({
						type : "POST",
						url : "<?php echo $plug_url.'/TleDaShang/pay.php?dashanggid='.$obj->cid;?>",
						data : {"action":"submitdashang","dashangqq":$("#dashangqq").val(),"dashangmoney":$("#dashangmoney").val(),"dashangmsg":$("#dashangmsg").val()},
						dataType : 'json',
						success : function(data) {
							layer.close(ii);
							if(data.type=="scan"){
								str="<center><div>支持QQ、微信、支付宝</div><img src='"+data.qr_code+"' width='200'><div><?=$option->alertmsg;?></div></center>";
							}else if(data.type=="youzan"){
								str="<center><div>支持微信、支付宝</div><img src='"+data.qr_code+"'><div><a href='"+data.qr_url+"' target='_blank'>跳转支付链接</a></div><div><?=$option->alertmsg;?></div></center>";
							}
							layer.confirm(str, {
								btn: ['已打赏','后悔了'],
								title:'手机扫一扫'
							},function(index){
								window.location.reload();
								layer.close(index);
							});
						},error:function(data){
							layer.close(ii);
							layer.msg('服务器错误');
							return false;
						}
					});
				}, function(){
					layer.msg('以后再打赏吧……', {
						time: 5000,/*20s后自动关闭*/
						btn: ['再考虑一下~']
					});
				});
			});
			
			/* Default carousel*/
			$('#carousel-default').floatingCarousel();

			/* Autoscroll*/
			$('#carousel-autoscroll').floatingCarousel({
				autoScroll : false,
				autoScrollDirection : 'right',
				autoScrollSpeed : 20000,
				scrollSpeed : 'fast'
			});

			/* vertical*/
			$('#carousel-vertical').floatingCarousel({
				scrollerAlignment : 'vertical'
			});

			/*responsive*/
			var opts = {
					autoScroll : false,
					autoScrollSpeed : 20000
				},
				responsiveCarousel = $('#carousel-responsive').floatingCarousel(opts);
			$(window).resize($.debounce(100, function () {
				responsiveCarousel.update(opts);
			}));
		/*});*/
		</script>
		<?php
	}
}