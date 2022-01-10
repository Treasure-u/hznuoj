<?php
require_once "admin-header.php";
require_once("../include/my_func.inc.php");
if(!HAS_PRI("generate_team")){
    echo "Permission denied!";
    exit(1);
}
if(isset($_POST['contest_id'])){
	//require_once("../include/check_post_key.php");
	$cid=$_POST['contest_id'];
	$user_id=explode("\n",trim($_POST['user_id']));
	$stu_id=explode("\n",trim($_POST['stu_id']));
  $password=explode("\n",trim($_POST['password']));
	$institute=explode("\n",trim($_POST['institute']));
	$class=explode("\n",trim($_POST['class']));
	$real_name=explode("\n",trim($_POST['real_name']));
	$nick=explode("\n",trim($_POST['nick']));
	$seat=explode("\n",trim($_POST['seat']));
	$cnt=count($user_id);
	echo "<table class='table'>";
	echo "<tr><th>user_id</th><th>stu_id</th><th>institute</th><th>class</th><th>real_name</th><th>nick</th><th>seat</th><th>password</th></tr>";
	foreach ($user_id as $key => $value) {
        $ori_pass=trim($password[$key]);
        $pass_hash=pwGen($ori_pass);

        $user_id[$key]=trim($user_id[$key]);
        $stu_id[$key]=trim($stu_id[$key]);
        $institute[$key]=trim($institute[$key]);
        $class[$key]=trim($class[$key]);
        $real_name[$key]=trim($real_name[$key]);
        $nick[$key]=trim($nick[$key]);
        $seat[$key]=trim($seat[$key]);
		$sql=<<<SQL
			INSERT INTO team (
				contest_id,
				user_id,
				stu_id,
				institute,
				class,
				real_name,
				nick,
				seat,
				PASSWORD,
				reg_time
			)
			VALUES
			(
				$cid,
				'{$user_id[$key]}',
				'{$stu_id[$key]}',
				'{$institute[$key]}',
				'{$class[$key]}',
				'{$real_name[$key]}',
				'{$nick[$key]}',
				'{$seat[$key]}',
				'$pass_hash',
				NOW()
			)
SQL;
		//echo "<pre>$sql</pre>";
		$mysqli->query($sql);
		echo <<<HTML
		<tr>
			<td>{$user_id[$key]}</td>
			<td>{$stu_id[$key]}</td>
			<td>{$institute[$key]}</td>
			<td>{$class[$key]}</td>
			<td>{$real_name[$key]}</td>
			<td>{$nick[$key]}</td>
			<td>{$seat[$key]}</td>
			<td>$ori_pass</td>
		</tr>
HTML;
	}
	echo "</table>";
	echo "DONE! $cnt teams imported!";
}

?>
<form method="post" accept-charset="utf-8">
	user_id:<textarea name="user_id" rows=20></textarea>
	stu_id:<textarea name="stu_id" rows=20></textarea>
  password:<textarea name="password" rows=20></textarea><br/>
	institute:<textarea name="institute" rows=20></textarea>
	class:<textarea name="class" rows=20></textarea>
	real_name:<textarea name="real_name" rows=20></textarea><br/>
	nick:<textarea name="nick" rows=20></textarea>
	seat:<textarea name="seat" rows=20></textarea>
	<?php require_once("../include/set_post_key.php");?>
	contest_id:<input name="contest_id" value="" placeholder="">
	<button>submit</button>
</form>
<?php
require_once "admin-footer.php";
?>