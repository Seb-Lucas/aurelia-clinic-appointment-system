INSERT OR IGNORE INTO doctor_services (doctor_id, service_id, is_active)
SELECT d.id, s.id, 1
FROM doctors d
INNER JOIN users u ON u.id = d.user_id
CROSS JOIN services s
WHERE u.email = 'doctor@example.com'
  AND s.is_active = 1
  AND NOT EXISTS (
    SELECT 1
    FROM doctor_services existing
    WHERE existing.doctor_id = d.id
      AND existing.service_id = s.id
  )
