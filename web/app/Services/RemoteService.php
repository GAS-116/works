<?php

namespace App\Services;

use App\Models\Total;
use App\Models\Transaction;
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

    /**
     *  We receive data from a remote microservice and write it to the leaderboard
     *
     * @param $data
     *
     * @return bool
     */
    public static function accrualRemuneration($data)
    {
        if ($data !== null) {
            // if the user data came from the membership microservice, we try to find the user in the leaderboard
            $data_total = Total::where('user_id', $data['id'])
                ->first();

            // if there is something in the leaderboard, update, if not, create a record
            if ($data_total == null) {
                $data_total = Total::create([
                    'user_id' => $data['id'],
                    'amount' => 1,
                    'reward' => $data['reward'],
                ]);
            } else {
                $data_total->update([
                    'amount' => $data_total->amount + 1,
                    'reward' => $data_total->reward + $data['reward'],
                ]);
            }

            // in any case, we will enter the data about the incoming data in the transaction history
            $transaction = Transaction::create([
                'user_id' => $data['id'],
                'user_plan' => $data['plan'],
                'reward' => $data['reward'],
                'currency' => '$',
                'operation_name' => 'invitation reward',
            ]);

            return true;
        }

        return false;
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
