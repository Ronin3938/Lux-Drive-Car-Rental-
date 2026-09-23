Lux Drive - Car Rental E-Commerce Platform
Lux Drive is a dual-model (B2B & B2C) Car Rental E-Commerce Platform designed to connect vehicle rental agencies, platform administrators, and direct customers under a unified web architecture.

Key Features
Admin Dashboard (System Oversight)

Full administrative control over user accounts, platform permissions, and vendor approvals.

System-wide analytics, revenue overview, and platform configuration.

Moderation of listings and business verification workflows.

Rental Vendor Dashboard (B2B)

Dedicated portal for rental vendors to register and manage fleet inventory.

Vehicle status tracking, dynamic pricing, and availability management.

Direct management of incoming customer booking requests.

Customer Portal (B2C)

Interactive e-commerce store to browse, search, and filter available vehicles.

Streamlined online booking system with rental history and status updates.

Account management features including wishlists and secure OTP verification.

Tech Stack
Frontend: HTML5, CSS3, JavaScript

Backend: PHP

Database: MySQL

Dependencies: Composer, PHPMailer (OTP & email notifications)

Database Setup
Open your database management tool (e.g., phpMyAdmin, MySQL Workbench).

Create a new database named car_rental.

Import the SQL file located in the root directory:

SQL
car_rental (26).sql
Local Installation
Clone the repository:

Bash
git clone https://github.com/Ronin3938/Lux-Drive-Car-Rental-.git
Move files to your server directory:
Copy the project directory to your local server path (e.g., htdocs for XAMPP or www for WampServer).

Install Dependencies:
Navigate into the project folder in your terminal and run:

Bash
composer install
Configure Environment:
Update your database host, username, and password inside your database connection file to match your local setup.

Run the Project:
Start Apache and MySQL on your server, then access http://localhost/CarRental in your browser.

Security Note
Ensure sensitive environment variables, API keys, and database credentials are set up in a secure configuration file and excluded from version control before deploying to production.
