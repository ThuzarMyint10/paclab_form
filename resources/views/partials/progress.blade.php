@php $current = $enquiry->progressIndex(); @endphp
@if ($current < 0)
    <div class="alert alert-secondary mb-0">
        <strong>{{ $enquiry->statusLabel() }}.</strong> This enquiry is closed and no further action will be taken.
    </div>
@else
    <div class="progress-steps">
        @foreach (\App\Models\Enquiry::PROGRESS_STEPS as $key => $label)
            @php $i = $loop->index; @endphp
            <div class="step {{ $i < $current || ($i === $current && in_array($enquiry->status, ['reported', 'dispatched', 'closed'])) ? 'done' : ($i === $current ? 'current' : '') }}">
                <div class="dot">{{ $i < $current ? '✓' : $loop->iteration }}</div>
                <div>{{ $label }}</div>
            </div>
        @endforeach
    </div>
    @if ($enquiry->status === 'on_hold')
        <div class="alert alert-warning py-2 mb-0 mt-2">This job is currently <strong>on hold</strong>. Our team will contact you.</div>
    @endif
@endif
