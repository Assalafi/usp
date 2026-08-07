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

        DB::beginTransaction();
        try {
            // Check for existing reservation by this student
            $existing = DB::table('hostel')
                ->where('occupant', $registrationNumber)
                ->where('bed_type', self::REMEDIAL_BED_TYPE)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a reserved bed: ' . $existing->hall . ' | ' . $existing->block . ' | ' . $existing->room . ' | ' . $existing->bed,
                ], 409);
            }

            // Locate the target bed (remedial, free, matching gender)
            $bed = DB::table('hostel')
                ->where('hall', $request->hall)
                ->where('block', $request->block)
                ->where('room', $request->room)
                ->where('bed', $request->bed)
                ->where('bed_type', self::REMEDIAL_BED_TYPE)
                ->where('status', 0)
                ->where('flag', 0)
                ->first();

            if (!$bed) {
                return response()->json(['success' => false, 'message' => 'Bed space is no longer available. Please select another.'], 409);
            }

            if (strtoupper($bed->gender) !== $gender) {
                return response()->json(['success' => false, 'message' => 'Gender mismatch for this bed space.'], 422);
            }

            // Occupy the bed
            DB::table('hostel')
                ->where('id', $bed->id)
                ->update([
                    'occupant' => $registrationNumber,
                    'status' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            DB::commit();

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
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to reserve bed: ' . $e->getMessage()], 500);
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

        $reservation = DB::table('hostel')
            ->where('occupant', trim($request->registration_number))
            ->where('bed_type', self::REMEDIAL_BED_TYPE)
            ->first();

        if (!$reservation) {
            return response()->json(['success' => false, 'message' => 'No reservation found to release.'], 404);
        }

        DB::table('hostel')
            ->where('id', $reservation->id)
            ->update([
                'occupant' => '',
                'status' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return response()->json(['success' => true, 'message' => 'Reservation released successfully.']);
    }
}
