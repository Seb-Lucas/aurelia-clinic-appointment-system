<?php

namespace Tests;

use App\Controllers\PatientController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class MedicalRecordsTest extends TestCase
{
    public function testPatientRecordsPageRendersMedicalRecordsSection(): void
    {
        Session::set('user', [
            'id' => 1,
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'role' => 'patient',
            'status' => 'active',
        ]);

        $response = (new PatientController())->records(new Request('GET', '/patient/records', [], [], $_SERVER));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('Medical Records', $response->content);
    }
}
