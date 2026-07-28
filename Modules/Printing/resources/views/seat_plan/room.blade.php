@php
    $room = $rooms[0] ?? null;
    $classroom = $room['classroom'] ?? null;
    $title = 'Seat Plan — '.$exam->name.($classroom ? ' — Room '.$classroom->room_no : '');
@endphp
@extends('documents::print.layout', ['title' => $title])

@section('print-styles')
    .seatp{background:#fff;max-width:800px;margin:24px auto;padding:32px 40px;box-shadow:0 14px 36px -18px rgba(2,6,23,.35);
        border:1px solid #e5e7eb}
    .seatp .top{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #4f46e5;padding-bottom:12px;margin-bottom:16px}
    .seatp .top h2{margin:0;font-size:1.35rem}
    .seatp .top .r{text-align:end;font-size:.9rem;color:#475569;font-weight:700}
    .seatp table{width:100%;border-collapse:collapse;font-size:.9rem}
    .seatp th,.seatp td{border:1px solid #cbd5e1;padding:.5rem .6rem;text-align:center}
    .seatp th{background:#eef2ff;color:#3730a3;font-size:.76rem;text-transform:uppercase;letter-spacing:.03em}
    .seatp td.name{text-align:start;font-weight:600}
@endsection

@section('documents')
@if(!$plan)
    <p style="text-align:center;color:#64748b;padding:40px">{{ __('ui.no_seat_plan') }}</p>
@else
    <div class="seatp">
        <div class="top">
            <h2>{{ $exam->name }}</h2>
            <div class="r">{{ __('ui.classroom') }}: {{ optional($classroom)->room_no }}</div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('ui.bench') }}</th>
                    <th>{{ __('ui.seat') }}</th>
                    <th>Roll No</th>
                    <th style="text-align:start">Student Name</th>
                    <th>Class/Section</th>
                </tr>
            </thead>
            <tbody>
            @forelse(($room['assignments'] ?? []) as $a)
                <tr>
                    <td>{{ $a->bench_no }}</td>
                    <td>{{ $a->seat_no }}</td>
                    <td>{{ $a->roll_no }}</td>
                    <td class="name">{{ optional($a->student)->full_name }}</td>
                    <td>{{ optional($a->schoolClass)->name }}/{{ optional($a->section)->name }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding:20px;color:#64748b">No seats assigned.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endif
@endsection
