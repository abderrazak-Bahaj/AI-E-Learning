<?php

declare(strict_types=1);

use App\Contracts\CertificateServiceInterface;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Passport\Passport;

uses(LazilyRefreshDatabase::class);

describe('Certificate', function (): void {
    it('student can list their own certificates', function (): void {
        $student = User::factory()->student()->create();
        $course1 = Course::factory()->create();
        $course2 = Course::factory()->create();
        Certificate::factory()->create(['student_id' => $student->id, 'course_id' => $course1->id, 'status' => 'GENERATED']);
        Certificate::factory()->create(['student_id' => $student->id, 'course_id' => $course2->id, 'status' => 'GENERATED']);
        Certificate::factory()->create(['status' => 'GENERATED']); // other student
        Passport::actingAs($student);

        $this->getJson('/api/v1/certificates')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');
    });

    it('student can view their own certificate', function (): void {
        $student = User::factory()->student()->create();
        $cert = Certificate::factory()->create([
            'student_id' => $student->id,
            'status' => 'GENERATED',
        ]);
        Passport::actingAs($student);

        $this->getJson("/api/v1/certificates/{$cert->id}")
            ->assertSuccessful()
            ->assertJsonPath('data.id', $cert->id);
    });

    it('student cannot view another student certificate', function (): void {
        $student = User::factory()->student()->create();
        $cert = Certificate::factory()->create(['status' => 'GENERATED']);
        Passport::actingAs($student);

        $this->getJson("/api/v1/certificates/{$cert->id}")
            ->assertForbidden();
    });

    it('download generates and returns a PDF', function (): void {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        $cert = Certificate::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'PENDING',
        ]);
        Passport::actingAs($student);

        // Mock CertificateService to avoid actual PDF generation in tests
        $this->mock(CertificateServiceInterface::class, function ($mock): void {
            $tmpFile = tempnam(sys_get_temp_dir(), 'cert_test_').'.pdf';
            file_put_contents($tmpFile, '%PDF-1.4 fake pdf content');
            $mock->shouldReceive('pdfPath')->once()->andReturn($tmpFile);
        });

        $this->getJson("/api/v1/certificates/{$cert->id}/download")
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'application/pdf');
    });

    it('unauthenticated user cannot access certificates', function (): void {
        $this->getJson('/api/v1/certificates')->assertUnauthorized();
    });
});
