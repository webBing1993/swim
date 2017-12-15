<?php
/**
 * File: Plan.php
 * User: Administrator
 * Date: 2017-09-04 14:24
 */
namespace app\admin\controller;
use app\admin\model\WechatUser;
use app\admin\model\WeekSummary;
use app\admin\model\WeekPlan;
use app\admin\model\ClassPlan;
use app\admin\model\ClassScore;
use app\admin\model\ClassContent;
use app\admin\model\ClassTrain;
use app\admin\model\WeekContent;
use app\admin\model\WeekTrain;
use think\Db;
use think\Collection;
/**
 * Class Plan
 * @package 训练档案控制器
 */
class Plan extends Admin {
	/**
	 * 列表
	 */
	public function index(){
		$list = WechatUser::where(['member_type' => WechatUser::MEMBER_TYPE_COACH])->select();
		if($list){
			$collection = new Collection($list);
			$list = $collection->toArray();
		}
		//var_dump($list);die;
		$this->assign('list',$list);
		return $this->fetch();
	}

	/**
	 * 导出周总结
	 */
	public function weekSummary(){
		$data = input('get.');
		$start_time = strtotime($data['start_time']);
		$end_time = strtotime($data['end_time']);
		//var_dump($data);die;
		$list = WeekSummary::where(['userid' => $data['userid'], 'status' => 0])->where('start',['>=',$start_time],['<=',$end_time],'and')->select();
		//var_dump($list->fetchSql()->select());die;
		//var_dump($list);die;
		if(!$list){
			echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
			echo "<script>alert('没有数据不允许导出！');history.go(-1);</script>";
			exit;
		}
		$html = [];
		foreach ($list as $model){
			$xls = [];
			$start = date("Y年n月j日",$model->start);
			$end = date("Y年n月j日",$model->end);
			//导出逻辑
			$xls[] = "
				<table>
				<tr>
					<th class=\"left\">周训练小结</th>
					<th class=\"right\">
						".$start."至".$end."
					</th>
				</tr>
				</table>
				<table>
				<tr class=\"six\">
					<td class=\"work\">重<br/>点<br/>运<br/>动<br/>员<br/>完<br/>成<br/>情<br/>况</td>
					<td colspan=\"14\">".$model->content."</td>
				</tr>
				<tr class=\"seven\">
					<td class=\"work\">周<br/>小<br/>结</td>
					<td colspan=\"14\">".$model->summary."</td>
				</tr>";
			$xls[] = '</table>';
			$html[] = join("\r\n", $xls);
		}
		$doc = '<html><head><meta http-equiv=content-type content="text/html; charset=UTF-8">
	<style>
        html, body, textarea, table, tr, th, td{padding: 0; margin: 0; font-style: normal; font-size: 16px; }
        table{ width: 100%; height: auto; margin: 0 auto; border-collapse:collapse; border-spacing: 0; }
        td{ border: 1px solid black; text-align: center; padding: 0;}
        th{ height: 50px;font-weight: normal;}
        .work{ width:50px; }
        .left{ letter-spacing : 2px; text-align: left;}
        .right{ letter-spacing : 2px; text-align: right;}
        .six { height: 345px;}
        .seven { height: 518px;}
        .img{ width:100%;height:100%; position:absolute;top:0;left:0;}
    </style></head><body>';
		$doc .= join("\r\n", $html);
        $doc .="<img src='data:image/png;base64,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' class='.img'>";
		$doc .= '</body></html>';
		//var_dump($doc);die;
		header('Content-Disposition: attachment; filename="'.$data['name'].'周训练小结['.$data['start_time'].']~['.$data['end_time'].'].doc"');
		die(mb_convert_encoding($doc,'UTF-8','UTF-8'));
	}

	/**
	 * 导出周计划
	 */
	public function weekPlan(){
		$data = input('get.');
		$start_time = strtotime($data['start_time']);
		$end_time = strtotime($data['end_time']);
		//var_dump($data);die;
		$list = WeekPlan::where(['userid' => $data['userid'], 'status' => 0])->where('start',['>=',$start_time],['<=',$end_time],'and')->select();
		//var_dump($list->fetchSql()->select());die;
		//var_dump($list);die;
		if(!$list){
			echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
			echo "<script>alert('没有数据不允许导出！');history.go(-1);</script>";
			exit;
		}
		$html = [];
		$collection = new Collection($list);
		$list = $collection->toArray();
		foreach ($list as $model){
			$model['contents'] = WeekContent::where(['pid' => $model['id']])->field('id, type, contentself, load, duration')->order('type')->select();
			if($model['contents']){
				$collection = new Collection($model['contents']);
				$model['contents'] = $collection->toArray();
				foreach($model['contents'] as $key => $mo) {
					$model['contents'][$key]['content'] = WeekTrain::where(['pid' => $mo['id']])->field('group, num, distance, pose, detail')->order('`order`')->select();
					if($model['contents'][$key]['content']){
						$collection = new Collection($model['contents'][$key]['content']);
						$model['contents'][$key]['content'] = $collection->toArray();
						foreach($model['contents'][$key]['content'] as $k =>  $content) {
							$model['contents'][$key]['content'][$k] = $content;
						}
					}
					//unset($model['contents'][$key]['id']);
				}
			}
			//var_dump($model);//die;
			$xls = [];
			$start = date("Y年n月j日",$model['start']);
			$end = date("Y年n月j日",$model['end']);
			//导出逻辑
			$xls[] = "
				<table>
				<tr>
					<th class=\"left\">周训练计划</th>
					<th class=\"right\">
						".$start."至".$end."
					</th>
				</tr>
				</table>
				<table>
				<tr class=\"two\">
					<td class=\"work\">任<br/>务<br/>要<br/>求</td>
					<td colspan=\"4\">".$model['need']."</td>
				</tr>
				<tr class=\"thr\">
					<td>星期</td>
					<td colspan=\"2\">内容</td>
    				<td class=\"work\">时间</td>
    				<td class=\"work\">负荷</td>
				</tr>";
			if(!empty($model['contents'])){
				$weeks = [1 => '一', 2 => '二', 3 => '三', 4 => '四', 5 => '五', 6 => '六', 7 => '日'];
				for($i=1;$i<=7;$i++){
					$week = [];
					foreach($model['contents'] as $mo){
						$week[] = $mo['type'];
						if($mo['type'] == $i){
							$xls[] = "
				<tr class=\"four\">
    				<td>".$weeks[$i]."</td>
					<td colspan=\"2\">";
							if(!empty($mo['content'])){
								foreach($mo['content'] as $k => $m){
									$dom = $k==0 ? '' : "，";
									$xls[] = $dom.$m['group']."组".$m['num']."个".$m['distance']."m ".$m['pose']." ".$m['detail'];
								}
							}
							if(!empty($mo['contentself'])){
								$xls[] = "，".$mo['contentself'];
							}
							$xls[] = "</td>
    				<td>".WeekContent::DURATION_ARRAY[$mo['duration']]."</td>
    				<td>".WeekContent::LOAD_ARRAY[$mo['load']]."</td>
				</tr>";
						}
					}
					if(!in_array($i, $week)){
						$xls[] = "
				<tr class=\"four\">
    				<td>".$weeks[$i]."</td>
					<td colspan=\"2\"></td>
    				<td></td>
    				<td></td>
				</tr>";
					}
				}
			}else{
				$xls[] = "
				<tr class=\"four\">
    				<td>一</td>
					<td colspan=\"2\"></td>
    				<td></td>
    				<td></td>
				</tr>
				<tr class=\"four\">
    				<td>二</td>
					<td colspan=\"2\"></td>
    				<td></td>
    				<td></td>
				</tr>
				<tr class=\"four\">
    				<td>三</td>
					<td colspan=\"2\"></td>
    				<td></td>
    				<td></td>
				</tr>
				<tr class=\"four\">
    				<td>四</td>
					<td colspan=\"2\"></td>
    				<td></td>
    				<td></td>
				</tr>
				<tr class=\"four\">
    				<td>五</td>
					<td colspan=\"2\"></td>
    				<td></td>
    				<td></td>
				</tr>
				<tr class=\"four\">
    				<td>六</td>
					<td colspan=\"2\"></td>
    				<td></td>
    				<td></td>
				</tr>
				<tr class=\"four\">
    				<td>日</td>
					<td colspan=\"2\"></td>
    				<td></td>
    				<td></td>
				</tr>";
			}
			$xls[] = '</table>';
			$html[] = join("\r\n", $xls);
		}
		$doc = '<html><head><meta http-equiv=content-type content="text/html; charset=UTF-8">
	<style>
        html, body, textarea, table, tr, th, td{padding: 0; margin: 0; font-style: normal; font-size: 16px; }
    	table{ width: 100%; height: auto; margin: 0 auto; border-collapse:collapse; border-spacing: 0; }
    	.thr{ height: 30px;}
    	.two{ height: 120px;}
    	.four{ height: 100px; }
    	td{ border: 1px solid black; text-align: center; padding: 0;}
    	th{ height: 50px;font-weight: normal;}
    	.work{ width:50px; }
    	.left{ letter-spacing : 2px; text-align: left;}
        .right{ letter-spacing : 2px; text-align: right;}
    </style></head><body>';
		$doc .= join("\r\n", $html);
		$doc .= '</body></html>';
//		var_dump($doc); return ;
		header('Content-Disposition: attachment; filename="'.$data['name'].'周训练计划['.$data['start_time'].']~['.$data['end_time'].'].doc"');
		die(mb_convert_encoding($doc,'UTF-8','UTF-8'));
	}

	/**
	 * 导出课时计划
	 */
	public function classPlan(){
		$data = input('get.');
		$start_time = strtotime($data['start_time']);
		$end_time = strtotime($data['end_time']);
		//var_dump($data);die;
		$list = ClassPlan::where(['userid' => $data['userid'], 'status' => 0])->where('start',['>=',$start_time],['<=',$end_time],'and')->select();
		//var_dump($list->fetchSql()->select());die;
		//var_dump($list);die;
		if(!$list){
			echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
			echo "<script>alert('没有数据不允许导出！');history.go(-1);</script>";
			exit;
		}
		$html = [];
		$collection = new Collection($list);
		$list = $collection->toArray();
		foreach ($list as $model){
			$model['score'] = ClassScore::where(['pid' => $model['id']])->field('userid, name, score, good')->order('`order`')->select();
			if($model['score']){
				$collection = new Collection($model['score']);
				$model['score'] = $collection->toArray();
			}
			$model['contents'] = ClassContent::where(['pid' => $model['id']])->field('id, type, contentself, load, strength, duration')->order('type')->select();
			if($model['contents']){
				$collection = new Collection($model['contents']);
				$model['contents'] = $collection->toArray();
				foreach($model['contents'] as $key => $mo) {
					$model['contents'][$key]['content'] = ClassTrain::where(['pid' => $mo['id']])->field('group, num, distance, pose, detail')->order('`order`')->select();
					if($model['contents'][$key]['content']){
						$collection = new Collection($model['contents'][$key]['content']);
						$model['contents'][$key]['content'] = $collection->toArray();
						foreach($model['contents'][$key]['content'] as $k =>  $content) {
							$model['contents'][$key]['content'][$k] = $content;
						}
					}
				}
			}
			//var_dump($model);die;
			$xls = [];
			$weekday = ClassPlan::WEEK_ARRAY[date("w",$model['start'])+1];
			$start = date("n月j日",$model['start']);
			$xls[] = "
				<table>
				<tr>
					<th class=\"left\">课时计划</th>
					<th class=\"right\">授课(训练)时间：
						".$start." ".$weekday."
					</th>
				</tr>
				</table>
				<table>
				<tr class=\"two\">
					<td class=\"work\">任<br/>务<br/>要<br/>求</td>
					<td colspan=\"4\">".$model['need']."</td>
				</tr>
				<tr class=\"thr\">
					<td rowspan=\"2\">部分</td>
					<td rowspan=\"2\">训练内容，方法</td>
    				<td colspan=\"2\">负荷</td>
    				<td rowspan=\"2\" class=\"work\">时间</td>
				</tr>
				<tr class=\"thr\">
					<td class=\"work\">量</td>
					<td class=\"work\">强度</td>
				</tr>";
			if(!empty($model['contents'])){
				$weeks = [1 => "准<br/>备<br/>部<br/>分", 2 => "基<br/>本<br/>部<br/>分", 3 => "结<br/>束<br/>部<br/>分"];
				$weeks_class = [1 => "two", 2 => "five", 3 => "four"];
				for($i=1;$i<=3;$i++){
					$week = [];
					foreach($model['contents'] as $mo){
						$week[] = $mo['type'];
						if($mo['type'] == $i){
							$xls[] = "
				<tr class=\"".$weeks_class[$i]."\">
    				<td>".$weeks[$i]."</td>
					<td>";
							if(!empty($mo['content'])){
								foreach($mo['content'] as $k => $m){
									$dom = $k==0 ? '' : "，";
									$xls[] = $dom.$m['group']."组".$m['num']."个".$m['distance']."m ".$m['pose']." ".$m['detail'];
								}
							}
							if(!empty($mo['contentself'])){
								$xls[] = "，".$mo['contentself'];
							}
							$xls[] = "</td>
					<td>".ClassContent::LOAD_ARRAY[$mo['load']]."</td>
    				<td>".ClassContent::STRENGTH_ARRAY[$mo['strength']]."</td>
    				<td>".ClassContent::DURATION_ARRAY[$mo['duration']]."</td>
				</tr>";
						}
					}
					if(!in_array($i, $week)){
						$xls[] = "
				<tr class=\"".$weeks_class[$i]."\">
    				<td>".$weeks[$i]."</td>
					<td></td>
    				<td></td>
    				<td></td>
    				<td></td>
				</tr>";
					}
				}
			}else{
				$xls[] = "
				<tr class=\"two\">
					<td>准<br/>备<br/>部<br/>分</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr class=\"five\">
					<td>基<br/>本<br/>部<br/>分</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr class=\"four\">
					<td>结<br/>束<br/>部<br/>分</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>";
			}
			if(!empty($model['score'])){
				$xls[] = "
				<tr class=\"four\">
					<td>课<br/>后<br/>小<br/>结</td>
					<td colspan=\"4\">".$model['summary']."    成绩:";
				foreach($model['score'] as $k => $m){
					$dom = $k==0 ? '' : "，";
					if($m['good']){
						$xls[] = $dom."<span style=\"display:inline-block;border:1px solid green;color:green\">".$m['name']."</span> ".$m['score'];
					}else{
						$xls[] = $dom.$m['name']." ".$m['score'];
					}
				}
				$xls[] = "</td>
				</tr>";
			}else{
				$xls[] = "
				<tr class=\"four\">
					<td>课<br/>后<br/>小<br/>结</td>
					<td colspan=\"4\">".$model['summary']."</td>
				</tr>";
			}
			$xls[] = '</table>';
			$html[] = join("\r\n", $xls);
		}
		$doc = '<html><head><meta http-equiv=content-type content="text/html; charset=UTF-8">
	<style>
        html, body, textarea, table, tr, th, td{padding: 0; margin: 0; font-style: normal; font-size: 16px; }
        table{ width: 100%; height: auto; margin: 0 auto; border-collapse:collapse; border-spacing: 0; }
        .two{ height: 130px;}
        .thr{ height: 20px;}
        .four{ height: 100px; }
        .five{ height: 360px; }
        td{ border: 1px solid black; text-align: center; padding: 0;}
        th{ height: 50px;font-weight: normal;}
        .work{ width:50px; }
        .left{ letter-spacing : 2px; text-align: left;}
        .right{ letter-spacing : 2px; text-align: right;}
    </style></head><body>';
		$doc .= join("\r\n", $html);
		$doc .= '</body></html>';
		//var_dump($doc);return ;
		header('Content-Disposition: attachment; filename="'.$data['name'].'课时计划['.$data['start_time'].']~['.$data['end_time'].'].doc"');
		die(mb_convert_encoding($doc,'UTF-8','UTF-8'));
	}


	//下载保存到服务器
	public function test(){
		$data = input('get.');
		$start_time = strtotime($data['start_time']);
		$end_time = strtotime($data['end_time']);
		$list = WeekSummary::where(['userid' => $data['userid']])->where('start',['>=',$start_time],['<=',$end_time],'and')->select();
		if(!$list){
			echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
			echo "<script>alert('没有数据不允许导出！');</script>";
			exit;
		}
		foreach ($list as $model){
			$start = date("Y年m月d日",$model->start);
			$end = date("Y年m月d日",$model->end);
			//导出逻辑
			$html = "<table border='1'><tr style='border-color: #fff'>
					<td colspan=3 align=center>周训练小结</td>
					<td colspan=3 align=right>".$start."至".$end."</td>
					</tr>
					<tr>
		            <td>重点运动员完成情况</td>
		            <td>".$model->content."</td>
		          	</tr>
		          	<tr>
		            <td>周小结</td>
		            <td>".$model->summary."</td>
		          	</tr></table>";
			ob_start();
			echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">';
			//$html = "aaa".$i;
			echo $html;
			echo "</html>";
			$data = ob_get_contents();
			ob_end_clean();
			header('Content-Disposition: attachment; filename="'.$model->name.'周训练小结.doc"');
			mb_convert_encoding($html,'UTF-8','UTF-8');
			//定义文件路径
			$file_dir = 'uploads/export';
			//判断文件是否存在
			if(!is_dir($file_dir)) {
				//不存在生成
				mkdir($file_dir);
				chmod($file_dir,0777);
			}
			$wordname = $file_dir.'/'.'222'.'.doc';
			$fp=fopen($wordname,"wb");
			fwrite($fp,$data);
			fclose($fp);
			ob_flush();//每次执行前刷新缓存
			flush();
		}
	}
}