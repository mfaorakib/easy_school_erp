<?php

use Illuminate\Support\Facades\Route;
use Modules\AcademicCore\Http\Controllers\ClassController;
use Modules\AcademicCore\Http\Controllers\ClassTeacherController;
use Modules\AcademicCore\Http\Controllers\DocumentTypeController;
use Modules\AcademicCore\Http\Controllers\PromotionController;
use Modules\AcademicCore\Http\Controllers\SectionController;
use Modules\AcademicCore\Http\Controllers\StudentController;
use Modules\AcademicCore\Http\Controllers\StudentDocumentController;
use Modules\AcademicCore\Http\Controllers\SubjectAssignmentController;
use Modules\AcademicCore\Http\Controllers\SubjectController;

Route::middleware('auth')->prefix('academic')->name('academic.')->group(function () {
    Route::resource('classes', ClassController::class)->except('show')->parameters(['classes' => 'class']);
    Route::resource('sections', SectionController::class)->except('show');
    Route::resource('subjects', SubjectController::class)->except('show');

    Route::get('subject-assignments', [SubjectAssignmentController::class, 'index'])->name('subject-assignments.index');
    Route::post('subject-assignments', [SubjectAssignmentController::class, 'store'])->name('subject-assignments.store');

    Route::get('class-teachers', [ClassTeacherController::class, 'index'])->name('class-teachers.index');
    Route::post('class-teachers', [ClassTeacherController::class, 'store'])->name('class-teachers.store');
    Route::delete('class-teachers/{classTeacher}', [ClassTeacherController::class, 'destroy'])->name('class-teachers.destroy');

    Route::get('students', [StudentController::class, 'index'])->name('students.index');
    Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('students', [StudentController::class, 'store'])->name('students.store');
    Route::get('students/{student}', [StudentController::class, 'show'])
        ->middleware('permission:students.view')->name('students.show');

    Route::get('promotion', [PromotionController::class, 'create'])->name('promotion.create');
    Route::post('promotion', [PromotionController::class, 'store'])->name('promotion.store');

    // Admin-configurable document catalog (drives the public admission form's dynamic upload fields)
    Route::resource('document-types', DocumentTypeController::class)->except('show')->parameters(['document-types' => 'documentType']);

    // Per-student document management (view what was submitted at admission + add more later)
    Route::get('students/{student}/documents', [StudentDocumentController::class, 'index'])->name('students.documents.index');
    Route::post('students/{student}/documents', [StudentDocumentController::class, 'store'])->name('students.documents.store');
    Route::delete('students/{student}/documents/{document}', [StudentDocumentController::class, 'destroy'])->name('students.documents.destroy');
});
