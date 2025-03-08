# QR-Line

QR-Line is a web-based queue management system utilizing QR codes for efficient and streamlined queue handling.

## Features
- **QR Code Generation**: Generate unique QR codes for users to join the queue.
- **Queue Management**: Track and manage queue positions in real-time.
- **User Interface**: Simple and intuitive front-end built with PHP, JavaScript, and Bootstrap.
- **Database Integration**: Uses MySQL to store and retrieve queue data.

## Installation

1. **Clone the Repository**
   ```sh
   git clone https://github.com/kyleengreso/qr-line.git
   cd qr-line
   ```

2. **Setup Database**
   - Import the database snapshot found in the `db_snapshot` folder into MySQL.
   - Configure database credentials in `includes/db_conn.php`.

3. **Run the Application**
   - Deploy the project on a local or remote server with PHP and MySQL support.
   - Open `index.php` in a browser.

## Dependencies
- PHP
- MySQL
- JavaScript (for front-end interactions)
- Bootstrap (for responsive UI design)

## Usage
1. Users scan a QR code to join the queue.
2. Admins monitor and manage queue positions.
3. Real-time updates ensure smooth queue management.

## License
This project is licensed under the MIT License.

