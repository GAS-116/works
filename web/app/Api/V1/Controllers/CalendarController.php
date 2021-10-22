<?php

namespace App\Api\V1\Controllers;

use Acaronlex\LaravelCalendar\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Calendar;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CalendarController extends Controller
{
    /**
     *  Show a list of scheduled appointments
     *
     * @OA\Get(
     *     path="/v1/sumrameet/calendars",
     *     description="Show a list of scheduled appointments sorted by current user",
     *     tags={"Calendar"},
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
     *      @OA\Response(
     *          response="200",
     *          description="The list of calendar showing successful",
     *          @OA\JsonContent(
     *              type="object",
     *              required={"name", "message"},
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="calendar UUID"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="string",
     *                  description="user UUId"
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  description="Name of the meeting"
     *              ),
     *              @OA\Property(
     *                  property="participants",
     *                  type="json",
     *                  description="List of participants"
     *              ),
     *              @OA\Property(
     *                  property="start_date",
     *                  type="date",
     *                  description="Scheduled start date of the meeting"
     *              ),
     *              @OA\Property(
     *                  property="end_date",
     *                  type="date",
     *                  description="End date of scheduled meeting"
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Calendar not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * ),
     *
     * @return \Sumra\JsonApi\JsonApiResponse
     */
    public function index()
    {
        $user_id = Auth::user()->getAuthIdentifier();

        try {
            $calendars_data = Calendar::whereJsonContains('participants', $user_id)->get();

            // hide unnecessary parameters
            foreach ($calendars_data as $k => $item)
            {
                unset($calendars_data[$k]->note);
                unset($calendars_data[$k]->password);
                unset($calendars_data[$k]->is_lobby);
                unset($calendars_data[$k]->live_stream);
            }

            return response()->jsonApi([
                'type' => 'success',
                'title' => "A list of scheduled appointments",
                'message' => 'The list of scheduled appointments is shown successfully',
                'data' => $calendars_data->toArray()
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "A list of scheduled appointments",
                'message' => "Error displaying the list #{$user_id} " . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Show one calendar information
     *
     * @OA\Get(
     *     path="/v1/sumrameet/calendars/{id}",
     *     description="Show calendar data",
     *     tags={"Calendar"},
     *
     *     security={{
     *          "default":{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *           }
     *     }},
     *
     *     x={
     *          "auth-type": "Application & Application User",
     *          "throttling-tier": "Unlimited",
     *          "wso2-application-security": {
     *              "security-types": {"oauth2"},
     *              "optimal": "false"
     *          }
     *     },
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID calendar data",
     *          example=1,
     *          @OA\Schema (
     *              type="integer"
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Calendar not found",
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
     *              ),
     *          ),
     *     ),
     * )
     *
     * @param string $id
     *
     * @return \Sumra\JsonApi\JsonApiResponse
     */
    public function show(string $id): \Sumra\JsonApi\JsonApiResponse
    {
        try {
            $data_calendar = \App\Models\Calendar::find($id);

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Operation was success",
                'message' => 'Get calendar info',
                'data' => $data_calendar,
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Operation not success",
                'message' => "Calendar #{$id} not found",
                'data' => null
            ], 404);
        }
    }

    /**
     *  Create calendar
     *
     * @OA\Post(
     *     path="/v1/sumrameet/calendars",
     *     description="Create calendar",
     *     tags={"Calendar"},
     *
     *     security={{
     *          "default": {
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *          },
     *     }},
     *
     *     x={
     *          "auth-type": "Application & Application User",
     *          "throttling-tier": "Unlimited",
     *          "wso2-application-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *          },
     *     },
     *
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={"title", "participants", "is_lobby", "start_date", "user_id"},
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  description="Name of calendar",
     *                  example="Valentine's Day",
     *              ),
     *              @OA\Property(
     *                  property="note",
     *                  type="text",
     *                  description="Notation of calendar",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="participants",
     *                  type="text",
     *                  description="List of attendees for a scheduled meeting",
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  description="Password to enter the room the scheduled meeting will take place",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="is_lobby",
     *                  type="tyninteger",
     *                  description="",
     *                  example="0"
     *              ),
     *              @OA\Property(
     *                  property="live_stream",
     *                  type="text",
     *                  description="Link with youtube",
     *              ),
     *              @OA\Property(
     *                  property="start_date",
     *                  type="date",
     *                  description="Start day of calendar",
     *                  example="2015-02-14"
     *              ),
     *              @OA\Property(
     *                  property="end_date",
     *                  type="date",
     *                  description="End date of calendar",
     *                  example="2015-02-14"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="char",
     *                  description="Uuid user",
     *                  example="44444444-a444-q4444-w4444-asd4444444"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Calendar create successful",
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Invalid request for calendar"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Calendar not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="ID not found"
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  description="Title not found"
     *              ),
     *              @OA\Property(
     *                  property="start_date",
     *                  type="string",
     *                  description="Start date not found"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="string",
     *                  description="User uuid not found"
     *              ),
     *              @OA\Property(
     *                  property="participants",
     *                  type="string",
     *                  description="Participants not found"
     *              ),
     *              @OA\Property(
     *                  property="is_lobby",
     *                  type="string",
     *                  description="Lobby not found"
     *              ),
     *          ),
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Sumra\JsonApi\JsonApiResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): \Sumra\JsonApi\JsonApiResponse
    {
        try {
            $this->validate($request, Calendar::rules());
        } catch (\Illuminate\Validation\ValidationException $e ) {
            return response()->jsonApi([
                'message' => $e->errors(),
            ], 422);
        }

        try {
            $create_data = new Calendar();
            $create_data->title = $request->get('title');
            $create_data->note = $request->get('note');
            $create_data->password = $request->get('password');
            $create_data->participants = $request->get('participants');
            $create_data->is_lobby = $request->get('is_lobby');
            $create_data->live_stream = $request->get('live_stream');
            $create_data->start_date = $request->get('start_date');
            $create_data->end_date = $request->get('end_date');
            $create_data->user_id = Auth::user()->getAuthIdentifier();

            $user = User::find(Auth::user()->getAuthIdentifier());
            $create_data->user()->associate($user);
            $create_data->save();

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Saving calendar data',
                'message' => 'Create calendar was successful',
                'data' => $create_data
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Saving calendar data',
                'message' => 'The operation for calendar was unsuccessful. ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     *  Update calendar
     *
     * @OA\Put(
     *     path="/v1/sumrameet/calendars/{id}",
     *     description="Update calendar",
     *     tags={"Calendar"},
     *
     *     security={{
     *          "default":{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *          },
     *     }},
     *
     *     x={
     *          "auth-type": "Application & Application User",
     *          "throttling-tier": "Unlimited",
     *          "wso2-application-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *          },
     *     },
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Calendar ID",
     *          example=1,
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *     ),
     *
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={"title", "participants", "is_lobby", "start_date", "user_id"},
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  description="Name of calendar",
     *              ),
     *              @OA\Property(
     *                  property="note",
     *                  type="text",
     *                  description="Notation of calendar",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="participants",
     *                  type="text",
     *                  description="List of attendees for a scheduled meeting",
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  description="Password to enter the room the scheduled meeting will take place",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="is_lobby",
     *                  type="tyninteger",
     *                  description="",
     *                  example="0"
     *              ),
     *              @OA\Property(
     *                  property="live_stream",
     *                  type="text",
     *                  description="Link with youtube",
     *              ),
     *              @OA\Property(
     *                  property="start_date",
     *                  type="date",
     *                  description="Start day of calendar",
     *                  example="2015-02-14"
     *              ),
     *              @OA\Property(
     *                  property="end_date",
     *                  type="date",
     *                  description="End date of calendar",
     *                  example="2015-02-14"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="char",
     *                  description="Uuid user",
     *                  example="44444444-a444-q4444-w4444-asd4444444"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="note",
     *                  type="text",
     *              ),
     *              @OA\Property(
     *                  property="participants",
     *                  type="text",
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="is_lobby",
     *                  type="tyninteger",
     *              ),
     *              @OA\Property(
     *                  property="live_stream",
     *                  type="text",
     *              ),
     *              @OA\Property(
     *                  property="start_date",
     *                  type="date",
     *              ),
     *              @OA\Property(
     *                  property="end_date",
     *                  type="date",
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="char",
     *              ),
     *     ),
     *     @OA\Response(
     *          response="500",
     *          description="Unknown error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  description="Titile error",
     *              ),
     *              @OA\Property(
     *                  property="participants",
     *                  type="string",
     *                  description="Participants error",
     *              ),
     *              @OA\Property(
     *                  property="is_lobby",
     *                  type="string",
     *                  description="Lobby error",
     *              ),
     *              @OA\Property(
     *                  property="start_date",
     *                  type="string",
     *                  description="Start date error",
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="string",
     *                  description="Uuid user error",
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Calendar not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="ID not found"
     *              ),
     *          ),
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @return \Sumra\JsonApi\JsonApiResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, string $id): \Sumra\JsonApi\JsonApiResponse
    {
//        $this->validate($request, Calendar::rules());
        try {
            $this->validate($request, Calendar::rules());
        } catch (\Illuminate\Validation\ValidationException $e ) {
            return response()->jsonApi([
                'message' => $e->errors(),
            ], 422);
        }

        try {
            $update_data = Calendar::where('id', $id)->update([
                'title' => $request->get('title'),
                'note' => $request->get('note'),
                'password' => $request->get('password'),
                'participants' => $request->get('participants'),
                'is_lobby' => $request->get('is_lobby'),
                'live_stream' => $request->get('live_stream'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
                'user_id' => Auth::user()->getAuthIdentifier(),
            ]);

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Updating scheduled appointments',
                'message' => "The calendar was successfully updated",
                'data' => $update_data
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Updating scheduled appointments',
                'message' => "A calendar #{$id} for update not found" . $e->getMessage(),
                'data' => null
            ], 404);
        }
    }

    /**
     *  Remove the calendar
     *
     * @OA\Delete(
     *     path="/v1/sumrameet/calendars/{id}",
     *     description="Delete calendar",
     *     tags={"Calendar"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         },
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Delete calendar by ID",
     *         required=true,
     *         example="",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="The calendar was successfuly deleted"
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Calendar for delete not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="ID not found"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              ),
     *          ),
     *     ),
     * )
     *
     * @param string                   $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Sumra\JsonApi\JsonApiResponse
     */
    public function destroy(string $id, Request $request): \Sumra\JsonApi\JsonApiResponse
    {
        try {
            Calendar::where('id', $id)->delete();

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Operation was success',
                'message' => 'The calendar has been successfully deleted',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Operation not success',
                'message' => "A calendar #{$id} for deleted not found",
                'data' => null
            ], 404);
        }
    }
}
