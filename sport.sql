CREATE DATABASE IF NOT EXISTS sport;
USE sport;

-- Normalized tables for equipment and quarterly stock.
DROP TABLE IF EXISTS stock;
DROP TABLE IF EXISTS equipment;

CREATE TABLE equipment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(64) NOT NULL UNIQUE
);

CREATE TABLE stock (
  equipment_id INT NOT NULL,
  quarter VARCHAR(3) NOT NULL,
  quantity INT NOT NULL,
  PRIMARY KEY (equipment_id, quarter),
  CONSTRAINT fk_stock_equipment FOREIGN KEY (equipment_id)
    REFERENCES equipment(id) ON DELETE CASCADE
);

-- Seed equipment names.
INSERT INTO equipment (name) VALUES
  ('Soccer Balls'),
  ('Tennis Rackets'),
  ('Basketballs'),
  ('Yoga Mats'),
  ('Dumbbells'),
  ('Skipping Ropes');

-- Seed quarterly stock values.
INSERT INTO stock (equipment_id, quarter, quantity) VALUES
  ((SELECT id FROM equipment WHERE name = 'Soccer Balls'), 'Q1', 150),
  ((SELECT id FROM equipment WHERE name = 'Soccer Balls'), 'Q2', 140),
  ((SELECT id FROM equipment WHERE name = 'Soccer Balls'), 'Q3', 160),
  ((SELECT id FROM equipment WHERE name = 'Soccer Balls'), 'Q4', 335),

  ((SELECT id FROM equipment WHERE name = 'Tennis Rackets'), 'Q1', 80),
  ((SELECT id FROM equipment WHERE name = 'Tennis Rackets'), 'Q2', 75),
  ((SELECT id FROM equipment WHERE name = 'Tennis Rackets'), 'Q3', 85),
  ((SELECT id FROM equipment WHERE name = 'Tennis Rackets'), 'Q4', 82),

  ((SELECT id FROM equipment WHERE name = 'Basketballs'), 'Q1', 120),
  ((SELECT id FROM equipment WHERE name = 'Basketballs'), 'Q2', 115),
  ((SELECT id FROM equipment WHERE name = 'Basketballs'), 'Q3', 125),
  ((SELECT id FROM equipment WHERE name = 'Basketballs'), 'Q4', 130),

  ((SELECT id FROM equipment WHERE name = 'Yoga Mats'), 'Q1', 200),
  ((SELECT id FROM equipment WHERE name = 'Yoga Mats'), 'Q2', 190),
  ((SELECT id FROM equipment WHERE name = 'Yoga Mats'), 'Q3', 210),
  ((SELECT id FROM equipment WHERE name = 'Yoga Mats'), 'Q4', 205),

  ((SELECT id FROM equipment WHERE name = 'Dumbbells'), 'Q1', 95),
  ((SELECT id FROM equipment WHERE name = 'Dumbbells'), 'Q2', 88),
  ((SELECT id FROM equipment WHERE name = 'Dumbbells'), 'Q3', 92),
  ((SELECT id FROM equipment WHERE name = 'Dumbbells'), 'Q4', 98),

  ((SELECT id FROM equipment WHERE name = 'Skipping Ropes'), 'Q1', 250),
  ((SELECT id FROM equipment WHERE name = 'Skipping Ropes'), 'Q2', 240),
  ((SELECT id FROM equipment WHERE name = 'Skipping Ropes'), 'Q3', 260),
  ((SELECT id FROM equipment WHERE name = 'Skipping Ropes'), 'Q4', 245);
