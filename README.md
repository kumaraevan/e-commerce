# ecommerce

To run this website, use XAMPP Server. Host the necessary files in your htdocs.
To ensure that this website is running, you also need to set up the database and name it as ecommerce_db.

Create a database and create the tables.

CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Email VARCHAR(255) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('superadmin', 'seller', 'buyer') NOT NULL,
    RegistrationDate DATETIME NOT NULL
);

CREATE TABLE Products (
    ProductID INT AUTO_INCREMENT PRIMARY KEY,
    SellerID INT NOT NULL,
    Name VARCHAR(255) NOT NULL,
    Description TEXT NOT NULL,
    Price DECIMAL(10, 2) NOT NULL,
    StockQuantity INT NOT NULL,
    Category VARCHAR(255) NOT NULL,
    ImageURLs TEXT NOT NULL,
    DateAdded DATETIME NOT NULL,
    FOREIGN KEY (SellerID) REFERENCES Users(UserID) ON DELETE CASCADE
);

CREATE TABLE Orders (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    BuyerID INT NOT NULL,
    TotalPrice DECIMAL(10, 2) NOT NULL,
    OrderStatus ENUM('pending', 'completed', 'shipped', 'cancelled') NOT NULL,
    PaymentMethod VARCHAR(255) NOT NULL,
    ShippingAddress TEXT NOT NULL,
    DateOrdered DATETIME NOT NULL,
    FOREIGN KEY (BuyerID) REFERENCES Users(UserID) ON DELETE CASCADE
);

CREATE TABLE OrderDetails (
    OrderDetailID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT NOT NULL,
    ProductID INT NOT NULL,
    Quantity INT NOT NULL,
    PriceAtPurchase DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID) ON DELETE CASCADE,
    FOREIGN KEY (ProductID) REFERENCES Products(ProductID) ON DELETE CASCADE
);

CREATE TABLE Reviews (
    ReviewID INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    BuyerID INT NOT NULL,
    Rating INT NOT NULL,
    Comment TEXT NOT NULL,
    DatePosted DATETIME NOT NULL,
    FOREIGN KEY (ProductID) REFERENCES Products(ProductID) ON DELETE CASCADE,
    FOREIGN KEY (BuyerID) REFERENCES Users(UserID) ON DELETE CASCADE
);
