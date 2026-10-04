<?php

use App\Controllers\AdminController;
use App\Controllers\AppointmentController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DoctorController;
use App\Controllers\NotificationController;
use App\Controllers\PatientController;
use App\Controllers\ReceptionistController;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

return [
    ['GET', '/health', fn(Request $request): Response => new Response(200, 'OK', ['Content-Type' => 'text/plain; charset=utf-8'])],
    ['GET', '/', [DashboardController::class, 'index']],
    ['GET', '/login', [AuthController::class, 'showLogin']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['POST', '/logout', [AuthController::class, 'logout'], [fn() => AuthMiddleware::requireAuth()]],

    ['GET', '/dashboard', [DashboardController::class, 'dashboard'], [fn() => AuthMiddleware::requireAuth()]],
    ['GET', '/patient/profile', [PatientController::class, 'profile'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['patient'])]],
    ['GET', '/patient/records', [PatientController::class, 'records'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['patient'])]],
    ['GET', '/doctor', [DoctorController::class, 'index'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['doctor'])]],
    ['GET', '/doctor/schedule', [DoctorController::class, 'schedule'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['doctor'])]],
    ['POST', '/doctor/schedule', [DoctorController::class, 'saveSchedule'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['doctor'])]],
    ['POST', '/doctor/appointments/{id}/status', [DoctorController::class, 'updateAppointmentStatus'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['doctor'])]],
    ['GET', '/receptionist', [ReceptionistController::class, 'index'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['receptionist'])]],
    ['POST', '/receptionist/appointments/{id}/check-in', [ReceptionistController::class, 'checkIn'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['receptionist'])]],
    ['GET', '/admin', [AdminController::class, 'index'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['admin'])]],
    ['GET', '/admin/users', [AdminController::class, 'users'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['admin'])]],
    ['POST', '/admin/users', [AdminController::class, 'store'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['admin'])]],
    ['GET', '/admin/reports', [AdminController::class, 'reports'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['admin'])]],

    ['GET', '/notifications', [NotificationController::class, 'index'], [fn() => AuthMiddleware::requireAuth()]],

    ['GET', '/appointments', [AppointmentController::class, 'index'], [fn() => AuthMiddleware::requireAuth()]],
    ['GET', '/appointments/create', [AppointmentController::class, 'create'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['patient'])]],
    ['POST', '/appointments/store', [AppointmentController::class, 'store'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['patient'])]],
    ['POST', '/appointments/{id}/cancel', [AppointmentController::class, 'cancel'], [fn() => AuthMiddleware::requireAuth(), fn() => RoleMiddleware::requireRoles(['patient', 'doctor', 'receptionist', 'admin'])]],
];
