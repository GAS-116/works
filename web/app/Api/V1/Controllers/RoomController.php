<?php

namespace App\Api\V1\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\DataService;

class RoomController extends Controller
{
    /**
     * View all meetings by owner
     *
     * @OA\Get(
     *     path="/v1/sumrameet/meetings",
     *     description="List of meetings",
     *     tags={"Meetings"},
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
     **     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit meetings of page",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Count meetings of page",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keywords",
     *         @OA\Schema(
     *             type="string"
     *         )
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
     *          description="This user has no meetings"
     *     )
     * )
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $rooms = Room::byOwner()
                ->when(($request->has('search') && !empty($request->get('search'))), function ($q) use ($request) {
                    $search = $request->get('search');

                    return $q->where('name', 'like', "%{$search}%");
                })
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            // Add invite link to this meeting room by user
         //   $room->setAttribute('invite', DataService::getLinkByUser($room));

            if ($rooms->isEmpty()) {
                return response()->jsonApi([
                    'type' => 'warning',
                    'title' => 'Getting a list of meetings',
                    'message' => ($request->has('search') ? 'No meetings found matching your search' : 'This user has no meetings'),
                    'data' => null,
                ], 404);
            }

            return response()->jsonApi(array_merge(
                [
                    'type' => 'success',
                    'title' => 'Getting a list of meetings',
                    'message' => 'List meetings by owner are shown successfully'
                ],
                $rooms->toArray()
            ), 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Getting a list of meetings',
                'message' => "Error showing all meetings by owner",
                'data' => null
            ], 500);
        }
    }

    /**
     *  Create new meeting
     *
     * @OA\Post(
     *     path="/v1/sumrameet/meetings",
     *     description="Create new meeting",
     *     tags={"Meetings"},
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
     *              required={"name"},
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="Title of meeting",
     *                  example="Meeting about global nature",
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  type="boolean",
     *                  description="Status of meeting",
     *                  example="true",
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  description="Password of meeting",
     *                  example="12345",
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Meeting created successful",
     *
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  maximum=100,
     *                  description="User name",
     *                  example="Maxx",
     *              ),
     *
     *              @OA\Property(
     *                  property="status",
     *                  type="boolean",
     *                  description="Status of meeting",
     *                  example=true,
     *              ),
     *
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="UUID of meeting",
     *                  example="946542f8-661c-4753-beb6-1c82c181feab",
     *              ),
     *
     *              @OA\Property(
     *                  property="invite",
     *                  type="string",
     *                  description="Invite code",
     *                  example="hvb-5ef-ncf-hwy",
     *              ),
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Invalid request"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Meeting not found"
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
        $this->validate($request, Room::rules());

        try {
            $owner = User::firstOrCreate([
                'id' => Auth::user()->getAuthIdentifier()
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Operation not success",
                'message' => "Cannot find user: {$e->getMessage()}",
                'data' => null
            ], 404);
        }

        try {
            $room = Room::create([
                'name' => $request->get('name'),
                'status' => $request->boolean('status'),
                'password' => Hash::make($request->get('password')),
                'owner_by' => $owner->id
            ]);

            // Add user to meeting
            $room->roomUsers()->attach($owner, ['invite_code' => $owner->slug . '-' . $room->slug]);

            // Add invite link to this meeting room by user
            $room->setAttribute('invite', DataService::getLinkByUser($room));

            $meeting = $room->toArray();
            unset($meeting['room_users']);
            unset($meeting['slug']);

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Operation was success',
                'message' => 'Meeting created successfully',
                'data' => $meeting
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Operation not success',
                'message' => 'An error occurred while creating the meeting: ' . $e->getMessage(),
                'data' => null
            ], 404);
        }
    }

    /**
     * Get detail info about meeting
     *
     * @OA\Get(
     *     path="/v1/sumrameet/meetings/{id}",
     *     summary="Get detail info about meeting",
     *     description="Get detail info about meeting",
     *     tags={"Meetings"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
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
     *         required=true,
     *         description="Meeting ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Data of meeting"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Meeting not found"
     *     )
     * )
     *
     * @param $id
     *
     * @return \App\Api\V1\Controllers\JsonApiResponse
     */
    public function show($id)
    {
        // Get object
        $meeting = $this->getObject($id);

        if ($meeting instanceof JsonApiResponse) {
            return $meeting;
        }

        // Load linked relations data
        $meeting->load([
            'roomUsers'
        ]);

        // Add invite link to this meeting room by user
        $meeting->setAttribute('invite', DataService::getLinkByUser($meeting));

        // Return response
        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Meeting details',
            'message' => "Meeting details received",
            'data' => $meeting->toArray()
        ], 200);
    }

    /**
     * Update meeting settings.
     *
     * @OA\Put(
     *     path="/v1/sumrameet/meetings/{id}",
     *     description="Update meeting settings",
     *     tags={"Meetings"},
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
     *          }
     *     },
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Meeting ID",
     *          example="9412947b-4245-4fa2-8fa9-26452f59e930",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="Title of meeting",
     *                  example="Meeting about global nature № 13"
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  type="boolean",
     *                  description="Status of meeting",
     *                  example="false"
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
     *                  property="name",
     *                  type="string",
     *                  description="Error meeting name"
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Error meeting status"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Meeting not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  description="Status not found"
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  description="Name not found"
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
     * @return \Sumra\JsonApi\JsonApiResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id): \Sumra\JsonApi\JsonApiResponse
    {
        // Validate input data
        $this->validate($request, Room::rules());

        // Get object
        $room = $this->getObject($id);

        if ($room instanceof JsonApiResponse) {
            return $room;
        }

        try {
            $room->fill($request->all());
            $room->save();

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Operation was success',
                'message' => 'Information of meeting was successfully updated',
                'data' => $room->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Operation not success',
                'message' => "Cannot update meeting with ID#{$id}: " . $e->getMessage(),
                'data' => null
            ], 404);
        }
    }

    /**
     *  Remove the meeting
     *
     * @OA\Delete(
     *     path="/v1/sumrameet/meetings/{id}",
     *     description="Delete meeting",
     *     tags={"Meetings"},
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
     *         description="Meeting ID",
     *         required=true,
     *         example="9412947b-4245-4fa2-8fa9-26452f59e930",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="The meeting was successfuly deleted"
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Meeting for delete not found",
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
     *              )
     *          )
     *     )
     * )
     *
     * @param $id
     *
     * @return \Sumra\JsonApi\JsonApiResponse
     */
    public function destroy($id): \Sumra\JsonApi\JsonApiResponse
    {
        // Get object
        $room = $this->getObject($id);

        if ($room instanceof JsonApiResponse) {
            return $room;
        }

        // Trying to soft delete a meeting
        try {
            $room->delete();

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Operation was success',
                'message' => 'The meeting has been successfully deleted',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Operation not success',
                'message' => "The ro
                om with #{$id} cannot was deleted",
                'data' => null
            ], 404);
        }
    }

    public function getLinkForToShare(Request $request)
    {
        $room_id = $request->get('room_id');

        if (!$room_id) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Operation not success",
                'message' => "Error when transferring meeting id",
                'data' => null
            ], 404);
        }

        try {
            $owner = User::findOrFail(Auth::user()->getAuthIdentifier());
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Operation not success",
                'message' => "Cannot find user: {$e->getMessage()}",
                'data' => null
            ], 404);
        }

        try {
            $rooms = Room::with('roomUsers')->byOwner()
                ->where('id', $room_id)
                ->first();

            if ($rooms->isEmpty()) {
                return response()->jsonApi([
                    'type' => 'danger',
                    'title' => "Operation not success",
                    'message' => 'This user has no meetings.',
                    'data' => null,
                ], 404);
            }

            $link = DataService::getLinkByUser($rooms);
//          $aa = $rooms->roomUsers()->sync('invite_link', $link);

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Operation was success',
                'message' => 'List meetings by owner are shown successfully',
                'data' => $link,
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Operation not success",
                'message' => "Error showing all meetings by owner",
                'data' => null
            ], 404);
        }
    }

    /**
     * Meeting's not found
     *
     * @param $id
     *
     * @return mixed
     */
    private function getObject($id)
    {
        try {
            return Room::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Operation not success",
                'message' => "Meeting with #{$id} not found: {$e->getMessage()}",
                'data' => null
            ], 404);
        }
    }
}
