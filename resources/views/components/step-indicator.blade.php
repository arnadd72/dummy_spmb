{{-- Step Indicator Component --}}
@php
// Delapan langkah pendaftaran, disusun mengikuti bagian-bagian formulir resmi
// SPMB 2027/2028 (Bagian A = data diri, B–D = pendidikan/kesehatan/lain-lain).
$steps = [
    1 => 'Persetujuan',
    2 => 'Dokumen',
    3 => 'Rapor',
    4 => 'Jurusan',
    5 => 'Data Diri',
    6 => 'Orang Tua',
    7 => 'B–D',
    8 => 'Review',
];
@endphp

<nav class="step-indicator" aria-label="Progres pendaftaran">
    @foreach ($steps as $num => $label)
        @php
            $isActive    = $num === $currentStep;
            $isCompleted = $num < $currentStep;
            $state = $isActive ? 'active' : ($isCompleted ? 'completed' : '');
        @endphp
        <div class="step-item {{ $state }}"
             aria-current="{{ $isActive ? 'step' : 'false' }}"
             aria-label="Langkah {{ $num }}: {{ $label }}{{ $isCompleted ? ' (selesai)' : '' }}{{ $isActive ? ' (saat ini)' : '' }}">
            <div class="step-circle">
                @if ($isCompleted)
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                @else
                    {{ $num }}
                @endif
            </div>
            <span class="step-label">{{ $label }}</span>
        </div>
    @endforeach
</nav>
