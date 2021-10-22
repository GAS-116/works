<?php

namespace App\Api\V1\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Services\RemoteService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\DataService;

class ProfileController extends Controller
{
    /**
     *  Get user profile data
     *
     * @OA\Get(
     *     path="/v1/sumrameet/profile",
     *     description="Get user profile data",
     *     tags={"Profile"},
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
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     * )
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        $user_id = Auth::user()->getAuthIdentifier();

        try {
            $user = User::findOrFail($user_id);

            $user->load('userRooms');

            // Read user avatar
            $user->setAttribute('avatar', $this->getImagesFromRemote($user_id));

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Operation was success",
                'message' => 'User profile page successfully received',
                'data' => $user->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Operation not success",
                'message' => "Data #{$user_id} not found " . $e->getMessage(),
                'data' => null
            ], 404);
        }
    }

    /**
     *  Store user
     *
     * @OA\Post(
     *     path="/v1/sumrameet/profile",
     *     description="Create user",
     *     tags={"Profile"},
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
     *                  property="display_name",
     *                  type="string",
     *                  maximum=50,
     *                  description="Name of user",
     *                  example="Igor"
     *              ),
     *              @OA\Property(
     *                  property="email",
     *                  type="string",
     *                  maximum=100,
     *                  example="gug@gug.gug"
     *              ),
     *              @OA\Property(
     *                  property="phone",
     *                  type="string",
     *                  maximum=50,
     *                  example="+12404225709"
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="User create successful"
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
     *                  property="display_name",
     *                  type="string",
     *                  description="Name not found"
     *              ),
     *              @OA\Property(
     *                  property="phone",
     *                  type="string",
     *                  description="Phone not found"
     *              ),
     *              @OA\Property(
     *                  property="email",
     *                  type="string",
     *                  description="Email not found"
     *              )
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
        $this->validate($request, User::rules());

        $user = User::find(Auth::user()->getAuthIdentifier());

        try {
            if(!$user){
                $user = new User();
                $user->id = Auth::user()->getAuthIdentifier();
            }

            $user->fill([
                'display_name' => $request->get('display_name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'total_time' =>  !$user ? 0 : $user->total_time + $request->get('total_time'),
            ]);
            $user->save();

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Profile set / update',
                'message' => 'Information of profile user was successfully updated',
                'data' => $user->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Profile set / update',
                'message' => 'Cannot update profile ',
                'data' => null
            ], 404);
        }
    }

    /**
     * Invite new user to refer
     *
     * Sending data to a remote microservice
     *
     * @OA\Post(
     *     path="/v1/sumrameet/profile/invite",
     *     description="Invite new user to refer",
     *     tags={"Profile"},
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
     *          }
     *     },
     *
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="invite",
     *                  type="string",
     *                  maximum="15",
     *                  description="Invite code",
     *                  example="qdf-thu-ral-rft"
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="User invited successful",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  maximum="36",
     *                  description="UUID room",
     *                  example="94694dbb-a77e-4589-9ef4-8a1f04f2681c"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  maximum="100",
     *                  description="Room name",
     *                  example="Doom"
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  type="integer",
     *                  description="Room status",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="invite",
     *                  type="string",
     *                  description="Invite code",
     *                  example="nh8-fym-gpl-t6i"
     *              ),
     *              @OA\Property(
     *                  property="room_users",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      type="string",
     *                      maximum="36",
     *                      description="UUID user",
     *                      example="00000000-1000-1000-1000-000000000000"
     *                  ),
     *                  @OA\Property(
     *                      property="display_name",
     *                      type="string",
     *                      maximum="50",
     *                      description="Username",
     *                      example="Vasya"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      maximum="50",
     *                      description="Phone user",
     *                      example="+14350151193"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      maximum="100",
     *                      description="Email user",
     *                      example="brekke.jeffery@example.com"
     *                  ),
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Invalid request"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="User not found"
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request : invited user link = user slug + room slug
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function invite(Request $request)
    {
        $this->validate($request,
            [ 'invite' => 'required|string|regex:/^[\w]{3}-[\w]{3}-[\w]{3}-[\w]{3}$/i' ],
            [ 'invite' => 'Error in request' ],
        );

        try {
            $data_slug_array = DataService::convertLinkToArray($request->get('invite'));

            $invited_user_uuid = User::select(['id'])->where('slug', $data_slug_array['user_slug'])->first();

            if($invited_user_uuid == null){
                return response()->jsonApi([
                    'type' => 'danger',
                    'title' => "Checking for the presence of a user by slug",
                    'message' => "User not found",
                    'data' => null
                ], 404);
            }

            $room = Room::where('slug', $data_slug_array['room_slug'])->first();

            if ($invited_user_uuid) {
                RemoteService::sendDataToReferrals(Auth::user()->getAuthIdentifier(), $invited_user_uuid->id);
            }

            // Add user to meeting
//            $room->load('roomUsers');

            // Add invite link to this meeting room by user
            $user = User::find(Auth::user()->getAuthIdentifier());
            $room->setAttribute('invite', $user->slug . '-' . $room->slug);

            unset($room->slug);
            unset($room->roomUsers[0]->slug);
            unset($room->roomUsers[0]->total_time);

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Invite user to meeting',
                'message' => 'Information about the invited user was sent successfully',
                'data' => $room->toArray(),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Operation not success",
                'message' => "Cannot find link: {$e->getMessage()}",
                'data' => null
            ], 404);
        }
    }

    /**
     * @param $id
     *
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getImagesFromRemote($id)
    {
        $images = null;

        $client = new Client(['base_uri' => config('settings.api.files.host')]);

        try {
            $response = $client->request('GET', config('settings.api.files.version') . "/files?entity=avatar&entity_id={$id}", [
                'headers' => [
                    'user-id' => Auth::user()->getAuthIdentifier(),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $response = json_decode($response->getBody(), JSON_OBJECT_AS_ARRAY);

                if (isset($response['attributes']['path'])) {
                    $images = $response['attributes']['path'];
                }
            }
        } catch (Exception $e) {
        }

        return $images;
    }
}
