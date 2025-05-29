# Spheria - Social Media Platform

Spheria is a modern social media platform built with PHP, offering features similar to popular social networks with enhanced privacy and security features.

## 🌟 Features

- **User Authentication**
  - Secure login and registration system
  - Email verification
  - Password reset functionality
  - Two-factor authentication

- **Profile Management**
  - Customizable user profiles
  - Profile and cover photo uploads
  - Bio and personal information management

- **Social Features**
  - News feed with posts and stories
  - Friend/Connection system
  - Direct messaging with end-to-end encryption
  - Story sharing (24-hour content)
  - Post reactions and comments

- **Privacy & Security**
  - End-to-end encrypted messaging
  - Privacy settings for posts and profile
  - Content moderation system
  - Secure file uploads

## 🛠️ Technical Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Security**: Custom encryption implementation
- **File Storage**: Local storage system

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for PHP dependencies)

## 🚀 Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/taksinraja/spheria.git
   ```

2. Set up your web server (Apache/Nginx) to point to the project directory

3. Import the database schema:
   ```bash
   mysql -u your_username -p your_database < database.sql
   ```

4. Configure the database connection:
   - Open `includes/config.php`
   - Update database credentials

5. Set up file permissions:
   ```bash
   chmod 755 -R uploads/
   ```

## 🔧 Configuration

1. Update `includes/config.php` with your database credentials
2. Configure email settings for user verification
3. Set up encryption keys in `includes/encryption/MessageEncryption.php`

## 📁 Project Structure

```
spheria/
├── includes/
│   ├── auth/           # Authentication related files
│   ├── encryption/     # Encryption implementation
│   ├── messages/       # Messaging system
│   └── config.php      # Configuration file
├── uploads/            # User uploaded content
│   ├── cover_images/
│   ├── profile_images/
│   ├── posts/
│   └── stories/
├── assets/            # Static assets
└── database.sql       # Database schema
```

## 🔐 Security Features

- End-to-end encryption for messages
- Secure password hashing
- XSS protection
- CSRF protection
- SQL injection prevention
- File upload validation

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Authors

- **Taksin Raja** - *Initial work* - [taksinraja](https://github.com/taksinraja)

## 🙏 Acknowledgments

- Thanks to all contributors who have helped shape this project
- Inspired by modern social media platforms
- Built with security and privacy in mind 