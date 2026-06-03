<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 20px; }
        .email-card { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .header { background: #0f172a; padding: 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 1.8rem; }
        .header span { color: #f59e0b; }
        .body { padding: 30px; }
        .ref-box { background: #f1f5f9; border-radius: 8px; padding: 16px; text-align: center; margin: 20px 0; }
        .ref-box .ref { font-size: 1.5rem; font-weight: 700; color: #1a56db; letter-spacing: 2px; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #64748b; font-size: .9rem; }
        .value { font-weight: 600; }
        .total { background: #1a56db; color: #fff; padding: 16px; border-radius: 8px; margin: 20px 0; text-align: center; font-size: 1.2rem; font-weight: 700; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; color: #94a3b8; font-size: .8rem; }
    </style>
</head>
<body>
<div class="email-card">
    <div class="header">
        <h1>Room<span>ora</span></h1>
        <p style="color:#94a3b8;margin:8px 0 0">Booking Confirmation</p>
    </div>

    <div class="body">
        <p>Dear <strong>{{ $booking->guest_name }}</strong>,</p>
        <p>Your booking has been confirmed! Here are your booking details:</p>

        <div class="ref-box">
            <div style="color:#64748b;font-size:.85rem;margin-bottom:4px">Booking Reference</div>
            <div class="ref">{{ $booking->booking_reference }}</div>
        </div>

        <div class="detail-row">
            <span class="label">Hotel</span>
            <span class="value">{{ $booking->room->hotel->name }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Room Type</span>
            <span class="value">{{ ucfirst($booking->room->room_type) }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Check-in</span>
            <span class="value">{{ $booking->check_in->format('D, M j, Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Check-out</span>
            <span class="value">{{ $booking->check_out->format('D, M j, Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Duration</span>
            <span class="value">{{ $booking->nights }} night{{ $booking->nights > 1 ? 's' : '' }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Guests</span>
            <span class="value">{{ $booking->guests }}</span>
        </div>

        <div class="total">
            Total: ৳{{ number_format($booking->total_price) }}
        </div>

        <p style="color:#64748b;font-size:.9rem">
            Need help? Contact us at <a href="mailto:support@roomora.com">support@roomora.com</a>
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Roomora. All rights reserved.</p>
    </div>
</div>
</body>
</html>
