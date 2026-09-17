-- SINGUP TABLE
CREATE TABLE `signup` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
    `fullname` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20),
    dob DATE,
    gender VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    gender VARCHAR(20) NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    dob DATE NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(20) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (customer_id),
    CONSTRAINT fk_password_resets_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- SELLER_DETAILS TABLE
CREATE TABLE `seller_details` (
    `seller_id` int(11) NOT NULL AUTO_INCREMENT,
    `sellername` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `mobile` varchar(10) NOT NULL,
    `password` varchar(255) NOT NULL,
    `gender` enum('Male', 'Female', 'Other') DEFAULT NULL,
    `dob` date DEFAULT NULL,
    `address` text DEFAULT NULL,
    `city` varchar(50) DEFAULT NULL,
    `state` varchar(50) DEFAULT NULL,
    `pincode` varchar(6) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`seller_id`),
    UNIQUE KEY `email` (`email`)
) ENGINE = InnoDB AUTO_INCREMENT = 7 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- SHOP_DETAILS TABLE
CREATE TABLE `shop_details` (
    `shop_id` int(11) NOT NULL AUTO_INCREMENT,
    `seller_id` int(11) DEFAULT NULL,
    `shop_name` varchar(100) NOT NULL,
    `owner_name` varchar(100) NOT NULL,
    `gst_number` varchar(20) DEFAULT NULL,
    `shop_address` text DEFAULT NULL,
    `shop_city` varchar(50) DEFAULT NULL,
    `shop_state` varchar(50) DEFAULT NULL,
    `shop_pincode` varchar(6) DEFAULT NULL,
    `bank_account_number` varchar(30) DEFAULT NULL,
    `ifsc_code` varchar(20) DEFAULT NULL,
    `upi_id` varchar(100) DEFAULT NULL,
    `registration_certificate` varchar(255) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`shop_id`),
    KEY `seller_id` (`seller_id`),
    CONSTRAINT `shop_details_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `seller_details` (`seller_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- PRODUCT DETAIL
CREATE TABLE `product_details` (
    `product_id` int(11) NOT NULL AUTO_INCREMENT,
    `seller_id` int(11) NOT NULL,
    `product_name` varchar(100) NOT NULL,
    `category` varchar(100) NOT NULL,
    `gender` enum(
        'male',
        'female',
        'boy',
        'girl'
    ) DEFAULT NULL,
    `description` text NOT NULL,
    `brand` varchar(100) NOT NULL,
    `price` decimal(10, 2) NOT NULL,
    `discountprice` decimal(10, 2) DEFAULT NULL,
    `stockqty` int(11) NOT NULL,
    `color` varchar(50) DEFAULT NULL,
    `size` varchar(50) DEFAULT NULL,
    `image` varchar(255) DEFAULT NULL,
    `visible` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`product_id`),
    KEY `seller_id` (`seller_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    phone VARCHAR(100),
    email VARCHAR(255),
    password_hash VARCHAR(255),
    method VARCHAR(10),
    created_at DATETIME
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(30) NOT NULL UNIQUE,
    customer_id INT NULL,
    customer_name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(30) NOT NULL,
    payment_method ENUM('cod', 'debit_card', 'paypal') NOT NULL DEFAULT 'cod',
    payment_reference VARCHAR(100) NULL,
    coupon_code VARCHAR(50) NULL,
    discount_percent DECIMAL(5, 2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    items_json JSON NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    status VARCHAR(40) NOT NULL DEFAULT 'Order Confirmed',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_percent INT NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NOT NULL,
    expire_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

-- 1. Insert Sellers
INSERT INTO
    `seller_details` (
        `sellername`,
        `email`,
        `mobile`,
        `password`,
        `gender`,
        `dob`,
        `address`,
        `city`,
        `state`,
        `pincode`
    )
VALUES (
        'Rahul Sharma',
        'rahul.sharma@example.com',
        '9876543210',
        '$2y$10$hash1',
        'Male',
        '1990-05-15',
        '123 Market Street',
        'Ahmedabad',
        'Gujarat',
        '380001'
    ),
    (
        'Priya Patel',
        'priya.patel@example.com',
        '9876543211',
        '$2y$10$hash2',
        'Female',
        '1992-08-20',
        '45 CG Road',
        'Ahmedabad',
        'Gujarat',
        '380009'
    ),
    (
        'Amit Kumar',
        'amit.kumar@example.com',
        '9876543212',
        '$2y$10$hash3',
        'Male',
        '1988-12-10',
        '78 Station Road',
        'Surat',
        'Gujarat',
        '395003'
    ),
    (
        'Sneha Gupta',
        'sneha.gupta@example.com',
        '9876543213',
        '$2y$10$hash4',
        'Female',
        '1995-03-25',
        '12 MG Road',
        'Mumbai',
        'Maharashtra',
        '400001'
    ),
    (
        'Vikram Singh',
        'vikram.singh@example.com',
        '9876543214',
        '$2y$10$hash5',
        'Male',
        '1985-07-11',
        '56 Civil Lines',
        'Jaipur',
        'Rajasthan',
        '302001'
    ),
    (
        'Anjali Rao',
        'anjali.rao@example.com',
        '9876543215',
        '$2y$10$hash6',
        'Female',
        '1993-11-05',
        '89 Brigade Road',
        'Bangalore',
        'Karnataka',
        '560001'
    ),
    (
        'Rohan Mehta',
        'rohan.mehta@example.com',
        '9876543216',
        '$2y$10$hash7',
        'Male',
        '1991-01-30',
        '34 Park Street',
        'Kolkata',
        'West Bengal',
        '700016'
    ),
    (
        'Neha Sharma',
        'neha.sharma@example.com',
        '9876543217',
        '$2y$10$hash8',
        'Female',
        '1994-09-14',
        '90 Connaught Place',
        'New Delhi',
        'Delhi',
        '110001'
    ),
    (
        'Karan Verma',
        'karan.verma@example.com',
        '9876543218',
        '$2y$10$hash9',
        'Male',
        '1989-06-22',
        '21 Mall Road',
        'Kanpur',
        'Uttar Pradesh',
        '208001'
    ),
    (
        'Pooja Nair',
        'pooja.nair@example.com',
        '9876543219',
        '$2y$10$hash10',
        'Female',
        '1996-04-18',
        '67 Marine Drive',
        'Kochi',
        'Kerala',
        '682011'
    );

-- 2. Insert Corresponding Shops (Linked via seller_id 1 to 10)
-- 2. Insert Corresponding Shops (Linked via correct seller_ids 1 to 10)
INSERT INTO
    `shop_details` (
        `seller_id`,
        `shop_name`,
        `owner_name`,
        `gst_number`,
        `shop_address`,
        `shop_city`,
        `shop_state`,
        `shop_pincode`,
        `bank_account_number`,
        `ifsc_code`,
        `upi_id`,
        `registration_certificate`
    )
VALUES (
        1,
        'Sharma Electronics',
        'Rahul Sharma',
        '24AABCS1234F1Z5',
        '45 Electronics Market',
        'Ahmedabad',
        'Gujarat',
        '380001',
        '123456789012',
        'HDFC0001234',
        'rahul@upi',
        'cert_1.pdf'
    ),
    (
        2,
        'Patel Fashion Hub',
        'Priya Patel',
        '24AABCP5678G1Z6',
        '12 Textile House',
        'Ahmedabad',
        'Gujarat',
        '380009',
        '234567890123',
        'SBIN0002345',
        'priya@okhdfcbank',
        'cert_2.pdf'
    ),
    (
        3, -- <--- FIXED: Changed from 11 to 3
        'Kumar General Store',
        'Amit Kumar',
        '24AABCK9012H1Z7',
        '78 Station Road',
        'Surat',
        'Gujarat',
        '395003',
        '345678901234',
        'ICIC0003456',
        'amit@ybl',
        'cert_3.pdf'
    ),
    (
        4,
        'Gupta Handicrafts',
        'Sneha Gupta',
        '27AABCG3456I1Z8',
        '12 MG Road',
        'Mumbai',
        'Maharashtra',
        '400001',
        '456789012345',
        'UTIB0004567',
        'sneha@paytm',
        'cert_4.pdf'
    ),
    (
        5,
        'Rajputana Artifacts',
        'Vikram Singh',
        '08AABCV7890J1Z9',
        '56 Civil Lines',
        'Jaipur',
        'Rajasthan',
        '302001',
        '567890123456',
        'BARB0JAIPUR',
        'vikram@oksbi',
        'cert_5.pdf'
    ),
    (
        6,
        'Bangalore Book House',
        'Anjali Rao',
        '29AABCA1234K1Z0',
        '89 Brigade Road',
        'Bangalore',
        'Karnataka',
        '560001',
        '678901234567',
        'CNRB0006789',
        'anjali@upi',
        'cert_6.pdf'
    ),
    (
        7,
        'Mehta Sweets & Snacks',
        'Rohan Mehta',
        '19AABCM5678L1Z1',
        '34 Park Street',
        'Kolkata',
        'West Bengal',
        '700016',
        '789012345678',
        'UTIB0007890',
        'rohan@axl',
        'cert_7.pdf'
    ),
    (
        8,
        'Delhi Ethnic Wear',
        'Neha Sharma',
        '07AABCN9012M1Z2',
        '90 Connaught Place',
        'New Delhi',
        'Delhi',
        '110001',
        '890123456789',
        'HDFC0008901',
        'neha@paytm',
        'cert_8.pdf'
    ),
    (
        9,
        'Verma Footwear',
        'Karan Verma',
        '09AABCV3456N1Z3',
        '21 Mall Road',
        'Kanpur',
        'Uttar Pradesh',
        '208001',
        '901234567890',
        'SBIN0009012',
        'karan@ybl',
        'cert_9.pdf'
    ),
    (
        10,
        'Malabar Spices',
        'Pooja Nair',
        '32AABCP7890O1Z4',
        '67 Marine Drive',
        'Kochi',
        'Kerala',
        '682011',
        '012345678901',
        'KKBK0001234',
        'pooja@oksbi',
        'cert_10.pdf'
    );