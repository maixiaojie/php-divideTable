<?php

class divideTable{
	public $host;       //数据库主机名
	public $user;       //数据库用户名
	public $pwd;        //数据库密码 
	public $db;         //数据库
	public $table;      // 要分割的表
	public $field;      //按照表中的某个字段
	public $expect;     //分割头信息配置
	public $arr;        //存放表字段及详情
	public $con;        //数据库链接通道
	public $pre;        //新建数据表名前缀
	/*
	*函数名:__construct
	*作者：Simless   
	*日期：2016.04.04   
	*功能：构造函数，初始化变量,根据需求来 配置
	*输入参数：无
	*返回值：无 
	*修改记录：
	*
	*/
	public function __construct(){
		$this->host = "localhost";
		$this->user = "root";
		$this->pwd = "cust_webexam";
		$this->db = "misyota";
		$this->table = "ura_items";
		$this->field = "CategoryID";
		$this->expect = range("0","9");
		$this->pre = 'table';		
	}
	/*
	*函数名:connect
	*作者：Simless   
	*日期：2016.04.04   
	*功能：链接数据库，选择数据库，设置字符集  
	*输入参数：无
	*返回值：无 
	*修改记录：	
	*/
	public function connect(){   
		$this->con = mysql_connect($this->host,$this->user,$this->pwd);
		if(!$this->con){
			die("数据库链接失败");
		}
		@mysql_select_db($this->db,$this->con);
		@mysql_query("set names utf8");
	}
	/*
	*函数名:query
	*作者：Simless   
	*日期：2016.04.04   
	*功能：负责处理sql语句，进行数据库查询   
	*输入参数：$sql
	*参数含义：$sql---需要执行的sql语句
	*返回值：无 
	*修改记录：
	*/
	public function query($sql){
		return mysql_query($sql,$this->con);
	}
	/*
	*函数名:getTableColumns
	*作者：Simless   
	*日期：2016.04.04   
	*功能：获取某个数据表的字段结构信息
	*输入参数：无
	*返回值：含有字段结构信息的数组arr
	*修改记录：
	*/
	public function getTableColumns(){
		$getColumnSql = "SHOW FULL COLUMNS FROM ".$this->table."";
		$rescolumns = $this->query($getColumnSql);
		$i = 0;
		while($row = mysql_fetch_array($rescolumns)){
			$this->arr[$i] = $row;
			$i++;
		}
		return $this->arr;
	}
	/*
	*函数名:createTableSql
	*作者：Simless   
	*日期：2016.04.04   
	*功能：创建sql语句  
	*输入参数：$arr,$name
	*参数含义：$arr---含有表字段结构信息的数组，$name---要创建表的名称
	*返回值：创建表的sql语句
	*修改记录：
	*/
	public function createTableSql($arr,$name){
		$createSql = "";
		$createSql .= "create table `".$name."` (";
		for($k=0;$k<count($arr);$k++){
			$createSql .= " `".$arr[$k]['Field']."` ".$arr[$k]['Type']."";
			if($arr[$k]['Null'] == "NO"){
				$createSql .= " not null ";
			}
			if($arr[$k]['Key'] == "PRI"){
				$createSql .= " primary key ";
			}
			// if($arr[$k]['Default'] != null){
			// 	$createSql .= "default '".$arr[$k]['Default']."'";
			// }
			$createSql .= " ".$arr[$k]['Extra']." ";
			if($k != count($arr)-1){
				$createSql .= ",";
			} 
			// $createSql .="<br>";
		}
		$createSql .= ")";
		return $createSql;
	}
	/*
	*函数名:createTable
	*作者：Simless   
	*日期：2016.04.05   
	*功能：创建表
	*输入参数：$expect,$pre,$arr
	*参数含义：$expect---分割头信息配置数组，$pre---数据表前缀，$arr---含有表字段结构信息的数组
	*返回值：
	*修改记录：这块有点问题，创建的sql语句,query后执行error(2016-04-05)
	*          之前的错误原因是sql语句中加个了<br>。。。。。现在没问题了(2016-04-09)
	*/
	public function createTable($expect,$pre,$arr){
		$Sql = Array();
		for($i=0;$i<count($expect);$i++){
			 $Sql[$i] = $this->createTableSql($arr,$pre.$expect[$i]);
			$this->query($Sql[$i]);
		}
	}
	/*
	*函数名:updateTable
	*作者：Simless   
	*日期：2016.04.05   
	*功能：更新表中的数据
	*输入参数：
	*参数含义：
	*返回值：
	*修改记录：
	*/
	public function updateTable(){
		for($i=0;$i<count($this->expect);$i++){
			//insert into tableA select * from file where filename like A
			$sql ="insert into ".$this->pre.$this->expect[$i]." select * from ".$this->table." where ".$this->field." like '".$this->expect[$i]."%'";
			$this->query($sql);
		}
	}
	/*
	*函数名:run
	*作者：Simless   
	*日期：2016.04.05   
	*功能：执行过程
	*输入参数：无
	*参数含义：
	*返回值：
	*修改记录：
	*/
	public function run(){
		$this->connect();
		$this->createTable($this->expect,$this->pre,$this->getTableColumns());
		$this->updateTable();
		mysql_close();
	}
}
$a = new divideTable;
$a->run();
?>
