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
    </style></head><body>';
		$doc .= join("\r\n", $html);
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