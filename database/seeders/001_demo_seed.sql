INSERT INTO departments (name, description) VALUES
  ('General Medicine', 'Primary care and patient assessment'),
  ('Cardiology', 'Cardiac care and evaluation');

INSERT INTO users (name, email, password_hash, role, status, phone) VALUES
  ('Patient User', 'patient@example.com', '$2y$10$YB8NCcjl2GFkio.GK7L75.sphR34ySi1UfimD4UOhfshwyV23IxBG', 'patient', 'active', '5551110001'),
  ('Doctor User', 'doctor@example.com', '$2y$10$YB8NCcjl2GFkio.GK7L75.sphR34ySi1UfimD4UOhfshwyV23IxBG', 'doctor', 'active', '5552220002'),
  ('Reception User', 'reception@example.com', '$2y$10$YB8NCcjl2GFkio.GK7L75.sphR34ySi1UfimD4UOhfshwyV23IxBG', 'receptionist', 'active', '5553330003'),
  ('Admin User', 'admin@example.com', '$2y$10$YB8NCcjl2GFkio.GK7L75.sphR34ySi1UfimD4UOhfshwyV23IxBG', 'admin', 'active', '5554440004');

INSERT INTO patients (user_id, date_of_birth, phone, address) VALUES
  (1, '1990-05-01', '5551110001', '123 Wellness Ave');

INSERT INTO doctors (user_id, department_id, specialty, bio, status) VALUES
  (2, 1, 'General Medicine', 'Experienced physician with a calm, patient-centered approach.', 'active');

INSERT INTO staff (user_id, department_id, role_title, status) VALUES
  (3, 1, 'Reception Supervisor', 'active');

INSERT INTO services (name, description, duration_minutes, price, is_active) VALUES
  ('Consultation', 'General clinical consultation', 30, 75, 1),
  ('Follow-up', 'Follow-up assessment', 20, 50, 1),
  ('Check-up', 'Routine health check', 45, 95, 1);

INSERT INTO doctor_services (doctor_id, service_id, is_active) VALUES
  (1, 1, 1),
  (1, 2, 1),
  (1, 3, 1);

INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, is_active) VALUES
  (1, 1, '09:00', '17:00', 1),
  (1, 2, '09:00', '17:00', 1),
  (1, 3, '09:00', '17:00', 1),
  (1, 4, '09:00', '17:00', 1),
  (1, 5, '09:00', '13:00', 1);

INSERT INTO notifications (user_id, type, message, is_read) VALUES
  (1, 'appointment', 'Your appointment reminder is ready.', 0),
  (2, 'system', 'New schedule update posted.', 0);
