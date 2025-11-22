# Jersey Shop - Custom Jersey Order Management System

A modern, dynamic single-page jersey order form with a comprehensive administrative backend. Built with PHP, MySQL, Tailwind CSS, and Alpine.js.

## ✨ Features

### Customer Frontend
- **Modern Split-Screen Design**: Image carousel on desktop, stacked on mobile
- **Dynamic Product Selection**: Card-based selectors for jersey qualities and combo packages
- **Real-time Price Calculation**: Live total updates based on selections
- **Customizable Fields**: Admin-defined custom input fields (e.g., Gamertag, Sleeve Patch)
- **Fabric Previews**: Optional fabric texture images on quality cards
- **WhatsApp Integration**: Direct messaging for payment receipt submission
- **Floating WhatsApp Button**: Always-accessible support button with pulse animation
- **Email Confirmation**: Automated order confirmation emails
- **Custom Order Numbers**: Professional order IDs (e.g., TRU-2411-7294)
- **Size Chart Modal**: Popup size chart for easy reference
- **Smart Disclaimers**: 
  - Image disclaimer below carousel
  - Delivery timeline notice above order button
- **Responsive Design**: Fully optimized for mobile and desktop

### Admin Panel
- **Order Management**: View all orders with detailed customer information and custom order numbers
- **Full CRUD Operations**:
  - Jersey quality tiers (name, price, description, fabric image)
  - Combo packages (add-ons with price adjustments)
  - Carousel images (upload or URL)
  - Custom form fields
- **Shop Configuration**: Customize title, description, and payment instructions
- **Content Management**:
  - Upload shop logo
  - Upload size chart image
  - Edit image disclaimer text
  - Edit delivery disclaimer
  - Customize success message (with variables)
  - Customize WhatsApp message template
- **Email System**:
  - Configure SMTP settings
  - Customize email templates
  - Send bulk emails to customers
- **WhatsApp Tools**: Bulk number export and direct messaging
- **Statistics Dashboard**: Total orders, pending payments, and revenue tracking
- **Secure File Uploads**: Image upload system with validation (max 5MB)

## 🛠️ Requirements

- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher
- **Web Server** (Apache, Nginx, or PHP built-in server)

## 📦 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/jersey-shop.git
cd jersey-shop
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
   - View all orders with custom order numbers (TRU-YYMM-XXXX format)
   - Track statistics
   - Contact customers via WhatsApp

2. **Communications** (`admin/communications.php`):
   - Configure SMTP email settings
   - Customize email templates
   - Send bulk emails to customers
   - Export WhatsApp numbers

3. **Settings** (`admin/settings.php`):
   - Configure shop title and description
   - Manage jersey qualities and prices
   - Upload optional fabric images for each quality
   - Add/edit combo packages
   - Upload carousel images
   - Create custom form fields

4. **Content Management** (`admin/content.php`):
   - Upload shop logo
   - Upload size chart image
   - Edit image disclaimer text
   - Edit delivery disclaimer
   - Customize success message (supports variables: {name}, {order_id}, {total})
   - Customize WhatsApp message template

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
├── order-helper.php            # Custom order number generator
├── README.md                   # This file
├── uploads/                    # Uploaded files (images, logos, charts)
│   ├── images/                 # Carousel images
│   ├── size-charts/            # Size chart images
│   ├── fabrics/                # Fabric texture images
│   └── logos/                  # Shop logos
└── admin/
    ├── auth.php                # Authentication helper
    ├── login.php               # Admin login page
    ├── index.php               # Orders dashboard
    ├── settings.php            # Product & shop settings
    ├── communications.php      # Email & WhatsApp tools
    ├── content.php             # Content management (NEW)
    └── upload-handler.php      # Secure file upload handler (NEW)
```

## 🎨 Customization

### Change Currency

The application currently uses **RM** (Ringgit Malaysia). To change:
1. Search for `RM` in `index.php`, `admin/index.php`, and `admin/settings.php`
2. Replace with your preferred currency symbol

### Change Order Number Prefix

Default order numbers use **TRU-YYMM-XXXX** format (e.g., TRU-2411-7294). To change the prefix:
1. Edit `order-helper.php`
2. Change `"TRU-{$year}{$month}-{$random}"` to your preferred prefix

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
