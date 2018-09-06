<?php
	//データベース接続
	$dsn='データベース名';
	$user='ユーザー名';
	$password='パスワード';
	$pdo=new PDO($dsn, $user, $password);
	
	$id=final_id($pdo);
	$name=$_POST['name'];
	$comment=$_POST['comment'];
	$date=date("Y/m/d H:i:s");
	$submit_pass=$_POST['pass1'];
	
	$delete_num=$_POST['delete'];
	$delete_pass=$_POST['pass2'];
	//削除処理
	if( !(empty($delete_num)) ){
		delete_data($delete_num, $delete_pass, $pdo);
	}
	
	//編集データ表示処理
	$tmp_edit_num=$_POST['edit'];	//編集するデータを取り出す変数
	$edit_pass=$_POST['pass3'];
	if( !(empty($tmp_edit_num)) ){
		$sql="SELECT * FROM boardtest WHERE id=$tmp_edit_num";
		$stmt=$pdo->query($sql);
		$results=$stmt->fetch();
		if($edit_pass==$results['password']){
			$edit_name=$results['name'];
			$edit_comment=$results['comment'];
		}else{
			echo "<h3>パスワードが間違っています</h3><br>";
		}
	}
	
	$edit_num=$_POST['pre_edit'];
	if( empty($edit_num) ){
		//通常投稿
		if( !(empty($name) || empty($comment)) ){
			input_data($id, $name, $comment, $date, $submit_pass, $pdo);
		}
	}else{
		edit_data($edit_num, $name, $comment, $date, $pdo);
	}
?>

<html>
<head><title>mission_4</title></head>
<body>
	<form action="mission_4.php" method="post">
		<input type="text" name="name" size="30" placeholder="名前" value="<?php echo $edit_name; ?>"><br>
		<input type="text" name="comment" size="30" placeholder="コメント" value="<?php echo $edit_comment; ?>"><br>
		<input type="text" name="pass1" size="30" placeholder="パスワード(半角英数字)※任意">
		<input type="submit" value="投稿する"><br>
		<input type="hidden" name="pre_edit" value="<?php echo $tmp_edit_num; ?>">
		
		<p><input type="text" name="delete" size="30" placeholder="削除対象番号(半角数字)"><br>
		<input type="text" name="pass2" size="30" placeholder="パスワード(半角英数字)">
		<input type="submit" value="削除する"></p>
		
		<p><input type="text" name="edit" size="30" placeholder="編集対象番号(半角数字)"><br>
		<input type="text" name="pass3" size="30" placeholder="パスワード(半角英数字)">
		<input type="submit" value="編集"></p>
	</form>
	
	<?php
		//表示
		$sql="SELECT * FROM boardtest";
		$results=$pdo->query($sql);
		foreach($results as $row){
			if($row['delete_flug'] == 0){
				echo h($row['id'].". <".$row['name']."> ".$row['comment']." <".$row['date'].">")."<br>";
			}
		}
		
		
		//関数-------------------------------------------------------------------------
		//データをデータベースへ入力する関数
		function input_data($id, $name, $comment, $date, $password, $pdo){
			$sql=$pdo->prepare("INSERT INTO boardtest(id, name, comment, date, password, delete_flug) VALUES(:id, :name, :comment, :date, :password, 0)");
			$sql -> bindParam(':id', $id, PDO::PARAM_INT);
			$sql -> bindParam(':name', $name, PDO::PARAM_STR);
			$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
			$sql -> bindParam(':date', $date, PDO::PARAM_STR);
			$sql -> bindParam(':password', $password, PDO::PARAM_STR);
			$sql -> execute();
		}
		
		//データをデータベースから削除する関数
		function delete_data($delete_id, $delete_pass, $pdo){
			$sql="SELECT password FROM boardtest WHERE id=$delete_id";
			$stmt=$pdo->query($sql);
			$results=$stmt->fetch();
			$data_pass=$results['password'];
			
			if($delete_pass==$data_pass){
				$sql="UPDATE boardtest SET delete_flug=1 WHERE id=$delete_id";
				$results=$pdo->query($sql);
			}else{
				echo "<h3>パスワードが違います</h3><br>";
			}
		}
		
		//編集投稿する関数
		function edit_data($edit_id, $name, $comment, $date, $pdo){
			$sql="UPDATE boardtest SET name=:name, comment=:comment, date=:date WHERE id=:edit_id";
			$stmt = $pdo -> prepare($sql);
			$stmt->bindParam(':name', $name, PDO::PARAM_STR);
			$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
			$stmt->bindParam(':date', $date, PDO::PARAM_STR);
			$stmt->bindParam(':edit_id', $edit_id, PDO::PARAM_INT);
			$stmt->execute();
		}
		
		//最終投稿番号を返す関数計算
		function final_id($pdo){
			$sql="SELECT * FROM boardtest";
			$results=$pdo->query($sql);
			$max_id = 0;
			foreach($results as $row){
				if($max_id < $row['id']){
					$max_id = $row['id'];
				}
			}
			$id = $max_id + 1;
			return $id;
		}
		
		function h($string){
			return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
		}
		//-------------------------------------------------------------------------------
	?>
</body>
</html>