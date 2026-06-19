<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Mail\BookingConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show booking form for a room.
     */
    public function create(Request $request, Room $room)
    {
        abort_unless($room->is_available, 404);

        $checkIn  = $request->get('check_in', today()->addDay()->format('Y-m-d'));
        $checkOut = $request->get('check_out', today()->addDays(2)->format('Y-m-d'));
        $guests   = $request->get('guests', 1);

        // Validate dates are available
        $isAvailable = $room->isAvailableForDates($checkIn, $checkOut);
        $nights      = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
        $totalPrice  = $room->calculateTotalPrice($checkIn, $checkOut);

        return view('bookings.create', compact(
            'room', 'checkIn', 'checkOut', 'guests', 'isAvailable', 'nights', 'totalPrice'
        ));
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id'          => ['required', 'exists:rooms,id'],
            'check_in'         => ['required', 'date', 'after:today'],
            'check_out'        => ['required', 'date', 'after:check_in'],
            'guests'           => ['required', 'integer', 'min:1', 'max:10'],
            'guest_name'       => ['required', 'string', 'max:100'],
            'guest_email'      => ['required', 'email', 'max:255'],
            'guest_phone'      => ['required', 'string', 'max:20'],
            'special_requests' => ['nullable', 'string', 'max:500'],
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Security: Check capacity
        if ($validated['guests'] > $room->capacity) {
            return back()->withErrors(['guests' => 'Number of guests exceeds room capacity.']);
        }

        $booking = DB::transaction(function () use ($validated, $room) {
            $lockedRoom = Room::query()
                ->whereKey($room->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedRoom->isAvailableForDates($validated['check_in'], $validated['check_out'])) {
                throw ValidationException::withMessages([
                    'check_in' => 'This room is not available for the selected dates.',
                ]);
            }

            $nights = Carbon::parse($validated['check_in'])->diffInDays($validated['check_out']);
            $totalPrice = round($lockedRoom->price * $nights, 2);

            return Booking::create([
                'user_id'          => Auth::id(),
                'room_id'          => $lockedRoom->id,
                'check_in'         => $validated['check_in'],
                'check_out'        => $validated['check_out'],
                'nights'           => $nights,
                'guests'           => $validated['guests'],
                'room_price'       => $lockedRoom->price,
                'total_price'      => $totalPrice,
                // Booking created as pending until payment is completed
                'status'           => 'pending',
                'guest_name'       => strip_tags($validated['guest_name']),
                'guest_email'      => $validated['guest_email'],
                'guest_phone'      => $validated['guest_phone'],
                'special_requests' => strip_tags($validated['special_requests'] ?? ''),
            ]);
        });

        // Redirect to booking page and instruct user to complete payment
        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking created. Please complete payment to confirm your reservation. Reference: ' . $booking->booking_reference);
    }

    /**
     * Show booking details.
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['room.hotel', 'user']);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, Booking $booking)
    {
        $this->authorize('cancel', $booking);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if (!$booking->canBeCancelled()) {
            return back()->withErrors(['status' => 'This booking cannot be cancelled.']);
        }

        $booking->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => strip_tags($request->reason ?? 'Cancelled by user'),
        ]);

        return redirect()->route('profile.bookings')
            ->with('success', 'Booking ' . $booking->booking_reference . ' has been cancelled.');
    }

    /**
     * Download PDF invoice.
     */
    public function downloadInvoice(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['room.hotel', 'user']);

        // Only allow invoice download after payment/confirmation
        if (! $booking->is_paid && $booking->status !== 'confirmed') {
            abort(403, 'Invoice is available after payment and confirmation.');
        }

        // Mark as downloaded
        $booking->update(['invoice_downloaded' => true]);

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            abort(500, 'PDF invoice generation is unavailable. Install barryvdh/laravel-dompdf and run composer install.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bookings.invoice', compact('booking'));
        $pdf->setPaper('A4');

        return $pdf->download('invoice-' . $booking->booking_reference . '.pdf');
    }

    /**
     * Confirm payment for a booking (called after payment gateway callback or manual pay).
     */
    public function pay(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        if ($booking->is_paid || $booking->status === Booking::STATUS_CANCELLED) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This booking has already been paid or is cancelled.'], 422);
            }
            return back()->with('error', 'This booking has already been paid or is cancelled.')->withErrors(['status' => 'This booking has already been paid or is cancelled.']);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'string', 'max:100'],
            'billing_address' => ['nullable', 'string', 'max:1000'],
        ]);

        // Optional: accept guest details payload when provided
        $guestDetails = $request->input('guest_details');
        if ($guestDetails && is_array($guestDetails)) {
            // Basic validation and enforcement: require NID for each guest when guests > 1
            $sanitized = collect($guestDetails)->map(function ($g) {
                return [
                    'name' => isset($g['name']) ? strip_tags($g['name']) : null,
                    'email' => isset($g['email']) ? filter_var($g['email'], FILTER_VALIDATE_EMAIL) ? $g['email'] : null : null,
                    'phone' => isset($g['phone']) ? strip_tags($g['phone']) : null,
                    'nid' => isset($g['nid']) ? strip_tags($g['nid']) : null,
                ];
            })->values()->all();

            if ($booking->guests > 1) {
                $missing = collect($sanitized)->filter(function ($g) {
                    return empty($g['nid']);
                })->count();
                if ($missing > 0) {
                    throw ValidationException::withMessages(['guest_details' => 'NID is required for each guest when number of guests is more than one.']);
                }
            }

            $booking->guest_details = $sanitized;
            $booking->save();
        }

        DB::transaction(function () use ($request, $booking, $data) {
            $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($lockedBooking->is_paid || $lockedBooking->status === Booking::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'status' => 'This booking has already been paid or is cancelled.',
                ]);
            }

            // Record the payment
            $lockedBooking->payments()->create([
                'amount' => $lockedBooking->total_price,
                'currency' => 'BDT',
                'method' => $data['payment_method'],
                'gateway' => $request->get('gateway', 'manual'),
                'transaction_id' => $request->get('transaction_id'),
                'status' => 'success',
                'metadata' => $request->except(['_token', 'payment_method', 'billing_address', 'transaction_id']),
                'paid_at' => now(),
            ]);

            // Update booking status
            $lockedBooking->update([
                'payment_method' => $data['payment_method'],
                'billing_address' => $data['billing_address'] ?? $lockedBooking->billing_address,
                'is_paid' => true,
                'paid_at' => now(),
                'status' => Booking::STATUS_CONFIRMED,
            ]);
        });

        // Refresh model relations/data for emails
        $booking->refresh();

        // Send confirmation email to guest and admin
        try {
            Mail::to($booking->guest_email)->send(new BookingConfirmation($booking));
        } catch (\Exception $e) {
            logger()->error('Booking email failed: ' . $e->getMessage());
        }

        $adminEmail = env('ADMIN_EMAIL', config('mail.from.address'));
        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new BookingConfirmation($booking));
            } catch (\Exception $e) {
                logger()->error('Admin booking email failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('bookings.show', $booking)->with('success', 'Payment received. Booking confirmed.');
    }

    /**
     * Update guest details for a booking.
     */
    public function updateGuests(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $data = $request->validate([
            'guests' => ['required', 'integer', 'min:1', 'max:20'],
            'guest_details' => ['nullable', 'array'],
            'guest_details.*.name' => ['nullable', 'string', 'max:100'],
            'guest_details.*.email' => ['nullable', 'email', 'max:255'],
            'guest_details.*.phone' => ['nullable', 'string', 'max:20'],
            'guest_details.*.nid' => ['nullable', 'string', 'max:50'],
        ]);

        $details = $data['guest_details'] ?? [];

            $sanitized = collect($details)->map(function ($g) {
            return [
                'name' => isset($g['name']) ? strip_tags($g['name']) : null,
                'email' => isset($g['email']) ? $g['email'] : null,
                'phone' => isset($g['phone']) ? strip_tags($g['phone']) : null,
                'nid' => isset($g['nid']) ? strip_tags($g['nid']) : null,
            ];
        })->values()->all();

        // If multiple guests, ensure each guest has a nid
        if ($data['guests'] > 1) {
            $missing = collect($sanitized)->filter(function ($g) {
                return empty($g['nid']);
            })->count();
            if ($missing > 0) {
                return back()->withErrors(['guest_details' => 'NID is required for each guest when number of guests is more than one.']);
            }
        }

        $booking->update(['guest_details' => $sanitized, 'guests' => $data['guests']]);

        return redirect()->route('bookings.show', $booking)->with('success', 'Guest details updated.');
    }

    /**
     * Check room availability (AJAX).
     */
    public function checkAvailability(Request $request, Room $room)
    {
        $request->validate([
            'check_in'  => ['required', 'date', 'after:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
        ]);

        $available  = $room->isAvailableForDates($request->check_in, $request->check_out);
        $nights     = Carbon::parse($request->check_in)->diffInDays($request->check_out);
        $total      = $room->calculateTotalPrice($request->check_in, $request->check_out);

        return response()->json([
            'available'   => $available,
            'nights'      => $nights,
            'price_night' => $room->price,
            'total_price' => $total,
            'message'     => $available
                ? 'Room is available for selected dates.'
                : 'Sorry, this room is not available for selected dates.',
        ]);
    }
}
