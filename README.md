🏅 Sports Equipment Inventory Management

Sports Equipment Inventory Management is a lightweight PHP & MySQL web application for tracking quarterly sports equipment quantities.
It analyzes stock levels, computes averages, highlights shortages, and displays everything in a clean, sortable, color-coded HTML report.

⸻

🚀 Features

📊 Inventory & Analytics
	•	Track equipment quantities across four quarters
	•	View highest/lowest total stock by quarter
	•	Identify item with highest average
	•	See overall inventory average
	•	Per-item:
	•	Quarterly totals
	•	Average quantity
	•	Ranking list (highest → lowest)

🎨 UI & Usability
	•	Clean HTML report
	•	Alphabetical sorting
	•	Color-coded averages:
	•	🟩 Green = average ≥ 150
	•	🟧 Orange = below threshold
	•	Fully generated dynamically from database

⸻

💻 Tech Stack

🖥️ Backend
	•	PHP 8+ with PDO (MySQL driver)
	•	MySQL 8+ database (normalized schema)
	•	Foreign key constraints & clean relational structure

🧰 Developer Tools
	•	VS Code SQLTools config included for easy DB access
	•	Prebuilt seed script for instant setup

⸻

🧠 Architecture Overview

🗄️ Database Schema

equipment
	•	id (PK)
	•	name

stock
	•	equipment_id (FK → equipment.id)
	•	quarter (Q1–Q4)
	•	quantity

Seed file: sport.sql

⸻

⚙️ Application Flow
	1.	PHP connects to MySQL using PDO
	2.	Queries fetch equipment & stock totals
	3.	Code computes:
	•	Quarterly totals
	•	Averages
	•	Highest/lowest metrics
	4.	HTML template renders:
	•	Main summary
	•	Full inventory table
	•	Ranked averages

