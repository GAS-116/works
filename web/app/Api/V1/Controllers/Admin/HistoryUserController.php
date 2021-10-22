<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\HistoryUser;
use Illuminate\Support\Facades\Auth;

class HistoryUserController extends Controller
{
    /**
     *  Save user data
     *
     * @OA\Post(
     *     path="/v1/sumrameet/admin/users",
     *     description="Start session / Create history",
     *     tags={"Admin History-user"},
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
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="Room ID",
     *                  example="9412bac8-ea4b-4e59-a76f-803eac029074"
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="History create successful"
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="History not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 type="string",
     *                 description="ID not found"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Error message"
     *             )
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Validate input data
        $this->validate($request, HistoryUser::rules());

        try {
            $history = HistoryUser::create([
                'user_id' => Auth::user()->getAuthIdentifier(),
                'room_id' => $request->get('room_id'),
                'start_time' => Carbon::now()
            ]);

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Operation was success',
                'message' => 'Create history was successful',
                'data' => $history->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Operation not success',
                'message' => 'History creation was unsuccessful: ' . $e->getMessage(),
                'data' => null
            ], 404);
        }
    }

    /**
     * Update user
     *
     * @OA\Put(
     *     path="/v1/sumrameet/admin/users/{id}",
     *     description="Stop session / Update history",
     *     tags={"Admin History-user"},
     *
     *     security={{
     *          "default":{
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
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Session ID",
     *          example="9412bac8-ea4b-4e59-a76f-803eac029074",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="room_id",
     *                  type="integer",
     *                  description="Room ID",
     *                  example="9412bac8-ea4b-4e59-a76f-803eac029074"
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *          response="500",
     *          description="Unknown error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="Error id"
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="History not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="ID not found"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              )
     *          )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, HistoryUser::rules());

        try {
            // Get session object
            $session = HistoryUser::findOrFail($id);

            // Update finish time
            $session->update([
                'finish_time' => Carbon::now()->toDateTimeString()
            ]);

            // Calculate time of session
            $startTime = Carbon::parse($session->start_time);
            $finishTime = Carbon::parse($session->finish_time);
            $totalDuration = $finishTime->diffInSeconds($startTime);

            // Send data to a remote rewards microservice
            $data = [
                'user_id' => Auth::user()->getAuthIdentifier(),
                'user_time' => $totalDuration,
                'application_id' => 'app.sumra.meet'
            ];

            \PubSub::publish('sendParamForStatistic', $data, 'rewards');

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Operation was success',
                'message' => 'Information about session was successfully updated',
                'data' => $session->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Operation not success',
                'message' => "History with ID# {$id} not found",
                'data' => null
            ], 404);
        }
    }
}
