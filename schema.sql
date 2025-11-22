-- Drop existing tables if they exist
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS form_fields;
DROP TABLE IF EXISTS images;
DROP TABLE IF EXISTS combos;
DROP TABLE IF EXISTS qualities;
DROP TABLE IF EXISTS shop_settings;
DROP TABLE IF EXISTS admin_users;

-- Admin Users Table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Shop Settings Table
CREATE TABLE shop_settings (
    id INT PRIMARY KEY DEFAULT 1,
    shop_title VARCHAR(255) DEFAULT 'Jersey Shop',
    shop_description TEXT,
    admin_phone VARCHAR(20) DEFAULT '60123456789',
    payment_instructions TEXT,
    email_config TEXT, -- Stores SMTP host, port, user, pass, and templates
    image_disclaimer TEXT DEFAULT 'Product images are for illustration purposes only. Actual product may vary.',
    size_chart_image VARCHAR(255),
    delivery_disclaimer TEXT DEFAULT 'Estimated delivery: 1 month after order closes',
    success_message TEXT DEFAULT 'Thank you for your order! We\'ll contact you shortly.',
    whatsapp_message_template TEXT DEFAULT 'Hi, I just placed Order #{order_id}.\nName: {name}\nTotal: {total}\nHere is my payment receipt.',
    shop_logo VARCHAR(255)
);

INSERT INTO shop_settings (id, shop_title, shop_description, admin_phone, payment_instructions, email_config) VALUES 
(1, 'Jersey Shop', 'Customize your kit like a pro.', '60123456789', 'Bank: Maybank\nAcc No: 1234567890\nName: Jersey Shop\n\nPlease send the receipt via WhatsApp after payment.', 
'{"smtp_host": "", "smtp_port": "", "smtp_user": "", "smtp_pass": "", "confirmation_subject": "Order Confirmation - Jersey Shop", "confirmation_body": "Hi {name},\\n\\nThank you for your order #{order_id}!\\nTotal: {total}\\n\\nPlease complete your payment."}');

-- Quality Tiers
CREATE TABLE qualities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    fabric_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO qualities (name, price, description) VALUES 
('Player Version', 89.00, 'Premium quality with authentic player features'),
('Fan Version', 59.00, 'Standard quality for everyday wear'),
('Pro Version', 129.00, 'Top-tier quality with match-day specifications');

-- Combo Packages
CREATE TABLE combos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price_adjustment DECIMAL(10, 2) DEFAULT 0,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO combos (name, price_adjustment, description) VALUES 
('Jersey Only', 0.00, 'Just the jersey'),
('Jersey + Shorts', 25.00, 'Complete kit with matching shorts'),
('Full Set', 45.00, 'Jersey, shorts, and socks');

-- Carousel Images
CREATE TABLE images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url TEXT NOT NULL,
    caption VARCHAR(255),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO images (url, caption, display_order) VALUES 
('https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=800', 'Premium Jerseys', 1),
('https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=800', 'Custom Designs', 2),
('https://images.unsplash.com/photo-1606925797300-0b35e9d1794e?w=800', 'Team Kits', 3);

-- Custom Form Fields
CREATE TABLE form_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(100) NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_type ENUM('text', 'number', 'select') DEFAULT 'text',
    options TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO form_fields (label, field_name, field_type, options, display_order) VALUES 
('Player Name', 'player_name', 'text', NULL, 1),
('Sleeve Patch', 'sleeve_patch', 'select', 'None,League,Champions League,World Cup', 2);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    phone_number VARCHAR(20) NOT NULL,
    quality_id INT,
    combo_id INT,
    jersey_number VARCHAR(10),
    jersey_size VARCHAR(10),
    quantity INT DEFAULT 1,
    total_price DECIMAL(10, 2),
    custom_data JSON,
    customer_notes TEXT,
    status ENUM('pending', 'paid', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quality_id) REFERENCES qualities(id),
    FOREIGN KEY (combo_id) REFERENCES combos(id)
);

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admin_users (username, password_hash) VALUES 
('admin', '$2y$10$5Tz2cT7j/KIqBc4Js0O.kOfq2AzAJZxYvxsGRRoNBynldRAOPFu8q');
