<?php
/**
 * File: Plan.php
 * User: Administrator
 * Date: 2017-09-04 14:24
 */
namespace app\admin\controller;
use app\home\model\WechatUser;
use app\home\model\WeekSummary;
use app\home\model\WeekPlan;
use app\home\model\ClassPlan;
use app\home\model\ClassScore;
use app\home\model\ClassContent;
use app\home\model\ClassTrain;
use app\home\model\WeekContent;
use app\home\model\WeekTrain;
use think\Db;
use think\Collection;
/**
 * Class Plan
 * @package 训练档案控制器
 */
class Plan extends Admin {
	/**
	 * 档案列表
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
	public function weekSummary(){
		$data = input('get.');
		$start_time = strtotime($data['start_time']);
		$end_time = strtotime($data['end_time']);
		//var_dump($data);die;
		$list = WeekSummary::where(['userid' => $data['userid']])->where('start',['>=',$start_time],['<=',$end_time],'and')->select();
		//var_dump($list->fetchSql()->select());die;
		//var_dump($list);die;
		if(!$list){
			echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
			echo "<script>alert('没有数据不允许导出！');</script>";
			exit;
		}
		$html = [];
		foreach ($list as $model){
			$xls = [];
			$start = date("Y年m月d日",$model->start);
			$end = date("Y年m月d日",$model->end);
			//导出逻辑
			$xls[] = "<table border='1'><tr style='border-color: #fff'>
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
		          	</tr>";
			$xls[] = '</table>';
			$html[] = join("\r\n", $xls);
		}
		$doc = '<html><meta http-equiv=content-type content="text/html; charset=UTF-8"><body>';
		$doc .= join("\r\n", $html);
		$doc .= '</body></html>';
		header('Content-Disposition: attachment; filename="'.$data['userid'].'周训练小结.doc"');
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