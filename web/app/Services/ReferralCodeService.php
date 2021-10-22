<?php

namespace App\Services;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReferralCodeService
{
    public static function createReferralCode($data, User $user = null)
    {
        if ($user == null) {
            $user = User::find(Auth::user()->getAuthIdentifier());
        }

        // Check if code is set as default, then reset all previous code
        if ($data['is_default']) {
            self::defaultReset($data['application_id'], $user->id);
        }

        // Create new referral code
        $rc = $user->referralCodes()->create([
            'application_id' => $data['application_id'],
            'link' => 'link' . rand(1, 1000),
            'is_default' => $data['is_default'] ?? false,
            'note' => $data['note'] ?? null
        ]);

        $generate_link = (string)Firebase::linkGenerate($rc->code, $data['application_id']);
        $rc->update(['link' => $generate_link]);

        return $rc;
    }

    /**
     * Reset all default codes by user and application
     *
     * @param $application_id
     * @param $user_id
     *
     * @return null
     */
    public static function defaultReset($application_id, $user_id)
    {
        $list = ReferralCode::byApplication($application_id)
            ->byOwner($user_id)
            ->get();
        $list->each->update(['is_default' => false]);

        return null;
    }

    /**
     *  Handler for uid users received from another microservice
     *
     * @param array |$data
     *
     * @return false|object
     */
    public static function addUniqueUser($data)
    {
        self::checkUser($data['user1']);

        $result = self::checkUser($data['user2'], $data['user1']);

        return $result;
    }

    /**
     *  Check the invited user for uniqueness.
     *
     * @param string | $user1 | inviting user
     * @param string | $user2 | invited user
     *
     * @return false | object $output_data
     */
    public static function checkUser($user2, $user1 = null)
    {
        // trying to search for the invited user in the microservice structure
        $user_info = User::find($user2);

        if ($user_info === null) {
            // save the invited unique user
            $output_data = User::create([
                'id' => $user2,
                'referrer_id' => $user1,
            ]);

            if ($user1) {
                // we send data to the membership microservice for information about the tariff plan and reward for the inviting user
                RemoteService::sendData('getDataAboutPlanAndReward', $user1, 'Membership');
            }

//            return $output_data;
        }

        return false;
    }
}
