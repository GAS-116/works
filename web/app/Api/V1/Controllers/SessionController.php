<?php

namespace App\Api\V1\Controllers;

use App\Models\HistoryMeeting;
use App\Models\HistoryUser;
use App\Models\Session;
use App\Models\User;
use App\Services\DataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SessionController extends Controller
{

    /**
     *  Show a list of users currently in the room
     *
     *  @OA\Get(
     *     path="/v1/sumrameet/sess-users",
     *     description="Displaying a list of users",
     *     tags={"Sessions"},
     *
     *     security={{
     *          "default" :{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *          },
     *     }},
     *
     *     x={
     *          "auth-type": "Applecation & Application Use",
     *          "throttling-tier": "Unlimited",
     *          "wso2-appliocation-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *           },
     *     },
     *
     *     @OA\Parameter(
     *          name="room_id",
     *          in="path",
     *          required=true,
     *          description="Room uuid",
     *          example="44444444-a444-q4444-w4444-asd4444444",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Service Contracts list",
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 required={"start_time"},
     *                 @OA\Property(
     *                     property="active",
     *                     type="integer",
     *                     description="Room status",
     *                     example=1,
     *                 ),
     *                 @OA\Property(
     *                     property="start_time",
     *                     type="timestamp",
     *                     description="The time the user appears in this room",
     *                     example="2021-09-18 16:17:29",
     *                 ),
     *                 @OA\Property(
     *                     property="finish_time",
     *                     type="timestamp",
     *                     description="The end time of the user's session in the current room",
     *                     example="2021-09-18 17:17:29",
     *                 ),
     *                 @OA\Property(
     *                     property="time",
     *                     type="integer",
     *                     description="User time",
     *                     example=100,
     *                 ),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     required={"id", "display_name"},
     *                      @OA\Property(
     *                          property="id",
     *                          type="string",
     *                          description="Uuid user",
     *                          example="00000000-1000-1000-1000-000000000000",
     *                      ),
     *
     *                      @OA\Property(
     *                          property="display_name",
     *                          type="string",
     *                          description="User name",
     *                          example="Missouri Schaden",
     *                      ),
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="users_count",
     *                 type="integer",
     *                 description="The number of users in the room during this session",
     *                 example=100,
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="User not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="Room UUID not found"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * )
     *
     *  @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $room_id = $request->get('room_id');
            $users = Session::with('user')->where(['room_id' => $room_id, 'active' => 1])->get();
            $cnt_user = count($users);

            return response()->jsonApi([
                'type' => 'success',
                'title' => "List of users in one room",
                'message' => 'Get referral code info with link',
                'data' => $users->toArray(),
                'users_count' => $cnt_user,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "List of users in one room",
                'message' => "Users in one room not found" . $e->getMessage(),
                'data' => null,
            ], 404);
        }
    }

    /**
     *  Adding a user who entered the room
     *
     * @OA\Post(
     *     path="/v1/sumrameet/sess-enter",
     *     description="Adding a user to a room",
     *     tags={"Sessions"},
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
 *                  required={"room_id"},
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="UUID room",
     *                  example="945d64bc-9901-4fb4-9d39-0a3e7298b3d7"
     *              ),
     *              @OA\Property(
     *                  property="active",
     *                  type="integer",
     *                  description="Room status number",
     *                  example=1
     *              ),
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="User create successful",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="active",
     *                  type="tinyint",
     *                  description="user activity"
     *              ),
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="UUID room"
     *              ),
     *              @OA\Property(
     *                  property="start_time",
     *                  type="string",
     *                  description="the time the user entered the room"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="string",
     *                  description="UUID user"
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Invalid request"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="User not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="UUID room not found"
     *              ),
     *              @OA\Property(
     *                  property="active",
     *                  type="string",
     *                  description="Status room not found"
     *              ),
     *          ),
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        try{
            $create_data = Session::create([
                'user_id' => Auth::user()->getAuthIdentifier(),
                'room_id' => $request->get('room_id'),
                'active' => 1,
                'start_time' => Carbon::now()->toDateTimeString()
            ]);

            return response()->jsonApi([
                'type' => 'success',
                'title'=> 'Adding a user',
                'message' => 'User added successfully',
                'data' => $create_data->toArray()
            ], 200);
        }
        catch (ModelNotFoundException $e){
            return response([
                'type' => 'danger',
                'title' => 'Adding a user',
                'message' => 'User adding error' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     *  Fixes the end date when the user leaves the room
     *
     * @OA\Post(
     *     path="/v1/sumrameet/sess-exit",
     *     description="When the user leaves the room, we fix the time and update the record of this user in the sessions table",
     *     tags={"Sessions"},
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
     *              required={"room_id", "cntParticipants"},
     *              @OA\Property(
     *                  property="cntParticipants",
     *                  type="integer",
     *                  description="Number of participants in the room",
     *                  example=10,
     *              ),
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="UUID room",
     *                  example="945d64bc-9901-4fb4-9d39-0a3e7298b3d7",
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="User create successful",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="user_id",
     *                  type="string",
     *                  description="UUID user",
     *                  example="945d64bc-9901-4fb4-9d39-0a3e7298b123",
     *              ),
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="UUID room",
     *                  example="945d64bc-9901-4fb4-9d39-0a3e7298b3d7",
     *              ),
     *              @OA\Property(
     *                  property="active",
     *                  type="integer",
     *                  description="Room status number",
     *                  example=1,
     *              ),
     *                 @OA\Property(
     *                     property="start_time",
     *                     type="timestamp",
     *                     description="The time the user appears in this room",
     *                     example="2021-09-18 16:17:29",
     *                 ),
     *                 @OA\Property(
     *                     property="finish_time",
     *                     type="timestamp",
     *                     description="The end time of the user's session in the current room",
     *                     example="2021-09-18 17:17:29",
     *                 ),
     *              @OA\Property(
     *                  property="time",
     *                  type="integer",
     *                  description="User time",
     *                  example=100,
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Invalid request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="UUID room",
     *                  example="945d64bc-9901-4fb4-9d39-0a3e7298b3d7",
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="User not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="room_id",
     *                  type="string",
     *                  description="UUID room not found",
     *              ),
     *              @OA\Property(
     *                  property="cntParticipants",
     *                  type="integer",
     *                  description="No parameter: 'Number of participants'",
     *              ),
     *          ),
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function exitRoom (Request $request)
    {
        $this->validate($request, ['cntParticipants' => 'required|integer', 'room_id' => 'required|string',]);

        // let's try to get data about the user, if he is in the room
        try{
            $user_sess_data = Session::where([ 'user_id' => Auth::user()->getAuthIdentifier(), 'room_id' =>
                $request->room_id])
                ->whereNull('finish_time')
                ->first();
        }
        catch (ModelNotFoundException $e){
            return response([
                'type' => 'danger',
                'title' => 'Updating data when the user leaves the room',
                'message' => 'User is not found' . $e->getMessage(),
                'data' => null
            ], 404);
        }

        try{
            $user_sess_data->update([
                'finish_time' => Carbon::now()->toDateTimeString(),
                'time' => DataService::getTimeDifference($user_sess_data->start_time, Carbon::now()->toDateTimeString()),
            ]);

            // we determine whether there are users in the room, and if not, we accrue rewards, and the meeting fix it as complete
            if($request->cntParticipants == 0)
            {
                // add the final time to the room in which the meeting took place
                $room_data = HistoryMeeting::where('room_id', $request->room_id)
                    ->orderBy('start_time', 'DESC')
                    ->first();

                $room_data->updateRoomForFinishMeeting($room_data->start_time);

                /* count the time of all users who met in this room */
                // we get the total time for each user after the meeting
                $sessions = Session::selectRaw('SUM(time) AS user_time')
                    ->addSelect('user_id')
                    ->with('user')
                    ->where('room_id', $request->room_id)
                    ->groupBy('user_id')
                    ->get();


                // we will write down the received data in the history of users
                $cnt = 0;
                foreach ($sessions as $user)
                {
                    $users['users'][$cnt] = HistoryUser::create([
                        'room_id' => $request->room_id,
                        'user_id' => $user->user_id,
                        'time' => $user->user_time,
                    ]);

                    // also, update the user's total time field
                    $total_time = $user->user->total_time + $user->user_time;
                    $total_time = User::where('id', $user->user_id)->update(['total_time' => $total_time]);

                    $cnt++;
                }
            }

            return response()->jsonApi([
                'type' => 'success',
                'title'=> 'Updating data when the user leaves the room',
                'message' => 'Session completed successfully',
                'data' => null
            ], 200);
        }
        catch (ModelNotFoundException $e){
            return response([
                'type' => 'danger',
                'title' => 'Updating data when the user leaves the room',
                'message' => 'Error while updating data' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
