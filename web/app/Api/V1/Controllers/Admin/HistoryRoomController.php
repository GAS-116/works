<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\HistoryMeeting;
use Carbon\Carbon;

class HistoryRoomController extends Controller
{

    /**
     *  Save meeting history
     *
     * @OA\Post(
     *     path="/v1/sumrameet/admin/sessions",
     *     description="Saving the history of user meetings",
     *     tags={"Admin History-meeting"},
     *
     *     security={{
     *          "default": {
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *          }
     *     }},
     *
     *     x={
     *          "auth-type": "Application & Application User",
     *          "throttling-tier": "Unlimited",
     *          "wso2-application-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *          }
     *     },
     *
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={"room_id", "start_time"},
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="Room UUID",
     *                  example="9412bac8-ea4b-4e59-a76f-803eac029074",
     *              ),
     *              @OA\Property(
     *                  property="start_time",
     *                  type="datetime",
     *                  description="Time fixed at the beginning of the meeting",
     *                  example="2021-09-24 11:26:06",
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="History create successful",
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="UUID",
     *                  example="94650762-c47b-420a-b42f-3d63200ffbf6",
     *              ),
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="Room UUID",
     *                  example="94650762-c47b-420a-b42f-3d63200ff777",
     *              ),
     *              @OA\Property(
     *                  property="start_time",
     *                  type="datetime",
     *                  description="Time at the start of the meeting",
     *                  example="2021-09-24 10:09:06",
     *              ),
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Invalid request",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="History not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="room_id",
     *                 type="string",
     *                 description="Room UUID not found",
     *             ),
     *             @OA\Property(
     *                 property="start_time",
     *                 type="string",
     *                 description="Start time not found",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error",
     *     ),
     * ),
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Validate input data
        $this->validate($request, HistoryMeeting::rules());

        try {
            $history = HistoryMeeting::create([
                'room_id' => $request->get('room_id'),
                'start_time' => $request->start_time,
            ]);

            // Add start time
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Adding new appointment details',
                'message' => 'Meeting added successfully',
                'data' => $history->toArray()
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Adding new appointment details',
                'message' => 'History creation was unsuccessful: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
