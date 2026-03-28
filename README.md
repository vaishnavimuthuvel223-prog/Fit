# 💪 FitPulse - Fitness Tracking Dashboard

A modern, professional fitness tracking web application with a premium glassmorphism UI, built with PHP, MySQL, HTML, CSS, and JavaScript.

---

## 🎯 Features

✅ **Modern Premium UI**
- Glassmorphism design with backdrop blur effects
- Gradient backgrounds (purple/blue/neon)
- Smooth animations and transitions
- Responsive mobile + desktop design
- Circular progress indicators
- Week activity charts using Chart.js

✅ **Authentication System**
- Secure login page
- User registration with validation
- Password hashing (bcrypt)
- Session management
- Auto-redirect for logged-in users

✅ **Dashboard Features**
- Real-time fitness metrics (steps, calories, water)
- Active minutes and distance calculations
- Weekly activity chart
- Goal setting interface
- Progress tracking with visual indicators
- AI motivational suggestions
- Workout timer (start/stop/reset)
- Daily progress form
- Responsive notification panel

✅ **Backend**
- PHP with error reporting enabled
- mysqli database connection
- Secure SQL queries with prepared statements
- Session-based authentication
- AJAX API for real-time updates

✅ **Database**
- users table (registration & authentication)
- fitness_data table (daily metrics)
- goals table (user targets)
- Auto-table creation on first run

---

## 📁 Project Structure

```
fitness/
├── index.php              # Entry point (redirects to login/dashboard)
├── login.php              # Login page
├── signup.php             # Registration page
├── dashboard.php          # Main fitness tracking dashboard
├── logout.php             # Logout handler
├── db.php                 # Database configuration & connection
├── api.php                # AJAX API endpoints
├── config.php             # Old config (can be removed)
├── init.sql               # Database schema
├── css/
│   └── style.css          # Complete styling (modern UI)
├── js/
│   └── script.js          # Client-side interactions
├── assets/                # Images and icons folder
└── README.md              # This file
```

---

## 🚀 Quick Start Guide

### Prerequisites
- XAMPP (Apache + MySQL)
- Web browser (Chrome, Firefox, Safari, Edge)

### Installation Steps

#### 1. **Place Files in XAMPP**
```
Copy the entire 'fitness' folder to:
C:\xampp\htdocs\

Your path should be:
C:\xampp\htdocs\fitness\
```

#### 2. **Start XAMPP Services**
- Open XAMPP Control Panel
- Start **Apache** (green indicator)
- Start **MySQL** (green indicator)

#### 3. **Create Database**
- Open: `http://localhost/phpmyadmin`
- Click "New" button
- Create database named: `fitness_db`
- Click "Create"
- That's it! Tables auto-create on first app launch

#### 4. **Access the App**
Open browser and go to:
```
http://localhost/fitness/
```

#### 5. **Create Your First Account**
- Click "Sign Up"
- Fill in name, email, password
- Click "Create Account"
- You'll be redirected to the dashboard!

---

## 🔐 Security Features

✅ **Password Security**
- bcrypt hashing (PASSWORD_DEFAULT)
- Secure verification with password_verify()
- Minimum 6 characters required

✅ **Database Security**
- Prepared statements (prevent SQL injection)
- mysqli parameter binding
- Error reporting enabled for debugging

✅ **Session Security**
- Session-based authentication
- Auto-redirect if not logged in
- Secure logout with session destruction

---

## 🎨 UI/UX Highlights

### Login & Signup Pages
- Premium glassmorphism effect
- Floating labels on input fields
- Yellow neon accent color
- Smooth animations
- Loading spinner
- Error alerts
- Social login buttons (Google/Facebook ready)

### Dashboard
- Card-based layout with glassmorphism
- Circular progress rings (animated)
- Weekly activity line chart
- Recent activity table
- Real-time progress updates
- Toast notifications with confetti
- Dark/Light mode toggle
- Sticky sidebar navigation

### Responsive Design
- **Desktop**: Full premium layout
- **Tablet**: Optimized grid
- **Mobile**: Touch-friendly interface

---

## 📊 Database Schema

### users table
```sql
id (INT, Primary Key)
name (VARCHAR 100)
email (VARCHAR 150, Unique)
password (VARCHAR 255, hashed)
created_at (TIMESTAMP)
```

### fitness_data table
```sql
id (INT, Primary Key)
user_id (INT, Foreign Key)
steps (INT, default 0)
calories (INT, default 0)
water (FLOAT, default 0)
date (DATE)
updated_at (TIMESTAMP)
```

### goals table
```sql
id (INT, Primary Key)
user_id (INT, Foreign Key)
steps_goal (INT, default 10000)
calories_goal (INT, default 500)
water_goal (FLOAT, default 2)
```

---

## 🔧 Configuration

### Database Settings (db.php)
```php
define('DB_HOST', '127.0.0.1');  // Localhost
define('DB_USER', 'root');        // Default XAMPP user
define('DB_PASS', '');            // Default XAMPP (no password)
define('DB_NAME', 'fitness_db');  // Database name
```

**Note**: If your XAMPP MySQL has a password, update it in db.php

---

## 📲 API Endpoints

### Update Goals
**Endpoint**: `api.php`
**Method**: POST
```javascript
{
  action: 'update_goals',
  steps_goal: 10000,
  calories_goal: 500,
  water_goal: 2
}
```

### Update Progress
**Endpoint**: `api.php`
**Method**: POST
```javascript
{
  action: 'update_progress',
  steps: 5000,
  calories: 250,
  water: 1.5
}
```

---

## 🐛 Troubleshooting

### Issue: Page not loading
**Solution**: 
- Ensure Apache is running in XAMPP
- Check URL: `http://localhost/fitness/`
- Clear browser cache (Ctrl+Shift+Delete)

### Issue: "Database Connection Failed"
**Solution**:
- Ensure MySQL is running in XAMPP
- Check db.php credentials match your setup
- Create `fitness_db` database in phpMyAdmin

### Issue: Login not working
**Solution**:
- Make sure you've signed up first
- Check email spelling
- Try signing up with a new email

### Issue: CSS/JS not loading
**Solution**:
- Verify file paths are correct
- Check web console (F12) for 404 errors
- Clear cache and reload (Ctrl+Shift+R)

### Issue: Forms not submitting
**Solution**:
- Check browser console for JavaScript errors
- Verify POST method is used
- Ensure db.php is accessible

---

## 🎯 Usage Tips

### Dashboard
1. **Track Daily Progress**: Fill in steps, calories, water with the "Today's Progress" form
2. **Set Goals**: Use the "Set Goals" card to customize targets
3. **View Analytics**: Check weekly charts to see trends
4. **Get Motivated**: Click "New Tip" for AI suggestions
5. **Monitor Time**: Use workout timer to track sessions

### Profile
- Click "Profile" in sidebar
- View member information
- See join date and email

### Logout
- Click "Log out" in sidebar
- Session is destroyed securely
- Redirected to login page

---

## 📈 Future Enhancements

- [ ] Password reset functionality
- [ ] Social media login integration
- [ ] Advanced charts and analytics
- [ ] Export data as PDF/CSV
- [ ] Mobile app version
- [ ] Push notifications
- [ ] Sync with fitness wearables
- [ ] User profile customization
- [ ] Achievement badges

---

## 📝 File Descriptions

| File | Purpose |
|------|---------|
| `index.php` | Entry point, redirects to login/dashboard |
| `login.php` | Login form with validation |
| `signup.php` | Registration form with validation |
| `dashboard.php` | Main dashboard with all features |
| `db.php` | Database connection & helpers |
| `api.php` | AJAX API for real-time updates |
| `logout.php` | Secure logout handler |
| `css/style.css` | All styling (glassmorphism, animations) |
| `js/script.js` | Client-side interactions & validation |
| `init.sql` | Database setup script |

---

## 🤝 Support

For issues or questions:
1. Check the Troubleshooting section
2. Review browser console (F12)
3. Check XAMPP logs for errors
4. Verify all files are in correct locations

---

## 📄 License

This project is open source and available for personal and educational use.

---

## 👨‍💻 Developer Notes

- All passwords are securely hashed with bcrypt
- Database queries use prepared statements
- Error reporting is enabled for debugging
- Code is well-commented for learning
- Follows PHP best practices
- Mobile-first responsive design
- Accessibility features included

---

**Happy Tracking!** 💪🏃‍♂️📊

For the best experience, use:
- Chrome/Firefox (latest version)
- 1920x1080 or higher resolution (desktop)
- Mobile browsers (iOS Safari, Chrome Mobile)
