<?php

namespace App\Services;

use PubSub;

class RemoteService
{
    /**
     * Get information about the referral code from the remote microservice of the referral program
     *
     * @param $uuid
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getReferralCode($uuid)
    {
        try {
            $body = [];

            $data = "?user_id={$uuid}";

            $client = new \GuzzleHttp\Client(['base_uri' => env('REFERRALS_MICROSERVICE_HOST')]);

            $url = env('API_V', '/v1') . '/referrals/referral-codes/user' . $data;

            $response = $client->request('GET', $url);

            if ($response->getStatusCode() == 200) {
                $body = json_decode((string)$response->getBody(), true);
                $result_data = json_decode($body['data']['row'], true);

                return $result_data;
            } else {
                return response()->jsonApi([
                    'type' => 'danger',
                    'title' => 'Operation not success',
                    'message' => 'An error occurred while fetching data from a remote microservice.',
                    'data' => null
                ]);
            }
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Operation not success',
                'message' => 'Error while retrieving user information',
                'data' => null
            ]);
        }
    }

    public static function sendDataToReferrals($user1, $user2)
    {
        $users = [
            'user1' => $user1,
            'user2' => $user2
        ];

        PubSub::transaction(function () {
        })->publish('ReferralUserInfo', $users, 'ReferralsMS');
    }
}
