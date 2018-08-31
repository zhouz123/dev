<?php
//include_once './cron_backdb.class.php';
header ( "content-Type: text/html; charset=utf-8" );
set_time_limit(0);
session_start();
date_default_timezone_set('Asia/Shanghai');
$dbconn = mysql_connect("localhost","root","root");
mysql_query("SET NAMES 'utf8'");
mysql_select_db("wlxpl",$dbconn) or die("Data Link Error!");

function table2sql($table,$dbconn)   
{  
	$tabledump = "DROP TABLE IF EXISTS $table;\n";
	$createtable = mysql_query("SHOW CREATE TABLE $table",$dbconn);
	$create = mysql_fetch_row($createtable,$dbconn);
	$tabledump .= $create[1].";\n\n";
	return $tabledump;
}

function tblist($dbconn) {
        $list=array();
        $rs=mysql_query("SHOW TABLES FROM wlxpl",$dbconn);
        while ($temp=mysql_fetch_array($rs)){
            $list[]=$temp[0];
        }
        return $list;
}

function data2sql($table,$dbconn)   
{  
    $tabledump = "DROP TABLE IF EXISTS $table;\n";
    $createtable = mysql_query("SHOW CREATE TABLE $table",$dbconn);
    $create = mysql_fetch_row($createtable);
    $tabledump .= $create[1].";\n\n";
    $rows = mysql_query("SELECT * FROM $table",$dbconn);
    $numfields = mysql_num_fields($rows);
    $numrows = mysql_num_rows($rows);
    while ($row = mysql_fetch_row($rows))  
    {  
      $comma = "";  
      $tabledump .= "INSERT INTO $table VALUES(";  
      for($i = 0; $i < $numfields; $i++){
       $tabledump .= $comma."'".MySQL_escape_string($row[$i])."'";
       $comma = ",";
	  }  
      $tabledump .= ");\n";
    }
    $tabledump .= "\n";
    return $tabledump;
} 
//$tables=tblist($dbconn);//备份所有

$tables = array('v9_news');
//print_r($tables);
//exit;
$prefix = 'wlxpl_';// 要保存的.sql文件的前缀
$saveto = 'server';// 要保存到什么地方，是本地还是服务器上，默认是服务器
$back_mode = 'all';// 要保存的方式，是全部备份还是只保存数据库结构
$admin = 'xyw';//管理员名称
$admin_email = '';// 管理员邮箱
// 定义数据保存的文件名
$filename = $prefix.date('Y-m-d').'.sql';
$_m="-".date('m')."-";

if(file_exists($filename)){
   echo "error";exit;
}


function cleanup_directory($dir) {

  $iter = new RecursiveDirectoryIterator($dir);//高效目录遍历
  $_m=date('m');



  foreach (new RecursiveIteratorIterator($iter, RecursiveIteratorIterator::CHILD_FIRST) as $f) {
    if ($f->isDir()) {
    	
    
      //rmdir($f->getPathname());
	  //echo $f->getPathname()."1";
    } else {
		$file_name=$f->getPathname();
		$file_time=filectime($file_name);
		$filectm=date("m",$file_time);

//echo $file_name."<br>";

     // unlink($f->getPathname());
$file=$dir."\backups.php";

 
	  if($_m==$filectm || $file_name==$file){ //当月的和执行文件不删
echo "当月文件不删除";
	  	  }else{
	    if(strpos($file_name,".sql")){//只删除.sql文件
	    
		  unlink($file_name);
		  
	rmdir($dir);
		}
	  }
    }
  }
   
}
cleanup_directory('D:\WWW\backups');

//$rand=rand();
//if (!$filename) { $filename = $db_backup_path.$prefix.date('Ymd_His_').$rand.".sql";}
//$filename =$prefix.date('Ymd_His_').$rand.".sql";// 保存在服务器上的文件名
// 注意后面的create_check_code()函数，这是一个生成随机码的函数，详细可以参考：
$sqldump="";
//开始备份数据
foreach($tables as $table)   
{  
  if ($back_mode == 'all') { $sqldump .= data2sql($table,$dbconn); }  
  if ($back_mode == 'table') { $sqldump .= table2sql($table,$dbconn); }  
} 
// 如果数据内容不是空就开始保存
if(trim($sqldump)){
  // 写入开头信息
 // $sqldump ="# --------------------------------------------------------\n"."# 数据表备份\n"."#\n"."# 服务器: $db->Host\n"."# 数据库：$db->Database\n"."# 备份编号: ".$rand."\n"."# 备份时间: ".time()."\n"."#\n"."# 管理员：$admin ($admin_email)\n"."# $copyright\n"."# --------------------------------------------------------\n\n\n".$sqldump;

	//保存到本地开始
//  if($saveto == "local"){
//		ob_end_clean();
//		header('Content-Encoding: none');
//		header('Content-Type: '.(strpos($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));
//	header('Content-Disposition: '.(strpos($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'MSIE') ? 'inline; ' : 'attachment; ').'filename="'.$local_filename);
//		header('Content-Length: '.strlen($sqldump));
//		header('Pragma: no-cache');
//		header('Expires: 0');
//	echo $sqldump;
//  }  
   //保存到本地结束

	//保存到服务器开始
	if($saveto == "server"){  
		
		if($filename != ""){  
		  $fp = fopen($filename, "w+");
			if($fp){  
				flock($fp, 3);
				if(!fwrite($fp, $sqldump)){
					fclose($fp);
					echo "数据文件无法保存到服务器，请检查目录属性你是否有写的权限。";
				}else{
					echo "数据成功备份至服务器 <a href=\"$filename\">".$filename."</a> 中。";
				}
			}else{
				echo "无法打开你指定的目录". $filename ."，请确定该目录是否存在，或者是否有相应权限";
			}
		}else{
			echo "您没有输入备份文件名，请返回修改。";
		}
	}
	//保存到服务器结束
}else{
   echo "数据表没有任何内容";
}

/*
$x=new dbBackup();
$x->database='dwspl3s';
$rs=$x->beifen('db.sql');
**/
//var_dump($rs);
//还原
//$x=new dbBackup();
//$x->database='test';
//$rs=$x->huanyuan('db.sql');
//var_dump($rs);

?>