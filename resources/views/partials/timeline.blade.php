@if ($updates->isEmpty())
    <p class="text-muted mb-0">No updates yet.</p>
@else
    <ul class="timeline">
        @foreach ($updates as $u)
            <li class="{{ $u->visible_to_customer ? '' : 'internal' }}">
                <div class="fw-semibold">{{ $u->label }}
                    @if (! empty($staffView) && ! $u->visible_to_customer)
                        <span class="badge text-bg-light border ms-1">internal</span>
                    @endif
                    @if (! empty($staffView) && $u->customer_notified)
                        <span class="badge text-bg-light border ms-1" title="Customer emailed">✉ emailed</span>
                    @endif
                </div>
                <div class="when">{{ $u->created_at->format('d M Y, h:i A') }}@if (! empty($staffView) && $u->user) · {{ $u->user->name }}@endif</div>
                @if (empty($staffView) && $u->status?->customer_message)
                    <div class="small text-muted">{{ $u->status->customer_message }}</div>
                @endif
                @if ($u->remarks)
                    <div class="small mt-1" style="white-space: pre-line">{{ $u->remarks }}</div>
                @endif
            </li>
        @endforeach
    </ul>
@endif
