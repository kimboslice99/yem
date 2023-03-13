# ![php](https://img.shields.io/badge/Php-8993BE?style=for-the-badge&logo=php&logoColor=white) E-commerce Website
E-commerce web application built using php routing. Instead of relying on the web server to map the request path to a file, all requests are forwarded to [index.php](/src/index.php) which has defined routes and callbacks registered to each route. If the request URI is a valid route, the callback returns a page to the user else, redirected to the 404 page.

[Live Demo](https://web.archive.org/web/20220907155514/https://tomiwa.com.ng/yemyem/)

## Features
- Login and registration system
- Password reset
- Ordering system
- Update profile
- Order history
- CSRF protection
- AbuseIPDB Integration
- Input sanitisation
- Sends invoice to user's email using ([PHPMailer](https://github.com/PHPMailer/PHPMailer))
- Canada Post shipping calculator (a better calculator coming soon)
- Braintree integration
- Livechat ([intercom](https://intercom.com))
    #### Admin Panel
- Create, modify and delete products, customers and faq
- Unlimited product pictures
- Image manipulation ([php_imagick](https://www.php.net/manual/en/book.imagick.php))
- Image magic bytes verification
- Upload scanning via ClamAV (if enabled) ([ClamAV](https://www.clamav.net/))
- Create or select product category
- Export/Import database (Export now has compression)
- Last 7 days sales and revenue stats using Chartjs
- Modify contact details and privacy policy
- Send email to users ([PHPMailer](https://github.com/PHPMailer/PHPMailer))

## Setup
- Create database
- Execute [db.sql](src/config/db.sql)
- Ensure webserver has full access to uploads and config directory (ex. IIS_IUSRS)
- Enter database config [config.ini](src/config/config.ini.sample)
- enable php extensions (imagick, curl, mysqli, openssl, filter, zlib, session, bcmath)

## Admin Credentials
```
uri: /admin/login
username: admin
password: 12345
```

## Screenshots
![Login](screenshots/login.png)
![Register](screenshots/register.png)
![Home](screenshots/home.png)
![Shop](screenshots/shop.png)
![Product](screenshots/item.png)
![Cart](screenshots/cart.png)
![Order Success](screenshots/success.png)
![Profile](screenshots/profile.png)
![Orders](screenshots/orders.png)
![Order Details](screenshots/order-details.png)
![Forgot Password](screenshots/forgot-password.png)
![Invoice](screenshots/invoice.png)
![Admin Login](screenshots/admin-login.png)
![Admin Home](screenshots/admin-home1.png)
![Admin Home](screenshots/admin-home2.png)
![Admin Customers](screenshots/admin-customers.png)
![Admin Orders](screenshots/admin-orders.png)
![Admin Products](screenshots/admin-products.png)
![Admin Reset Password](screenshots/admin-reset-password.png)
![Admin Settings](screenshots/admin-settings.png)
