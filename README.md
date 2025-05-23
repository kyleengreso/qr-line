# QR-Line

QR-Line is a web-based queue management system utilizing QR code for efficient and streamlined queue handling.

## Features

- **QR Code Invitation**: Requesters can join via invitation in QR Code to directly to the form , so can join for queue.
- **Queue Management**: Track and manage queue positions in real-time.
- **User Interface**: Simple and intuitive front-end built with PHP, CSS, JavaScript and Bootstrap.
- **Database Integration**: Uses MySQL to store and retrieve queue data.
- **Transaction Stats:** Shows transaction totals based in time.
- **Counter Stats:** Shows what counter number will hold the number.

## Installation

For Windows

1. **Download and install XAMPP**
- Make sure you have already installed XAMPP to your Windows System. If you dont have it, you can [download here](https://www.apachefriends.org/).
2. **Clone the Repository**

```sh
git clone https://github.com/kyleengreso/qr-line.git
```

3. Place the source code to where **XAMPP** directory is installed for example **C:\\xampp**. Inside the xampp folder, if you are an beginner you can place all source code at **C:\\xampp\\htdocs** or if you want only change the path, you can edit the *httpd.conf* located at **C:\\xampp\\apache\\conf\\httpd.conf**.
- Find the content inside the *<u>httpd.conf</u>* for the following.

```apacheconf
  DocumentRoot "C:/xampp/htdocs"
  <Directory "C:/xampp/htdocs">
```

4. Place it where **qr-line** folder was located.

```apacheconf
   DocumentRoot "C:/path/to/qr-line"
   <Directory "C:/path/to/qr-line">
```

5. Make sure you have **MySQL Community** installed to your Windows System. If you dont have it, you can [download here](https://dev.mysql.com/downloads/installer/)  then select the version **8.x** and install it.

6. During you're installing the **MySQL Community** to your Windows System, Make sure you choose only **MySQL Server** and **MySQL Workbench 8.0 CE** which is only used for work for import the database.

7. After install the **MySQL Community** to your Windows system, run the **MySQL Workbench**, for import the database file into the system.

8. After you import the database file, make sure the **Host**, **Port**, **Username** and **Password** of the **MySQL Server** is same at **C:/path/to/qr-line/public/includes/db_conn.php** for mentioned that path that **Username** and **Password** is same <u>**root**</u>.

9. Open **XAMPP Control Panel** and start <u>Apache</u> only 

10. Open the browser and place **localhost** if youre onworking to your current machine. So you will directly to the **QR-Line's Requester Invite** go to the below usage section or if you want to login, you can go at **http://{your_address}/public/auth/**  or at the footer page.

11. Ready to serve for your requesters.

## Dependencies

- PHP
- CSS
- MySQL
- JavaScript (for front-end interactions)
- Bootstrap (for responsive UI design)

## Usage

**Requesters:**

1. Can scan a QR code to join the queue. At the **{your_host_address}/public/requester/requester_invite.php**
2. Has a ability to cancel their queue request.
3. Can show their transaction status in realtime.

**Employees:**

1. Employees can manage the queue (e.g., call next, skip, recall).
2. Employees can cut-off the queue.

**Admin:**

1. Can manage employees and counters.
2. Can check or track transaction history.
3. Can show chart on dashboard.
4. Can generate reports.

## License

This project is licensed under the [MIT License](https://github.com/kyleengreso/qr-line/blob/main/LICENSE).
