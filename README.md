# 🏅 Sports Equipment Inventory Management

**Sports Equipment Inventory Management** is a lightweight PHP & MySQL web application designed to track quarterly sports equipment quantities and generate analytical insights. It provides a clean, sortable, color-coded HTML report that highlights trends, averages, and stock performance across all equipment items.

Built using **PHP**, **MySQL**, and **HTML**, the system delivers fast, reliable analytics suitable for assignments, dashboards, or internal reporting tools.

---

## 🚀 Features

### 📊 Inventory & Reporting
- Track quarterly stock levels for each equipment item  
- Compute per-item averages and ranking  
- Identify:
  - Highest & lowest total quarters  
  - Item with highest average  
  - Overall inventory average  
- Alphabetically sorted inventory table  
- Color-coded averages:
  - 🟩 Green for averages ≥ 150  
  - 🟧 Orange for lower averages  

### 🗄️ Database & Structure
- Normalized MySQL schema:
  - `equipment` (id, name)
  - `stock` (equipment_id, quarter, quantity)
- Seed script (`sport.sql`) included to auto-create tables and sample data

---

# 💻 Tech Stack

## 🖥️ Backend
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)

- **PHP 8+** with PDO  
- **MySQL 8+** with FK constraints  

## 🧰 Dev Tools
![VSCode](https://img.shields.io/badge/VS_Code-007ACC?style=for-the-badge&logo=visualstudiocode&logoColor=white)
![SQLTools](https://img.shields.io/badge/SQLTools-5C2D91?style=for-the-badge&logo=datagrip&logoColor=white)

- SQLTools preconfigured in `.vscode/settings.json`  
- Easy local DB testing and browsing  

---

# 🧠 Architecture Overview

## 🎨 Presentation Layer
- `index.php`
- Dynamic HTML table
- Inline styling and color logic

## ⚙️ Business Logic
- Quarterly totals calculation  
- Average per equipment  
- Item ranking  
- Threshold-based color coding  

## 🗄️ Data Access Layer
- PDO connection  
- Queries for `equipment` and `stock` tables  
- Read-only analytics workflow  

---

# 🛠️ Setup Instructions

1. **Create Database + Load Sample Data**
   ```bash
   mysql < sport.sql
   ```

2. **Configure Database Credentials** in `index.php`
   ```php
   $dsn = 'mysql:host=localhost;dbname=sport;charset=utf8';
   $user = 'root';
   $pass = '';
   ```

3. **Run Local PHP Server**
   ```bash
   php -S localhost:8000
   ```

4. Open in browser:  
   👉 http://localhost:8000  

5. (Optional) Use VS Code SQLTools to explore/edit the database.

---

# 📊 Project Stats

| Metric               | Value                         |
|----------------------|-------------------------------|
| 🧑‍💻 Main Language     | PHP                           |
| 🗃️ Database           | MySQL 8+                      |
| 📁 Structure          | Minimal (single-page app)     |
| ⏳ Development Time   | ~1–2 days                     |

---

# 📚 Top Languages Used

![PHP](https://img.shields.io/badge/PHP-85%25-777BB4?style=for-the-badge&logo=php&logoColor=white)
![SQL](https://img.shields.io/badge/SQL-15%25-005C84?style=for-the-badge&logo=mysql&logoColor=white)

---

# 👥 Team Members

- [**Arshia Salehi**](https://github.com/arshiasalehi)
