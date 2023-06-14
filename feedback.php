<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Feedback</title>
	<style>
    body {
      background-image: url("https://i.pinimg.com/originals/9b/53/36/9b533610afe276cce783a0c52fb959ad.png");
      background-repeat: no-repeat;
      background-size: cover;
    }
  	</style>
</head>
<body>
	
	<form method="post" enctype="multipart/form-data" class="comment-form" action="feedback.php">
		<table class="submit-table">
		<tr>
				<td><p>First Name:</p></td>
				<td><input type="text" name="First_Name" /></td>
			</tr>
			<tr>
				<td><p>Last Name</p></td>
				<td><input type="text" name="Last_Name" /></td>
		</tr>
		<tr>
				<td><p>Your Comment: </p></td>
				<td><textarea name="Comment" rows="10" cols="60"></textarea></td>

		</tr>
			<tr>
			<td><input type="file" name="imageFile" />
			<p>Feel free to upload a small photo</p>
			</td>
			</tr>
			<tr>
	<td><input type="submit" name="submit" value="submit"/></br></td>
			</tr>
		</table>
	</form>  	
</body>
</html>


<?php


if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	$myfile = fopen("feedback/feedback.txt", "a") or die("Unable to open file 1!");

	//upload file
	// Kiểm tra xem có file được upload hay không
	if (isset($_FILES["imageFile"]) && $_FILES["imageFile"]["error"] === UPLOAD_ERR_OK) {
		$targetDir = "uploads/"; // Thư mục lưu trữ file ảnh
		$targetFile = $targetDir . basename($_FILES["imageFile"]["name"]);
		
		$uploaded_type = $_FILES[ 'imageFile' ][ 'type' ];

		// Kiểm tra định dạng file ảnh
		if ($uploaded_type == "image/jpeg" || $uploaded_type == "image/png" || $uploaded_type == "image/jpg") {
			
			// Kiểm tra và di chuyển file tải lên vào thư mục đích
			if (move_uploaded_file($_FILES["imageFile"]["tmp_name"], $targetFile)) {
				echo "Upload thành công vào thư mục: ".$targetFile;
				//write file
				$name = htmlspecialchars($_POST['First_Name'] ." ". $_POST['Last_Name'], ENT_QUOTES, 'UTF-8'. "\n");
				fwrite($myfile, $name. "\n");

				$comment = htmlspecialchars($_POST['Comment'], ENT_QUOTES, 'UTF-8'. "\n");
				fwrite($myfile, $comment. "\n");

				$targetFile = "image:".$targetFile;
				fwrite($myfile, $targetFile. "\n");
			} else {
				echo "Có lỗi xảy ra khi upload file.". "<br>";
			}		
		}else{
			echo "Lỗi: Chỉ được phép upload các file ảnh JPG, JPEG, PNG, GIF."."<br>";
		}		
	}else{
		echo "Vui lòng chọn một file ảnh để upload.". "<br>";
	}
	fclose($myfile);
}

//read file
$myfile = fopen("feedback/feedback.txt", "r") or die("Unable to open file 2!");
echo "<br>". "---------------All Comments---------------". "<br>" ."<br>";
while (!feof($myfile)) {
	$line = fgets($myfile); // Đọc từng dòng trong file

	if (substr($line, 0, 6) !== "image:") {
		echo $line . "<br>"; // Hiển thị nội dung từ file
	}
	
	if (substr($line, 0, 6) === "image:") {
		$imageLine = $line; // Lưu dòng chứa đường dẫn ảnh vào biến $imageLine
		$imagePath = str_replace("image:", "", $imageLine);
		$imagePath = trim($imagePath); // Xóa bỏ các khoảng trắng thừa ở đầu và cuối chuỗi

		if (file_exists($imagePath)) {
		echo '<img src="' . $imagePath . '" alt="Image" style="max-width: 500px;"><br>'; // Hiển thị ảnh
		} else {
		echo "Image not found."; // Hiển thị thông báo khi không tìm thấy file ảnh
		}
	}
}
fclose($myfile);

?>

