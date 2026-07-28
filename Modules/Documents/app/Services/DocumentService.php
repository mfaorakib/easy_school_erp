<?php

namespace Modules\Documents\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\AcademicCore\Models\Student;
use Modules\Builder\Models\SiteSetting;
use Modules\Documents\Models\CertificateTemplate;
use Modules\Documents\Models\DocumentIssuance;
use Modules\Documents\Models\IdCardTemplate;
use Modules\Foundation\Models\AcademicYear;
use Modules\Foundation\Models\BaseSetup;
use Modules\Foundation\Support\IdPattern;

/**
 * Resolves a student/staff into the data an ID card or certificate needs, and
 * fills certificate placeholders. Missing fields degrade gracefully to blank.
 */
class DocumentService
{
    /** Absolute URL, a data: URI, or a public-disk path — passed through as-is except the last case. */
    public static function media(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Str::startsWith($value, ['http://', 'https://', '/', 'data:']) ? $value : asset('storage/'.$value);
    }

    public function schoolName(): string
    {
        return SiteSetting::current()->site_name ?: config('app.name');
    }

    public function session(): string
    {
        return (string) optional(AcademicYear::current())->title;
    }

    // ------------------------------------------------------------- ID cards

    /**
     * Normalised card data for one holder.
     * @return array{name:string, subtitle:string, photo:?string, id_label:string, id_no:string, rows:array<int,array{0:string,1:string}>}
     */
    public function idCardData(IdCardTemplate $template, Model $holder): array
    {
        return $template->holder_type === IdCardTemplate::HOLDER_STAFF
            ? $this->staffCard($template, $holder)
            : $this->studentCard($template, $holder);
    }

    private function studentCard(IdCardTemplate $template, Student $student): array
    {
        $record = $student->liveRecord()->with(['schoolClass', 'section'])->first();
        $class = $record ? trim(optional($record->schoolClass)->name.' · '.optional($record->section)->name, ' ·') : '';

        $rows = $this->pickRows($template, [
            ['roll', 'Roll', optional($record)->roll_no],
            ['blood_group', 'Blood', optional(BaseSetup::find($student->blood_group_id))->name],
            ['phone', 'Phone', $student->mobile],
            ['dob', 'DOB', optional($student->date_of_birth)->format('d M Y')],
            ['address', 'Address', $student->current_address],
            ['session', 'Session', $this->session()],
        ]);

        return [
            'name'     => (string) $student->full_name,
            'subtitle' => $class,
            'photo'    => static::media($student->photo),
            'id_label' => $student->unique_id ? 'Student ID' : 'Admission No',
            'id_no'    => (string) ($student->unique_id ?: $student->admission_no),
            'rows'     => $rows,
        ];
    }

    private function staffCard(IdCardTemplate $template, Model $staff): array
    {
        $rows = $this->pickRows($template, [
            ['phone', 'Phone', $staff->mobile],
            ['department', 'Dept', optional($staff->department)->name],
            ['dob', 'DOB', optional($staff->date_of_birth)->format('d M Y')],
            ['address', 'Address', $staff->current_address],
        ]);

        return [
            'name'     => (string) $staff->full_name,
            'subtitle' => (string) optional($staff->designation)->title,
            'photo'    => static::media($staff->photo),
            'id_label' => 'Staff ID',
            'id_no'    => (string) $staff->staff_no,
            'rows'     => $rows,
        ];
    }

    /**
     * Keep only the [key,label,value] triples the template shows and that have a value.
     * @param array<int,array{0:string,1:string,2:mixed}> $candidates
     * @return array<int,array{0:string,1:string}>
     */
    private function pickRows(IdCardTemplate $template, array $candidates): array
    {
        $rows = [];
        foreach ($candidates as [$key, $label, $value]) {
            if ($template->showsField($key) && $value !== null && $value !== '') {
                $rows[] = [$label, (string) $value];
            }
        }

        return $rows;
    }

    // --------------------------------------------------------- Certificates

    /** @return array<int,array{key:string,label:string}> */
    public function placeholders(): array
    {
        return [
            ['key' => 'name', 'label' => 'Holder name'],
            ['key' => 'class', 'label' => 'Class'],
            ['key' => 'section', 'label' => 'Section'],
            ['key' => 'roll', 'label' => 'Roll'],
            ['key' => 'admission_no', 'label' => 'Admission no'],
            ['key' => 'staff_no', 'label' => 'Staff no'],
            ['key' => 'designation', 'label' => 'Designation'],
            ['key' => 'joining_date', 'label' => 'Joining date'],
            ['key' => 'leaving_date', 'label' => 'Leaving date'],
            ['key' => 'duration', 'label' => 'Duration of service'],
            ['key' => 'document_no', 'label' => 'Document no (auto-numbered — only fills if the template has a document prefix set)'],
            ['key' => 'session', 'label' => 'Session'],
            ['key' => 'date', 'label' => "Today's date"],
            ['key' => 'school_name', 'label' => 'School name'],
        ];
    }

    /** Fill {{placeholders}} in the template body for a holder (student or staff, or null). */
    public function resolveCertificate(CertificateTemplate $template, ?Model $holder): string
    {
        $data = $this->placeholderValues($template, $holder);
        $body = (string) $template->body;

        foreach ($data as $key => $value) {
            $body = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], $value, $body);
        }

        return $body;
    }

    public function holderName(?Model $holder): string
    {
        return (string) (optional($holder)->full_name ?? '');
    }

    /**
     * The template's numbered document reference for this holder (e.g. a TC
     * number) — generated once on first use and reused on every reprint, so
     * an official document number never changes underneath someone. Blank
     * for a template with no `document_prefix` set (most certificates don't
     * need a formal number).
     */
    public function documentNumber(CertificateTemplate $template, ?Model $holder): string
    {
        if (! $holder || ! $template->document_prefix) {
            return '';
        }

        $holderType = $holder instanceof Student ? 'student' : 'staff';

        $issuance = DocumentIssuance::firstOrCreate(
            [
                'certificate_template_id' => $template->id,
                'holder_type'             => $holderType,
                'holder_id'               => $holder->id,
            ],
            [
                'document_no' => IdPattern::format(
                    $template->document_prefix.'-{YYYY}-{SEQ:5}',
                    (int) date('Y'),
                    DocumentIssuance::where('certificate_template_id', $template->id)->count() + 1,
                ),
                'issued_at'   => now()->toDateString(),
                'issued_by'   => Auth::id(),
            ],
        );

        return $issuance->document_no;
    }

    /**
     * Render a template's body with SAMPLE placeholder data — used by the
     * "Preview" action so an admin can see how a certificate looks while
     * still editing it, without generating a real numbered document (never
     * touches DocumentIssuance/documentNumber(), unlike resolveCertificate()).
     */
    public function previewBody(CertificateTemplate $template): string
    {
        $sample = [
            'name'         => 'John Doe',
            'class'        => 'Class 5',
            'section'      => 'A',
            'roll'         => '12',
            'admission_no' => 'ADM-2026-0001',
            'staff_no'     => 'STF-0001',
            'designation'  => 'Senior Teacher',
            'joining_date' => now()->subYears(2)->format('d M Y'),
            'leaving_date' => now()->format('d M Y'),
            'duration'     => '2 years',
            'document_no'  => $template->document_prefix ? $template->document_prefix.'-SAMPLE-00001' : '',
            'session'      => $this->session(),
            'date'         => now()->format('d M Y'),
            'school_name'  => $this->schoolName(),
        ];

        $body = (string) $template->body;
        foreach ($sample as $key => $value) {
            $body = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], $value, $body);
        }

        return $body;
    }

    /** A human "X years Y months" span between two dates (or up to today if $to is null). */
    private function duration(?\DateTimeInterface $from, ?\DateTimeInterface $to): string
    {
        if (! $from) {
            return '';
        }

        $diff = \Carbon\Carbon::instance($from)->diff($to ? \Carbon\Carbon::instance($to) : now());
        $parts = [];
        if ($diff->y) {
            $parts[] = $diff->y.' year'.($diff->y > 1 ? 's' : '');
        }
        if ($diff->m) {
            $parts[] = $diff->m.' month'.($diff->m > 1 ? 's' : '');
        }

        return $parts ? implode(' ', $parts) : 'Less than a month';
    }

    /** @return array<string,string> */
    private function placeholderValues(CertificateTemplate $template, ?Model $holder): array
    {
        $base = [
            'session'     => $this->session(),
            'date'        => now()->format('d M Y'),
            'school_name' => $this->schoolName(),
            'name'        => $this->holderName($holder),
            'class' => '', 'section' => '', 'roll' => '',
            'admission_no' => '', 'staff_no' => '', 'designation' => '',
            'joining_date' => '', 'leaving_date' => '', 'duration' => '',
            'document_no' => $this->documentNumber($template, $holder),
        ];

        if ($holder instanceof Student) {
            $record = $holder->liveRecord()->with(['schoolClass', 'section'])->first();
            $base['class'] = (string) optional(optional($record)->schoolClass)->name;
            $base['section'] = (string) optional(optional($record)->section)->name;
            $base['roll'] = (string) optional($record)->roll_no;
            $base['admission_no'] = (string) $holder->admission_no;
        } elseif ($holder) {
            $base['staff_no'] = (string) $holder->staff_no;
            $base['designation'] = (string) optional($holder->designation)->title;
            $base['joining_date'] = $holder->date_of_joining ? $holder->date_of_joining->format('d M Y') : '';
            $base['leaving_date'] = $holder->leaving_date ? $holder->leaving_date->format('d M Y') : '';
            $base['duration'] = $this->duration($holder->date_of_joining, $holder->leaving_date);
        }

        return $base;
    }
}
