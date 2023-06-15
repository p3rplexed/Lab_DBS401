#!/bin/bash

# Kiểm tra xem Apache đã được cài đặt hay chưa
if ! command -v apache2 &> /dev/null; then
    echo "Apache chưa được cài đặt. Tiến hành cài đặt..."

    # Cài đặt Apache
    apt update
    apt install -y apache2

    echo "Apache đã được cài đặt thành công."
else
    echo "Apache đã được cài đặt trên hệ thống."
fi

# Kiểm tra xem PHP đã được cài đặt hay chưa
if ! command -v php &> /dev/null; then
    echo "PHP chưa được cài đặt. Tiến hành cài đặt..."

    # Cài đặt PHP và các gói liên quan
    apt update
    apt install -y php

    echo "PHP đã được cài đặt thành công."
else
    echo "PHP đã được cài đặt trên hệ thống."
fi

# Kiểm tra xem MySQL đã được cài đặt hay chưa
if ! dpkg -l | grep -q mysql-server; then
    echo "MySQL chưa được cài đặt. Tiến hành cài đặt..."

    apt update
    apt install -y mysql-server
    apt install -y php-mysql
    # Khởi động MySQL service
    sudo systemctl start mysql
    echo "MySQL đã được cài đặt thành công."
else
    echo "MySQL đã được cài đặt trên hệ thống."
    sudo service mysql start
fi

# Kiểm tra xem Git đã được cài đặt hay chưa
if ! command -v git &> /dev/null; then
    echo "Git chưa được cài đặt. Tiến hành cài đặt..."

    # Cài đặt Git
    apt update
    apt install -y git

    echo "Git đã được cài đặt thành công."
else
    echo "Git đã được cài đặt trên hệ thống."
fi

# Đường dẫn đến thư mục web gốc
web_root="/var/www/html"

# Đường dẫn URL của repository GitHub
repository_url="https://github.com/p3rplexed/Lab_DBS401"

# Tên thư mục cho trang web PHP
web_folder="DBS401"

# Kiểm tra xem thư mục web gốc đã tồn tại hay chưa
if [ ! -d "$web_root" ]; then
    echo "Thư mục web gốc không tồn tại. Vui lòng kiểm tra lại đường dẫn."
    exit 1
fi

# Kiểm tra xem thư mục web cho trang web PHP đã tồn tại hay chưa
if [ -d "$web_root/$web_folder" ]; then
    echo "Thư mục $web_folder đã tồn tại trong thư mục web gốc. Vui lòng chọn tên thư mục khác."
    exit 1
fi

# Clone repository từ GitHub
echo "Đang tải về trang web từ GitHub..."
git clone "$repository_url" "$web_root/$web_folder"

# Kiểm tra xem việc clone thành công hay không
if [ $? -eq 0 ]; then
    echo "Trang web đã được tải về thành công vào thư mục $web_root/$web_folder."
else
    echo "Đã xảy ra lỗi trong quá trình tải về trang web từ GitHub."
    exit 1
fi

#Tao database
# Thông tin cấu hình MySQL
mysql_user="lab_root"
mysql_password="123456"
database_name="DBS401"

# Kiểm tra xem cơ sở dữ liệu đã tồn tại hay chưa
echo "Mật khẩu mặc định của mysql sẽ là rỗng,nên không cần nhập  mk và ấn enter.Nếu trước đó bạn đã đặt mk cho mysql thì hãy nhập mk rồi enter"
if mysql -u"root" -p -e "use $database_name;" &> /dev/null; then
    echo "Cơ sở dữ liệu $database_name đã tồn tại."
    
   # Kiểm tra xem người dùng đã tồn tại hay chưa
   if mysql -u"root" -p -e "SELECT User FROM mysql.user WHERE User='$mysql_user';" | grep -q "$mysql_user"; then
   	 echo "Người dùng $mysql_user đã tồn tại trong MySQL."
   else
   	 # Tạo người dùng và cấp quyền trên cơ sở dữ liệu
   	 echo "Người dùng $mysql_user chưa tồn tại trong MySQL. Đang tạo người dùng mới..." 
	 sudo mysql -u"root" -p -e "CREATE USER '$mysql_user'@'localhost' IDENTIFIED BY '$mysql_password';"
   	 sudo mysql -u"root" -p -e "GRANT ALL PRIVILEGES ON $database_name.* TO '$mysql_user'@'localhost';"
    	 sudo mysql -u"root" -p -e "FLUSH PRIVILEGES;"
   fi 
   echo "insert data to database...."
   mysql -u "$mysql_user" -p"$mysql_password" DBS401 < "$web_root/$web_folder/sql/instrumentstore.sql" 
else
    echo "Cơ sở dữ liệu $database_name chưa tồn tại. Tiến hành tạo cơ sở dữ liệu..."

    # Tạo cơ sở dữ liệu
    mysql -u"root" -p -e "CREATE DATABASE $database_name;"

    # Kiểm tra xem cơ sở dữ liệu đã được tạo thành công hay không
    if [ $? -eq 0 ]; then
        echo "Cơ sở dữ liệu $database_name đã được tạo thành công."
    if mysql -u"root" -p -e "SELECT User FROM mysql.user WHERE User='$mysql_user';" | grep -q "$mysql_user"; then
         echo "Người dùng $mysql_user đã tồn tại trong MySQL."
    else  
         # Tạo người dùng và cấp quyền trên cơ sở dữ liệu
         echo "Người dùng $mysql_user chưa tồn tại trong MySQL. Đang tạo người dùng mới..." 
         sudo mysql -u"root" -p -e "CREATE USER '$mysql_user'@'localhost' IDENTIFIED BY '$mysql_password';"
         sudo mysql -u"root" -p -e "GRANT ALL PRIVILEGES ON $database_name.* TO '$mysql_user'@'localhost';"
         sudo mysql -u"root" -p -e "FLUSH PRIVILEGES;"
    fi
	
	echo "insert data to database...."
        mysql -u "$mysql_user" -p"$mysql_password" DBS401 < "$web_root/$web_folder/sql/instrumentstore.sql" 
    else
        echo "Đã xảy ra lỗi trong quá trình tạo cơ sở dữ liệu $database_name."
        exit 1
    fi
fi

#restart apache2,mysql
# Khởi động lại dịch vụ Apache
echo "Restart apache,mysql"

# Khởi động lại dịch vụ Apache
sudo service apache2 restart

# Kiểm tra xem khởi động lại thành công hay không
if [ $? -eq 0 ]; then
    echo "Dịch vụ Apache đã được khởi động lại thành công."
else
    echo "Đã xảy ra lỗi trong quá trình khởi động lại dịch vụ Apache."
    exit 1
fi

# Khởi động lại dịch vụ mysql
sudo service mysql restart

# Kiểm tra xem khởi động lại thành công hay không
if [ $? -eq 0 ]; then
    echo "Dịch vụ Mysql đã được khởi động lại thành công."
else
    echo "Đã xảy ra lỗi trong quá trình khởi động lại dịch vụ Mysql."
    exit 1
fi

# Đường dẫn tới trang web
sudo chmod -R 757 /var/www/html/DBS401
touch flag.txt
echo "flag{done_File_Uploads_to_RCE_DBS401}" > flag.txt
echo "Truy cập đường dẫn sau để tới trang web của bạn:http://localhost/DBS401"
