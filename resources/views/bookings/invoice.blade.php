<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 13px; }
        .page { padding: 40px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #1a56db; }
        .brand { font-size: 28px; font-weight: 800; color: #1a56db; }
        .brand span { color: #f59e0b; }
        .invoice-meta { text-align: right; }
        .invoice-meta .ref { font-size: 20px; font-weight: 800; color: #1a56db; letter-spacing: 1px; }

        /* Status badge */
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .status-confirmed  { background: #d1fae5; color: #065f46; }
        .status-cancelled  { background: #fee2e2; color: #991b1b; }
        .status-checked_out{ background: #e0e7ff; color: #3730a3; }

        /* Info grid */
        .info-grid { display: flex; gap: 30px; margin: 25px 0; }
        .info-box { flex: 1; background: #f8fafc; border-radius: 8px; padding: 16px; border-left: 3px solid #1a56db; }
        .info-box h4 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 8px; }
        .info-box p { margin: 3px 0; font-size: 13px; }

        /* Stay details */
        .stay-grid { display: flex; gap: 0; margin: 20px 0; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        .stay-item { flex: 1; padding: 16px; text-align: center; border-right: 1px solid #e5e7eb; }
        .stay-item:last-child { border-right: none; }
        .stay-item .label { font-size: 11px; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; }
        .stay-item .value { font-size: 18px; font-weight: 700; color: #1a56db; }
        .stay-item .sub { font-size: 11px; color: #9ca3af; }

        /* Room info */
        .room-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .room-box h3 { font-size: 16px; font-weight: 700; color: #1e40af; margin-bottom: 6px; }

        /* Price table */
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table thead th { background: #0f172a; color: #fff; padding: 10px 14px; font-size: 12px; text-align: left; }
        table thead th:last-child { text-align: right; }
        table tbody td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; }
        table tbody td:last-child { text-align: right; font-weight: 600; }
        table tfoot td { padding: 10px 14px; font-weight: 700; font-size: 14px; border-top: 2px solid #e5e7eb; }
        table tfoot .total-label { color: #1a56db; }
        table tfoot td:last-child { text-align: right; color: #1a56db; font-size: 16px; }

        /* Footer */
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 11px; }
        .watermark { color: #f1f5f9; font-size: 80px; font-weight: 900; text-align: center; margin: -30px 0; opacity: 0.3; letter-spacing: -4px; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div>
            <div class="brand">Room<span>ora</span></div>
            <div style="font-size:11px;color:#6b7280;margin-top:4px">Hotel Booking & Comparison Platform</div>
        </div>
        <div class="invoice-meta">
            <div style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:1px">Tax Invoice</div>
            <div class="ref">{{ $booking->booking_reference }}</div>
            <div style="font-size:12px;color:#6b7280;margin-top:4px">Issued: {{ now()->format('M j, Y') }}</div>
            <div style="margin-top:8px">
                <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
            </div>
        </div>
    </div>

    {{-- Info Grid --}}
    <div class="info-grid">
        <div class="info-box">
            <h4>Bill To</h4>
            <p><strong>{{ $booking->guest_name ?? $booking->user->name }}</strong></p>
            <p>{{ $booking->guest_email ?? $booking->user->email }}</p>
            <p>{{ $booking->guest_phone ?? $booking->user->phone ?? '—' }}</p>
        </div>

        <div class="info-box">
            <h4>Account</h4>
            <p><strong>Account ID:</strong> {{ $booking->user->id }}</p>
            <p><strong>Account Email:</strong> {{ $booking->user->email }}</p>
            <p><strong>Registered:</strong> {{ $booking->user->created_at->format('M j, Y') }}</p>
        </div>

        <div class="info-box">
            <h4>Hotel</h4>
            <p><strong>{{ $booking->room->hotel->name }}</strong></p>
            <p>{{ $booking->room->hotel->address }}</p>
            <p>{{ $booking->room->hotel->city }}</p>
        </div>

        <div class="info-box">
            <h4>Booking Date</h4>
            <p><strong>{{ $booking->created_at->format('M j, Y') }}</strong></p>
            <p>{{ $booking->created_at->format('h:i A') }}</p>
        </div>
    </div>

    {{-- Billing & Payment summary --}}
    <div style="display:flex;gap:20px;margin-top:10px;">
        <div style="flex:1;background:#f8fafc;padding:12px;border-radius:8px;border-left:3px solid #1a56db;">
            <h4 style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:1px">Billing Address</h4>
            <p style="margin-top:6px">{{ $booking->user->address ?? '—' }}</p>
        </div>
        <div style="width:260px;background:#f8fafc;padding:12px;border-radius:8px;border-left:3px solid #1a56db;">
            <h4 style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:1px">Payment</h4>
            <p style="margin-top:6px"><strong>Method:</strong> {{ $booking->payment_method ?? 'N/A' }}</p>
            <p style="margin-top:4px"><strong>Paid:</strong> {{ $booking->status === 'confirmed' ? 'Yes' : 'No' }}</p>
        </div>
    </div>

    {{-- Stay Details --}}
    <div class="stay-grid">
        <div class="stay-item">
            <div class="label">Check-in</div>
            <div class="value">{{ $booking->check_in->format('M j') }}</div>
            <div class="sub">{{ $booking->check_in->format('Y, l') }}</div>
            <div class="sub">After {{ $booking->room->hotel->check_in_time }}</div>
        </div>
        <div class="stay-item">
            <div class="label">Check-out</div>
            <div class="value">{{ $booking->check_out->format('M j') }}</div>
            <div class="sub">{{ $booking->check_out->format('Y, l') }}</div>
            <div class="sub">Before {{ $booking->room->hotel->check_out_time }}</div>
        </div>
        <div class="stay-item">
            <div class="label">Duration</div>
            <div class="value">{{ $booking->nights }}</div>
            <div class="sub">Night{{ $booking->nights > 1 ? 's' : '' }}</div>
        </div>
        <div class="stay-item">
            <div class="label">Guests</div>
            <div class="value">{{ $booking->guests }}</div>
            <div class="sub">Person{{ $booking->guests > 1 ? 's' : '' }}</div>
        </div>
    </div>
        @if(($booking->guests ?? 1) > 1)
        <div style="margin-top:16px">
            <h4 style="font-size:13px;color:#374151;margin-bottom:8px">Guest Information</h4>
            @if(!empty($booking->guest_details) && is_array($booking->guest_details))
                <table style="width:100%;border-collapse:collapse;margin-bottom:8px">
                    <thead>
                        <tr>
                            <th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e5e7eb">#</th>
                            <th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e5e7eb">Name</th>
                            <th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e5e7eb">Email</th>
                            <th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e5e7eb">Phone</th>
                            <th style="text-align:left;padding:6px 8px;border-bottom:1px solid #e5e7eb">NID</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($booking->guest_details as $i => $g)
                        <tr>
                            <td style="padding:6px 8px">{{ $i + 1 }}</td>
                            <td style="padding:6px 8px">{{ $g['name'] ?? '—' }}</td>
                            <td style="padding:6px 8px">{{ $g['email'] ?? '—' }}</td>
                            <td style="padding:6px 8px">{{ $g['phone'] ?? '—' }}</td>
                            <td style="padding:6px 8px">{{ $g['nid'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p style="color:#6b7280">Total guests: {{ $booking->guests }} (no individual details provided)</p>
            @endif
        </div>
        @endif

    {{-- Room Info --}}
    <div class="room-box">
        <h3>{{ $booking->room->type_name }} Room</h3>
        <p style="color:#374151">
            Capacity: up to {{ $booking->room->capacity }} guests
            @if($booking->room->size_sqm) · {{ $booking->room->size_sqm }} m² @endif
        </p>
        @if($booking->room->facilities)
        <p style="color:#6b7280;font-size:11px;margin-top:4px">
            Facilities: {{ implode(' · ', $booking->room->facilities) }}
        </p>
        @endif
    </div>

    {{-- Price Table --}}
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Rate</th>
                <th>Quantity</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $booking->room->type_name }} — {{ $booking->room->hotel->name }}</strong><br>
                    <span style="color:#6b7280;font-size:11px">
                        {{ $booking->check_in->format('M j, Y') }} → {{ $booking->check_out->format('M j, Y') }}
                    </span>
                </td>
                <td>৳{{ number_format($booking->room_price) }}/night</td>
                <td>{{ $booking->nights }} night{{ $booking->nights > 1 ? 's' : '' }}</td>
                <td>৳{{ number_format($booking->room_price * $booking->nights) }}</td>
            </tr>
        </tbody>
        <tfoot>
            @if($booking->discount > 0)
            <tr>
                <td colspan="3" style="color:#059669">Discount</td>
                <td style="color:#059669;text-align:right">- ৳{{ number_format($booking->discount) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="3" class="total-label">Total Amount</td>
                <td>৳{{ number_format($booking->total_price) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>Thank you for choosing Roomora. For support: support@roomora.com</p>
        <p style="margin-top:4px">This is a computer-generated invoice. No signature required.</p>
        <p style="margin-top:4px">© {{ date('Y') }} Roomora. All rights reserved.</p>
    </div>

</div>
</body>
</html>
