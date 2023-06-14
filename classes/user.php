<?php
$filepath = realpath(dirname(__FILE__));
include_once($filepath . '/../lib/session.php');
include_once($filepath . '/../lib/database.php');
include_once($filepath . '/../lib/PHPMailer.php');
include_once($filepath . '/../lib/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
?>

<?php
/**
 * 
 */
class user
{

	public function login($email, $password)
	{
		global $conn;
  		connectDB();
		 //check enter password and username
		if (!$email || !$password) {
			$message_error = "Please enter the full login email and password!";
			echo "<script type='text/javascript'>alert('$message_error');</script>";
			exit;
		}
	
		$regex1 = preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $email);
		$regex2 = preg_match('/[\'"^£$%&*()}{@#~?><>,|=_+¬-]/', $password);
		if ($regex1 && !$regex2) {
	
			$query = "SELECT * FROM users WHERE email = ? and password = ?";
	
			// use prepared statement to prevent SQL injection
			$preparedStatement = $conn->prepare($query);
			$preparedStatement->bind_param('ss', $email, $password);
			$preparedStatement->execute();
			$result = $preparedStatement->get_result();
	
			if (mysqli_num_rows($result) <= 0) {
				$message = "Incorrect email or password!";
				echo "<script type='text/javascript'>alert('$message');</script>";
			} else {
				//lưu tên đăng nhập
				$row = $result->fetch_assoc();
				Session::set('user', true);
				Session::set('userId', $value['id']);
				Session::set('role_id', $value['role_id']);
				header("Location:index.php");
			}
		} else {
			http_response_code(400);
			die('Error processing bad or malformed request');
		}
			
	}

	public function insert($data)
	{
		$fullName = $data['fullName'];
		$email = $data['email'];
		$dob = $data['dob'];
		$address = $data['address'];
		$password = $data['password'];


		$check_email = "SELECT * FROM users WHERE email='$email' LIMIT 1";
		$result_check = select($check_email);
		if ($result_check) {
			return 'Email đã tồn tại!';
		} else {
			// Genarate captcha
			$captcha = rand(10000, 99999);

			$query = "INSERT INTO users VALUES (NULL,'$email','$fullName','$dob','$password',2,1,'$address',0,'" . $captcha . "') ";
			$result = insert($query);
			if ($result) {
				// Send email
				$mail = new PHPMailer();
				$mail->IsSMTP();
				$mail->Mailer = "smtp";

				$mail->SMTPDebug  = 0;
				$mail->SMTPAuth   = TRUE;
				$mail->SMTPSecure = "tls";
				$mail->Port       = 587;
				$mail->Host       = "smtp.gmail.com";
				$mail->Username   = "khuongip564gb@gmail.com";
				$mail->Password   = "googlekhuongip564gb";

				$mail->IsHTML(true);
				$mail->CharSet = 'UTF-8';
				$mail->AddAddress($email, "recipient-name");
				$mail->SetFrom("khuongip564gb@gmail.com", "Instrument Store");
				$mail->Subject = "Xác nhận email tài khoản - Instruments Store";
				$mail->Body = "<h3>Cảm ơn bạn đã đăng ký tài khoản tại website InstrumentStore</h3></br>Đây là mã xác minh tài khoản của bạn: " . $captcha . "";

				$mail->Send();

				return true;
			} else {
				return false;
			}
		}
	}

	public function get()
	{
		$userId = Session::get('userId');
		$query = "SELECT * FROM users WHERE id = '$userId' LIMIT 1";
		$mysqli_result = select($query);
		if ($mysqli_result) {
			$result = mysqli_fetch_all(select($query), MYSQLI_ASSOC)[0];
			return $result;
		}
		return false;
	}

	public function getLastUserId()
	{
		$query = "SELECT * FROM users ORDER BY id DESC LIMIT 1";
		$mysqli_result = select($query);
		if ($mysqli_result) {
			$result = mysqli_fetch_all(select($query), MYSQLI_ASSOC)[0];
			return $result;
		}
		return false;
	}

	public function confirm($userId, $captcha)
	{
		$query = "SELECT * FROM users WHERE id = '$userId' AND captcha = '$captcha' LIMIT 1";
		$mysqli_result =select($query);
		if ($mysqli_result) {
			// Update comfirmed
			$sql = "UPDATE users SET isConfirmed = 1 WHERE id = $userId";
			$update = update($sql);
			if ($update) {
				return true;
			}
		}
		return 'Mã xác minh không đúng!';
	}
}
?>
