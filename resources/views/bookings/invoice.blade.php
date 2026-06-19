<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 13px; }
        .page { padding: 40px; }

        /* Header */
        .brand { font-size: 28px; font-weight: 800; color: #1a56db; }
        .brand span { color: #f59e0b; }
        .invoice-meta { text-align: right; }
        .invoice-meta .ref { font-size: 20px; font-weight: 800; color: #1a56db; letter-spacing: 1px; }

        /* Status badge */
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .status-pending    { background: #fef3c7; color: #d97706; }
        .status-confirmed  { background: #d1fae5; color: #065f46; }
        .status-checked_in { background: #e0f2fe; color: #0284c7; }
        .status-cancelled  { background: #fee2e2; color: #991b1b; }
        .status-checked_out{ background: #e0e7ff; color: #3730a3; }

        /* Info grid */
        .stay-item { text-align: center; }

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
    <table style="width: 100%; border-bottom: 3px solid #1a56db; margin-bottom: 30px; padding-bottom: 20px;">
        <tr>
            <td style="vertical-align: top; text-align: left;">
                <div class="brand">Room<span>ora</span></div>
                <div style="font-size:11px;color:#6b7280;margin-top:4px">Hotel Booking & Comparison Platform</div>
            </td>
            <td style="vertical-align: top; text-align: right;" class="invoice-meta">
                <div style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:1px">Tax Invoice</div>
                <div class="ref">{{ $booking->booking_reference }}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:4px">Issued: {{ now()->format('M j, Y') }}</div>
                <div style="margin-top:8px">
                    <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- Info Grid --}}
    <table style="width: 100%; margin: 25px 0; border-spacing: 15px 0; border-collapse: separate; margin-left: -15px; margin-right: -15px;">
        <tr>
            <td style="width: 25%; vertical-align: top; background: #f8fafc; border-radius: 8px; padding: 16px; border-left: 3px solid #1a56db;">
                <h4 style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 8px; margin-top: 0;">Bill To</h4>
                <p style="margin: 3px 0; font-size: 13px;"><strong>{{ $booking->guest_name ?? $booking->user->name }}</strong></p>
                <p style="margin: 3px 0; font-size: 13px;">{{ $booking->guest_email ?? $booking->user->email }}</p>
                <p style="margin: 3px 0; font-size: 13px;">{{ $booking->guest_phone ?? $booking->user->phone ?? '—' }}</p>
            </td>
            <td style="width: 25%; vertical-align: top; background: #f8fafc; border-radius: 8px; padding: 16px; border-left: 3px solid #1a56db;">
                <h4 style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 8px; margin-top: 0;">Account</h4>
                <p style="margin: 3px 0; font-size: 13px;"><strong>Account ID:</strong> {{ $booking->user->id }}</p>
                <p style="margin: 3px 0; font-size: 13px;"><strong>Account Email:</strong> {{ $booking->user->email }}</p>
                <p style="margin: 3px 0; font-size: 13px;"><strong>Registered:</strong> {{ $booking->user->created_at->format('M j, Y') }}</p>
            </td>
            <td style="width: 25%; vertical-align: top; background: #f8fafc; border-radius: 8px; padding: 16px; border-left: 3px solid #1a56db;">
                <h4 style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 8px; margin-top: 0;">Hotel</h4>
                <p style="margin: 3px 0; font-size: 13px;"><strong>{{ $booking->room->hotel->name }}</strong></p>
                <p style="margin: 3px 0; font-size: 13px;">{{ $booking->room->hotel->address }}</p>
                <p style="margin: 3px 0; font-size: 13px;">{{ $booking->room->hotel->city }}</p>
            </td>
            <td style="width: 25%; vertical-align: top; background: #f8fafc; border-radius: 8px; padding: 16px; border-left: 3px solid #1a56db;">
                <h4 style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 8px; margin-top: 0;">Booking Date</h4>
                <p style="margin: 3px 0; font-size: 13px;"><strong>{{ $booking->created_at->format('M j, Y') }}</strong></p>
                <p style="margin: 3px 0; font-size: 13px;">{{ $booking->created_at->format('h:i A') }}</p>
            </td>
        </tr>
    </table>

    {{-- Billing & Payment summary --}}
    <table style="width: 100%; margin-top: 10px; margin-bottom: 15px; border-spacing: 15px 0; border-collapse: separate; margin-left: -15px; margin-right: -15px;">
        <tr>
            <td style="vertical-align: top; background: #f8fafc; padding: 12px; border-radius: 8px; border-left: 3px solid #1a56db;">
                <h4 style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-top:0;margin-bottom:6px">Billing Address</h4>
                <p style="margin: 0;">{{ $booking->user->address ?? '—' }}</p>
            </td>
            <td style="width: 260px; vertical-align: top; background: #f8fafc; padding: 12px; border-radius: 8px; border-left: 3px solid #1a56db;">
                <h4 style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-top:0;margin-bottom:6px">Payment</h4>
                <p style="margin: 0;"><strong>Method:</strong> {{ $booking->payment_method ?? 'N/A' }}</p>
                <p style="margin: 4px 0 0 0;"><strong>Paid:</strong> {{ $booking->is_paid ? 'Yes' : 'No' }}</p>
                @if($booking->is_paid && $booking->paid_at)
                    <p style="margin: 4px 0 0 0; font-size:11px; color:#6b7280;"><strong>Paid At:</strong> {{ $booking->paid_at->format('M j, Y h:i A') }}</p>
                @endif
            </td>
        </tr>
    </table>

    {{-- Stay Details --}}
    <table style="width: 100%; margin: 20px 0; border: 1px solid #e5e7eb; border-radius: 8px; border-collapse: collapse; overflow: hidden;">
        <tr>
            <td style="width: 25%; padding: 16px; text-align: center; border-right: 1px solid #e5e7eb;">
                <div style="font-size: 11px; color: #6b7280; text-transform: uppercase; margin-bottom: 6px;">Check-in</div>
                <div style="font-size: 18px; font-weight: 700; color: #1a56db;">{{ $booking->check_in->format('M j') }}</div>
                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">{{ $booking->check_in->format('Y, l') }}</div>
                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">After {{ $booking->room->hotel->check_in_time }}</div>
            </td>
            <td style="width: 25%; padding: 16px; text-align: center; border-right: 1px solid #e5e7eb;">
                <div style="font-size: 11px; color: #6b7280; text-transform: uppercase; margin-bottom: 6px;">Check-out</div>
                <div style="font-size: 18px; font-weight: 700; color: #1a56db;">{{ $booking->check_out->format('M j') }}</div>
                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">{{ $booking->check_out->format('Y, l') }}</div>
                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">Before {{ $booking->room->hotel->check_out_time }}</div>
            </td>
            <td style="width: 25%; padding: 16px; text-align: center; border-right: 1px solid #e5e7eb;">
                <div style="font-size: 11px; color: #6b7280; text-transform: uppercase; margin-bottom: 6px;">Duration</div>
                <div style="font-size: 18px; font-weight: 700; color: #1a56db;">{{ $booking->nights }}</div>
                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">Night{{ $booking->nights > 1 ? 's' : '' }}</div>
            </td>
            <td style="width: 25%; padding: 16px; text-align: center;">
                <div style="font-size: 11px; color: #6b7280; text-transform: uppercase; margin-bottom: 6px;">Guests</div>
                <div style="font-size: 18px; font-weight: 700; color: #1a56db;">{{ $booking->guests }}</div>
                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">Person{{ $booking->guests > 1 ? 's' : '' }}</div>
            </td>
        </tr>
    </table>

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
                <td>BDT {{ number_format($booking->room_price) }}/night</td>
                <td>{{ $booking->nights }} night{{ $booking->nights > 1 ? 's' : '' }}</td>
                <td>BDT {{ number_format($booking->room_price * $booking->nights) }}</td>
            </tr>
        </tbody>
        <tfoot>
            @if($booking->discount > 0)
            <tr>
                <td colspan="3" style="color:#059669">Discount</td>
                <td style="color:#059669;text-align:right">- BDT {{ number_format($booking->discount) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="3" class="total-label">Total Amount</td>
                <td>BDT {{ number_format($booking->total_price) }}</td>
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
