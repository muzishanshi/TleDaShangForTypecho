<?php
/**
 * 打赏记录页面
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$db = Typecho_Db::get();
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}
$pluginsname='TleDaShang';
$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin($pluginsname);
$plug_url = $options->pluginUrl;
?>
<?php
date_default_timezone_set('Asia/Shanghai');

$operation = isset($_GET['operation']) ? addslashes($_GET['operation']) : '';
if($operation=='show'){
	$id = isset($_GET['id']) ? addslashes($_GET['id']) : '';
	$update = $db->update('table.tledashang_item')->rows(array('dashangstatus'=>'y'))->where('dashangid=?',$id);
	$updateRows= $db->query($update);
	echo '<script>location.href="'.$url.'";</script>';
}else if($operation=='hide'){
	$id = isset($_GET['id']) ? addslashes($_GET['id']) : '';
	$update = $db->update('table.tledashang_item')->rows(array('dashangstatus'=>'n'))->where('dashangid=?',$id);
	$updateRows= $db->query($update);
	echo '<script>location.href="'.$url.'";</script>';
}else if($operation=='del'){
	$id = isset($_GET['id']) ? addslashes($_GET['id']) : '';
	$delete = $db->delete('table.tledashang_item')->where('dashangid = ?', $id);
	$deletedRows = $db->query($delete);
	echo '<script>location.href="'.$url.'";</script>';
}
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
<title><?php $this->options->title();?>打赏记录</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=0.9">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/4.3.1/cerulean/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://css.letvcdn.com/lc04_yinyue/201612/19/20/00/bootstrap.min.css">
<link rel="alternate icon" type="image/png" href="https://ws3.sinaimg.cn/large/ecabade5ly1fxpiemcap1j200s00s744.jpg">
<script src="https://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
<body background="https://ww2.sinaimg.cn/large/a15b4afegy1fpp139ax3wj200o00g073.jpg">
<div class="container" style="padding-top:20px;">
	<div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
		<div class="panel panel-primary">
			<div class="panel-heading" style="background: linear-gradient(to right,#8ae68a,#5ccdde,#b221ff);">
				<center><font color="#000000"><b>全天24小时接受打赏</b></font></center>
			</div>
			<div class="panel-body">
				<center>
					<div class="alert alert-success">
						<a href="https://wpa.qq.com/msgrd?v=3&uin=<?=$option->tledashangqq;?>&site=qq&menu=yes" target="_blank"><img class="img-circle m-b-xs" style="border: 2px solid #1281FF; margin-left:3px; margin-right:3px;" src="https://q4.qlogo.cn/headimg_dl?dst_uin=<?=$option->tledashangqq;?>&spec=100"; width="60px" height="60px" alt="全天24小时接受打赏"><br></a>
						<?=$option->tledashangtalk;?>
					</div>
				</center>
				<form id=payform action="<?=$plug_url;?>/TleDaShang/pay.php" method=post target="_blank" onSubmit="return false;">
					<div class="input-group">
						<span class="input-group-addon"><span class="glyphicon glyphicon-user"></span> Q&nbsp;&nbsp;Q&nbsp;&nbsp;号</span>
						<input type="number" id="qq" name="qq" class="form-control" placeholder="QQ号（可选）" />
					</div>
					<div class="input-group">
						<span class="input-group-addon"><span class="glyphicon glyphicon-bullhorn"></span> 打赏留言</span>
						<input type="text" maxLength="8" id="attachData" name="attachData" value="日常打赏" class="form-control" required="required" placeholder="想要对我说些啥（可选）" />
					</div>
					<div class="input-group">
						<span class="input-group-addon"><span class="glyphicon glyphicon-yen"></span> 打赏金额</span>
						<input type="text" id="Money" name="Money" value="1" class="form-control" required="required" placeholder="打赏金额（元）" oninput="if(value.length>4)value=value.slice(0,4)"/>
					</div>    
					<center>
						<input type="hidden" id="returnurl" name="returnurl" value="<?=$url;?>" />
						<p>
							<center>
							<div class="btn-group btn-group-justified" role="group" aria-label="...">
								<div id="submit" class="btn btn-primary">
									确定打赏(打赏后你的信息会展示到下方，让更多人看到！)
									<span id="msg"></span>
								</div>
							</div>
							</center>
						</p>
					</center> 
				</form>
			</div>
		</div>
		<?php
		$sumTodayQuery= $this->db->select('count(*) as total')->from('table.tledashang_item')->where('dashangstatus=?','y')->where('DATEDIFF(now(),dashangtime)=?',0);
		$sumTodayRow = $this->db->fetchRow($sumTodayQuery);
		$totalTodayQuery= $this->db->select('sum(dashangmoney) as total')->from('table.tledashang_item')->where('dashangstatus=?','y')->where('DATEDIFF(now(),dashangtime)=?',0);
		$totalTodayRow = $this->db->fetchRow($totalTodayQuery);
		
		$sumYesterdayQuery= $this->db->select('count(*) as total')->from('table.tledashang_item')->where('dashangstatus=?','y')->where('DATEDIFF(now(),dashangtime)=?',1);
		$sumYesterdayRow = $this->db->fetchRow($sumYesterdayQuery);
		$totalYesterdayQuery= $this->db->select('sum(dashangmoney) as total')->from('table.tledashang_item')->where('dashangstatus=?','y')->where('DATEDIFF(now(),dashangtime)=?',1);
		$totalYesterdayRow = $this->db->fetchRow($totalYesterdayQuery);
		
		$sumQuery= $this->db->select('count(*) as total')->from('table.tledashang_item')->where('dashangstatus=?','y');
		$sumRow = $this->db->fetchRow($sumQuery);
		$totalQuery= $this->db->select('sum(dashangmoney) as total')->from('table.tledashang_item')->where('dashangstatus=?','y');
		$totalRow = $this->db->fetchRow($totalQuery);
		?>
		<div class="panel panel-info">
			<div class="panel-heading" style="background: linear-gradient(to right,#14b7ff,#5ccdde,#b221ff);">
				<center><font color="#000000"><b>打赏统计</b></font></center>
			</div>
			<table class="table table-bordered">
				<tbody>
					<tr>
						<td align="center"><font color="#808080"><b>今日打赏总数</b></br><code><?=$sumTodayRow['total'];?></code></br>次</font></td>
						<td align="center"><font color="#808080"><b>今日打赏金额</b></br><code><?=$totalTodayRow['total']!=''?$totalTodayRow['total']:0;?></code></br>元</font></td>
					</tr>
					<tr>
						<td align="center"><font color="#808080"><b>昨日打赏总数</b></br><code><?=$sumYesterdayRow['total'];?></code></br>次</font></td>
						<td align="center"><font color="#808080"><b>昨日打赏金额</b></br><code><?=$totalYesterdayRow['total']!=''?$totalYesterdayRow['total']:0;?></code></br>元</font>
					</td>
					</tr>
					<tr height=50>
						<td align="center"><font color="#808080"><b>累计打赏总数</b></br><code><?=$sumRow['total'];?></code></br>次</font></td>
						<td align="center"><font color="#808080"><b>累计打赏金额</b></br><code><?=$totalRow['total']!=''?$totalRow['total']:0;?></code></br>元</font>
					</td>
					</tr>
				<tbody>
			</table>
		</div>
		<?php
		if ($this->user->group=='administrator'){
			$query= $this->db->select()->from('table.tledashang_item');
		}else{
			$query= $this->db->select()->from('table.tledashang_item')->where('table.tledashang_item.dashangstatus = ?', 'y');
		}
		$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
		if($page_now<1){
			$page_now=1;
		}
		$resultTotal = $this->db->fetchAll($query);
		$page_rec=$option->pagerec;
		$totalrec=count($resultTotal);
		$page=ceil($totalrec/$page_rec);
		if($page_now>$page){
			$page_now=$page;
		}
		if($page_now<=1){
			$before_page=1;
			if($page>1){
				$after_page=$page_now+1;
			}else{
				$after_page=1;
			}
		}else{
			$before_page=$page_now-1;
			if($page_now<$page){
				$after_page=$page_now+1;
			}else{
				$after_page=$page;
			}
		}
		$i=($page_now-1)*$page_rec<0?0:($page_now-1)*$page_rec;
		if ($this->user->group=='administrator'){
			$query= $this->db->select()->from('table.tledashang_item')->order('dashangtime',Typecho_Db::SORT_DESC)->order('dashangtime',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec);
		}else{
			$query= $this->db->select()->from('table.tledashang_item')->where('table.tledashang_item.dashangstatus = ?', 'y')->order('dashangtime',Typecho_Db::SORT_DESC)->order('dashangtime',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec);
		}
		$rows = $this->db->fetchAll($query);
		?>
		<div class="panel panel-primary">
			<div class="panel-heading" style="background: linear-gradient(to right,#8ae68a,#5ccdde,#b221ff);">
				<center><font color="#000000"><b>打赏记录</b></font></center>
			</div>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead>
					<tr>
						<th>订单</th><th>标题</th><th>打赏人</th><th width="80">打赏金额</th><th width="80">打赏留言</th><th width="80">打赏渠道</th><th width="80">打赏时间</th><?php if ($this->user->group=='administrator'){?><th width="80">状态</th><th width="100">操作</th><?php }?>
					</tr>
					</thead>
					<tbody id="content">
					<?php
					if(count($rows)>0){
						foreach ($rows as $value) {
						?>
						<tr class="tleajaxpage">
							<td><?=$value['dashangnumber']!=''?$value['dashangnumber']:$value['dashangid'];?></td>
							<td>
								<?php
								if($value['dashanggid']==0){
									echo "日常打赏";
								}else{
									$query= $db->select()->from('table.contents')->where('cid = ?', $value['dashanggid']); 
									$row = $db->fetchRow($query);
									echo $row["title"];
								}
								?>
							</td>
							<td>
								<?php
									if($value['dashangqq']==0){
										?>
										<img src="<?=$plug_url;?>/TleDaShang/images/default.jpg" width="50" />
										<?php
									}else{
										?>
										<a href="https://wpa.qq.com/msgrd?v=3&uin=<?=$value['dashangqq'];?>&site=qq&menu=yes" target="_blank"><img src="https://q1.qlogo.cn/g?b=qq&nk=<?=$value['dashangqq'];?>&s=100" width="50" /></a>
										<?php
									}
								?>
							</td>
							<td><?=$value['dashangmoney'];?>元</td>
							<td>
								<?php
									if($value['dashangmsg']==''){
										echo '日常打赏';
									}else{
										echo $value['dashangmsg'];
									}
								?>
							</td>
							<td>
								<?php
								if($value['dashangtype']=='alipay'){
									echo '支付宝';
								}else if($value['dashangtype']=='qqpay'){
									echo 'QQ钱包';
								}else if($value['dashangtype']=='wxpay'||$value['dashangtype']=='wx'){
									echo '微信';
								}else if($value['dashangtype']=='bank_pc'){
									echo '网银';
								}
								?>
							</td>
							<td><?=date('m月d日',strtotime($value['dashangtime']));?></td>
							<?php if ($this->user->group=='administrator'){?>
							<td>
								<?php
								if($value['dashangstatus']=='y'){
									echo '<font color="green">已打赏</font>';
								}else if($value['dashangstatus']=='n'){
									echo '<font color="red">未打赏</font>';
								}
								?>
							</td>
							<td>
								<?php if($value['dashangstatus']=='n'){?>
								<a href="<?php echo $url;?>?operation=show&id=<?=$value['dashangid'];?>">显示</a>&nbsp;
								<?php }?>
								<?php if($value['dashangstatus']=='y'){?>
								<a href="<?php echo $url;?>?operation=hide&id=<?=$value['dashangid'];?>">隐藏</a>&nbsp;
								<?php }?>
								<a href="<?php echo $url;?>?operation=del&id=<?=$value['dashangid'];?>">删除</a>&nbsp;
							</td>
							<?php }?>
						</tr>
						<?php
						}
						?>
						<nav aria-label="...">
							<ul class="pager">
							  <?php if($page_now!=1){?>
								<li><a href="<?=$url;?>?page_now=1">首页</a></li>
							  <?php }?>
							  <?php if($page_now>1){?>
								<li><a href="<?=$url;?>?page_now=<?=$before_page;?>">&laquo; 上一页</a></li>
							  <?php }?>
							  <?php if($page_now<$page){?>
								<li><a id="next" href="<?=$url;?>?page_now=<?=$after_page;?>">下一页 &raquo;</a></li>
							  <?php }?>
							  <?php if($page_now!=$page){?>
								<li><a href="<?=$url;?>?page_now=<?=$page;?>">尾页</a></li>
							  <?php }?>
							</ul>
						</nav>
						<!--
						<script src="<?php echo $plug_url; ?>/TleDaShang/js/jquery.ias.min.js" type="text/javascript"></script>
						<script>
						var ias = $.ias({
							container: "#content", /*包含所有文章的元素*/
							item: ".tleajaxpage", /*文章元素*/
							pagination: ".pager", /*分页元素*/
							next: ".pager a#next", /*下一页元素*/
							onRenderComplete: function() {
								
							}
						});
						ias.extension(new IASTriggerExtension({
							text: '', /*此选项为需要点击时的文字*/
							offset: false, /*设置此项后，到 offset+1 页之后需要手动点击才能加载，取消此项则一直为无限加载*/
						}));
						ias.extension(new IASSpinnerExtension());
						ias.extension(new IASNoneLeftExtension({
							text: '', /*加载完成时的提示*/
						}));
						</script>
						-->
						<?php
					}else{
						?>
						<tr align="center"><td colspan="<?php if ($this->user->group=='administrator'){echo 9;}else{echo 7;}?>">暂无打赏记录</td></tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
		
		<p style="text-align:center"><br>&copy; <?=date("Y");?> <a href="<?=$this->options ->siteUrl();?>" target="_blank"><?php $this->options->title();?></a> and Plugin By <a href="http://www.tongleer.com" target="_blank">Tongleer</a>. All rights reserved.</p>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/layer/2.3/layer.js"></script>
<script>
$(function(){
	//随机金额
	function randomData(){
	   var moneys=[[0.66,'66大顺'],[0.88,'恭喜发财'],[1.1,'一生一世'],[2.33,'笑看人生'],[3.14,'数学之美'],[5.20,'爱你哟'],[6.66,'真的很6']];
	   var value = moneys[Math.round(Math.random()*(moneys.length-1))];
	   $('#attachData').val(value[1]);
	   $('#Money').val(value[0]);
	}
	randomData();
	/*限制键盘只能按数字键、小键盘数字键、退格键*/
	$("#qq").keyup(function(){
		$("#qq").val($("#qq").val().replace(/[^\d.]/g,""));
		$("#qq").val($("#qq").val().replace(/\.{2,}/g,"."));
		$("#qq").val($("#qq").val().replace(/^\./g,""));
		$("#qq").val($("#qq").val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
		$("#qq").val($("#qq").val().replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3"));
	});
	$("#Money").keyup(function(){
		$("#Money").val($("#Money").val().replace(/[^\d.]/g,""));
		$("#Money").val($("#Money").val().replace(/\.{2,}/g,"."));
		$("#Money").val($("#Money").val().replace(/^\./g,""));
		$("#Money").val($("#Money").val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
		$("#Money").val($("#Money").val().replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3"));
	});
	$("#submit").click(function(){
		var timer;
		var oldtime = getCookie('paytime');
		var nowtime = Date.parse(new Date()); 
		if((nowtime-oldtime)/1000<=10){
			$("#msg").html('<font color="red">打赏太快，我会脸红的^_^</font>');
			timer=setTimeout(function() { 
				clearTimeout(timer);
				$("#msg").html('');
			},1000) 
			return;
		}
		if($("#Money").val()==''||$("#Money").val()==0){
			return;
		}
		var Money = $("#Money").val();
		var str = "老板，谢谢打赏<br>打赏金额：￥"+Money;
		layer.confirm(str, {
			btn: ['我要打赏','不打赏了']
		}, function(){
			var ii = layer.load(2, {shade:[0.1,'#fff']});
			var dashang_payjstype="native";
			if(isTleDashangWeiXin()){
				dashang_payjstype="cashier";
			}
			$.ajax({
				type : "POST",
				url : "<?php echo $plug_url.'/TleDaShang/pay.php';?>",
				data : {"action":"submitdashang","dashangqq":$("#qq").val(),"dashangmoney":$("#Money").val(),"dashangmsg":$("#attachData").val(),"dashang_payjstype":dashang_payjstype,"url":$('#returnurl').val()},
				dataType : 'json',
				success : function(data) {
					layer.close(ii);
					if(data.status=="ok"){
						if(data.type=="native"||data.type=="scan"){
							str="<center><div>"+data.alertChannel+"</div><img src='"+data.qrcode+"' width='200'><div><?=$option->alertmsg;?></div></center>";
							var nowtime = Date.parse(new Date()); 
							setCookie('paytime',nowtime,24);
						}else if(data.type=="cashier"){
							open("<?=$plug_url;?>/TleDaShang/pay.php?dashangqq="+$('#qq').val()+"&dashangmoney="+$('#Money').val()+"&dashangmsg="+$('#attachData').val()+"&dashang_payjstype="+dashang_payjstype+"&url="+$('#returnurl').val());
							return;
						}
					}else{
						str="<center><div>请求支付过程出了一点小问题，稍后重试一次吧！</div></center>";
					}
					layer.confirm(str, {
						btn: ['已打赏','后悔了']
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
	function isTleDashangWeiXin(){
		var ua = window.navigator.userAgent.toLowerCase();
		if(ua.match(/MicroMessenger/i) == "micromessenger"){
			return true;
		}else{
			return false;
		}
	}
	/*对象转数组*/
	function objToArray(array) {
		var arr = []
		for (var i in array) {
			arr.push(array[i]); 
		}
		console.log(arr);
		return arr;
	}
	/*Cookie操作*/
	function clearCookie(){ 
		var keys=document.cookie.match(/[^ =;]+(?=\=)/g); 
		if (keys) { 
			for (var i = keys.length; i--;) 
			document.cookie=keys[i]+'=0;expires=' + new Date( 0).toUTCString() 
		} 
	}
	function setCookie(name,value,hours){  
		var d = new Date();
		d.setTime(d.getTime() + hours * 3600 * 1000);
		document.cookie = name + '=' + value + '; expires=' + d.toGMTString();
	}
	function getCookie(name){  
		var arr = document.cookie.split('; ');
		for(var i = 0; i < arr.length; i++){
			var temp = arr[i].split('=');
			if(temp[0] == name){
				return temp[1];
			}
		}
		return '';
	}
	function removeCookie(name){
		var d = new Date();
		d.setTime(d.getTime() - 10000);
		document.cookie = name + '=1; expires=' + d.toGMTString();
	}
	$('#bgAudio')[0].volume = <?=$option->tledashangaudiovolume;?>;
});
</script>
<?php if($option->tledashangisaudio=='y'){?>
<audio id="bgAudio" autoplay="autoplay" loop="loop" height="100" width="100">
<source src="<?=$option->tledashangaudiourl;?>" type="audio/mp3" />
</audio>
<?php }?>
</body>
</html>