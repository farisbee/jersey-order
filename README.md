# Jersey Shop - Custom Jersey Order Management System

A modern, dynamic single-page jersey order form with a comprehensive administrative backend. Built with PHP, MySQL, Tailwind CSS, and Alpine.js.

## ✨ Features

### Customer Frontend
- **Modern Split-Screen Design**: Image carousel on desktop, stacked on mobile
- **Dynamic Product Selection**: Card-based selectors for jersey qualities and combo packages
- **Real-time Price Calculation**: Live total updates based on selections
- **Customizable Fields**: Admin-defined custom input fields (e.g., Gamertag, Sleeve Patch)
- **WhatsApp Integration**: Direct messaging for payment receipt submission
- **Email Confirmation**: Automated order confirmation emails
- **Responsive Design**: Fully optimized for mobile and desktop

### Admin Panel
- **Order Management**: View all orders with detailed customer information
- **Full CRUD Operations**:
  - Jersey quality tiers (name, price, description)
  - Combo packages (add-ons with price adjustments)
  - Carousel images
  - Custom form fields
- **Shop Configuration**: Customize title, description, and payment instructions
- **Email System**:
  - Configure SMTP settings
  - Customize email templates
  - Send bulk emails to customers
- **WhatsApp Tools**: Bulk number export and direct messaging
- **Statistics Dashboard**: Total orders, pending payments, and revenue tracking

## 🛠️ Requirements

- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher
- **Web Server** (Apache, Nginx, or PHP built-in server)

## 📦 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/farisbee/jersey-order.git
cd jersey-order
```

### 2. Set Up the Database

1. Create a new MySQL database:
   ```sql
   CREATE DATABASE jersey_shop;
   ```

2. Import the schema:
   ```bash
   mysql -u root -p jersey_shop < schema.sql
   ```

### 3. Configure Database Connection

Edit `db.php` and update the database credentials:

```php
$host = '127.0.0.1';
$db = 'jersey_shop';
$user = 'root';          // Your MySQL username
$pass = 'root';          // Your MySQL password
```

### 4. Start the Application

Using PHP's built-in server:

```bash
php -S localhost:8000
```

Or configure your web server (Apache/Nginx) to point to this directory.

## 🚀 Usage

### Customer Access

Visit `http://localhost:8000/index.php` to:
- Browse jersey options
- Customize your order
- Submit orders
- Receive email confirmations
- Send payment receipts via WhatsApp

### Admin Access

Visit `http://localhost:8000/admin/login.php`

**Default Admin Credentials:**
- **Username**: `admin`
- **Password**: `admin123`

> ⚠️ **Important**: Change the default admin password immediately after first login!

### Admin Functions

1. **Dashboard** (`admin/index.php`):
   - View all orders
   - Track statistics
   - Contact customers via WhatsApp

2. **Settings** (`admin/settings.php`):
   - Configure shop title and description
   - Manage jersey qualities and prices
   - Add/edit combo packages
   - Upload carousel images
   - Create custom form fields

3. **Communications** (`admin/communications.php`):
   - Configure SMTP email settings
   - Customize email templates
   - Send bulk emails to customers
   - Export WhatsApp numbers

## 🔧 Configuration

### SMTP Email Setup

1. Go to Admin Panel → Communications
2. Click "Show Settings"
3. Enter your SMTP details:
   - **Host**: e.g., `smtp.gmail.com`
   - **Port**: e.g., `587`
   - **Username**: Your email address
   - **Password**: Your email password or app-specific password

### Email Template Variables

Use these placeholders in your email templates:
- `{name}` - Customer name
- `{order_id}` - Order ID
- `{total}` - Order total amount

## 📁 Project Structure

```
jersey-shop/
├── db.php                      # Database connection
├── index.php                   # Customer frontend
├── schema.sql                  # Database schema
├── README.md                   # This file
└── admin/
    ├── auth.php                # Authentication helper
    ├── login.php               # Admin login page
    ├── index.php               # Orders dashboard
    ├── settings.php            # Product & shop settings
    └── communications.php      # Email & WhatsApp tools
```

## 🎨 Customization

### Change Currency

The application currently uses **RM** (Ringgit Malaysia). To change:
1. Search for `RM` in `index.php`, `admin/index.php`, and `admin/settings.php`
2. Replace with your preferred currency symbol

### Design Customization

- **Font**: Edit the Google Fonts link in each PHP file (currently using "Outfit")
- **Colors**: Modify Tailwind CSS classes throughout the templates
- **Layout**: Adjust the grid/flex layouts in the HTML sections

## 🔒 Security Notes

1. **Change default admin password** after installation
2. Use **HTTPS** in production
3. Store sensitive credentials in environment variables
4. Enable **prepared statements** (already implemented)
5. Implement **rate limiting** for production use
6. Consider adding **CSRF protection**

## 🐛 Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check credentials in `db.php`
- Ensure database exists

### Images Not Showing on Mobile
- Check that carousel images have valid URLs
- Verify images are accessible

### Email Not Sending
- Confirm SMTP settings are correct
- For Gmail, use an [App Password](https://support.google.com/accounts/answer/185833)
- Check your hosting provider allows outbound SMTP

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Support

For issues or questions, please open an issue on GitHub or contact the maintainer.

---

**Built with ❤️ using PHP, MySQL, Tailwind CSS, and Alpine.js**
