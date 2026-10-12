<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HostelApiController extends Controller
{
    /**
     * Remedial bed type constant (bed_type = 3 marks Remedial Student beds).
     */
    private const REMEDIAL_BED_TYPE = 3;

    /**
     * Summary + available halls for remedial students.
     */
    public function overview(Request $request)
    {
        $gender = strtoupper(trim((string) $request->input('gender', '')));

        $halls = DB::table('hostel')
            ->select('hall')
            ->where('bed_type', self::REMEDIAL_BED_TYPE)
            ->where('status', 0)
            ->where('flag', 0)
            ->when($gender !== '', fn ($q) => $q->where('gender', $gender))
            ->groupBy('hall')
            ->orderBy('hall', 'asc')
            ->get()
            ->map(fn ($row) => $row->hall);

        $availableBeds = DB::table('hostel')
            ->where('bed_type', self::REMEDIAL_BED_TYPE)
            ->where('status', 0)
            ->where('flag', 0)
            ->when($gender !== '', fn ($q) => $q->where('gender', $gender))
            ->count();

        $reservedBeds = DB::table('hostel')
            ->where('bed_type', self::REMEDIAL_BED_TYPE)
            ->where('status', 1)
            ->where('flag', 0)
            ->when($gender !== '', fn ($q) => $q->where('gender', $gender))
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'gender' => $gender !== '' ? $gender : null,
                'available_beds' => $availableBeds,
                'reserved_beds' => $reservedBeds,
                'halls' => $halls,
            ],
        ]);
    }

    /**
     * Available blocks within a hall.
     */
    public function blocks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hall' => 'required|string',
            'gender' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $gender = strtoupper(trim((string) $request->input('gender', '')));

        $blocks = DB::table('hostel')
            ->select('block')
            ->where('hall', $request->hall)
            ->where('bed_type', self::REMEDIAL_BED_TYPE)
            ->where('status', 0)
            ->where('flag', 0)
            ->when($gender !== '', fn ($q) => $q->where('gender', $gender))
            ->groupBy('block')
            ->orderBy('block', 'asc')
            ->get()
            ->map(fn ($row) => $row->block);

        return response()->json(['success' => true, 'data' => $blocks]);
    }

    /**
     * Available rooms within a hall + block.
     */
    public function rooms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hall' => 'required|string',
            'block' => 'required|string',
            'gender' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $gender = strtoupper(trim((string) $request->input('gender', '')));

        $rooms = DB::table('hostel')
            ->select('room')
            ->where('hall', $request->hall)
            ->where('block', $request->block)
            ->where('bed_type', self::REMEDIAL_BED_TYPE)
            ->where('status', 0)
            ->where('flag', 0)
            ->when($gender !== '', fn ($q) => $q->where('gender', $gender))
            ->groupBy('room')
            ->orderBy('room', 'asc')
            ->get()
            ->map(fn ($row) => (int) $row->room);

        return response()->json(['success' => true, 'data' => $rooms]);
    }

    /**
     * Available beds within a hall + block + room.
     */
    public function beds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hall' => 'required|string',
            'block' => 'required|string',
            'room' => 'required|integer',
            'gender' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $gender = strtoupper(trim((string) $request->input('gender', '')));

        $beds = DB::table('hostel')
            ->select('id', 'bed', 'amount', 'hostel_amount', 'category')
            ->where('hall', $request->hall)
            ->where('block', $request->block)
            ->where('room', $request->room)
            ->where('bed_type', self::REMEDIAL_BED_TYPE)
            ->where('status', 0)
            ->where('flag', 0)
            ->when($gender !== '', fn ($q) => $q->where('gender', $gender))
            ->orderBy('bed', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $beds]);
    }

    /**
     * Check whether Student Affairs has assigned a hostel PIN to this
     * registration number. The PIN itself is never returned over the API.
     */
    public function pinStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $validated = DB::table('hostel_pin')
            ->where('username', trim((string) $request->registration_number))
            ->exists();

        return response()->json([
            'success' => true,
            'data' => ['validated' => $validated],
        ]);
    }

    /**
     * Assign an unused Student Affairs PIN to an SBRS registration number.
     * This mirrors the UG validation flow while keeping the SBRS account out
     * of the UG users table.
     */
    public function validatePin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string|max:50',
            'pin' => 'required|string|max:64',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $registrationNumber = trim((string) $request->registration_number);
        $pinValue = trim((string) $request->pin);

        try {
            return DB::transaction(function () use ($registrationNumber, $pinValue) {
                $existing = DB::table('hostel_pin')
                    ->where('username', $registrationNumber)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    if (hash_equals((string) ($existing->pin ?? ''), $pinValue)) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Hostel PIN already validated for this student.',
                            'data' => ['validated' => true],
                        ]);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => 'A hostel PIN is already linked to this student. Contact Student Affairs if you need help.',
                    ], 409);
                }

                $pin = DB::table('hostel_pin')
                    ->where('pin', $pinValue)
                    ->lockForUpdate()
                    ->first();

                if (!$pin) {
                    return response()->json(['success' => false, 'message' => 'Invalid hostel PIN.'], 422);
                }

                if (strtolower(trim((string) ($pin->username ?? ''))) !== 'awaiting') {
                    return response()->json(['success' => false, 'message' => 'This hostel PIN has already been used.'], 409);
                }

                DB::table('hostel_pin')->where('id', $pin->id)->update([
                    'username' => $registrationNumber,
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Hostel PIN validated successfully.',
                    'data' => ['validated' => true],
                ]);
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'The hostel PIN could not be validated right now. Please try again.'], 500);
        }
    }

    /**
     * Reserve a bed for a remedial student.
     */
    public function reserve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string|max:50',
            'hall' => 'required|string',
            'block' => 'required|string',
            'room' => 'required|integer',
            'bed' => 'required|integer',
            'gender' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $registrationNumber = trim($request->registration_number);
        $gender = strtoupper(trim($request->gender));

        // Reject empty registration numbers
        if ($registrationNumber === '' || $registrationNumber === 'vacant') {
            return response()->json(['success' => false, 'message' => 'Invalid registration number.'], 422);
        }

        try {
            return DB::transaction(function () use ($request, $registrationNumber, $gender) {
                // A PIN must be assigned before a remedial/SBRS reservation
                // can be created. This check is server-side and cannot be
                // bypassed by calling the API directly.
                $hasPin = DB::table('hostel_pin')
                    ->where('username', $registrationNumber)
                    ->lockForUpdate()
                    ->exists();

                if (!$hasPin) {
                    return response()->json(['success' => false, 'message' => 'Validate your hostel PIN before reserving a bed space.'], 422);
                }

                // Lock both the student's existing allocation and the target
                // bed. This prevents two concurrent requests from assigning
                // the same bed or giving one student multiple allocations.
                $existing = DB::table('hostel')
                    ->where('occupant', $registrationNumber)
                    ->where('bed_type', self::REMEDIAL_BED_TYPE)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You already have a reserved bed: ' . $existing->hall . ' | ' . $existing->block . ' | ' . $existing->room . ' | ' . $existing->bed,
                    ], 409);
                }

                $bed = DB::table('hostel')
                    ->where('hall', $request->hall)
                    ->where('block', $request->block)
                    ->where('room', $request->room)
                    ->where('bed', $request->bed)
                    ->where('bed_type', self::REMEDIAL_BED_TYPE)
                    ->where('status', 0)
                    ->where('flag', 0)
                    ->lockForUpdate()
                    ->first();

                if (!$bed) {
                    return response()->json(['success' => false, 'message' => 'Bed space is no longer available. Please select another.'], 409);
                }

                if (strtoupper($bed->gender) !== $gender) {
                    return response()->json(['success' => false, 'message' => 'Gender mismatch for this bed space.'], 422);
                }

                $updated = DB::table('hostel')
                    ->where('id', $bed->id)
                    ->where('status', 0)
                    ->update([
                        'occupant' => $registrationNumber,
                        'status' => 1,
                        'updated_at' => now(),
                    ]);

                if ($updated !== 1) {
                    return response()->json(['success' => false, 'message' => 'Bed space is no longer available. Please select another.'], 409);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Bed reserved successfully.',
                    'data' => [
                        'hall' => $bed->hall,
                        'block' => $bed->block,
                        'room' => $bed->room,
                        'bed' => $bed->bed,
                        'amount' => $bed->amount,
                        'hostel_amount' => $bed->hostel_amount,
                        'category' => $bed->category,
                        'occupant' => $registrationNumber,
                        'status' => 1,
                    ],
                ]);
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Failed to reserve bed. Please try again.'], 500);
        }
    }

    /**
     * Get the current reservation status for a remedial student.
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $reservation = DB::table('hostel')
            ->select('id', 'hall', 'block', 'room', 'bed', 'occupant', 'amount', 'hostel_amount', 'category', 'hostel_payment', 'payment_method', 'status')
            ->where('occupant', trim($request->registration_number))
            ->where('bed_type', self::REMEDIAL_BED_TYPE)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No reservation found for this student.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'hall' => $reservation->hall,
                'block' => $reservation->block,
                'room' => $reservation->room,
                'bed' => $reservation->bed,
                'occupant' => $reservation->occupant,
                'amount' => $reservation->amount,
                'hostel_amount' => $reservation->hostel_amount,
                'category' => $reservation->category,
                'hostel_payment' => $reservation->hostel_payment,
                'payment_method' => $reservation->payment_method,
                'status' => $reservation->status,
            ],
        ]);
    }

    /**
     * Release a reserved bed (cancellation).
     */
    public function release(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $reservation = DB::table('hostel')
                    ->where('occupant', trim($request->registration_number))
                    ->where('bed_type', self::REMEDIAL_BED_TYPE)
                    ->lockForUpdate()
                    ->first();

                if (!$reservation) {
                    return response()->json(['success' => false, 'message' => 'No reservation found to release.'], 404);
                }

                if ((string) $reservation->hostel_payment === '1') {
                    return response()->json(['success' => false, 'message' => 'A paid reservation cannot be released here. Contact Student Affairs for a refund or correction.'], 409);
                }

                DB::table('hostel')
                    ->where('id', $reservation->id)
                    ->update([
                        'occupant' => '',
                        'status' => 0,
                        'hostel_payment' => '0',
                        'payment_method' => null,
                        'updated_at' => now(),
                    ]);

                return response()->json(['success' => true, 'message' => 'Reservation released successfully.']);
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'The reservation could not be released right now.'], 500);
        }
    }

    /**
     * Confirm that the student has paid for the hostel (called by SBRS after
     * successful Remita payment verification).
     */
    public function confirmPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string|max:50',
            'rrr' => 'required|string|max:64',
            'amount' => 'required|numeric|min:0.01',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $reservation = DB::table('hostel')
                    ->where('occupant', trim($request->registration_number))
                    ->where('bed_type', self::REMEDIAL_BED_TYPE)
                    ->lockForUpdate()
                    ->first();

                if (!$reservation) {
                    return response()->json(['success' => false, 'message' => 'No reservation found for this student.'], 404);
                }

                $expected = round((float) $reservation->amount, 2);
                $received = round((float) $request->amount, 2);
                if (abs($expected - $received) > 0.009) {
                    return response()->json(['success' => false, 'message' => 'The payment amount does not match the hostel fee.'], 422);
                }

                if ((string) $reservation->hostel_payment === '1') {
                    return response()->json([
                        'success' => true,
                        'message' => 'Hostel payment was already confirmed.',
                        'data' => ['hostel_payment' => '1'],
                    ]);
                }

                DB::table('hostel')
                    ->where('id', $reservation->id)
                    ->update([
                        'hostel_payment' => '1',
                        'payment_method' => 'Online',
                        'updated_at' => now(),
                    ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Hostel payment confirmed.',
                    'data' => [
                        'hall' => $reservation->hall,
                        'block' => $reservation->block,
                        'room' => $reservation->room,
                        'bed' => $reservation->bed,
                        'hostel_payment' => '1',
                    ],
                ]);
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'The hostel payment could not be confirmed right now.'], 500);
        }
    }
}
