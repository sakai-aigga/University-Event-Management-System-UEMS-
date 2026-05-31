# University Event Management System (UEMS)

A web-based University Event Management System that lets users register, log in, view events, and manage event details.

## 📌 Project Summary

UEMS is a streamlined event management application built with PHP, JavaScript, and CSS, specifically designed for university ecosystems. It provides a centralized platform for:

* **User Authentication:** Secure registration and login for students.
* **Event Discovery:** Dynamic listing of upcoming university activities.
* **Profile Management:** Personalized dashboards for managing user information.
* **Administrative Control:** Dedicated panel for event handling and updates.

## 🧩 Technologies Used

| Layer | Technology |
| --- | --- |
| **Backend** | PHP |
| **Frontend** | HTML, CSS, JavaScript |
| **Database** | MySQL |
| **Server** | Apache (XAMPP/WAMP) |

## 🗂️ Project Structure

```text
/
├── admin-panel/      # Admin side dashboard and features
├── api/              # Server endpoints (PHP)
├── assets/           # Stylesheets & images
├── includes/         # Database Connection & core logic
├── login/            # Login page logic
├── profile/          # User profile page
├── register/         # Registration logic
├── index.php         # Main landing page
├── uems/             # About and Contact pages
└── README.md         # Project documentation

```

## 🚀 Installation & Setup

1. **Clone the project:**
```bash
git clone https://github.com/sakai-aigga/University-Event-Management-System-UEMS-.git

```


2. **Move into project folder:**
```bash
cd University-Event-Management-System-UEMS-

```


3. **Database Setup:**
* Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
* Create a new database (e.g., `uems`).
* Import your `.sql` file into the new database.


4. **Configuration:**
* Update the database connection settings in the `includes/` folder to match your local credentials (DB Name, Username, Password).
* Create a Google App and put the credentials into the `includes/mail-config.php` file to ensure that the PHP mailer works well.


5. **Deployment:**
* Place the project folder inside your local server's root directory (e.g., `htdocs` for XAMPP).
* Start **Apache** and **MySQL** from your control panel.


6. **Access the App:**
* Visit: `http://localhost/University-Event-Management-System-UEMS-/`
